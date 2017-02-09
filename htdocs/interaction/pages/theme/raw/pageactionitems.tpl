{* Use capture here so we can later see, whether the list will have any
   items to show. *}
    {capture "actionitems"}
        {*< KYVYT -> blog editing goes to title and description modification page*}
        {if $page.is_editable}
            {if $page.type == "blog"}
                <li><a href="{$WWWROOT}view/edit.php?id={$page.id}">{str tag=editcontent section=view}</a></li>
            {else}
                <li><a href="{$WWWROOT}view/blocks.php?id={$page.id}">{str tag=editcontentandlayout section=view}</a></li>
            {/if}
        {* KYVYT >*}
        {*< EKAMPUS*}
            <li><a href="{$WWWROOT}view/access.php?id={$page.id}&backto=interaction/pages/">{str tag=editaccess section=view}</a></li>
        {* EKAMPUS >*}
        {/if}
        {if $page.is_removable}
        <li><a href="{$WWWROOT}view/delete.php?id={$page.id}">{str tag=deletethisview section=view}</a></li>
        {/if}
    {/capture}
{if $.capture.actionitems|trim != ''}
    <ul class="page-action-items">
        {$.capture.actionitems|safe}
    </ul>
{/if}