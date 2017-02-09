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
 * @subpackage interaction-learningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'local', 'jquery.searchbox'], function (jq, gridder, local) {
    var _$ = jq;
    var _gridder = gridder;
    var _local = local;
    var _opts = {};
    var _timer = null;

    var init_observers = function () {
        // Create new -button
        _$('.gridder-new').click(function () {
            document.location.href = _opts.newurl;
        });

        _$('.view-shared button').on('click', function () {
            _gridder.set_page(0);
            _gridder.sharing_selection_changed(_$(this), _do_search);
        });

        _$('.sort-by input[name="sortpagesby"]').on('change', function () {
            _gridder.set_page(0);
            _do_search();
        });

        init_teacher_search_field();
        init_search_field();
        init_copying();
    };

    var init_copying = function () {
        _$('html').on('click', 'a.copytoskillsfolder', function (evt) {
            evt.preventDefault();

            var id = _$(evt.target).parents('ul').data('itemid');
            var params = { id: id };

            sendjsonrequest('copytoskillsfolder.json.php', params, 'post', function (resp) {
                var copyid = resp.copyid;

                window.location.href = window.config.wwwroot +
                        'interaction/pages/collections.php';
            });
        });
    };

    var init_teacher_search_field = function () {
        var field = _$('#teacher-search');

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

    var apply_filter = function() {
        _gridder.set_page(0);
        _do_search();
    };

    var init_search_field = function () {
        var url = 'search.json.php?limit=' + _gridder.get_items_per_page();

        _$('#learningobject-search').searchbox({
            url: url,
            param: 'query',
            liveparams: {
                sortby: function () {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                offset: function () {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                },
                ownerquery: function () {
                    return _$('#teacher-search').val();
                },
                shared: get_selected_shared
            },
            callback: handle_search_results,
            onkeyup: function () {
                _gridder.set_page(0);
            },
            delay: 250,
            loading_css: '#search-spinner'
        });
    };

    var get_active_shared_buttons = function() {
        return _$('.view-shared button[class*="active"]');
    };

    var get_selected_shared = function() {
        var active = get_active_shared_buttons().map(function() {
            return _$(this).attr('id');
        }).toArray();

        return active;
    };

    var handle_search_results = function (data) {
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function () {
                return true;
            }, _gridder.get_sort_opts());
        });

        _gridder.update_pagination(data.message.total);
    };

    var _do_search = function () {
        _$.searchbox.process(_$('#learningobject-search').val());
    };

    return {
        init: function () {
            _gridder.init(this);
            _opts = _$.extend({

            }, arguments[0] || {});

            init_observers();
            _gridder.change_page();
        },

        do_search: function () {
            _do_search();
        },

        get_copy_params: function (item) {
            return { collectionid: item.data('id') };
        },

        get_edit_path: function (id) {
            return 'collection/edit.php?copy=1&id=' + id;
        },

        access_changeable: function (item) {
            return true;
        }
    };
});