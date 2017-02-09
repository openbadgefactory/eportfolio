{include file="header.tpl"}

{if $GROUP}
    <h2>{str tag=groupviews section=view}</h2>
{/if}

<div class="gridder-filters">

    {if $fulltextsearch}
        <div class="filter-row search-filter">
            <!--<label class="search-label" for="page-search">{str tag="search"}</label>-->
            <input type="search" id="page-search" placeholder="{str tag="pagesearchhelp" section="interaction.pages"}" />
            <span id="search-spinner"></span>
        </div>
    {/if}
    {if $tags !== false}
        <div class="filter-row view-tags filter-horizontal">
            <span class="filter-label">{str tag="tags"}</span>
            <div class="filter-buttons tag-buttons">
                <button data-value="all" id="show-all" class="all active">{str tag="all" section="interaction.pages"}</button>
                {foreach from=$tags item=tag}
                    <button data-value="{$tag}">{$tag}</button>
                {/foreach}
            </div>
        </div>
    {/if}
    

    <div class="filter-row view-shared filter-horizontal" id="filter-publicity">
        <span class="filter-label">{str tag=show section="interaction.pages"}</span>
        <div class="filter-buttons">
            <button data-value="all" class="active">{str tag="all" section="interaction.pages"}</button>
            <button data-value="private">{str tag="notshared" section="interaction.pages"}</button>
            <button data-value="published">{str tag="sharedinsystem" section="interaction.pages"}</button>
            <button data-value="public">{str tag="sharedinpublic" section="interaction.pages"}</button>
        </div>
    </div>
        
    <div class="filter-row sort-by filter-horizontal">
        <div class="filter-label">{str tag="sortby" section="interaction.pages"}:</div>
        <div class="filter-sorting">
            <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
            <label for="sort-by-modified">{str tag="sortbymodified" section="interaction.pages"}</label>
            <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
            <label for="sort-by-title">{str tag="sortbytitle" section="interaction.pages"}</label>
        </div>
    </div>

</div>

<ul id="pages" class="gridder">
    {if $createviewform}
    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{$createviewform|safe}</span>
        </div>
    </li>
    {/if}
    <li class="shuffle-sizer"></li>
</ul>
<div id="gridder-pagination"></div>
    
{include file="footer.tpl"}