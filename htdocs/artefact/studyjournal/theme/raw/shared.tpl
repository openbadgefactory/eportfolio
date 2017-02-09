{include file="header.tpl"}

<h1>{str tag=studentstudyjournals section="artefact.studyjournal"}</h1>

<div class="gridder-filters">

    <div class="filter-row search-filter">
        <input type="search" id="page-search" placeholder="{str tag=filter section="artefact.studyjournal"}" />
        <input type="search" id="student-search" placeholder="{str tag="filterbyname" section="artefact.studyjournal"}" />
    </div>
    
    <div class="filter-row filter-horizontal" id="filter-publicity">
        <span class="filter-label">{str tag=show section="artefact.studyjournal"}</span>
        <button data-value="all" class="active">{str tag=all section="artefact.studyjournal"}</button>
        <button data-value="published">{str tag=shared section="artefact.studyjournal"}</button>
        <button data-value="public">{str tag=public section="artefact.studyjournal"}</button>
    </div>

    <div class="filter-row sort-by filter-horizontal">
        <div class="filter-label">{str tag="sortby" section="interaction.pages"}</div>
        <div class="filter-sorting">
            <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
            <label for="sort-by-modified">{str tag="sortbymodified" section="interaction.pages"}</label>
            <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
            <label for="sort-by-title">{str tag="sortbytitle" section="interaction.pages"}</label>
        </div>
    </div>

</div>

<ul id="studyjournals" class="gridder">
    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="footer.tpl"}