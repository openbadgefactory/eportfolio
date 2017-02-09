{if $learningobjects}
    <div id="learningobjecttable" class="fullwidth listing itemlist">
        {foreach from=$learningobjects item=item name=learningobjects}
            <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.learningobjects.iteration > 5} extra hidden{/if}" title="{str tag=assignmentreturndate section="interaction.learningobject"}: {if $item->relativedate}{$item->relativedate}{else}-{/if}">
                <h3 class="title">
                    <a href="{$WWWROOT}interaction/learningobject/view.php?id={$item->id}">{$item->name}</a>
                </h3>
                {if $item->relativedate}
                    <span class="postedon">- {$item->relativedate}</span>
                {/if}
                {if $item->description}
                    <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
                {/if}
            </div>
        {/foreach}

        {if count($learningobjects) > 5}
            <div class="morelinkwrap">
                <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
            </div>
        {/if}
    </div>
{else}
    <p class="message">{str tag='noassignedlearningobjects' section='blocktype.assignedlearningobject'}</p>
{/if}