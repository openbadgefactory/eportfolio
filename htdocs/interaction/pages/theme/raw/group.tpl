{include file="groupactionitems.tpl" returntoabsolute="interaction.pages.mygroups" group=$group assign=action_items}
{assign var=description value=$group->description|strip_tags|clean_html|safe}

{if $action_items|trim != ''}
    <div class="action-button">
        <span class="inner popovertoggle"></span>
        <div class="action-items" style="display: none">{$action_items|safe}</div>
    </div>
{/if}
<div class="page-container {if $group->myinst}myinst{/if}">
    <h3><a href="{group_homepage_url($group)}">{$group->name}</a></h3>
    <div class="description">
        <div class="wrapper">
            <a href="{$WWWROOT|cat:"user/view.php?id="}{$group->admins[0]->id}" title="{str tag=adminuser section='interaction.pages'}">{$group->admins[0]->firstname} {$group->admins[0]->lastname}</a><br/>
            {if $description}
                {$description}
            {else}
                {str tag=nodescription section="interaction.pages"}
            {/if}
        </div>
    </div>
    <div class="publicity">
        <div class="publicity-icon"></div>
        <div class="publicity-text">
            <p class="publicity-status">
                {if $group->grouptype == 'institution'}
                    {str tag=grouptypeinstitution section="interaction.pages"}
                {elseif $group->jointype == 'open'}
                    {str tag=jointypeopen section="interaction.pages"}
                {elseif $group->jointype == 'approve' && $group->request == '1'}
                    {str tag=jointyperequest section="interaction.pages"}
                {elseif $group->jointype == 'approve' && $group->request == '0'}
                    {str tag=jointypeinvite section="interaction.pages"}
                {elseif $group->jointype == 'controlled'}
                    {str tag=jointypecontrolled section="interaction.pages"}
                {else}
                    -
                {/if}
            </p>
            
        </div>
        <div class="members">{str tag=nmembers section=group arg1=$group->membercount}</div>
    </div>

</div>