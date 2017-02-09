{* Use capture here so we can later see, whether the list will have any
   items to show. *}
{capture "actionitems"}
    {if $group->membershiptype == 'member'}
        {if $group->canleave}
            <li class="leavegroup">
                <a href="{$WWWROOT}group/leave.php?id={$group->id}&amp;returntoabsolute={$returntoabsolute}">{str tag=leavegroup section=group}</a>
            </li>
        {/if}
        {if $group->invitefriends}
            <li class="invitefriends">
                <a href="{$WWWROOT}group/inviteusers.php?id={$group->id}&amp;friends=1">{str tag=invitefriends section=group}</a>
            </li>
        {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
            <li class="suggestfriends">
                <a href="{$WWWROOT}/group/suggest.php?id={$group->id}">{str tag=suggesttofriends section=group}</a>
            </li>
        {/if}

    {elseif $group->membershiptype == 'admin'}
        <li class="editgroup">
            <a href="{$WWWROOT}group/edit.php?id={$group->id}">{str tag=edit}</a>
        </li>
        <li class="deletegroup">
            <a href="{$WWWROOT}group/delete.php?id={$group->id}">{str tag=delete}</a>
        </li>
        {if $group->requests}
            <li class="grouprequests">
                <a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request">{str tag=membershiprequests section=group} ({$group->requests})</a>
            </li>
        {/if}
    {elseif $group->membershiptype == 'invite'}
        {* TODO: test this *}
        <li class="invite">{$group->invite|safe}</li>
    {* Joining via popup disabled for now. If the element is created
    dynamically (via AJAX-request), the form doesn't exist in the page
    when the Pieform's submit handler is looking for it and submission
    fails miserably. *}
    {*    {elseif $group->jointype == 'open'}
        <li class="joingroup">{$group->groupjoin|safe}</li>*}
    {elseif $group->membershiptype == 'request'}
        {* User has requested to join this group *}
        <li class="requestedtojoin">{str tag=requestedtojoin section=group}</li>
    {elseif $group->request}
        <li class="request"><a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returntoabsolute={$returntoabsolute}">{str tag=requestjoingroup section=group}</a>
    {/if}
    
    {if $group->membershiptype == 'member' || $group->membershiptype == 'admin'}
    <li><a href='#edit-item-tags' data-toggle='modal'>{str tag=tags}</a></li>
    {/if}
{/capture}

{if $.capture.actionitems|trim != ''}
    <ul class="group-action-items" data-itemid="{$group->id}">
        {$.capture.actionitems|safe}
    </ul>
{/if}