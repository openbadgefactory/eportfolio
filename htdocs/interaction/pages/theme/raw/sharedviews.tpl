{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row">
        <div class="search-filter">
            <input type="search" id="gallery-search" placeholder="{str tag="gallerysearchhelp" section="interaction.pages"}" />
            {if !$returns || ($returns && $teacher)}
            <input type="search" id="student-search" placeholder="{str tag="studentsearchhelp" section="interaction.pages"}" />
            {/if}
            <span id="search-spinner"></span>
        </div>
        <div class="filter-selects">
            {$searchform|safe}
        </div>
    </div>
   {if !$returns}
    <div class="filter-row view-shared">
        <div class="filter-label">{str tag="show"}:</div>
        <div class="tag-buttons">
            <button id="all" class="all active">{str tag="all" section="interaction.pages"}</button>
            <button id="own" class="own">{str tag="own" section="interaction.pages"}</button>
            <button id="published">{str tag="sharedinsystem" section="interaction.pages"}</button>
            <button id="public">{str tag="sharedinpublic" section="interaction.pages"}</button>
        </div>
    </div>
    {/if}
    <div class="filter-row view-types">
        <div class="tag-buttons">
            <button id="show-all" class="all active">{str tag="all" section="interaction.pages"}</button>
            {foreach from=$types key=k item=type}
                <button id="{$k}">{$type}</button>
            {/foreach}
        </div>
    </div>

    <div class="filter-row sort-by">
        <div class="filter-label">{str tag="sortby" section="interaction.pages"}</div>
        <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
        {if !$returns}
        <label for="sort-by-modified">{str tag="sortbymodified" section="interaction.pages"}</label>
        {else}
        <label for="sort-by-modified">{str tag="sortbylastreturned" section="interaction.pages"}</label>
        {/if}
        <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
        <label for="sort-by-title">{str tag="sortbytitle" section="interaction.pages"}</label>
    </div>

</div>

<ul id="pages" class="gridder">
    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>
{include file="footer.tpl"}