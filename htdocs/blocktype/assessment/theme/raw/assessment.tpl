{if $assessments}
    <div id="assignmenttable" class="fullwidth listing itemlist">
        {foreach from=$assessments item=item name=assessments}
            {if $item->url}
                <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.assessments.iteration > 5} extra hidden{/if}">
                    <h3 class="title"><a href="{$WWWROOT}{$item->url}">{$item->title}</a></h3>
                    {if $item->description}
                                <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
                    {/if}
                    <div class="groupuserdate">
                        <a href="{$WWWROOT}/user/view.php?id={$item->author}" > {$item->firstname} {$item->lastname} ({$item->username})</a>
                        <span class="postedon"> - {$item->prev_date}</span>
                    </div>
                </div>
            {/if}
        {/foreach}
        {if count($assessments) > 5}
            <div class="morelinkwrap">
                <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
            </div>
        {/if}
    </div>
{else}
    <p class="message">{str tag='notanyassignments' section='blocktype.assessment'}</p>
{/if}
