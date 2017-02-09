{if $assignments}
    <div id="assignmenttable" class="fullwidth listing itemlist">
        {foreach from=$assignments item=item name=myassignments}
            <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.myassignments.iteration > 5} extra hidden{/if}">
                <h3 class="title">
                    <a href="{$WWWROOT}interaction/learningobject/view.php?id={$item->collection}">{$item->name}</a>
                </h3>
                {if $item->description}
                    <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
                {/if}
                <div class="groupuserdate">
                    <a href="{profile_url($item->owner)}">{$item->author} </a>
                    <span class="postedon">- {$item->from} - {$item->to}</span>
                </div>
            </div>
        {/foreach}

        {if count($assignments) > 5}
            <div class="morelinkwrap">
                <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
            </div>
        {/if}
    </div>
{else}
    <p class="message">{str tag='notanyassignments' section='blocktype.assignment'}</p>
{/if}
