{include file="header.tpl"}

<p>{str tag=oauthdesc section=artefact.cloudfile}</p>
<hr>
{foreach from=$services, item=service}
    <h3>{$service.title}{if $service.authorized} - <em>{str tag=authorized section=artefact.cloudfile}</em>{/if}</h3>
    {if $service.authorized}
        <div style="margin: 20px 0">{$service.resyncform|safe}</div>
        <div>{$service.unlinkform|safe}</div>
    {else}
        <div>{$service.linkform|safe}</div>
    {/if}
    <hr>
{/foreach}

{include file="footer.tpl"}
