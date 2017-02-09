{include file="header.tpl"}
{if !$USER->is_logged_in()}
    {include file="loggedouthomeinfo.tpl" url=$url}
{else}
    {if !$dashboardview}
        {$page_content|clean_html|safe}
    {/if}
{/if}
{if get_config('homepageinfo') && (!$USER->is_logged_in() || $USER->get_account_preference('showhomeinfo'))}
    {include file="homeinfo.tpl" url=$url}
{/if}
{if $dashboardview}
    {include file="user/dashboard.tpl"}
{/if}
{include file="footer.tpl"}
