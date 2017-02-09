{if $views}
<div class="itemlist viewlist fullwidth listing">
  {foreach from=$views item=view name=othersviews}
    <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.othersviews.iteration > 5} extra hidden{/if}">
            <h3 class="title"><a href="{$view.fullurl}">{$view.title}</a></h3>
            <div class="detail">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div>
            {if $view.sharedby}
            <div class="groupuserdate">
                {if $view.group && $loggedin}
                  <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
                {elseif $view.owner && $loggedin}
                  <a href="{profile_url($view.user)}">{$view.sharedby}</a>
                {else}
                  {$view.sharedby}
                {/if}
             	<span class="postedon">
                  - {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                  {$view.mtime|strtotime|format_date:'strftimedate'}</span>
            </div>
            {/if}
        </div>
  {/foreach}
</div>

{if count($views) > 5}
    <div class="morelinkwrap">
        <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
    </div>
{/if}

{else}
    <p class="message">{str tag=noviews section=view}</p>
{/if}
