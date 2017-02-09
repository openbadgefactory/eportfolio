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
 * @subpackage blocktype-singleepspfield
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
(function (_$) {
    _$(function () {
        var FIELD_CACHE = {};
        var url = window.config.wwwroot + 'artefact/epsp/blocktype/singleepspfield/fields.json.php';

        var update_field_selector = function () {
            var epspid = _$(this).val();

            // Fields not in cache yet, get from server.
            if (!FIELD_CACHE[epspid]) {
                FIELD_CACHE[epspid] = [];

                sendjsonrequest(url, {id: epspid}, 'get', function (resp) {
                    _$(resp.fields).each(function () {
                        var prefix = this.field.type === 'subtitle' ? '--' : (this.field.type === 'goal' || this.field.type === 'textfield' ? '----' : '');

                        FIELD_CACHE[epspid].push({
                            id: this.id,
                            title: prefix + ' ' + this.title
                        });
                    });

                    update_field_selector_from_cache(epspid);
                });
            }
            else {
                update_field_selector_from_cache(epspid);
            }
        };

        var update_field_selector_from_cache = function (id) {
            var fields = FIELD_CACHE[id];

            if (fields) {
                var html = [];

                _$('#instconf_artefactid').get(0).options.length = 0;

                _$(fields).each(function () {
                    html.push('<option value="' + this.id + '">' + this.title + '</option>');
                });

                _$('#instconf_artefactid').html(html.join(''));
            }
        };

        _$('#instconf_epsp').change(update_field_selector);
    });
})(jQuery);