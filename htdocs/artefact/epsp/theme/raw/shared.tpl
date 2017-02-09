{include file="header.tpl"}

<div class="gridder-filters {if $progress}progress{/if}">

    <div class="filter-row">
        <div class="search-filter">
            <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.epsp"}" />
            <input type="search" id="student-search" placeholder="{str tag=studentsearchhelp section="artefact.epsp"}" />
            <span id="search-spinner"></span>
        </div>

        <div class="filter-selects">
            {$searchform|safe}
        </div>
    </div>

    <div class="filter-row filter-horizontal view-shared" id="filter-publicity">
        <span class="filter-label">{str tag=show section="artefact.studyjournal"}</span>
        <div class="tag-buttons">
            <button data-value="all" class="active">{str tag=all section="artefact.studyjournal"}</button>
            <button data-value="published">{str tag=shared section="artefact.studyjournal"}</button>
            <button data-value="public">{str tag=public section="artefact.studyjournal"}</button>
        </div>
    </div>

    <div class="filter-row sort-by filter-horizontal">
        <div class="filter-label">{str tag="sortby" section="artefact.epsp"}</div>
        <div class="filter-sorting">
            <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="modified" />
            <label for="sort-by-modified">{str tag="sortbymodified" section="artefact.epsp"}</label>
            <input type="radio" id="sort-by-title" name="sortpagesby" value="title" />
            <label for="sort-by-title">{str tag="sortbytitle" section="artefact.epsp"}</label>
            {if $progress}
                <input type="radio" id="sort-by-author" name="sortpagesby" value="author" />
                <label for="sort-by-author">{str tag="sortbyauthor" section="artefact.epsp"}</label>
            {/if}
        </div>
    </div>

</div>
{if $progress}
    <div id="openall"><a href="">{str tag="openall" section="artefact.epsp"}</a></div>
    <div id="headings">
        <div class="bs-grid headings">
            <div class="row">
                    <div class="col-md-12">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-5">{str tag=student section="artefact.epsp"}</div>
                                    <div class="col-md-7">{str tag=epsptitle section="artefact.epsp"}</div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="lastsection col-md-4">{str tag=latestsection section="artefact.epsp"}</div>
                                    <div class=" col-md-4">
                                        <span>{str tag=markedcomplete section="artefact.epsp"}</span>
                                    </div>
                                    <div class=" col-md-4">
                                        <span>{str tag=markedcompletebyteacher section="artefact.epsp"}</span>
                                    </div>
                                </div>
                            </div>
                    </div>
            </div>
        </div>
    </div>

{/if}
<ul id="plans" class="gridder {if $progress} progress bs-grid{/if}">
    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="footer.tpl"}