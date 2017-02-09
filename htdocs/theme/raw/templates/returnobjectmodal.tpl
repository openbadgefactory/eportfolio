<div class="modal fade" id="return-view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h1 id="noicon">{if $collectionid}{str tag=returnthiscollection section="interaction.learningobject"}{else}
                    {str tag=returnthisview section="interaction.learningobject"}{/if}</h1>
                </div>

                <div class="modal-body">
                    <div id="modalmessages"></div>
                    <div id="group-message-container">
                        <h2>{str tag=assignmentinstructors section=interaction.learningobject}</h2>
                        <div id="lo-viewacl-advanced" class="potential-recipients {if $instructors}hidden{/if}">
                            <div id="potential-recipients-form">
                                <label for="type">{str tag=searchinstructorshelp section=interaction.learningobject}</label><br/>
                                <input type="search" name="search" id="lo-search" />
                                <button id="lo-dosearch" class="btn-search" type="button">{str tag=go}</button>
                                {if $defaultinstructors}<button id="resetinstructors" class="btn-search" type="button">{str tag=resetinstructors section=interaction.learningobject}</button>{/if}
                            </div>
                            <table id="lo-results">
                                <tbody></tbody>
                            </table>
                        </div>

                        <div id="recipient-container" {if $instructors}class="fullwidth"{/if}>
                            {if $instructors}<div class="message">{str tag="returnedpreviously" section=interaction.learningobject}: {$prevreturndate}</div>{/if}
                            <div class="message {if $instructors || $defaultinstructors}hidden{/if}" id="no-recipients">{str tag=noinstructorsyet section=interaction.learningobject}</div>

                            <table id="group-message-recipients">
                                <tbody id="lo-accesslistitems">
                                    {foreach from=$instructors item=instructor}
                                        <tr class="ai-container teacher-container" id="accesslistitem-teacher-{$instructor->user}">
                                            <td class="icon-container"><img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=25&maxheight=25&id={$instructor->user}"></td>
                                            <td class="accesslistname">{$instructor->firstname} {$instructor->lastname}</td>
                                            <td class="right removebutton">
                                                <input type="hidden" class="item-id" value="{$instructor->user}">
                                            </td>
                                        </tr>
                                    {/foreach}
                                    {foreach from=$defaultinstructors item=defaultinstructor}
                                        <tr class="ai-container teacher-container" id="accesslistitem-teacher-{$defaultinstructor->user}">
                                            <td class="icon-container"><img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=25&maxheight=25&id={$defaultinstructor->user}"></td>
                                            <td class="accesslistname">{$defaultinstructor->name}</td>
                                            <td class="right removebutton">
                                                <button type="button" title="{str tag=remove}"></button>
                                                <input type="hidden" class="item-id" value="{$defaultinstructor->user}">
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="send-message" class="btn btn-default">
                        {if $collectionid}
                            {str tag=returnthiscollection section="interaction.learningobject"}
                            {if $instructors}
                                {str tag=again section="interaction.learningobject"}
                            {/if}
                        {else}
                            {str tag=returnthisview section="interaction.learningobject"}
                        {if $instructors}{str tag=again section="interaction.learningobject"}{/if}
                    {/if}
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{str tag=cancel}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function () {
    var count = 0;
    var paramname = '{if $collectionid}collectionid{else}viewid{/if}';
    var paramvalue = {if $collectionid}{$collectionid}{else}{$viewid}{/if};
    var searchTable = new TableRenderer('lo-results',
            window.config.wwwroot + 'view/access.json.php?grouptype=system',
            [undefined, undefined, undefined]);

    searchTable.statevars.push('type');
    searchTable.statevars.push('query');
    $j('#lo-results').children().empty();

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
    var strsubject = {jstr tag=returnlearningobjectsubject section=interaction.learningobject}
    var strmessage = {jstr tag=returnlearningobjectmessage section=interaction.learningobject}
    var strremove = {jstr tag=remove};
    var strnorecipients = {jstr tag=noinstructorsyet section=interaction.learningobject}


    {literal}
        searchTable.query = '';
        searchTable.rowfunction = function (rowdata, rownumber, globaldata) {
            rowdata.type = searchTable.type;
            rowdata.type = rowdata.type == 'friend' ? 'user' : rowdata.type;

            var buttonTD = TD({'class': 'buttontd'});

            var addButton = BUTTON({'type': 'button', 'class': 'button'}, stradd);
            connect(addButton, 'onclick', function () {
// Do not allow putting the same item on the list multiple times.
                if ($j('tr#accesslistitem-' + rowdata.type + '-' + rowdata.id).length === 0) {
                    renderAccessListItem(rowdata);
                }
            });
            appendChildNodes(buttonTD, addButton);

            var identityNodes = [], profileIcon = null, roleSelector = null;
            if (rowdata.type == 'teacher') {
                profileIcon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&maxwidth=25&maxheight=25&id=' + rowdata.id});
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
            searchTable.query = $('lo-search').value;
            searchTable.type = 'teacher';
            searchTable.offset = 0;
            searchTable.doupdate();
            e.stop();
        }

        function send() {
            var subject = strsubject; //$j.trim($j('#groupmessageform_subject').val());
            var message = strmessage; //$j.trim($j('#groupmessageform_body').val());
            var rows = $j('tr.ai-container');

            if (subject === '' || message === '') {
                displayMessage(strnosubjectorbody, 'error', true, 'modalmessages');
                return;
            }
            else if (rows.length === 0) {
                displayMessage(strnorecipients, 'error', true, 'modalmessages');
                return;
            }

            var users = $j('tr.teacher-container input.item-id').map(function () {
                return parseInt($j(this).val());
            });
            var params = {
                subject: subject,
                message: message,
//                viewid: viewid,
                'users[]': users
            };

            params[paramname] = paramvalue;

            sendjsonrequest(window.config.wwwroot + 'view/returnobject.json.php', params, 'post', function (resp) {
                $j('#return-view-modal').modal('hide');
                if (resp.error === false) {
                    $j('#group-message-container').load(document.URL + ' #group-message-container');
                }
            }, null, false, false, 'modalmessages');
        }

        addLoadEvent(function () {
            connect($('lo-search'), 'onkeydown', function (e) {
                if (e.key().string == 'KEY_ENTER') {
                    search(e);
                }
            });
            connect($('lo-dosearch'), 'onclick', search);
            connect($('send-message'), 'onclick', send);

            // Initialize modal UI on close.
            $j('#return-view-modal').on('hidden.bs.modal', function () {
                $j('#modalmessages').empty();
                $j('#lo-search').val('');
                $j('#lo-results').children().empty();

                searchTable.pager = null;
            });
            $j('button#resetinstructors').on('click', function () {
                $j('#no-recipients').addClass('hidden');
                $j('#group-message-recipients').load(document.URL + ' #group-message-recipients', function () {
                    $j('.removebutton button').on('click', function (evt) {
                        removerow(evt);
                    });
                });
            });
            $j('.removebutton button').on('click', function (evt) {
                removerow(evt);
            });
            var removerow = function (evt) {
                var target = $j(evt.target);
                target.parents('tr.ai-container').remove();
                if (!getFirstElementByTagAndClassName('tr', null, 'lo-accesslistitems')) {
                    removeElementClass('no-recipients', 'hidden');
                }
            }
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
            if (item.type == 'teacher') {
                icon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + item.id + '&maxwidth=25&maxheight=25'});
            }
            else if (item.type == 'group') {
                icon = SPAN({'title': ''}, '');
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

            connect(removeButton, 'onclick', function (evt) {
                removeElement(row);

                if (!getFirstElementByTagAndClassName('tr', null, 'lo-accesslistitems')) {
                    removeElementClass('no-recipients', 'hidden');
                }
            });
            appendChildNodes('lo-accesslistitems', row);
            addElementClass('no-recipients', 'hidden');

            count++;

            return row;
        }
    })();
    {/literal}
</script>
