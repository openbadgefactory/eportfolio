<button title="{if $collectionid}{str tag=returnthiscollection section="interaction.learningobject"}{else}{str tag=returnthisview section="interaction.learningobject"}{/if}" data-target="#return-view-modal" data-toggle="modal" class="btn returnview">
    {if $collectionid}
        {str tag=returnthiscollection section="interaction.learningobject"}
    {else}
        {str tag=returnthisview section="interaction.learningobject"}
    {/if}
</button>

{include file="returnobjectmodal.tpl" collectionid=$collectionid instructors=$instructors
    defaultinstructors=$defaultinstructors prevreturndate=$prevreturndate viewid=$viewid}