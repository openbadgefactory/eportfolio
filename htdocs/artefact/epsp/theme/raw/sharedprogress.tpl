{if $sortby == "author"}
    <li class="{$extraclasses} byauthor gridder-item gridder-existing {if $studentselected}open{/if}" id="{if $uniqueid}{$uniqueid}{else}item-{$id}{/if}" data-id="{$id}" {if $mtime}data-mtime="{$mtime}"{/if}  {if $author}data-author="{$author}"{/if}  {if $title}data-title="{$title}"{/if}{if $extradata}{foreach from=$extradata item=d key=k} data-{$k}="{$d}"{/foreach}{/if} {if $description}data-description="{$description}{/if}">

        <div class="row">
            <div class="col-md-3 planauthor">
                <h2 class="planauthor toggle-studentplans"><a href="{$wwwroot}user/view.php?id={$userid}">{$author}</a></h2>
            </div>
            {if $latest}
            <div class="latest epsp-field col-md-9">
                    <div class="col-md-12">
                        <div class="col-md-4">
                            <div class="plantitle"><a href="{$wwwroot}view/view.php?id={$latest->viewid}">{$latest->plantitle}</a><br/><h3 class="completable">{$latest->title}</h3></div>
                        </div>
                        <div class="col-md-4">
                            <div class="progress-wrapper">
                                <div class="progress">
                                    <div class="progress-barz" role="progressbar" aria-valuenow="{$latest->bystudentprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$latest->bystudentprog}%;">
                                        <span>{$latest->bystudent}  / {$latest->totalgoals}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="percent">{$latest->bystudentprog}%</div>
                        </div>

                        <div class="col-md-4">
                            <div class="progress-wrapper">
                                <div class="progress">
                                    <div class="progress-barz" role="progressbar" aria-valuenow="{$latest->byteacherprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$latest->byteacherprog}%;">
                                        <span>{$latest->byteacher}  / {$latest->totalgoals}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="percent">{$latest->byteacherprog}%</div>
                        </div>

                    </div>
            </div>
            {/if}
        </div>

        <div class="row">
            <div class="studentplans" {if $studentselected}style="display: block;"{/if}>
            {foreach from=$plans item=plan name='plans'}

            <div class="plan blockinstance {if $dwoo.foreach.plans.first}{if $studentselected}open{/if}{/if}">
                <div class="epsp-field  row" data-viewid="{$plan.viewid}">
                    <div class="col-md-4 toggle-subfields">
                        <h2 class="plantitle" >
                            <a href="{$wwwroot}view/view.php?id={$plan.viewid}">{$plan.title}</a><i class="fa"></i>
                        </h2>
                    </div>

                    <div class="fields subfields" {if $dwoo.foreach.plans.first}{if $studentselected}style="display: block;"{/if}{/if}>
                        <!-- div class="row heading">
                            <div class="title col-md-6">{str tag=field_subtitles_goal section="artefact.epsp"}</div>
                            <div class="title col-md-3">
                                <span>{str tag=markedcomplete section="artefact.epsp"}</span>
                            </div>
                            <div class="title col-md-3">
                                <span>{str tag=markedcompletebyteacher section="artefact.epsp"}</span>
                            </div>
                        </div-->

                        {foreach from=$plan.fields item=field}
                            <div class="row">
                                <div class="epsp-field epsp-field-subtitle col-md-6">
                                    <h3 class="completable">{$field->title}</h3>
                                </div>
                                <div class="epsp-field col-md-3">
                                    <div class="titlerow">{str tag=markedcomplete section="artefact.epsp"}</div>
                                    <div class="percent-wrapper">
                                        <div class="progress-wrapper">
                                            <div class="progress">
                                                <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->bystudentprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->bystudentprog}%;">
                                                  <span>{$field->sums->bystudent} / {$field->sums->totalgoals}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="percent">{$field->sums->bystudentprog}%</div>
                                    </div>
                                </div>

                                <div class="epsp-field col-md-3">
                                    <div class="titlerow">{str tag=markedcompletebyteacher section="artefact.epsp"}</div>
                                    <div class="percent-wrapper">
                                        <div class="progress-wrapper">
                                            <div class="progress">
                                                <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->byteacherprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->byteacherprog}%;">
                                                    <span>{$field->sums->byteacher}  / {$field->sums->totalgoals}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="percent">{$field->sums->byteacherprog}%</div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            {/foreach}
            </div>
    </li>

