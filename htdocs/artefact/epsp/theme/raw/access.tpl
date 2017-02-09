{include file="header.tpl"}

<div id="access-wrapper">
    <div id="access-controls">
        <form id="access-potential">
            <label for="type">{str tag=search}</label>
            <select id="access-type">
                <option value="user">{str tag=users}</option>
                <option value="group">{str tag=recipientgroups section=activity}</option>
                <option value="institution">{str tag=institutions}</option>
            </select>
            <input type="text" id="access-query" />
            <button id="do-search" class="btn-search" type="submit">{str tag=go}</button>
        </form>

        <table id="search-results">
            <tbody></tbody>
        </table>
    </div>

    <div id="access-current">
        <h2>{str tag=useraccess section="artefact.epsp"}</h2>
        <div id="access-added-users">
            <p class="no-items"{if $useraccess} style="display: none"{/if}>
                {str tag=nouseraccess section="artefact.epsp"}
            </p>
            <ul class="access-items">
                {if $useraccess}
                    {foreach from=$useraccess item=accessitem}
                        <li id="user-{$accessitem->user}">
                            <div>
                                <img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=25&maxheight=25&id={$accessitem->user}" />
                                <span>{$accessitem->name}</span>
                            </div>
                            <button type="button" class="remove">{str tag=remove}</button>
                            <input type="hidden" value="{$accessitem->user}" />
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>

        <h2>{str tag=groupaccess section="artefact.epsp"}</h2>
        <div id="access-added-groups">
            <p class="no-items"{if $groupaccess} style="display: none"{/if}>
                {str tag=nogroupaccess section="artefact.epsp"}
            </p>
            <ul class="access-items">
                {if $groupaccess}
                    {foreach from=$groupaccess item=accessitem}
                        <li id="group-{$accessitem->group}">
                            <div>{$accessitem->name}</div>
                            <button type="button" class="remove">{str tag=remove}</button>
                            <input type="hidden" value="{$accessitem->group}" />
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>

        <h2>{str tag=institutionaccess section="artefact.epsp"}</h2>
        <div id="access-added-institutions">
            <p class="no-items"{if $institutionaccess} style="display: none"{/if}>
                {str tag=noinstitutionaccess section="artefact.epsp"}
            </p>
            <ul class="access-items">
                {if $institutionaccess}
                    {foreach from=$institutionaccess item=accessitem}
                        <li id="institution-{$accessitem->institution}">
                            <div>{$accessitem->displayname}</div>
                            <button type="button" class="remove">{str tag=remove}</button>
                            <input type="hidden" value="{$accessitem->institution}" />
                        </li>
                    {/foreach}
                {/if}
            </ul>
        </div>

        <button type="button" class="button" id="save-access">{str tag=save}</button>
        <a href="{$WWWROOT}artefact/epsp/" class="btn">{str tag=cancel}</a>
    </div>
</div>


{include file="footer.tpl"}