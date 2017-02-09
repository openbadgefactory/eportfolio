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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'jquery.searchbox'], function (jq, gridder) {
    var _$ = jq;
    var _gridder = gridder;
    var _timer = null;
    var _opts = {};

    var init_observers = function () {
        // Create new -button
        _$('.gridder-new').click(function () {
            document.location.href = window.config.wwwroot + 'artefact/epsp/edit.php';
        });

        _$('html').on('click', '.copy-template', function (evt) {
            evt.preventDefault();
            var id = _$(evt.target).parents('ul').data('itemid');
            copy_template(id);
        });

        _$('html').on('click', '.delete-template', function (evt) {
            evt.preventDefault();
            delete_template(_$(evt.target).parents('ul').data('itemid'));
        });

        // Order by -buttons.
        _$('.sort-by input[name="sortpagesby"]').on('change', apply_filter);

        _$('.view-shared button').on('click', function () {
            _gridder.set_page(0);
            tag_selection_changed(_$(this), _do_search);
        });

        _$('#author-institution').change(apply_filter);

        init_author_search_field();
        init_search_field();
    };

    var init_author_search_field = function () {
        var field = _$('#author-search');

        field.keyup(function() {
            var val = _$.trim(field.val());
            var previous = _$(this).data('previousvalue');

            if (!previous || val !== previous) {
                if (_timer) {
                    clearTimeout(_timer);
                }

                _timer = setTimeout(apply_filter, 250);
                _$(this).data('previousvalue', val);
            }
        });
    };

    var apply_filter = function () {
        _gridder.set_page(0);
        _do_search();
    };

    var tag_selection_changed = function (btn, callback) {
        btn.addClass('active');
        btn.siblings().removeClass('active');

        callback();
    };

    var init_search_field = function () {
        var url = 'search.json.php?limit=' + _gridder.get_items_per_page();

        _$('#template-filter').searchbox({
            url: url,
            param: 'query',
            liveparams: {
                sortby: function () {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                offset: function () {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                },
                institution: get_selected_institution,
                shared: get_selected_shared,
                ownerquery: function () {
                    return _$('#author-search').val();
                }
            },
            callback: handle_search_results,
            onkeyup: function () {
                _gridder.set_page(0);
            },
            delay: 250,
            loading_css: '#search-spinner'
        });
    };

    var get_selected_institution = function () {
        return _$('#author-institution').val();
    };

    var get_active_shared_buttons = function () {
        return _$('.view-shared button[class*="active"]');
    };

    var get_selected_shared = function () {
        var active = get_active_shared_buttons().map(function () {
            return _$(this).data('value');
        }).toArray();

        return active.length ? active[0] : '';
    };

    var handle_search_results = function (data) {
        _gridder.set_total(data.message.total);
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());

            _gridder.update_pagination(data.message.total);
        });
    };

    var _do_search = function () {
        _$.searchbox.process(_$('#template-filter').val());
    };

    var copy_template = function (id) {
        sendjsonrequest('copy.json.php', {id: id}, 'post', function (resp) {
            var path = _opts.is_teacher ? '' : 'own.php';
            window.location.href = window.config.wwwroot + 'artefact/epsp/' + path;
        });
    };

    var delete_template = function (id) {
        if (!window.confirm(get_string('confirmremovetemplate'))) {
            return;
        }

        sendjsonrequest('delete.json.php', {id: id}, 'post',
                function () {
                    delete_success(id);
                });
    };

    var delete_success = function (id) {
        _gridder.set_total(_gridder.get_total() - 1);
        var items = _$('.gridder-item[data-id="' + id + '"]');

        _gridder.get_grid().shuffle('remove', items);

//            _gridder.refresh();
//            _gridder.update_pagination(_total);

        _$('.popover-toggle').popover('destroy');
    };

    return {
        init: function () {
            _opts = _$.extend({
                is_teacher: false
            }, arguments[0] || {});

            _gridder.init(this);

            init_observers();
            _gridder.change_page();
        },
        do_search: function () {
            _do_search();
        }
    };
});


