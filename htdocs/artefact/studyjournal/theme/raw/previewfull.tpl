{include file="header.tpl"}

{if $is_owner}
<div class="rbuttons">
    <a class="btn" href="{$WWWROOT}artefact/studyjournal/{$editurl}?id={$id}">{str tag=edittemplate section="artefact.studyjournal"}</a>
</div>
{/if}

<div class="template-preview">
    {$form|safe}
</div>

{include file="footer.tpl"}