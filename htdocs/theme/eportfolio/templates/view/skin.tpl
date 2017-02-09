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

{include file="view/editviewtabs.tpl" selected='skin' new=$new issiteview=$issiteview backto=$backto}
<div class="subpage">
    <div class="rbuttons skinsbtns">
        <a class="btn" href="{$WWWROOT}skin/index.php" id="manageskins">{str tag=manageskins section=skin}</a>
    </div>
    <div class="skins-wrap">
        <div class="currentskin">
            <h2>{str tag=currentskin section=skin}</h2>
            {if !$saved}<div class="message warning">{str tag=notsavedyet section=skin}</div>{/if}
            {if $incompatible}<div class="message warning">{$incompatible}</div>{/if}
            <h3 class="title">{$currenttitle|safe}</h3>
            <img src="{$WWWROOT}skin/thumb.php?id={$currentskin}" width="240" height="135" alt="{$currenttitle}">
            <div class="submitcancel">{$form|safe}
                <!--< EKAMPUS modal part -->
                    <div class="skin-controls">
                    {if $cskin->editable}
                            <a href="{$WWWROOT}skin/design.php?id={$cskin->id}{if $cskin->type == 'site'}&site=1{/if}&from=viewskin" class="btn-big-edit" title="{str tag='editthisskin' section='skin'}" id="skinedit">
                                {str tag=editspecific arg1=$cskin->title}
                            </a>
                    {/if}
                    </div>
                <!-- EKAMPUS modal part >-->
            </div>
            {if $currentmetadata}
            <div class="skin-metadata">
                <div class="metadisplayname"><span>{str tag=displayname section=skin}:</span> {$currentmetadata.displayname|clean_html|safe}</div>
                <div class="metadescription"><span>{str tag=description section=skin}:</span><br>{$currentmetadata.description|clean_html|safe}</div>
                <div class="metacreationdate"><span>{str tag=creationdate section=skin}:</span> {$currentmetadata.ctime}</div>
                <div class="metamodifieddate"><span>{str tag=modifieddate section=skin}:</span> {$currentmetadata.mtime}</div>
            </div>
            {/if}
        </div>
        <div class="skins-right">
            <h3 class="title">{str tag=userskins section=skin}</h3>
            <div class="userskins">
                <ul class="userskins">
                {foreach from=$userskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
            <h3 class="title favouriteskins">{str tag=favoriteskins section=skin}</h3>
            <div class="favorskins">
                <ul class="favorskins">
                {foreach from=$favorskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
            <h3 class="title">{str tag=siteskins section=skin}</h3>
            <div class="siteskins">
                <ul class="siteskins">
                {foreach from=$siteskins item=skin}
                    <li><a href="{$WWWROOT}view/skin.php?id={$viewid}&skin={$skin->id}"><img src="{$WWWROOT}skin/thumb.php?id={$skin->id}" width="180" height="101" alt="{$skin->title}"/><span>{$skin->title|safe}</span></a></li>
                {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>
<!--< EKAMPUS modal part -->

<div class="modal fade" id="edit-skin-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
            <iframe id="skineditframe" src="" width="100%" height="600px" frameborder="0" scrolling="auto"></iframe>
      </div>
    </div>
  </div>
</div>
<!-- EKAMPUS > modal part end-->
{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
