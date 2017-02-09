{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  {* <EKAMPUS *}
  {if $owner && !$learningobject}
      <div class="viewrbuttons">
        {include
            file="returnobjectbutton.tpl"
            collectionid=$collectionid
            instructors=$instructors
            defaultinstructors=$defaultinstructors
            prevreturndate=$prevreturndate
            viewid=$viewid}
      </div>
    {/if}
  {* EKAMPUS> *}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='layout' new=$new issiteview=$issiteview backto=$backto}
<div class="subpage">
  {$form|safe}
</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
