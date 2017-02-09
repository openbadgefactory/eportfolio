{include file="pageactionitems.tpl" page=$page assign=action_items}

{if $page.submittedto}
    {assign var="description" value=$page.submittedto|clean_html|safe}
{elseif $page.type == "profile"}
    {str tag=profiledescription assign=description}
{elseif $page.type == "dashboard"}
    {str tag=dashboarddescription assign=description}
{elseif $page.type == "grouphomepage"}
    {str tag=grouphomepagedescription section=view assign=description}
{elseif $page.description}
    {assign var="description" value=$page.description|strip_tags|clean_html|safe}
{/if}

{if $action_items|trim != ''}
    <div class="action-button">
        <span class="inner popovertoggle"></span>
        <div class="action-items" style="display: none">{$action_items|safe}</div>
    </div>
{/if}

<div class="page-container">
    <div class="gridder-item-top">
        <h3><a href="{$page.fullurl}">{$page.displaytitle}</a></h3>
    </div>
    <div class="author"><a href="{$WWWROOT}user/view.php?id={$page.user->id}">{$page.user->firstname} {$page.user->lastname}</a></div>
    <!--<div class="description">
        <div class="wrapper">
            {if $description}
                {$description}
            {else}
                {str tag=nodescription section="interaction.pages"}
            {/if}
        </div>
    </div>-->

    <div class="publicity">
        <div class="publicity-text">
            <div class="publicity-icon"></div>
            <div class="publicity-status">
                Tämän julkaisun tila on: <br/>
                    {assign var=shared value=' '|explode:$page.shared_to}
                    {str tag=$shared[0] section="interaction.pages"}
            </div>
        </div>
        {if $page.layout}
            <a href="{$WWWROOT}view/layout.php?id={$page.id}" title="{str tag=layout section='interaction.pages'}">
                <img alt="{str tag=layout section='interaction.pages'}" title="{str tag=layout section='interaction.pages'}" src="{$WWWROOT}thumb.php?type=viewlayout&vl={$page.layout}" class="page-layout" /> </a>
        
            {/if}
    </div>

</div>
