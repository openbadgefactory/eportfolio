<div class="field-comment" data-id="{$comment->id}">
    {if $deletable}
        <div class="fr btns">
            <a href="#" class="delete-comment" title="{str tag=delete}">
                <img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}" />
            </a>
        </div>
    {/if}
    <div class="author">
        <a href="{$WWWROOT}user/view.php?id={$comment->author}">{$authorname}</a>
        <span class="postedon"> - {$comment->ctime|format_date}</span>
    </div>
    <div class="detail">{$comment->description|safe}</div>
</div>