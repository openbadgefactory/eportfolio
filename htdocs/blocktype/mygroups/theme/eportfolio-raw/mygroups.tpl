{if $USERGROUPS}
<div id="usergroupstable" class="itemlist fullwidth listing">
{foreach from=$USERGROUPS item=item name=mygroups}
    <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.mygroups.iteration > 5} extra hidden{/if}">
        <h3 class="title"><a href="{group_homepage_url($item)}">{$item->name}</a><span class="roledisplay"> - {$item->roledisplay}</span></h3>
        {if $item->description}
            <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
        {/if}
    </div>
{/foreach}
</div>

{if count($USERGROUPS) > 5}
    <div class="morelinkwrap">
        <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
    </div>
{/if}

{else}
    <p class="message">{str tag='notinanygroups' section='group'}</p>
{/if}
