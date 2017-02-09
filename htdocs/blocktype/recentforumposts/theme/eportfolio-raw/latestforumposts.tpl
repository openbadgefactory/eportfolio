{if count($posts) > 0}
    {foreach from=$posts item=postinfo} 
        <table class="fullwidth" id="latestforumposts">
            <tr class="{cycle values='r0,r1'}">
                <td><h3 class="title"><a href="{$WWWROOT}interaction/forum/topic.php?id={$postinfo->topic|escape}&post={$postinfo->id}">{$postinfo->topicname}</a></h3>
                <div class="detail">{$postinfo->body|str_shorten_html:100:true|safe}</div></td>
                <td class="valign right s"><a href="{profile_url($postinfo->author)}"><img src="{profile_icon_url user=$postinfo->author maxheight=20 maxwidth=20}" alt="{str tag=profileimagetext arg1=$postinfo->author|display_default_name}"> {$postinfo->author|display_name}</a>
                </td>
            </tr>
        </table>
    {/foreach}
{else}
    <table class="fullwidth"><tr class="{cycle values='r0,r1'}">
        <tr>
            <td align="center">{str tag=noforumpostsyet section=interaction.forum}</td>
        </tr>
    </table>
{/if}

<div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}interaction/forum/index.php?group={$group->id}">{str tag=gotoforums section=interaction.forum} &raquo;</a></div>
