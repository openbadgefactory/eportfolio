/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage TODO
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'local'], function (_$, local) {

    var _module_initialized = false;

    var add_observers = function () {
        add_toggle_observers();
        add_comment_save_observers();
        add_comment_delete_observers();
    };

    var add_comment_delete_observers = function () {
        _$('#column-container').on('click', '.field-feedback-comments .delete-comment', function (evt) {
            evt.preventDefault();

            if (window.confirm(get_string('reallydeletethiscomment'))) {
                var comment = _$(this).parents('.field-comment');
                var commentid = comment.data('id');
                var viewid = _$('#viewid').val();
                var data = {commentid: commentid, viewid: viewid};
                var field = _$(this).parents('.epsp-field');

                sendjsonrequest(window.config.wwwroot + 'artefact/epsp/deletecomment.json.php',
                        data, 'post', function (resp) {
                            comment.hide('fast', function () {
                                _$(this).remove();

                                // No comments, show the message.
                                if (field.find('.field-comment').length === 0) {
                                    field.find('.comments').hide();
                                    field.find('.no-comments').show('fast');
                                }
                            });
                        });
            }
        });
    };

    var add_comment_save_observers = function () {
        _$('#column-container').on('click', '.save-field-comment', function () {
            tinyMCE.triggerSave();

            var parent = _$(this).parents('.field-feedback');
            var textareaid = parent.find('textarea').attr('id');
            var instance = tinyMCE.get(textareaid);

            if (instance.getBody().textContent.trim() === '') {
                alert(get_string('messageemptynofiles'));
                return;
            }

            var field = _$(this).parents('.epsp-field');
            var comment = _$(this).parents('.field-feedback').find('textarea').val();
            var fieldid = field.data('fieldid');
            var viewid = _$('#viewid').val();
            var data = {comment: comment, fieldid: fieldid, viewid: viewid};

            sendjsonrequest(window.config.wwwroot + 'artefact/epsp/comment.json.php',
                    data, 'post', function (resp) {
                        instance.setContent('');

                        var new_comment = _$(resp.comment).hide();
                        var comment_block = field.find('.comments').show();

                        comment_block.scrollTop(0);
                        comment_block.prepend(new_comment);

                        new_comment.show('slow');
                        field.find('.no-comments').hide();
                    });
        });
    };

    var add_toggle_observers = function () {
        // Toggle collapsible ePSP-fields.
        _$('#column-container').on('click', '.epsp-field-titlearea .toggle-subfields', function () {
            var field = _$(this).parents('.epsp-field');
            toggle_field(field);
        });
    };

    var toggle_field = function (field) {
        var commentid = arguments[1] || false;
        var content = field.find('.epsp-field-contentarea');
        var toggler = field.find('.epsp-field-titlearea .toggle-subfields');

        // Opened the first time, load comments from the server (if can see
        // comments).
        if (!field.data('comments-loaded') && field.find('.field-feedback').length > 0) {
            load_comments(field, commentid);
            field.data('comments-loaded', 1);
        }

        if (content.is(':hidden')) {
            toggler.addClass('open');

            // Add TinyMCE-editor to textarea
            var textareas = field.find('.field-feedback textarea');

            if (textareas.length > 0) {
                var textarea = textareas.first();

                if (!textarea.hasClass('tinymced')) {
                    textarea.addClass('tinymced');
                    local.identify(textarea);
                    local.add_tinymce('#' + _$(textarea).attr('id'), {
                        images: false,
                        advanced: false
                    });
                }
            }
        }
        else {
            toggler.removeClass('open');
        }

        content.toggle('fast');
    };

    var load_comments = function (field) {
        var fieldid = field.data('fieldid');
        var data = {fieldid: fieldid};
        var commentid = arguments[1] || false;

        sendjsonrequest(window.config.wwwroot + 'artefact/epsp/comments.json.php',
                data, 'get', function (resp) {
                    field.find('.loading-comments').hide('fast');

                    if (resp.comments) {
                        render_comments(field, resp.comments, commentid);
                    }
                    else {
                        field.find('.no-comments').show('fast');
                    }
                });
    };

    var render_comments = function (field, comments) {
        var commentid = arguments[2] || false;
        var commentblock = field.find('.comments');

        commentblock.html(comments).show('fast', function () {
            if (commentid) {
                var commentelement = commentblock.find('.field-comment[data-id="' + commentid + '"]');

                if (commentelement.length > 0) {
                    _$(this).scrollTop(commentelement.position().top);
                    commentelement.find('.detail').addClass('highlight');
                }
            }
        });
    };

    var preopen_selected_field = function () {
        var artefactid = get_query_parameter('artefact');
        var commentid = get_query_parameter('showcomment');

        if (artefactid !== false) {
            var field = _$('.epsp-field[data-fieldid="' + artefactid + '"]').first();

            if (field) {
                toggle_field(field, commentid);
                _$('html, body').prop('scrollTop', field.offset().top);
            }
        }
    };

    var get_query_parameter = function (name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
        var results = regex.exec(window.location.search);

        return results === null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
    };

    var update_grid = function () {
        _$('.columns3 .epsp-field .bs-grid .row, .columns2 .epsp-field .bs-grid .row').each(function () {
            _$(this).removeClass('row');
            _$(this).children().each(function () {
                var el = _$(this);
                var classes = el.attr('class').split(/\s+/);

                _$.each(classes, function () {
                    var str = this.toString();
                    if (str.indexOf('col-') === 0) {
                        el.removeClass(str);
                    }
                });
            });
        });
    };

    return {
        init: function () {
            // HACK: I guess this is one way to make sure the initialization
            // is done only once.
            if (!_module_initialized) {
                add_observers();
                preopen_selected_field();
                update_grid();

                _module_initialized = true;
            }

        }
    };
});