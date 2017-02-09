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
define(['jquery-loader'], function (jq) {
    var _$ = jq;
    var _templateid = -1;
    var _table = null;
    var _animspeed = 'fast';

    var do_search = function (evt) {
        evt.preventDefault();

        var type = _$('#access-type').val();
        var query = _$('#access-query').val();

        _table.query = query;
        _table.type = type;
        _table.offset = 0;

        _table.doupdate();
    };

    var init_searchtable = function () {
        _table = new TableRenderer('search-results',
                window.config.wwwroot + 'view/access.json.php?grouptype=system',
                [undefined, undefined, undefined]);

        _table.statevars.push('type');
        _table.statevars.push('query');
        _table.type = 'group';
        _table.query = '';
        _table.rowfunction = add_result_row;
        _table.pagerOptions = {
            'firstPageString': '\u00AB',
            'previousPageString': '<',
            'nextPageString': '>',
            'lastPageString': '\u00BB',
            'linkOptions': {
                'href': '',
                'style': 'padding-left: 0.5ex; padding-right: 0.5ex;'
            }
        };
    };

    var add_result_row = function (rowdata, rownumber, globaldata) {
        var buttonTD = _$('<td/>').addClass('buttontd');
        var addButton = _$('<button/>')
                .addClass('button')
                .attr('type', 'button')
                .text(get_string('add'));

        buttonTD.append(addButton);

        var link = '';
        var profileicon = false;

        if (_table.type === 'group' || _table.type === 'user') {
            link = _$('<a></a>').attr('href', rowdata.url).
                    attr('target', '_blank').
                    text(rowdata.name);
        }
        else if (_table.type === 'institution') {
            rowdata.id = rowdata.name;
            rowdata.name = rowdata.displayname;
            link = rowdata.displayname;
        }

        if (_table.type === 'user') {
            profileicon = _$('<img/>').attr('src', window.config.wwwroot +
                    'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' + rowdata.id);
        }

        addButton.click(function () {
            if (_$('li#' + _table.type + '-' + rowdata.id).length === 0) {
                render_access_list_item(rowdata);
            }
        });

        var row = _$('<tr/>').addClass('r' + rownumber % 2)
                .append(buttonTD)
                .append(_$('<td/>').append(link));

        if (profileicon !== false) {
            row.append(_$('<td/>').addClass('profile-icon').append(profileicon));
        }

        return row.get(0);
    };

    var render_access_list_item = function (item) {
        var type = _table.type;
        var removeButton = _$('<button/>').attr('type', 'button').text(get_string('remove'))
                .addClass('remove');
        var input = _$('<input/>').attr('type', 'hidden').val(item.id);
        var namediv = _$('<div/>');
        var row = _$('<li/>').append(namediv).append(removeButton)
                .append(input)
                .attr('id', type + '-' + item.id);

        if (type === 'user') {
            namediv.append(_$('<img/>').attr('src', window.config.wwwroot +
                    'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' +
                    item.id))
                    .append(_$('<span/>').text(item.name));
        }
        else {
            namediv.text(item.name);
        }

        var container = _$('#access-added-' + type + 's');
        var list = container.find('ul.access-items');
        var msg = container.find('p.no-items');

        msg.hide(_animspeed);
        list.append(row.hide());
        row.show(_animspeed);
    };

    var remove_item = function (evt) {
        var row = _$(this).parents('li');
        var list = _$(this).parents('ul.access-items');
        var msg = list.siblings('p.no-items');

        row.hide(_animspeed, function () {
            _$(this).remove();

            if (list.find('li').length === 0) {
                msg.show(_animspeed);
            }
        });
    };

    var save_access = function () {
        var groups = _$('#access-added-groups input').map(function () {
            return parseInt(_$(this).val(), 10);
        }).toArray();

        var institutions = _$('#access-added-institutions input').map(function () {
            return _$(this).val();
        }).toArray();

        var users = _$('#access-added-users input').map(function () {
            return parseInt(_$(this).val(), 10);
        });

        sendjsonrequest('access.json.php', {
            'groups[]': groups,
            'institutions[]': institutions,
            'users[]': users,
            id: _templateid
        }, 'post', function () {
            window.location.href = window.config.wwwroot + 'artefact/epsp/';
        });
    };

    return {
        init: function (templateid) {
            _templateid = templateid;
            init_searchtable();

            _$('#access-potential').submit(do_search);
            _$('#save-access').click(save_access);
            _$('#access-current ul.access-items').on('click', 'button.remove', remove_item);
        }
    };
});