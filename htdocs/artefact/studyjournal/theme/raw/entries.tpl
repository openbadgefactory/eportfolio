{foreach from=$entries item=entry}
    <li class="post">
        {include file="entry.tpl" entry=$entry view=$view}
    </li>
{/foreach}