{if $returns}
    <div id="assignmenttable" class="fullwidth listing itemlist">
        {foreach from=$returns item=item name=returnedassignments}

            <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.returnedassignments.iteration > 5} extra hidden{/if}">
                {if $item->viewid}
                    <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title}</a></h3>
                {else}
                    <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$item->first_view_id}">{$item->name}</a></h3>
                {/if}
                {if $item->description}
                            <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
                {/if}
                <div class="groupuserdate">
                    <a href="{$WWWROOT}/user/view.php?id={$item->owner}" > {$item->firstname} {$item->lastname} ({$item->username})</a>
                    <span class="postedon"> - {$item->prev_date}</span>
                </div>

            </div>
        {/foreach}
        {if count($returns) > 5}
            <div class="morelinkwrap">
                <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
            </div>
        {/if}
    </div>
{else}
    <p class="message">{str tag='notanyassignments' section='blocktype.assignment'}</p>
{/if}
