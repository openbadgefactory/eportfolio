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
define(['jquery-loader'], function (jq) {
    // Reference to jQuery
    var _$ = jq;

    // Module options.
    var _opts = {};

    // Table for the students.
    var _membertable = null;

    // Table for the insturctors.
    var _instructortable = null;

    // Initializes the page observers.
    var init_observers = function () {
        // Institution list.
        _$('#institutionselect_institution').change(function (evt) {
            get_groups(_$(this).val(), update_groups);
        });

        // Group list.
        _$('#group-selector').change(function (evt) {
            // Disable the "add group" button if there aren't any groups.
            _$('#add-group').prop('disabled', parseInt(_$(this).val(), 10) === -1);
            update_members(_$(this).val());
        });

        // Student table.
        init_member_table();

        // Instructor table.
        init_instructor_table();

        // Assign to institution.
        _$('#add-institution').click(assign_to_institution);

        // Assign to group.
        _$('#add-group').click(assign_to_group);

        // Assign to single user.
        _$('#student-list').on('click', 'button', assign_to_user);

        // Assign to own groups.
        _$('ul#mygroups').on('click', 'button', assign_to_my_group);

        // Remove assignees
        _$('table#recipients').on('click', 'button.remove', remove_assignee);

        _$('table#potential-instructors').on('click', 'button', add_instructor);

        // Remove instructors
        _$('table#instructors').on('click', 'button.remove', remove_instructor);

        // Instructor search
        _$('form#instructor-search').submit(search_instructors);

        _$('button#assign').click(save_assignment);
    };

    // Saves the assignment.
    var save_assignment = function () {
        var assignees = get_assignees();
        var instructors = get_instructors();

        var do_scroll = function () {
            _$('html, body').animate({
                scrollTop: _$('#container').offset().top
            }, 250);
        };

        sendjsonrequest('assign.json.php', {
            id: _opts.learningobjectid,
            due_date: _$('#assignment-return-date').val(),
            assignees: JSON.stringify(assignees),
            instructors: JSON.stringify(instructors)
        }, 'post', function (resp) {
            if (resp.error === false) {
                window.location.href = window.config.wwwroot + 'interaction/learningobject/index.php';
            }
            else {
                do_scroll();
            }
        }, do_scroll);
    };

    // Gets the selected assignees.
    var get_assignees = function () {
        var assignees = [];

        _$('table#recipients tbody tr').each(function () {
            assignees.push({
                type: _$(this).data('type'),
                id: _$(this).data('value'),
                date: _$(this).find('input.assignedat').val()
            });
        });

        return assignees;
    };

    // Gets the selected instructors.
    var get_instructors = function () {
        var instructors = [];

        _$('table#instructors tbody tr').each(function () {
            instructors.push({
                id: _$(this).data('value')
            });
        });
        return instructors;
    };

    // Performs the instructor search.
    var search_instructors = function (evt) {
        evt.preventDefault();

        _instructortable.query = _$('#search-instructors').val();
        _instructortable.offset = 0;
        _instructortable.doupdate();
    };

    // Removes an assignee from the selected list.
    var remove_assignee = function (evt) {
        remove_row(_$(this));
    };

    // Removes an instrcutor from the selected list.
    var remove_instructor = function (evt) {
        remove_row(_$(this));
    };

    // Removes a row from a table.
    var remove_row = function (el) {
        var row = el.closest('tr');
        var tbody = el.closest('tbody');

        row.remove();

        if (tbody.find('tr').size() === 0) {
            tbody.siblings('tfoot').show();
        }
    };

    // Adds the selected institution to the assignee list.
    var assign_to_institution = function (evt) {
        var id = _$('#institutionselect_institution').val();
        var text = _$('#institutionselect_institution option:checked').text();

        assign(id, text, 'institution');
    };

    // Adds the selected group to the assignee list.
    var assign_to_group = function (evt) {
        var id = _$('#group-selector').val();
        var text = _$('#group-selector option:checked').text();

        assign(id, text, 'group');
    };

    // Adds the selected group from own groups to the assignee list.
    var assign_to_my_group = function (evt) {
        var id = _$(this).data('id');
        var text = _$(this).siblings('span').text();

        assign(id, text, 'group');
    };

    // Adds the selected user to the assignee list.
    var assign_to_user = function (evt) {
        var id = _$(this).data('userid');
        var text = _$(this).data('username');
        var profileurl = get_profile_icon_url(id);

        assign(id, text, 'user', profileurl);
    };

    // Returns the profile icon url for user with selected id.
    var get_profile_icon_url = function (id) {
        return window.config.wwwroot +
                'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' + id;
    };

    // Adds an item to the assignee list.
    var assign = function (value, text, type) {
        var tbody = _$('table#recipients tbody');
        var id = type + '-' + value;

        // Cannot add same assignee multiple times.
        if (tbody.find('tr#' + id).size() > 0) {
            return;
        }

        var icon = arguments[3] ? _$('<img/>').attr('src', arguments[3]) : _$('<span/>').html('&nbsp;');
        var name = _$('<td/>').text(text);
        var button = _$('<button/>').addClass('button remove').attr('type', 'button').
                text(get_string('remove'));
        var calendar = create_calendar_input(id);
        var calendarlink = create_calendar_link(id);

        var buttontd = _$('<td/>').addClass('removebutton').append(button);
        var icontd = _$('<td/>').addClass('profileicon').append(icon);
        var datetd = _$('<td/>').append(calendar, calendarlink);

        var row = _$('<tr/>').attr('id', id).data('type', type).
                data('value', value).
                append(icontd, name, datetd, buttontd);

        // Hide the "no rows" message.
        _$('table#recipients tfoot').hide();

        tbody.append(row);

        // Initialize the calendar and set the initial date to today.
        setup_calendar('date-' + id);
        _$('#date-' + id).val(new Date().print(get_string('strfdaymonthyearshort')));
    };

    // Adds the selected instructor to the instructor list.
    var add_instructor = function (evt) {
        var userid = _$(this).data('userid');
        var id = 'instructor-' + userid;
        var text = _$(this).data('username');
        var profile_icon = get_profile_icon_url(userid);
        var tbody = _$('table#instructors tbody');

        // Cannot add same instructor multiple times.
        if (tbody.find('tr#' + id).size() > 0) {
            return;
        }

        var icon = _$('<img/>').attr('src', profile_icon);
        var button = _$('<button/>').addClass('button remove').attr('type', 'button').
                text(get_string('remove'));

        var nametd = _$('<td/>').text(text);
        var icontd = _$('<td/>').addClass('profileicon').append(icon);
        var buttontd = _$('<td/>').addClass('removebutton').append(button);
        var row = _$('<tr/>').attr('id', id).data('value', userid).
                append(icontd, nametd, buttontd);

        // Hide the "no rows" message.
        _$('table#instructors tfoot').hide();

        tbody.append(row);
    };

    // Creates a calendar input element.
    var create_calendar_input = function (id) {
        var input = _$('<input/>').attr('type', 'text').attr('id', 'date-' + id).
                addClass('assignedat').attr('size', 15).val('');

        return input;
    };

    // Creates a calendar link element.
    var create_calendar_link = function (id) {
        var link = _$('<a/>').attr('href', '#').attr('id', 'date-' + id + '-btn').
                addClass('pieform-calendar-toggle');
        var img = _$('<img/>').attr('src', _opts.calendar_icon).
                attr('alt', get_string('element.calendar.opendatepicker', 'pieforms'));

        return link.append(img);
    };

    var init_calendars = function () {
        setup_calendar('assignment-return-date');

        _$('table#recipients tbody tr').each(function () {
            setup_calendar('date-' + _$(this).attr('id'));
        });
    };

    // Initializes the selected calendar element.
    var setup_calendar = function (id) {
        Calendar.setup({
            ifFormat: get_string('strfdaymonthyearshort'),
            daFormat: get_string('strfdaymonthyearshort'),
            inputField: id,
            button: id + '-btn',
            showsTime: false
        });
    };

    // Initializes the student table renderer.
    var init_member_table = function () {
        var url = window.config.wwwroot + 'view/access.json.php';
        var columns = [undefined, undefined, undefined];

        _membertable = new TableRenderer('students', url, columns);
        _membertable.statevars.push('group');
        _membertable.statevars.push('type');
        _membertable.group = '-1';
        _membertable.type = 'user';
        _membertable.emptycontent = get_string('noresultsfound');
        _membertable.pagerOptions = get_tablerenderer_pager();
        _membertable.rowfunction = add_member_row;

        // HACK: We don't know, when the TableRenderer's init function has been
        // called and thus the renderer initialized. A callback would be nice,
        // but it's possible that the init function is called after the
        // constructor but BEFORE setting the variables (above). So this will
        // have to do for now.
        //
        // This triggers the group selector's onchange event, because it
        // doesn't trigger automatically when it is created and filled with crap.
        var interval = window.setInterval(function () {
            if (_membertable.isinitialized) {
                window.clearInterval(interval);
                _$('#group-selector').change();
            }
        }, 50);
    };

    // Returns the pager settings for table renderers.
    var get_tablerenderer_pager = function () {
        return {
            firstPageString: '\u00AB',
            previousPageString: '<',
            nextPageString: '>',
            lastPageString: '\u00BB',
            linkOptions: {
                href: '',
                style: 'padding-left: 0.5ex; padding-right: 0.5ex;'
            }
        };
    };

    // Initializes the instructor table renderer.
    var init_instructor_table = function () {
        var url = window.config.wwwroot + 'view/access.json.php?includeuser=1';
        var columns = [undefined, undefined, undefined];

        _instructortable = new TableRenderer('potential-instructors', url, columns);
        _instructortable.statevars.push('query');
        _instructortable.statevars.push('type');
        _instructortable.query = '';
        _instructortable.type = 'teacher';
        _instructortable.emptycontent = get_string('noresultsfound');
        _instructortable.pagerOptions = get_tablerenderer_pager();
        _instructortable.rowfunction = add_instructor_row;
    };

    // Updates the student table.
    var update_members = function (groupid) {
        _membertable.group = groupid;
        _membertable.offset = 0;
        if (groupid <= 0) {
            _membertable.clear();
        }
        else {
            _membertable.doupdate();
        }
    };

    // Adds a student row to student table.
    var add_member_row = function (rowdata, rownumber, globaldata) {
        // Profile icon.
        var profileurl = window.config.wwwroot +
                'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' + rowdata.id;
        var icon = _$('<td/>').addClass('right icon-container').
                append(_$('<img/>').attr('src', profileurl));
        // User name.
        var name = _$('<td/>').addClass('sharewithusersname').
                append(_$('<a/>').
                        attr('href', rowdata.url).attr('target', '_blank').append(rowdata.name));
        // Add-button.
        var btn = _$('<td/>').addClass('buttontd').
                append(_$('<button/>').addClass('button').attr('type', 'button').
                        text(get_string('add')).data('userid', rowdata.id).
                        data('username', rowdata.name));
        return _$('<tr/>').addClass('r' + (rownumber % 2)).
                append(btn, name, icon).get(0);
    };

    // Row renderer for the instructor table renderer.
    var add_instructor_row = function (rowdata, rownumber, globaldata) {
        return add_member_row(rowdata, rownumber, globaldata);
    };

    // Gets the institution groups from the server and calls the callback
    // function afterwards.
    var get_groups = function (institution, callback) {
        sendjsonrequest('institutiongroups.json.php', {institution: institution}, 'get', function (resp) {
            callback(resp.groups);
        });
    };

    // Updates the group select list.
    var update_groups = function (groups) {
        var grouplist = _$('#group-selector');
        var html = [];

        grouplist.get(0).options.length = 0; // Clear the list.
        groups.unshift({id: -1, name: get_string('selectgroup')});

        _$.each(groups, function (index, group) {
            html.push('<option value="' + group['id'] + '">' + group['name'] + '</option>');
        });

        grouplist.html(html.join(''));
        grouplist.change();
    };

    // Public methods.
    return {
        // Module initializer.
        init: function () {
            _opts = _$.extend({
                calendar_icon: '',
                learningobjectid: -1
            }, arguments[0] || {});

            init_observers();
            init_calendars();
        }
    };
});