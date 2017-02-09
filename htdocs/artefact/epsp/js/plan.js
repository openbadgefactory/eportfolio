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
 * @subpackage artefact.epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'jquery.searchbox'], function (jq, gridder) {
    var _$ = jq;
    var _gridder = gridder;

    var init_observers = function () {
        _$('.gridder-new').click(function () {
            document.location.href = window.config.wwwroot +
                    'artefact/epsp/edit.php';
        });

        _$('html').on('click', '.create-view', function (evt) {
            evt.preventDefault();
            create_view(_$(evt.target).parents('ul').data('itemid'));
        });

        _$('.view-tags button').on('click', function () {
            _gridder.tag_selection_changed(_$(this), function () {
                _gridder.set_page(0);
                _gridder.update_pagination(_gridder.filter());
            });
        });

        // Order by -buttons.
        _$('.sort-by input[name="sortpagesby"]').on('change', function () {
            _gridder.set_page(0);
            _gridder.update_pagination(_gridder.filter());
        });

        _$('html').on('click', '.delete-plan', function (evt) {
            evt.preventDefault();
            delete_plan(_$(evt.target).parents('ul').data('itemid'));
        });
    };

    var create_view = function (id) {
        sendjsonrequest('create_view.json.php', { id: id}, 'post', function (resp) {
            window.location.href = window.config.wwwroot + 'view/access.php?new=1&id=' +
                    resp.view + '&backto=artefact/epsp/own.php';
        });
    };

    var delete_plan = function (id) {
        if (!window.confirm(get_string('confirmremoveplan'))) {
            return;
        }

        sendjsonrequest('delete.json.php', {id: id}, 'post',
                function () {
                    delete_success(id);
                });
    };

    var delete_success = function (id) {
        var items = _$('.gridder-item[data-id="' + id + '"]');

        _gridder.get_grid().shuffle('remove', items);
        _$('.popover-toggle').popover('destroy');
    };

    return {
        init: function () {
            _gridder.init(this, {
                faux_pagination: true,
                on_publicity_changed: function () {
                    _gridder.set_page(0);
                    _gridder.update_pagination(_gridder.filter());
                }
            });

            init_observers();
            _gridder.update_pagination(_gridder.filter());
        }
    };
});