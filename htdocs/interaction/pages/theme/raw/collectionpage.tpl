{if $page.description}
    {assign var="description" value=$page.description|strip_tags|clean_html|safe}
{/if}
{if $page.is_editable}
    <div class="action-button">
        <span class="inner popovertoggle"></span>
        <div class="action-items" style="display: none">
            <ul class="page-action-items">
                <li><a href="{$WWWROOT}collection/edit.php?id={$page.id}">{str tag=editcontent section=view}</a></li>
                <li><a href="{$WWWROOT}collection/views.php?id={$page.id}">{str tag=manageviews section=collection}</a></li>
                {*< EKAMPUS*}
                <li><a href="{$WWWROOT}view/access.php?id={$subpage.views.0.id}&backto=interaction/pages/collections.php">{str tag=editaccess section=view}</a></li>
                {* EKAMPUS >*}
                <li><a href="{$WWWROOT}collection/delete.php?id={$page.id}">{str tag=deletethisview section=view}</a></li>
            </ul>
        </div>
    </div>
{/if}

<div class="page-container">

    <h3><a href="{$subpage.views.0.fullurl}">{$subpage.name}</a></h3>

    <div class="description">
        <div class="wrapper">
            <div class="descriptioncontent">
                {if $description}
                    {$description}
                {else}
                    {str tag=nodescription section="interaction.pages"}
                {/if}
            </div>
            <div class="collectionpages">
                <label>{str tag=Pages section="interaction.pages"}:</label>
                {foreach from=$subpage.views item=page}              
                    <a href="{$page.fullurl}">{$page.displaytitle}</a>,         
                {/foreach}
            </div>
        </div>
        
    </div>
    
    <div class="publicity">
        <div class="publicity-text">
            <div class="publicity-icon"></div>
            <div class="publicity-status">
                {*dump $subpage*}
                {if $subpage.shared_to == 'published'}
                    <a href="#" class="show-shared-to">{str tag="sharedto_$subpage.shared_to" section="interaction.pages"}</a>
                    <div class="shared-to" style="display: none">
                        <div class="shared-to-title">{str tag=sharewith section=view}</div>
                        <div class="shared-to-content">
                            <ul>
                            {foreach $subpage.access item=accessitem}
                                <li>
                                    {if $accessitem.type == "token"}
                                        <a href="{$WWWROOT}view/view.php?t={$accessitem.id}">{str tag=token section=view}</a>
                                    {elseif $accessitem.type == "friends"}
                                        {str tag=Friends section=group}
                                    {elseif $accessitem.type == "user"}
                                        {str tag=User} <a href="{$WWWROOT}user/view.php?id={$accessitem.id}">{$accessitem.name}</a>
                                    {elseif $accessitem.type == "group"}
                                        {str tag=Group section=group} <a href="{$WWWROOT}group/view.php?id={$accessitem.id}">{$accessitem.name}</a>
                                    {elseif $accessitem.type == "institution"}
                                        {str tag=institution}  {$accessitem.name}
                                    {/if}
                                </li>
                            {/foreach}
                            </ul>
                        </div>
                    </div>
                {else}
                    {str tag="sharedto_$subpage.shared_to" section="interaction.pages"}
                {/if}
            </div>
        </div>
        {if $page.layout}
            <img alt="{str tag=layout section="interaction.pages"}" title="{str tag=layout section="interaction.pages"}" src="{$WWWROOT}thumb.php?type=viewlayout&vl={$page.layout}" class="page-layout" />
        {/if}
    </div>

</div>