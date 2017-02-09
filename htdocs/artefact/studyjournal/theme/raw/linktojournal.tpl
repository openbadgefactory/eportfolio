<p>{str tag=linktoportfoliodescription section="artefact.studyjournal"}</p>

{if $collections}
    {foreach from=$collections item=type}
        <h3>{$type.title}</h3>

        <ul id="attach-collections">
            {foreach from=$type.collections item=collection}
                <li>
                    <input type="checkbox" id="link-collection-{$collection.id}" data-title="{$collection.name}" data-url="{$collection.url}" value="{$collection.id}" />
                    <label for="link-collection-{$collection.id}">
                        {$collection.name} - <a href="{$collection.url}" target="_blank">{str tag=showview section="artefact.studyjournal"}</a>
                    </label>
                </li>
            {/foreach}
        </ul>
    {/foreach}
{/if}

{if $views}
    {foreach from=$views item=type}
        <h3>{$type.title}</h3>

        <ul class="attach-views">
            {foreach from=$type.views item=view}
                <li>
                    <input type="checkbox" id="link-view-{$view.id}" data-title="{$view.name}" data-url="{$view.url}" value="{$view.id}" />
                    <label for="link-view-{$view.id}">
                        {$view.name} - <a href="{$view.url}" target="_blank">{str tag=showview section="artefact.studyjournal"}</a>
                    </label>
                </li>
            {/foreach}
        </ul>
    {/foreach}
{/if}