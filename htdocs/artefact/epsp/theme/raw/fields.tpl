{include file="header.tpl"}

{if $description}
    <div class="epsp-description">{$description}</div>
{/if}

<div id="fields"{if $readonly} class="readonly"{/if}>
    {foreach from=$fields item=field}
        {$type=$field->field->type}
        {if !$readonly}
            {include file="artefact:epsp:fields/`$type`.tpl" field=$field}
        {else}
            <div class="epsp-field template-field epsp-field-{$type}">
                {include file="artefact:epsp:fields/`$type`_display.tpl" artefact=$field field=$field->field readonly=$readonly}
            </div>
        {/if}
    {/foreach}
</div>

{if !$readonly}
    <button id="add-field">{str tag=addnewfield section="artefact.epsp"}</button>
{/if}

<input type="hidden" id="templateid" value="{$templateid}" />

<div class="buttons">
    {if !$readonly}
        <button id="save-fields">{str tag=save}</button>
    {/if}
    <a class="btn" href="{$WWWROOT}artefact/epsp/{if !$is_teacher && !$readonly}own.php{/if}">{str tag=cancel}</a>
</div>

{if !$readonly}
    <div id="epsp-field-selector" style="display: none">
        <ul>
            <li><a data-type="title" href="#">{str tag=field_title section="artefact.epsp"}</a></li>
            <li><a data-type="completabletitle" href="#">{str tag=field_completabletitle section="artefact.epsp"}</a></li>
            <li><a data-type="subtitle" href="#">{str tag=field_subtitle section="artefact.epsp"}</a></li>
            <li><a data-type="goal" href="#">{str tag=field_goal section="artefact.epsp"}</a></li>
            <li><a data-type="textfield" href="#">{str tag=field_textfield section="artefact.epsp"}</a></li>
        </ul>
    </div>

    <div style="display: none" id="epsp-templates">
        {include file="artefact:epsp:fields/textfield.tpl" field=null}
        {include file="artefact:epsp:fields/title.tpl" field=null}
        {include file="artefact:epsp:fields/completabletitle.tpl" field=null}
        {include file="artefact:epsp:fields/subtitle.tpl" field=null}
        {include file="artefact:epsp:fields/goal.tpl" field=null}
    </div>
{/if}

{include file="footer.tpl"}