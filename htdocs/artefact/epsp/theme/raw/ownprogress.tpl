{include file="header.tpl"}



    {foreach from=$plans item=plan}
    <div class="plan ownprogress blockinstance {if $dwoo.foreach.default.first} open {/if}" >
        <div class="epsp-field epsp-field-title" data-viewid="{$plan->view}">
            <h2 class="toggle-subfields "><a href="{$wwwroot}view/view.php?id={$plan->view}">{$plan->title}</a></h2>
        </div>
        <div class="fields subfields bs-grid" {if $dwoo.foreach.default.first}style="display: block;" {/if} >
            <div class="row heading">
                <div class="title col-md-6">{str tag=field_subtitles_goal section="artefact.epsp"}</div>
                <div class="title col-md-3">
                    <span>{str tag=markedcomplete section="artefact.epsp"}</span>
                </div>
                <div class="title col-md-3">
                    <span>{str tag=markedcompletebyteacher section="artefact.epsp"}</span>
                </div>
            </div>
            {foreach from=$plan->fields item=field}
                <div class="row">
                    <div class="epsp-field epsp-field-subtitle col-md-6" data-fieldid="{$field->id}">
                        <h3 class="completable">{$field->title}</h3>
                    </div>
                    <div class="epsp-field col-md-3">
                        <div class="pull-left">{str tag=markedcomplete section="artefact.epsp"}</div>
                        <div class="progress-wrapper"><div class="progress">
                            <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->bystudentprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->bystudentprog}%;">
                              <span>{$field->sums->bystudent} / {$field->sums->totalgoals}</span>
                            </div>
                        </div></div>
                        <div class="pull-right">{$field->sums->bystudentprog}%</div>
                    </div>
                    <div class="epsp-field col-md-3">
                        <div class="pull-left">{str tag=markedcompletebyteacher section="artefact.epsp"}</div>
                        <div class="progress-wrapper"><div class="progress">
                            <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->byteacherprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->byteacherprog}%;">
                                <span>{$field->sums->byteacher}  / {$field->sums->totalgoals}</span>
                            </div>
                        </div></div>
                        <div class="pull-right">{$field->sums->byteacherprog}%</div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    {/foreach}



<input type="hidden" id="templateid" value="{$templateid}" />



{include file="footer.tpl"}