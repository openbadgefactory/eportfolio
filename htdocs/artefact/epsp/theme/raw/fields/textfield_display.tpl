<div class="bs-grid">
    <div class="container-fluid">
        <div class="row">
            <div class="epsp-field-titlearea col-md-12">
                <p>
                    <span class="toggle-subfields title">
                        <span class="icon"></span>
                        <span class="text">{$artefact->title}</span>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="epsp-field-contentarea subfields bs-grid" style="display: none">
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="subfield-wrapper col-md-7 col-sm-7 col-xs-12">
                    {$field->value|default:"-"|clean_html|safe}
                </div>
                {if $can_see_comments}
                    <div class="field-feedback col-md-5 col-sm-5 col-xs-12">

                        <div class="field-feedback-comments">
                            <p class="loading-comments">
                                <span class="loading-spinner"></span>
                                {str tag=loadingcomments section="artefact.epsp"}
                            </p>
                            <p class="no-comments" style="display: none">{str tag=nocomments section=artefact.epsp}</p>
                            <div class="comments" style="display: none"></div>
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