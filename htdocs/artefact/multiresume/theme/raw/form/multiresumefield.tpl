
{foreach from=$languages key=lang item=title name="langcontainer"}
<h3>{$title}</h3>
<div id="multiresume_lang_container_{$lang}" class="multiresume_lang_container" style="width:95%; clear:both; padding: 10px 0;">

    <div class="{$name}_artefact_container">
        <select name="{$name}_artefact_{$lang}" id="{$name}_artefact_{$lang}" onchange="multiresumeArtefactChanged(this, '{$lang}');">
        {foreach $artefacts a}
        <option value="{$a->id}"{if $a->id == $default.$lang.artefact} selected="selected"{/if}>{$a->title}</option>
        {/foreach}
        </select>

        {foreach from=$fields key=a item=fs}
        <select name="{$name}_artefact_{$a}_fields_{$lang}" class="multiresume_artefact_fields" id="multiresume_artefact_{$a}_fields_{$lang}"{if $a != $default.$lang.artefact} style="display:none"{/if} onchange="multiresumeArtefactFieldChanged(this, '{$lang}');">
            {foreach from=$fs item=f}
                <option value="{$f.id}"{if $default.$lang.field == $f.id} selected="selected"{/if}>{$f.title}</option>
            {/foreach}
        </select>
        {/foreach}
    </div>

    <div>
    {foreach from=$rows key=field item=row}
        <div class="multiresume_artefactfield_rows_container" id="multiresume_artefactfield_{$field}_rows_container_{$lang}"{if $field != $default.$lang.field} style="display:none"{/if}>
            {$c = 0}
            {foreach from=$row item=obj}
                <label><input type="checkbox" name="{$name}_artefactfield_{$field}_rows_{$lang}[]" value="{$c}"{if $field != $default.$lang.field || in_array($c, $default.$lang.rows)} checked="checked"{/if}>{$obj->rowtitle()}</label><br>
                {$c = $c + 1}
            {/foreach}
        </div>
    {/foreach}
    </div>
</div>
{/foreach}
