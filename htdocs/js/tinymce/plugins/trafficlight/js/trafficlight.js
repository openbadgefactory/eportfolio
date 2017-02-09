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
 * global tinyMCEPopup
 */
tinyMCEPopup.requireLangPack();

var TrafficlightDialog = {
    init: function (ed) {
        tinyMCEPopup.resizeToInnerSize();
        this.addListeners();
    },

    addListeners: function () {
        var list = document.getElementById('trafficlights');
        var children = list.getElementsByTagName('img');
        var func = list.addEventListener ? 'addEventListener' : 'attachEvent';
        var self = this;

        for (var i = 0; i < children.length; i++) {
            children[i][func]('click', self.insert);
        }
    },

    insert: function () {
        var editor = tinyMCEPopup.editor;
        var dom = editor.dom;
        var file = this.src;

        tinyMCEPopup.execCommand('mceInsertContent', false, dom.createHTML('img', {
            src: file,
            alt: this.title,
            title: this.title,
            border: 0
        }));

        tinyMCEPopup.close();
    }
};

tinyMCEPopup.onInit.add(TrafficlightDialog.init, TrafficlightDialog);
