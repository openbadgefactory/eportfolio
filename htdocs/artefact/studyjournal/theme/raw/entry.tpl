{capture "notitle"}
    {str tag=noentrytitle section="artefact.studyjournal"}
{/capture}

<div class="studyjournal-entry">
    <h3 class="studyjournal-title title">
        {if $view}<a href="{$WWWROOT}view/artefact.php?view={$view}&artefact={$entry->get('id')}">{/if}
            {default $entry->get('title') $.capture.notitle}
            {if $view}</a>{/if}
    </h3>

    <div class="studyjournal-entry-date">{$entry->get_postdate()}</div>
    <div class="studyjournal-entry-content">
        {foreach from=$entry->get_fields($view) item=field}
            <h4>{$field->title}</h4>
            <div class="studyjournal-entry-field-value">
                {if $field->type == 'vibe'}
                    <div class="vibe-value vibe-value-{$field->value}">{$field->value}</div>
                {else}
                    {$field->value|clean_html|safe}
                {/if}
            </div>
        {/foreach}

        {if $entry->has_artefacts()}
            {if $entry->has_collections()}
                <h4>{str tag=attachedcollections section="artefact.studyjournal"}</h4>
                <ul>
                    {foreach from=$entry->get_collections() item=collection}
                        <li><a href="{$collection.url}">{$collection.name}</a></li>
                        {/foreach}
                </ul>
            {/if}

            {if $entry->has_views()}
                <h4>{str tag=attachedviews section="artefact.studyjournal"}</h4>
                <ul>
                    {foreach from=$entry->get_views() item=v}
                        <li><a href="{$v.url}">{$v.name}</a></li>
                        {/foreach}
                </ul>
            {/if}
        {/if}

        {if $entry->has_files()}
            <div id="entryfiles-{$entry->get('id')}">
                <table class="cb attachments fullwidth">
                    <thead class="expandable-head">
                        <tr>
                            <td colspan="2">
                                <a class="toggle" href="#">{str tag=attachedfiles section="artefact.blog"}</a>
                                <span class="fr">
                                    <img class="fl" src="{theme_url filename='images/attachment.png'}" alt="{str tag=Attachments section=artefact.resume}" />
                                    {$entry->get_files()|count}
                                </span>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="expandable-body">
                        {foreach from=$entry->get_files() item=file}
                            <tr class="{cycle values='r1,r0'}">
                                <td class="icon-container"><img src="{$file->icon}" alt="" /></td>
                                <td>
                                    <h3 class="title">
                                        <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}{if $view}&view={$view}{/if}">{$file->title}</a>
                                        <span class="description">({$file->size|display_size})</span>
                                    </h3>
                                    <div class="detail">
                                        {$file->description}
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</div>