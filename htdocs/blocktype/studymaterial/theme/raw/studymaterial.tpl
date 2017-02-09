{if $studymaterials}
<div id="assignmenttable" class="fullwidth listing">
{foreach from=$studymaterials item=item}
        <div class="{cycle values='r0,r1'} listrow">
            <h3 class="title"><a href="{$WWWROOT}artefact/assignment/index.php">{$item->title}</a><span class="roledisplay"> - {$item->author}</span></h3>
            {if $item->description}
                <div class="details">{$item->description|str_shorten_html:100:true|safe}</div>
            {/if}
        </div>
{/foreach}
</div>
{else}
    <p class="message">{str tag='notanyassignments' section='blocktype.studymaterial'}</p>
{/if}
