{include file="header.tpl"}

{$form|safe}

<div id="template-field-selector" style="display: none">
    <ul>
        <li><a data-type="text" href="#">{str tag=templatetextfield section="artefact.studyjournal"}</a></li>
        <li><a data-type="vibe" href="#">{str tag=templatevibefield section="artefact.studyjournal"}</a></li>
    </ul>
</div>
    
{include file="templatepreviewwindow.tpl"}
{include file="footer.tpl"}