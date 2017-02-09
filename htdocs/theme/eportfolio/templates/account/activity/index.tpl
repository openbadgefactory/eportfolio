{include file="header.tpl"}

			<div id="notifications">
			<form method="post" style="float: left">
			<label for="notifications_type">{str section='activity' tag='type'}:</label>
			<select id="notifications_type" name="type">
				<option value="all">--</option>
			{foreach from=$options item=name key=t}
				<option value="{$t}"{if $type == $t} selected{/if}>{$name}</option>
			{/foreach}
			</select>{contextualhelp plugintype='core' pluginname='activity' section='activitytypeselect'}
			</form>
            
            {* <EKAMPUS *}
            <div class="rbuttons">
                {if $isteacher}
                    <button data-target="#group-message-modal" data-toggle="modal" id="sendgroupmessage" class="btn">{str tag=sendgroupmessage section=activity}</button>
                {/if}
                {$deleteall|safe}
            </div>
            
            {if $isteacher}
            <div class="modal fade" id="group-message-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h1>{str tag=sendgroupmessage section=activity}</h1>
                        </div>
                        
                        <div class="modal-body">
                            
                            <div id="modalmessages"></div>
                            
                            {$messageform|safe}
                            
                            <div id="group-message-container">
                                <h2>{str tag=groupmessagerecipients section=activity}</h2>
                                
                                <div id="viewacl-advanced" class="potential-recipients">
                                    
                                    <div id="potential-recipients-form">
                                        <label for="type">{str tag=search}</label>
                                        <select name="type" id="type">
                                            <option value="group" selected="selected">{str tag=recipientgroups section=activity}</option>
                                            <option value="user">{str tag=users}</option>
                                        </select>
                                        <input type="search" name="search" id="search" />
                                        <button id="dosearch" class="btn-search" type="button">{str tag=go}</button>
                                    </div>
                                    
                                    <table id="results">
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div id="recipient-container">
                                    <div class="message" id="no-recipients">{str tag=norecipientsselected section=activity}</div>
                                    <table id="group-message-recipients">
                                        <tbody id="accesslistitems"></tbody>
                                    </table>
                                </div>
                            </div>
                                
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" id="send-message" class="btn btn-default">{str tag=sendmessage section="activity"}</button>    
                            <button type="button" class="btn btn-default" data-dismiss="modal">{str tag=cancel}</button>
                        </div>
                        
                    </div>
                </div>
            </div>
                        
            <script type="text/javascript">
var count = 0;
var searchTable = new TableRenderer('results',
    window.config.wwwroot + 'view/access.json.php?grouptype=system',
    [ undefined, undefined, undefined ]);
                    
searchTable.statevars.push('type');
searchTable.statevars.push('query');
searchTable.type = 'friend';
searchTable.pagerOptions = {
    'firstPageString': '\u00AB',
    'previousPageString': '<',
    'nextPageString': '>',
    'lastPageString': '\u00BB',
    'linkOptions': {
        'href': '',
        'style': 'padding-left: 0.5ex; padding-right: 0.5ex;'
    }
};

var stradd = {jstr tag=add};
var streveryone = {jstr tag=everyoneingroup section=view};
var strremove = {jstr tag=remove};
var strnorecipients = {jstr tag=norecipientsselected section=activity}
var strnosubjectorbody = {jstr tag=nosubjectorbody section=activity}

{literal}
searchTable.query = '';
searchTable.rowfunction = function(rowdata, rownumber, globaldata) {
    rowdata.type = searchTable.type;
    rowdata.type = rowdata.type == 'friend' ? 'user' : rowdata.type;

    var buttonTD = TD({'class': 'buttontd'});

    var addButton = BUTTON({'type': 'button', 'class': 'button'}, stradd);
    connect(addButton, 'onclick', function() {
        // Do not allow putting the same item on the list multiple times.
        if ($j('tr#accesslistitem-' + rowdata.type + '-' + rowdata.id).length === 0) {
            renderAccessListItem(rowdata);
        }
    });
    appendChildNodes(buttonTD, addButton);

    var identityNodes = [], profileIcon = null, roleSelector = null;
    if (rowdata.type == 'user') {
        profileIcon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' + rowdata.id});
        identityNodes.push(A({'href': rowdata.url, 'target': '_blank'}, rowdata.name));
    }
    else if (rowdata.type == 'group') {
        rowdata.role = null;
        identityNodes.push(A({'href': rowdata.url, 'target': '_blank'}, rowdata.name));
    }

    return TR({'id': rowdata.type + '-' + rowdata.id, 'class': 'r' + (rownumber % 2)},
        buttonTD,
        TD({'class': 'sharewithusersname'}, identityNodes),
        TD({'class': 'right icon-container'}, profileIcon)
    );
};

