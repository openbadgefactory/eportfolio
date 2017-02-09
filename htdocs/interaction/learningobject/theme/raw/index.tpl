{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row">
        <div class="search-filter">
            <input type="search" id="learningobject-search" placeholder="{str tag="searchlearningobjects" section="interaction.learningobject"}" />
            <input type="search" id="teacher-search" placeholder="{str tag="searchlearningobjectsbyteachername" section="interaction.learningobject"}" />
            <span id="search-spinner"></span>
        </div>
    </div>

    <div class="filter-selects">
        {$searchform|safe}
    </div>

    <div class="filter-row filter-horizontal view-shared">
        <div class="filter-label">{str tag="show"}:</div>
        <div class="tag-buttons">
            {if $is_teacher}
                <button id="own" class="own">{str tag="own" section="interaction.learningobject"}</button>
            {/if}
            <button id="published">{str tag="sharedinsystem" section="interaction.learningobject"}</button>
            <button id="public">{str tag="sharedinpublic" section="interaction.learningobject"}</button>
            <button id="all" class="all active">{str tag="all" section="interaction.learningobject"}</button>
        </div>
    </div>

    <div class="filter-row filter-horizontal sort-by">
        <div class="filter-label">{str tag="sortby" section="interaction.learningobject"}</div>
        <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
        <label for="sort-by-modified">{str tag="sortbymodified" section="interaction.learningobject"}</label>
        <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
        <label for="sort-by-title">{str tag="sortbytitle" section="interaction.learningobject"}</label>
    </div>

</div>

<ul id="pages" class="gridder">
    {if $is_teacher}
        <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
            <div class="gridder-new-icon">
                <span></span>
            </div>
            <div class="gridder-new-text">
                <span>{str tag=createnewlearningobject section="interaction.learningobject"}</span>
            </div>
        </li>
    {/if}
    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="footer.tpl"}