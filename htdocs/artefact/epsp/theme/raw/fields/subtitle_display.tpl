<h3 class="completable">{$artefact->title}</h3>

{if !$readonly}
    <ul class="mark-as-completed">
        <li>
            <div class="marked-completed {if !$field->completed}in{/if}complete">
                <span class="tick"></span>
                {str tag=markedcomplete section="artefact.epsp"}
            </div>
        </li>
        <li>
            <div class="marked-completed-by-user{if $is_teacher && (!$field->marked_completed_by_user || $field->marked_completed_by_user == $userid)} editable{/if} {if !$field->marked_completed_by_user}in{/if}complete" title="{str tag=markcompletedhelp section="artefact.epsp"}">
                <span class="tick"></span>
                {str tag=markedcompletebyteacher section="artefact.epsp"}
                <span class="name">
                    {if $field->marked_completed_by_user}
                        ({$field->marked_completed_by_user_name}, {$field->marked_completed_at|format_date})
                    {/if}
                </span>
            </div>
        </li>
    </ul>
{/if}