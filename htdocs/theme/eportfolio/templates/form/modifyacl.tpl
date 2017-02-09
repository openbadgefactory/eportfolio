<input type="hidden" name="accesslist" value="">

<div id="editaccesswrap">
    <div class="fl presets-container">
        <div id="potentialpresetitems">
            <h3>{{str tag=allowedit section=view}}</h3>
        </div>
        <fieldset id="viewacl-advanced" class="cb">
            <legend><a href="#" id="viewacl-advanced-show">{{str tag=otherusers section=view}}</a></legend>
            <div class="fl viewacl-advanced-search">
                <label>{{str tag=search}}</label>
                <input type="hidden" name="type" value="groupmember">
                <input type="hidden" name="group" id="groupid" value="{{$groupid}}">
                <input type="text" name="search" id="search">
                <button id="dosearch" class="btn-search" type="button">{{str tag=go}}</button>
            </div>
            <table id="results" class="fl">
                <tbody></tbody>
            </table>
            <div class="cb"></div>
        </fieldset>
    </div>

<table id="accesslisttable" class="fr hidden">
  <thead>
    <tr class="accesslist-head1">
      <th colspan="2">{{str tag=Added section=view}}</th>
      <th colspan="2">{{str tag=accessdates section=view}}</th>
      <th></th>
    </tr>
    <tr class="accesslist-head2">
      <th></th>
      <th></th>
      <th>{{str tag=From}}:</th>
      <th>{{str tag=To}}:</th>
      <th></th>
    </tr>
  </thead>
  <tbody id="accesslistitems">
  </tbody>
</table>

<div class="cb"></div>
</div>
<script type="text/javascript">
var count = 0;

// Utility functions

// Given a row, render it on the left hand side
function renderPotentialPresetItem(item) {
    var addButton = BUTTON({'type': 'button'}, {{jstr tag=add}});
    var attribs = {};
    if (item.preset) {
        attribs = {'class': 'preset'};
    }
    else if (item['class']) {
        attribs = {'class': item['class']};
    }

    var row = DIV(attribs, addButton, ' ', item.shortname ? SPAN({'title':item.name}, item.shortname) : item.name);
    item.preset = true;

    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(item));
    });
    appendChildNodes('potentialpresetitems', row);

    return row;
}

// Given a row, render it on the right hand side
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button', 'title': {{jstr tag=remove}}});

    var cssClass = 'ai-container';
    if (item.preset) {
        cssClass += '  preset';
    }
    cssClass += ' ' + item.type + '-container';
    var name = [item.shortname ? SPAN({'title': item.name}, item.shortname) : item.name];
    if (item.role != null) {
        name.push(' - ', item.roledisplay);
    }
    var icon = null;
    if (item.type == 'user' || item.type == 'groupmember') {
        icon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + item.id + '&maxwidth=20&maxheight=20'});
    }

    var row2 = TR(null, TD({'colspan': 7}, ''));

    var row = TR({'class': cssClass, 'id': 'accesslistitem' + count},
        TD(null, icon),
        TH({'class': 'accesslistname'}, name),
        TD(null, makeCalendarInput(item, 'start'), makeCalendarLink(item, 'start')),
        TD(null, makeCalendarInput(item, 'stop'), makeCalendarLink(item, 'stop')),
        TD({'class': 'right removebutton'}, removeButton,
            INPUT({
                'type': 'hidden',
                'name': 'accesslist[' + count + '][type]',
                'value': item.type
            }),
            (item.id ?
            INPUT({
                'type': 'hidden',
                'name': 'accesslist[' + count + '][id]',
                'value': item.id
            })
            :
            null
            ),
            (item.role != null ?
            INPUT({
                'type': 'hidden',
                'name': 'accesslist[' + count + '][role]',
                 'value': item.role
            })
            :
            null
            )
        )
    );
    
    connect(removeButton, 'onclick', function() {
        removeElement(row);
        removeElement(row2);
        if (!getFirstElementByTagAndClassName('tr', null, 'accesslistitems')) {
            addElementClass('accesslisttable', 'hidden');
        }
    });
    appendChildNodes('accesslistitems', row);
    appendChildNodes('accesslistitems', row2);
    removeElementClass('accesslisttable', 'hidden');

    setupCalendar(item, 'start');
    setupCalendar(item, 'stop');
    count++;
}

function makeCalendarInput(item, type) {
    return INPUT({
        'type':'text',
        'name': 'accesslist[' + count + '][' + type + 'date]',
        'id'  :  type + 'date_' + count,
        'value': item[type + 'date'] ? item[type + 'date'] : '',
        'size': '15'
    });
}

function makeCalendarLink(item, type) {
    var link = A({
        'href'   : '',
        'id'     : type + 'date_' + count + '_btn',
        'onclick': 'return false;', // @todo do with mochikit connect
        'class'  : 'pieform-calendar-toggle'},
        IMG({
            'src': '{{theme_url filename='images/btn_calendar.png'}}',
            'alt': ''})
    );
    return link;
}

