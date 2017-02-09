<li class="{$extraclasses} gridder-item gridder-existing {$publicity} {if $group}grouppage-item{/if}" id="{if $uniqueid}{$uniqueid}{else}item-{$id}{/if}" data-id="{$id}" data-publicity="{$publicity}" data-mtime="{$mtime}" data-tags='{$tags|safe}' data-type="{$type}" {if $view}data-view="{$view}"{/if} {if $group}data-groupid='{$group}'{/if} data-title="{$title}"{if $extradata}{foreach from=$extradata item=d key=k} data-{$k}="{$d}"{/foreach}{/if} data-description="{$description}">

    {$headdata|safe}

    <div class="gridder-item-top">
        <h3>
            <a href="{if $url}{$url}{else}#item-{$id}{/if}" {$titleparams}>{$title|str_shorten_text:50:true|safe}</a>
        </h3>
    </div>

    <div class="author">
        {if $group}
            <a title="{$author}" href="{$WWWROOT}group/view.php?id={$group}">
        {else}
            <a title="{$author}" href="{$WWWROOT}user/view.php?id={$author_id}">
        {/if}
        {$author}</a>
    </div>

    <div class="publicity{if !$cannoteditaccess} access-editable" title="{str tag='editaccess' section='view'}{/if}">
        <div class="publicity-icon"></div>
        <div class="publicity-status">
            <div class="publicity-title">{$publicitydescription}</div>
            <div class="publicity-value">{$publicityvalue}</div>
        </div>
    </div>

    {if $menuitems}
        <div class="action-button">
            <span class="inner popover-toggle"></span>
            <div class="action-items" style="display: none">
                <ul data-itemid="{$id}">
                {foreach from=$menuitems item=menuitem}
                    <li>
                    {if $menuitem.form}
                        <span {if $menuitem.classes} class="{$menuitem.classes}"{/if}>{$menuitem.form|safe}</span>
                    {else}
                        <a href="{$menuitem.url}" {if $menuitem.classes} class="{$menuitem.classes}"{/if}>
                            {$menuitem.title}
                        </a>
                    {/if}
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
    {/if}
</li>
