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
 * @subpackage interaction-pages
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'local', 'jquery.searchbox'], function(jq, gridder, local) {
    var _$ = jq;
    var _gridder = gridder;
    var _local = local;
    var _opts = {};

    var init_observers = function () {
        _$('.gridder-new').click(function() {
            document.location.href = _opts.newurl;
        });

        _$('.view-tags button').on('click', function() {
            _gridder.set_page(0);
            _gridder.tag_selection_changed(_$(this), _do_search);
        });

        // Order by -buttons.
        _$('.sort-by input[name="sortpagesby"]').on('change', function () {
            _gridder.set_page(0);
            _do_search();
        });

        init_copying();
        init_search_field();
    };

    // Enables the functionality to copy copyable collections to learning
    // objects.
    var init_copying = function () {
        _$('html').on('click', 'a.copytolearningobject', function (evt) {
            evt.preventDefault();

            var id = _$(evt.target).parents('ul').data('itemid');
            var params = { id: id };
            var url = window.config.wwwroot + 'interaction/learningobject/createfromcollection.json.php';

            sendjsonrequest(url, params, 'post', function (resp) {
                window.location.href = window.config.wwwroot +
                        'interaction/learningobject/index.php';
            });
        });
    };

    var init_search_field = function() {
        var url = 'collectionsearch.json.php?limit=' + _gridder.get_items_per_page() +
                '&groupid=' + _opts.groupid;

        _$('#page-search').searchbox({
            url: url,
            param: 'query',
            liveparams: {
                sortby: function () {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                offset: function() {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                },
                shared: _gridder.get_selected_publicity,
                tags: _gridder.get_selected_tags
            },
            callback: handle_search_results,
            onkeyup: function() {
                _gridder.set_page(0);
            },
            delay: 250,
            loading_css: '#search-spinner'
        });
    };

    var handle_search_results = function (data) {
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());

            _gridder.update_pagination(data.message.total);
        });
    };

    var _do_search = function () {
        _$.searchbox.process(_$('#page-search').val());
    };

    return {
        init: function() {
            _gridder.init(this);
            _opts = _$.extend({
                total: 0,
                groupid: 0
            }, arguments[0] || {});

            init_observers();
            _gridder.change_page();
        },

        publicity_changed: function () {
            _gridder.set_page(0);
            _do_search();
        },

        do_search: function () {
            _do_search();
        }
    };
});