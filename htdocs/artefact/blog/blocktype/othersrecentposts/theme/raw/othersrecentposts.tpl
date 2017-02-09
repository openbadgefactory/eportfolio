{if $mostrecent}
<div class="othersrecentblogpost">
    <div class="itemlist viewlist fullwidth listing">
{foreach from=$mostrecent item=post name=othersrecent}
        <div class="{cycle values='r0,r1'} listrow{if $dwoo.foreach.othersrecent.iteration > 5} extra hidden{/if}">
            <h3 class="title">
                <a href="{$WWWROOT}view/artefact.php?artefact={$post->parent}&amp;view={$post->view}">{$post->parenttitle}:</a> <a href="{$WWWROOT}view/artefact.php?artefact={$post->blogpost}&amp;view={$post->view}">{$post->title}</a>
            </h3>
            <div class="groupuserdate">
                 <a href="{profile_url($post->owner)}">{$post->authorname} </a>
                <span class="postedon">- {str tag='postedon' section='artefact.blog'} {$post->displaydate} </span>
            </div>
        </div>
{/foreach}
</div>

{if count($mostrecent) > 5}
    <div class="morelinkwrap">
        <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
    </div>
{/if}

</div>
{else}
    <p class="message">{str tag=noresults section="artefact.blog"}</p>
{/if}
