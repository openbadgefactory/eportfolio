{if $returns}
    <div id="assignmenttable" class="fullwidth listing itemlist">
        {foreach from=$returns item=item name=returnedassignments}

            <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.returnedassignments.iteration > 5} extra hidden{/if}">
                {if $item->viewid}
                    <span class="postedon">{$item->prev_date}</span> - <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title}</a></h3>
                {elseif $item->first_view_id}
                    <span class="postedon">{$item->prev_date}</span> - <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$item->first_view_id}">{$item->name}</a></h3>
                {else}
                {* If no views in collection, do not try to link to first view *}
                <span class="postedon">{$item->prev_date}</span> -
                    <h3 class="title">
                        <a href="{$WWWROOT}collection/views.php?id={$item->collectionid}">{$item->name}</a>
                    </h3>
                </span>
                {/if}
                {if $item->description}
                            <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
                {/if}
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
