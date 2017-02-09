    <div class="blockinstance cb bt-{$blocktype}{if $retractable} retractable{/if}" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header{if $retractable && $retractedonload} retracted{/if}">
            {if $retractable}
                <span class="arrow"></span>
            {/if}
            {if $titlelinkurl}
                <h2 class="title">{if $titlelinkurl}<a href="{$titlelinkurl}" title="{str tag=clickformoreinformation section=view}">{/if}{$title}{if $titlelinkurl}</a>{/if}
            {else}
                <h2 class="title">{if $viewartefacturl}<a href="{$viewartefacturl}" title="{str tag=clickformoreinformation section=view}">{/if}{$title}{if $viewartefacturl}</a>{/if}
            {/if}
            {if $feedlink}&nbsp;<a href="{$feedlink}"><img class="feedicon" src="{theme_url filename='images/feed.png'}"></a>{/if}</h2>
            <span class="cb"></span>
        </div>{/if}
        <div class="blockinstance-content{if $retractable && $retractedonload} js-hidden{/if}">
            {$content|safe}
        </div>
    </div>
    {if $retractable}
        <script>
            {include file="view/retractablejs.tpl" id=$id}
        </script>
    {/if}