function setupCalendar(item, type) {
    //log(type);
    var dateStatusFunc, selectedFunc;
    //if (type == 'start') {
    //    dateStatusFunc = function(date) {
    //        startDateDisallowed(date, $(item.id + '_stopdate'));
    //    };
    //    selectedFunc = function(calendar, date) {
    //        startSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
    //    }
    //}
    //else {
    //    dateStatusFunc = function(date) {
    //        stopDateDisallowed(date, $(item.id + '_startdate'));
    //    };
    //    selectedFunc = function(calendar, date) {
    //        stopSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
    //    }
    //}
    if (!$(type + 'date_' + count)) {
        logWarn('Couldn\'t find element: ' + type + 'date_' + count);
        return;
    }
    Calendar.setup({
        "ifFormat"  :{{jstr tag=strftimedatetimeshort}},
        "daFormat"  :{{jstr tag=strftimedatetimeshort}},
        "inputField": type + 'date_' + count,
        "button"    : type + 'date_' + count + '_btn',
        //"dateStatusFunc" : dateStatusFunc,
        //"onSelect"       : selectedFunc
        "showsTime" : true
    });
}

// SETUP

// Left top: public, loggedin, friends
var potentialPresets = {{$potentialpresets|safe}};
forEach(potentialPresets, function(preset) {
    renderPotentialPresetItem(preset);
});

var loggedinindex = {{$loggedinindex}};
function ensure_loggedin_access() {
    var oldaccess = getFirstElementByTagAndClassName(null, 'loggedin-container', 'accesslistitems');
    if (oldaccess) {
        forEach(getElementsByTagAndClassName(null, 'loggedin-container', 'accesslistitems'), function (elem) {
            if (oldaccess != elem) {
                removeElement(elem);
            }
        });
    }
    else {
        renderAccessListItem(potentialPresets[loggedinindex]);
    }
    var newaccess = getFirstElementByTagAndClassName(null, 'loggedin-container', 'accesslistitems');
    addElementClass(getFirstElementByTagAndClassName(null, 'removebutton', newaccess), 'hidden');
    forEach(getElementsByTagAndClassName(null, 'pieform-calendar-toggle', newaccess), function (elem) { addElementClass(elem, 'hidden'); });
    forEach(getElementsByTagAndClassName('input', null, newaccess), function (elem) {
        if (elem.name.match(/\[st(art|op)date\]$/)) {
            elem.value = '';
            elem.disabled = true;
        }
    });
}
function relax_loggedin_access() {
    forEach(getElementsByTagAndClassName(null, 'loggedin-container', $('accesslistitems')), function (elem) {
        removeElementClass(getElementsByTagAndClassName(null, 'removebutton', elem)[0], 'hidden');
        forEach(getElementsByTagAndClassName(null, 'pieform-calendar-toggle', elem), function (elem1) { removeElementClass(elem1, 'hidden'); });
        forEach(getElementsByTagAndClassName('input', null, elem), function (elem1) { elem1.disabled = false; });
    });
}

// Left hand side
var searchTable = new TableRenderer(
    'results',
    'access.json.php',
    [
        undefined, undefined, undefined
    ]
);
searchTable.statevars.push('type');
searchTable.statevars.push('group');
searchTable.statevars.push('query');
searchTable.type = 'groupmember';
searchTable.group = $('groupid').value;
searchTable.pagerOptions = {
    'firstPageString': '\u00AB',
    'previousPageString': '<',
    'nextPageString': '>',
    'lastPageString': '\u00BB',
    'linkOptions': {
        'href': '',
        'style': 'padding-left: 0.5ex; padding-right: 0.5ex;'
    }
}
searchTable.query = '';
searchTable.rowfunction = function(rowdata, rownumber, globaldata) {
    rowdata.type = searchTable.type;
    var buttonTD = TD({'style': 'white-space:nowrap;'});

    var addButton = BUTTON({'type': 'button', 'class': 'button'}, {{jstr tag=add}});
    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(rowdata));
    });
    appendChildNodes(buttonTD, addButton);

    var identityNodes = [], profileIcon = null, roleSelector = null;
    profileIcon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&maxwidth=20&maxheight=20&id=' + rowdata.id});
    identityNodes.push(A({'href': config.wwwroot + 'user/view.php?id=' + rowdata.id, 'target': '_blank'}, rowdata.name));

    return TR({'class': 'r' + (rownumber % 2)},
        buttonTD,
        TD({'style': 'vertical-align: middle;'}, identityNodes),
        TD({'class': 'center', 'style': 'vertical-align:top;width:20px;'}, profileIcon)
    );
}
searchTable.updateOnLoad();

function search(e) {
    searchTable.group = $('groupid').value;
    searchTable.query = $('search').value;
    searchTable.type  = $('type').options[$('type').selectedIndex].value;
    searchTable.doupdate();
    e.stop();
}


// Right hand side
addLoadEvent(function () {
    var accesslist = {{$accesslist|safe}};
    if (accesslist) {
        forEach(accesslist, function(item) {
            renderAccessListItem(item);
        });
    }
    update_loggedin_access();
});

addLoadEvent(function() {
    // Populate the "potential access" things (public|loggedin|allfreidns)

    connect($('search'), 'onkeydown', function(e) {
        if (e.key().string == 'KEY_ENTER') {
            search(e);
        }
    });
    connect($('dosearch'), 'onclick', search);
    connect('viewacl-advanced-show', 'onclick', function(e) {
        e.stop();
        toggleElementClass('collapsed', 'viewacl-advanced');
    });
});

</script>
