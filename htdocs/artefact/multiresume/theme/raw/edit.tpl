{include file="header.tpl"}
<div class="rbuttons">
    {*<EKAMPUS*}
    {if $resumeview}
        <a href="{$WWWROOT}view/view.php?id={$resumeview}" class="btn">{str tag="viewresumepage" section="artefact.multiresume"}</a>
        <a href="{$WWWROOT}view/access.php?id={$resumeview}&backto=artefact/multiresume" class="btn">{str tag=editaccess section="artefact.multiresume"}</a>
    {else}
        <a href="{$WWWROOT}artefact/multiresume/edit.php?id={$resumeid}&publish=1" class="btn">{str tag=createeditaccess section="artefact.multiresume"}</a>
    {/if}
     {*EKAMPUS>*}
    <form action="{$WWWROOT}artefact/multiresume/">
        <input class="btn" type="submit" value="{str tag=backtoresumes section="artefact.multiresume"}" />
    </form>
</div>
<div id="iframediv"><iframe id="frame" src="" ></iframe></div>
{if $pagedescription}
  <p class="intro">{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
 {*<EKAMPUS*}
 <div class="multiresumeform">
{*EKAMPUS>*}
 {$form|safe}
</div>
{include file="footer.tpl"}
