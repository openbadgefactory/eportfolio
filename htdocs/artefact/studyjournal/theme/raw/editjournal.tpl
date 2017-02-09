{include file="header.tpl"}
{if !$form}
    <p>{str tag=notemplates section="artefact.studyjournal"}</p>
{else}
    {$form|safe}
{/if}
{include file="templatepreviewwindow.tpl"}
{include file="footer.tpl"}
