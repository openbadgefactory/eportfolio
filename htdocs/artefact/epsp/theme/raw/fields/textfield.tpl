<div class="template-field template-field-template epsp-field epsp-field-textfield epsp-template-field-textfield"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    {if !$field->locked}
        <div class="template-field-name">{str tag=field_textfield section="artefact.epsp"}</div>
    {/if}
    <div class="template-field-content">
        <form>
            <input type="hidden" name="type" value="textfield" />
            <input type="hidden" name="fieldid" value="{$field->id}" />
            <div class="form-row epsp-field-titlearea">
                {if $field->locked}
                    <p>
                        <span class="toggle-subfields title">
                            <span class="icon"></span>
                            <span class="text">{$field->title}</span>
                        </span>
                    </p>
                {else}
                    <span class="toggle-subfields">
                        <span class="icon"></span>
                        <span class="text"></span>
                    </span>
                    <input type="text" name="title" value="{$field->title}" />
                {/if}
            </div>

            <div class="subfields" style="display: none">
                <div class="form-row">
                    <textarea cols="60" rows="4" name="text">{$field->field->value}</textarea>
                </div>
            </div>
        </form>
    </div>
    {if !$field->locked}
        <div class="fr btns">
            <a href="#" class="remove-field"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
        </div>
    {/if}
</div>