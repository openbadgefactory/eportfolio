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
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
(function (tinymce) {
    tinymce.PluginManager.requireLangPack('trafficlight');
    tinymce.create('tinymce.plugins.TrafficlightPlugin', {
        init: function (ed, url) {
            // Register commands
            ed.addCommand('mceTrafficlight', function () {
                ed.windowManager.open({
                    file: url + '/trafficlights.htm',
                    width: 360,
                    height: 140,
                    inline: 1
                }, {
                    plugin_url: url
                });
            });

            // Register buttons.
            ed.addButton('trafficlight', {
                title: 'trafficlights.desc',
                cmd: 'mceTrafficlight',
                image: url + '/img/icon.png'
            });
        },

        getInfo: function () {
            return {
                longname: 'Trafficlight',
                author: 'Discendum Ltd',
                authorurl: 'http://www.discendum.com/',
                version: tinymce.majorVersion + '.' + tinymce.minorVersion
            };
        }
    });

    // Register plugin.
    tinymce.PluginManager.add('trafficlight', tinymce.plugins.TrafficlightPlugin);
})(tinymce);

