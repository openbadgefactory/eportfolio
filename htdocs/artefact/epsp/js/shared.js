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
    var _has_group_select = false;
    var _has_student_select = false;
    var _timer = null;

    var init_observers = function () {
        // Institution, group & user filters
        _$('#search_institution').on('change', apply_filter);
        _$('#search_group').on('change', apply_filter);
        _$('#search_student').on('change', apply_filter);

        // Order by -buttons.
        _$('.sort-by input[name="sortpagesby"]').on('change', function () {
            var sortby = _$(this).val();
            _gridder.set_page(0);
            _do_search();
        });

        _$('.view-shared button').on('click', function () {
            _gridder.set_page(0);
            tag_selection_changed(_$(this), _do_search);
        });

        init_search_field();
        init_student_search_field();
    };

    var init_student_search_field = function () {
        var field = _$('#student-search');

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
        var url = 'shared.json.php?limit=' + _gridder.get_items_per_page();

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
                shared: get_selected_shared,
                institution: function () {
                    return _$('#search_institution').val();
                },
                groups: function () {
                    return _$('#search_group').val() || 0;
                },
                student: function () {
                    return _$('#search_student').val() || 0;
                },
                ownerquery: function () {
                    return _$('#student-search').val();
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
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());

            if (!_$.isEmptyObject(data.message.groups) && _has_group_select) {
                update_group_select(data.message.groups);
            }

            if (!_$.isEmptyObject(data.message.students) && _has_student_select) {
                update_student_select(data.message.students);
            }

            _gridder.update_pagination(data.message.total);
        });
    };

    var update_group_select = function (groups) {
        var selected_option = _$('#search_group option:selected');
        var first_option = _$('#search_group option:first-child').removeAttr('selected');

        _$('#search_group').get(0).options.length = 0; // Clear the list

        if (groups) {
            _$.each(groups, function(index, group) {
                var el = _$('<option></option>').attr('value', index).text(group);

                if (index == selected_option.attr('value')) {
                    el.attr('selected', 'selected');
                }

                _$('#search_group').append(el);
            });

        }
        var keys = _$.map(groups, function(value, key) {
            return key;
        });

        var selected = selected_option.val();
        var selected_in_options = _$.grep(keys, function(n, i) {
            return n == selected;
        });

        selected_in_options = _$.makeArray(selected_in_options);

        if (!selected_in_options.length > 0) {
            first_option.attr('selected', 'selected');
            _$('#search_group').val(0);
        }
    };

    var update_student_select = function (students) {
        var selectedOption = _$("#search_student option:selected");
        var html = [];

        _$('#search_student').get(0).options.length = 0; // Clear the list

        if (students) {
            _$.each(students, function(index, student) {
                html.push('<option value="' + index + '"' +
                        (index == selectedOption.attr('value') ? ' selected="selected"' : '') +
                        '>' + student + '</option>');

            });

            _$('#search_student').html(html.join(''));
        }
    };

    var _do_search = function () {
        _$.searchbox.process(_$('#template-filter').val());
    };

    return {
        init: function () {
            _has_group_select = _$('#search_group').length > 0;
            _has_student_select = _$('#search_student').length > 0;

            _gridder.init(this);
            init_observers();
            _gridder.change_page();
        },
        do_search: function () {
            _do_search();
        }
    };
});