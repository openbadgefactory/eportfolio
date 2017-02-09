<div id="recentforumpostsblock">
    {if !$posts}
        <p class="message">{str tag=noforumpostsyet section=interaction.forum}</p>
    {else}
        <table class="fullwidth itemlist">
            {counter assign=i start=0 print=false}
            {foreach from=$posts item=post name=recentforum}
                {counter}
                <tr class="{cycle values='r0,r1'}{if $dwoo.foreach.recentforum.iteration > 5} extra hidden{/if}">
                    <td>
                        <h3 class="title">
                            <a class="morelink" href="{$WWWROOT}interaction/forum/index.php?group={$post->group}">{$post->groupname}:</a>
                            <a href="{$WWWROOT}interaction/forum/topic.php?id={$post->topic}&post={$post->id}">{$post->topicname}</a>
                        </h3>
                        <div class="detail">{$post->body|str_shorten_html:100:true|safe}</div>
                        <div class="groupuserdate">
                            <a href="{profile_url($post->author)}">{$post->author|display_name}</a>
                            <span class="postedon"> - {$post->relativedate}</span>
                        </div>
                    </td>
                </tr>
            {/foreach}
        </table>

        {if count($posts) > 5}
            <div class="morelinkwrap">
                <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
            </div>
        {/if}
    {/if}

    <div class="cb"></div>
</div>
