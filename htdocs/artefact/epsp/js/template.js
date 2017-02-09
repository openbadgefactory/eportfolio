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
define(['jquery-loader', 'local', 'bootstrap', 'jquery-ui'], function (jq, local) {
    _$ = jq;
    _local = local;
    _opts = {};

    var init_observers = function () {
        if (window.config.handheld_device) {
            _local.init_touchhandler();
        }

        var fieldcontainer = _$('#fields');

        fieldcontainer.on('click', '.remove-field', remove_field);
        fieldcontainer.on('click', '.toggle-subfields', toggle_subfields);
        _$('#save-fields').click(save_fields);

        fieldcontainer.find('.template-field').each(function () {
            init_field(_$(this));
        });

        var button = _$('#add-field');
        var list = _$('#epsp-field-selector').clone().show();

        button.popover({
            html: true,
            placement: 'right',
            content: function () {
                return list;
            }
        });

        fieldcontainer.sortable({
            handle: '.handle',
            items: '.template-field',
            axis: 'y',
            update: function () {
                update_row_indices();
            },
            start: disable_tinymce,
            stop: enable_tinymce
        });

        list.on('click', 'a', add_field);
    };

    var save_fields = function () {
        var fields = [];

        tinyMCE.triggerSave(); // Synchronize textarea with TinyMCE-content.

        _$('#fields .template-field').each(function (idx) {
            var formvalues = _local.form_to_object(_$(this).find('form'));
            formvalues.index = idx;
            fields.push({data: formvalues});
        });

        sendjsonrequest('save.json.php', {
            fields: JSON.stringify(fields),
            id: _$('#templateid').val()
        }, 'post', function () {
            // Do redirect after successful saving.
            window.location.href = window.config.wwwroot + 'artefact/epsp/' +
                    (_opts.is_teacher ? '' : 'own.php');
        });
    };

    var toggle_subfields = function () {
        _$(this).toggleClass('open');
        _$(this).parents('.template-field').find('.subfields').slideToggle('fast');
    };

    var remove_field = function (evt) {
        evt.preventDefault();

        if (window.confirm(get_string('confirmremovefield'))) {
            _$(this).parents('.template-field').slideToggle('fast', function () {
                _$(this).remove();
                update_row_indices();
            });
        }

        update_row_indices();
    };

    var disable_tinymce = function (evt, ui) {
        if (typeof tinyMCE !== 'undefined') {
            _$(ui.item).find('textarea').each(function () {
                tinyMCE.execCommand('mceRemoveControl', false, _$(this).uniqueId().attr('id'));
            });
        }
    };

    var enable_tinymce = function (evt, ui) {
        if (typeof tinyMCE !== 'undefined') {
            _$(ui.item).find('textarea').each(function () {
                _local.add_tinymce('#' + _$(this).uniqueId().attr('id'), {
                    images: false
                });
            });
        }
    };

    var add_field = function (evt) {
        evt.preventDefault();

        var type = _$(this).data('type');
        var field = _$('.template-field-template.epsp-template-field-' + type).clone();

        _$('#fields').append(field);
        _$('#add-field').popover('hide');

        init_field(field);

        field.find('.form-row input').first().focus(); // Focus first element.
    };

    var init_field = function (field) {
        var type = field.find('input[name=type]').val();

        field.removeClass('template-field-template').show('fast');

        switch (type) {
            case 'textfield':
            case 'goal':
                var id = field.find('textarea').uniqueId().attr('id');
//                _local.add_tinymce('#' + id, false, true);
                _local.add_tinymce('#' + id, {
                    images: false
                });

                field.find('.datefield').each(function () {
                    var input = _$(this);
                    var button = input.siblings('.pieform-calendar-toggle');

                    setup_calendar(input.uniqueId().attr('id'), button.uniqueId().attr('id'));
                });

                break;
        }

        update_row_indices();
    };

    var setup_calendar = function (inputid, buttonid) {
        Calendar.setup({
            ifFormat: get_string('strfdaymonthyearshort'),
            daFormat: get_string('strfdaymonthyearshort'),
            inputField: inputid,
            button: buttonid,
            showsTime: false
        });
    };

    var update_row_indices = function () {
//        _$('#fields').find('.template-field').each(function (index) {
//            _$(this).removeClass('r0 r1').addClass('r' + (index % 2));
//        });
    };

    return {
        init: function () {
            _opts = _$.extend({
                is_teacher: 0
            }, arguments[0] || {});

            init_observers();
        }
    };
});
