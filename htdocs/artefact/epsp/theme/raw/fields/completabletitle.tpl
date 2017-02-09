<div class="template-field template-field-template epsp-template-field-completabletitle"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    {if !$field->locked}
        <div class="template-field-name">{str tag=field_completabletitle section="artefact.epsp"}</div>
    {/if}
    <div class="template-field-content">
        <form>
            <input type="hidden" name="type" value="completabletitle" />
            <input type="hidden" name="fieldid" value="{$field->id}" />
            <div class="form-row">
                {if $field->locked}
                    <h2>{$field->title}</h2>
                {else}
                    <input type="text" name="title" value="{$field->title}" />
                {/if}
                <label>
                    <input type="checkbox"{if $field->field->completed} checked="checked"{/if} name="markedcomplete"{if $is_teacher} disabled="disabled"{/if} />
                    {str tag=markedcomplete section="artefact.epsp"}
                </label>
                <label>
                    <input type="checkbox"{if $field->field->marked_completed_by_user} checked="checked"{/if} name="markedcompletebyteacher" disabled="disabled" />
                    {str tag=markedcompletebyteacher section="artefact.epsp"}
                </label>
            </div>
        </form>
    </div>
    {if !$field->locked}
        <div class="fr btns">
            <a href="#" class="remove-field"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
        </div>
    {/if}
</div>