searchTable.updateOnLoad();

function search(e) {
    searchTable.query = $('search').value;
    searchTable.type  = $('type').options[$('type').selectedIndex].value;
    searchTable.offset = 0;
    searchTable.doupdate();
    e.stop();
}

function send() {
    var subject = $j.trim($j('#groupmessageform_subject').val());
    var message = $j.trim($j('#groupmessageform_body').val());
    var rows = $j('tr.ai-container');
    
    if (subject === '' || message === '') {
        displayMessage(strnosubjectorbody, 'error', true, 'modalmessages');
        return;
    }
    else if (rows.length === 0) {
        displayMessage(strnorecipients, 'error', true, 'modalmessages');
        return;
    }
    
    var groups = $j('tr.group-container input.item-id').map(function () { return parseInt($j(this).val()); });
    var users = $j('tr.user-container input.item-id').map(function () { return parseInt($j(this).val()); });
    
    sendjsonrequest('sendgroupmessage.json.php', {
        subject: subject,
        message: message,
        'users[]': users,
        'groups[]': groups
    }, 'post', function () {
        $j('#group-message-modal').modal('hide');
    }, null, false, false, 'modalmessages');
}

addLoadEvent(function() {
    connect($('search'), 'onkeydown', function(e) {
        if (e.key().string == 'KEY_ENTER') {
            search(e);
        }
    });
    connect($('dosearch'), 'onclick', search);
    connect($('send-message'), 'onclick', send);
    
    // Initialize modal UI on close.
    $j('#group-message-modal').on('hidden.bs.modal', function () {
        $j('#modalmessages').empty();
        $j('#accesslistitems').empty();
        $j('#groupmessageform_subject').val('');
        $j('#groupmessageform_body').val('');
        $j('#search').val('');
        $j('#results').children().empty();
        $j('#no-recipients').removeClass('hidden');
        
        searchTable.pager = null;
    });
});

// Given a row, render it on the right hand side
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button', 'title': strremove});

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
    if (item.type == 'user') {
        icon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + item.id + '&maxwidth=25&maxheight=25'});
    }
    else if (item.type == 'group') {
        icon = SPAN({'title':''},'');
    }

    var row = TR({'class': cssClass, 'id': 'accesslistitem-' + item.type + '-' + item.id},
        TD({'class': 'icon-container'}, icon),
        TD({'class': 'accesslistname'}, name),
        TD({'class': 'right removebutton'}, removeButton,
            INPUT({
                'type': 'hidden',
                'class': 'item-id',
                'value': item.id
            })
        )
    );

    connect(removeButton, 'onclick', function() {
        removeElement(row);
        
        if (!getFirstElementByTagAndClassName('tr', null, 'accesslistitems')) {
            removeElementClass('no-recipients', 'hidden');
        }
    });
    appendChildNodes('accesslistitems', row);
    addElementClass('no-recipients', 'hidden');

    count++;

    return row;
}

{/literal}
            </script>
            {/if}
            {* EKAMPUS> *}

			<form name="notificationlist" method="post" onSubmit="markread(this, 'read'); return false;">
			<table id="activitylist" class="fullwidth">
				<thead>
					<tr>
						<th><span class="accessible-hidden">{str section='activity' tag='messagetype'}</span></th>
						<th>{str section='activity' tag='subject'}</th>
						<th>{str section='activity' tag='date'}</th>
						<th class="center">{str section='activity' tag='read'}</th>
						<th class="center">{str tag='delete'}</th>
					</tr>
				</thead>
            <tfoot>
					  <tr>
						<td colspan="3"></td>
                        <td class="center">
                            <a href="" onclick="toggleChecked('tocheckread'); return false;">{str section='activity' tag='selectall'}</a>
                        </td>
                        <td class="center">
                            <a href="" onclick="toggleChecked('tocheckdel'); return false;">{str section='activity' tag='selectall'}</a>
                        </td>
				  	</tr>
				</tfoot>
				<tbody>
                {$activitylist.tablerows|safe}
                </tbody>
			</table>
            <div class="right activity-buttons">
                <input class="submit" type="submit" value="{str tag='markasread' section='activity'}" />
                <input class="submit btn-del" type="button" value="{str tag='delete'}" onClick="markread(document.notificationlist, 'del'); return false;" />
            </div>
            {$activitylist.pagination|safe}
			</form>
			</div>
			
{include file="footer.tpl"}
