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
 * @subpackage artefact-blog
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'local'], function(jq, gridder, local) {
    var _$ = jq;
    var _gridder = gridder;
    var _local = local;

    var create_view = function (blogid) {
        sendjsonrequest('create_view.json.php', {id: blogid}, 'post', function (resp) {
            window.location.href = window.config.wwwroot + 'view/access.php?new=1&id=' +
                    resp.view + '&backto=artefact/blog';
        });
    };

     var delete_blog = function(blogid) {
        if (!window.confirm(get_string('deleteblog?'))) {
            return;
        }
        sendjsonrequest('delete.json.php', {id: blogid}, 'post', function() {
            delete_success(blogid);
        });

    };

    var delete_success = function(templateid) {
        var items = _$('.gridder-item[data-id="' + templateid + '"]');
        _gridder.get_grid().shuffle('remove', items);
        _$('.popover-toggle').popover('destroy');
//        update_layout();
    };

    var update_layout = function () {
        _gridder.update_pagination(_gridder.filter());
    };

    return {
        init: function() {
            _gridder.init(this, {
                faux_pagination: true
            }, arguments[0] || {});

            _$('.gridder-new').click(function() {
                document.location.href = window.config.wwwroot +
                        'artefact/blog/new/index.php';
            });

            _$('.tag-buttons button').on('click', function() {
                _gridder.tag_selection_changed(_$(this), function () {
                    _gridder.set_page(0);
                    update_layout();
                });
            });

            _$('html').on('click', '.create-view', function (evt) {
                evt.preventDefault();
                create_view(_$(evt.target).parents('ul').data('itemid'));
            });

            _$('html').on('click', '.delete-blog', function(evt) {
                evt.preventDefault();
                delete_blog(_$(evt.target).parents('ul').data('itemid'));
            });

            // Order by -buttons.
            _$('.sort-by input[name="sortpagesby"]').on('change', function () {
                _gridder.set_page(0);
                update_layout();
            });

            update_layout();
        },

        publicity_changed: function () {
            _gridder.set_page(0);
            update_layout();
        }
    };
});
