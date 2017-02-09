<div class="template-field template-field-template epsp-field epsp-field-goal epsp-template-field-goal"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    {if !$field->locked}
        <div class="template-field-name">{str tag=field_goal section="artefact.epsp"}</div>
    {/if}
    <div class="template-field-content">
        <form>
            <input type="hidden" name="type" value="goal" />
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
                <label>
                    <input type="checkbox"{if $field->field->completed} checked="checked"{/if} name="markedcomplete"{if $is_teacher} disabled="disabled"{/if} />
                    {str tag=markedcomplete section="artefact.epsp"}
                </label>
                <label>
                    <input type="checkbox"{if $field->field->marked_completed_by_user} checked="checked"{/if} name="markedcompletebyteacher" disabled="disabled" />
                    {str tag=markedcompletebyteacher section="artefact.epsp"}
                </label>
            </div>

            <div class="subfields" style="display: none">
                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goalstartdate section="artefact.epsp"}</span>
                        <input type="text" name="startdate" value="{$field->field->start}" class="datefield" />
                        <a href="#" class="pieform-calendar-toggle">
                            <img src="{theme_url filename=images/btn_calendar.png}" alt="{str tag="element.calendar.opendatepicker" section="pieforms"}" />
                        </a>
                    </label>
                </div>

                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goalenddate section="artefact.epsp"}</span>
                        <input type="text" name="enddate" value="{$field->field->end}" class="datefield" />
                        <a href="#" class="pieform-calendar-toggle">
                            <img src="{theme_url filename=images/btn_calendar.png}" alt="{str tag="element.calendar.opendatepicker" section="pieforms"}" />
                        </a>
                    </label>
                </div>

                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goaldemonstrationdate section="artefact.epsp"}</span>
                        <input type="text" name="demonstrationdate" value="{$field->field->demo}" class="datefield" />
                        <a href="#" class="pieform-calendar-toggle">
                            <img src="{theme_url filename=images/btn_calendar.png}" alt="{str tag="element.calendar.opendatepicker" section="pieforms"}" />
                        </a>
                    </label>
                </div>

                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goalrecognition section="artefact.epsp"}</span>
                        <input type="text" name="recognition" value="{$field->field->rpl}" />
                    </label>
                </div>

                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goalwherelearned section="artefact.epsp"}</span>
                        <input type="text" name="wherelearned" value="{$field->field->where}" />
                    </label>
                </div>

                <div class="form-row">
                    <label>
                        <span class="label">{str tag=goalmethods section="artefact.epsp"}</span>
                        <textarea class="textarea" cols="60" rows="8" name="methods">{$field->field->methods}</textarea>
                    </label>
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