{else}

<li class="{$extraclasses} gridder-item gridder-existing" id="{if $uniqueid}{$uniqueid}{else}item-{$id}{/if}" data-id="{$id}" data-mtime="{$mtime}"  {if $author}data-author="{$author}"{/if}  data-title="{$title}"{if $extradata}{foreach from=$extradata item=d key=k} data-{$k}="{$d}"{/foreach}{/if} data-description="{$description}">



    <div class="plan blockinstance {if $studentselected}open{/if}">
        <div class="epsp-field row" data-viewid="{$plan.viewid}">
            <div class="col-md-5">
                <div class="row">
                    <h2 class="planauthor col-md-5" ><a href="{$wwwroot}user/view.php?id={$plan.owner}">{$author}</a></h2>
                    <div class="toggle-subfields col-md-7">
                        <h2 class="plantitle" >
                            <a href="{$wwwroot}view/view.php?id={$plan.viewid}">{$title}</a>
                            <i class="fa"> . </i>
                        </h2>
                    </div>
                </div>
            </div>
        {if $plan.latest}
            <div class="latest col-md-7">
                <div class="row">
                    <div class="col-md-4">
                        <h3 class="completable">{$plan.latest->title}</h3>
                    </div>

                    <div class="col-md-4">
                        <div class="progress-wrapper">
                            <div class="progress">
                                <div class="progress-barz" role="progressbar" aria-valuenow="{$plan.latest->bystudentprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$plan.latest->bystudentprog}%;">
                                    <span>{$plan.latest->bystudent}  / {$plan.latest->totalgoals}</span>
                                </div>
                            </div>
                        </div>
                        <div class="percent">{$plan.latest->bystudentprog}%</div>
                    </div>
                    <div class="col-md-4">
                        <div class="progress-wrapper">
                            <div class="progress">
                                <div class="progress-barz" role="progressbar" aria-valuenow="{$plan.latest->byteacherprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$plan.latest->byteacherprog}%;">
                                    <span>{$plan.latest->byteacher}  / {$plan.latest->totalgoals}</span>
                                </div>
                            </div>
                        </div>
                        <div class="percent">{$plan.latest->byteacherprog}%</div>
                    </div>
                </div >
            </div>
        {/if}
        </div>
        <div class="fields subfields" {if $studentselected}style="display: block;"{/if}>
            <!-- div class="row heading">
                <div class="title col-md-6">{str tag=field_subtitles_goal section="artefact.epsp"}</div>
                <div class="title col-md-3">
                    <span>{str tag=markedcomplete section="artefact.epsp"}</span>
                </div>
                <div class="title col-md-3">
                    <span>{str tag=markedcompletebyteacher section="artefact.epsp"}</span>
                </div>
            </div -->
            {foreach from=$plan.fields item=field}
                <div class="row">
                    <div class="epsp-field epsp-field-subtitle col-md-3" data-fieldid="{$field->id}">
                        <h3 class="completable">{$field->title}</h3>
                    </div>
                    <div class="epsp-field col-md-3">
                        <div class="titlerow">{str tag=markedcomplete section="artefact.epsp"}</div>
                        <div class="percent-wrapper">
                            <div class="progress-wrapper">
                                <div class="progress">
                                    <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->bystudentprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->bystudentprog}%;">
                                        <span>{$field->sums->bystudent} / {$field->sums->totalgoals}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="percent">{$field->sums->bystudentprog}%</div>
                        </div>
                    </div>
                    <div class="epsp-field col-md-3">
                        <div class="titlerow">{str tag=markedcompletebyteacher section="artefact.epsp"}</div>
                        <div class="percent-wrapper">
                            <div class="progress-wrapper">
                                <div class="progress">
                                    <div class="progress-barz" role="progressbar" aria-valuenow="{$field->sums->byteacherprog}" aria-valuemin="0" aria-valuemax="100" style="width: {$field->sums->byteacherprog}%;">
                                        <span>{$field->sums->byteacher}  / {$field->sums->totalgoals}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="percent">{$field->sums->byteacherprog}%</div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>

</li>
{/if}