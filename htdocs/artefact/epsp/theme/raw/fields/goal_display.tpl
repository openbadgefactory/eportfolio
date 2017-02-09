<div class="bs-grid">
    <div class="container-fluid">
        <div class="row">
            <div class="epsp-field-titlearea col-md-7 col-sm-12 col-xs-12">
                <p{if $field->completable} class="completable"{/if}>
                    <span class="toggle-subfields title">
                        <span class="icon"></span>
                        <span class="text">{$artefact->title}</span>
                    </span>
                </p>
            </div>

            {if !$readonly}
                <div class="epsp-completion-container col-md-5 col-sm-12 col-xs-12">
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
                </div>
            {/if}
        </div>
    </div>
</div>

<div class="epsp-field-contentarea subfields bs-grid" style="display: none">

    <div>
        <div class="container-fluid">
            <div class="row">

                <div class="subfield-wrapper col-md-7 col-sm-7 col-xs-12">
                    <dl>
                        <dt>{str tag=goalstartdate section="artefact.epsp"}<dt>
                        <dd>{tif $field->start ?: "-"}</dd>
                        <dt>{str tag=goalenddate section="artefact.epsp"}</dt>
                        <dd>{tif $field->end ?: "-"}</dd>
                        <dt>{str tag=goaldemonstrationdate section="artefact.epsp"}</dt>
                        <dd>{tif $field->demo ?: "-"}</dd>
                        <dt>{str tag=goalrecognition section="artefact.epsp"}</dt>
                        <dd>{tif $field->rpl ?: "-"}</dd>
                        <dt>{str tag=goalwherelearned section="artefact.epsp"}</dt>
                        <dd>{tif $field->where ?: "-"}</dd>
                        <dt>{str tag=goalmethods section="artefact.epsp"}</dt>
                        <dd>{tif $field->methods ? $field->methods|clean_html|safe : "-"}</dd>
                    </dl>
                </div>

                {if $can_see_comments}
                    <div class="field-feedback col-md-5 col-sm-5 col-xs-12">

                        <div class="field-feedback-comments">
                            <p class="loading-comments">
                                <span class="loading-spinner"></span>
                                {str tag=loadingcomments section="artefact.epsp"}
                            </p>
                            <p class="no-comments" style="display: none">{str tag=nocomments section=artefact.epsp}</p>
                            <div class="comments" style="display: none">
                            </div>
                        </div>

                        {if $can_comment}
                            <div class="form-row">
                                <textarea cols="60" rows="4" name="text"></textarea>
                            </div>
                            <div class="form-row">
                                <button type="button" class="save-field-comment">{str tag=placefeedbackbutton section="artefact.comment"}</button>
                            </div>
                        {/if}

                    </div>
                {/if}
            </div>
        </div>
    </div>

</div>