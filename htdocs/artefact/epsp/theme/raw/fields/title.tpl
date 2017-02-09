<div class="template-field template-field-template epsp-template-field-title"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    {if !$field->locked}
        <div class="template-field-name">{str tag=field_title section="artefact.epsp"}</div>
    {/if}
    <div class="template-field-content">
        <form>
            <input type="hidden" name="type" value="title" />
            <input type="hidden" name="fieldid" value="{$field->id}" />
            <div class="form-row">
                {if $field->locked}
                    <h2>{$field->title}</h2>
                {else}
                    <input type="text" name="title" value="{$field->title}" />
                {/if}
            </div>
        </form>
    </div>
    {if !$field->locked}
        <div class="fr btns">
            <a href="#" class="remove-field"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
        </div>
    {/if}
</div>