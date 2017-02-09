{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row">
        <div class="search-filter">
            <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.epsp"}" />
            <input type="search" id="author-search" placeholder="{str tag=authorsearchhelp section="artefact.epsp"}" />
            <span id="search-spinner"></span>
        </div>
    </div>

    {if $is_teacher}
        <div class="filter-row filter-horizontal">
            <div class="filter-label">{str tag=createdininstitution section="artefact.epsp"}</div>
            {if $institutions}
                <select id="author-institution">
                    {foreach from=$institutions item=institution key=id}
                        <option value="{$id}">{$institution}</option>
                    {/foreach}
                </select>
            {/if}
        </div>


        <div class="filter-row filter-horizontal view-shared">
            <div class="filter-label">{str tag=show section="artefact.epsp"}</div>
            <div class="tag-buttons">
                <button data-value="all" class="active">{str tag=all section="artefact.studyjournal"}</button>
                <button data-value="private">{str tag=private section="artefact.epsp"}</button>
                <button data-value="published">{str tag=published section="artefact.epsp"}</button>
                <button data-value="own">{str tag=owntemplates section="artefact.epsp"}</button>
                <button data-value="others">{str tag=otherstemplates section="artefact.epsp"}</button>
            </div>
        </div>
    {/if}

    <div class="filter-row sort-by filter-horizontal">
        <div class="filter-label">{str tag="sortby" section="artefact.epsp"}</div>
        <div class="filter-sorting">
            <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
            <label for="sort-by-modified">{str tag="sortbymodified" section="artefact.epsp"}</label>
            <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
            <label for="sort-by-title">{str tag="sortbytitle" section="artefact.epsp"}</label>
        </div>
    </div>

</div>

<ul id="epsps" class="gridder">
    {if $is_teacher}
        <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
            <div class="gridder-new-icon">
                <span></span>
            </div>
            <div class="gridder-new-text">
                <span>{str tag=createnewepsp section="artefact.epsp"}</span>
            </div>
        </li>
    {/if}

    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="footer.tpl"}