<div class="tabswrap">
    <h3 class="rd-tab-title">{str tag="tabs"}<span class="rd-tab"></span></h3>
    <ul class="in-page-tabs" id="edit-view-tabs">
        {if $edittitle}
            <li {if $selected == 'title'} class="current-tab"{/if}>
                <a{if $selected == 'title'} class="current-tab"{/if} href="{$WWWROOT}view/edit.php?id={$viewid}{if $new}&new=1{/if}{if $from}&from={$from}{/if}{if $backto}&backto={$backto}{/if}">{str tag=edittitleanddescription section=view}</a>
            </li>
        {/if}
        <li {if $selected == 'layout'} class="current-tab"{/if}>
            <a{if $selected == 'layout'} class="current-tab"{/if} href="{$WWWROOT}view/layout.php?id={$viewid}{if $new}&new=1{/if}{if $from}&from={$from}{/if}{if $backto}&backto={$backto}{/if}">{str tag=editlayout section=view}</a>
        </li>
        <li {if $selected == 'content'} class="current-tab"{/if}>
            <a{if $selected == 'content'} class="current-tab"{/if} href="{$WWWROOT}view/blocks.php?id={$viewid}{if $new}&new=1{/if}{if $from}&from={$from}{/if}{if $backto}&backto={$backto}{/if}">{str tag=editcontent section=view}</a>
        </li>
        {if can_use_skins()}
            <li {if $selected == 'skin'} class="current-tab"{/if}>
                <a{if $selected == 'skin'} class="current-tab"{/if} href="{$WWWROOT}view/skin.php?id={$viewid}{if $new}&new=1{/if}{if $from}&from={$from}{/if}{if $backto}&backto={$backto}{/if}">{str tag=chooseskin section=skin}</a>
            </li>
        {/if}
        {* <EKAMPUS *}
        {if !$from}
            <li class="displaypage{if $selected == 'displaypage'} current-tab{/if}">
                <a{if $selected == 'displaypage'} class="current-tab"{/if} href="{$displaylink}&showtabs=true{if $backto}&backto={$backto}{/if}">{str tag=displayview section=view} </a>
            </li>
        {/if}
        {if $edittitle || $viewtype == 'profile' || $viewtype == 'grouphomepage'}
            <li class="{if $selected == 'share'}current-tab {/if}sharepage">
                <a{if $selected == 'share'} class="current-tab"{/if} href="{$WWWROOT}view/access.php?id={$viewid}{if $new}&new=1{/if}{if $from}&from={$from}{/if}{if $backto}&backto={$backto}{/if}">{str tag=shareview section=view} </a>
            </li>
        {* EKAMPUS> *}
        {/if}
        </ul>
    </div>

{* <EKAMPUS *}
<script>
$j(document).ready(function () {
    $j('#edit-view-tabs').children().each(function (index) {
        var link = $j(this).children('a');
        var tabtitle = link.text();

        link.text((index + 1) + '. ' + tabtitle);
    });
});
</script>
{* EKAMPUS> *}
