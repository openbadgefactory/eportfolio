{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row search-filter">
        <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.multiresume"}" />
    </div>

    <div class="filter-row view-tags filter-horizontal">
        <span class="filter-label">{str tag="tags"}</span>
        <div class="filter-buttons tag-buttons">
            <button data-value="all" id="show-all" class="all active">{str tag="all" section="interaction.pages"}</button>
            {foreach from=$tags item=tag}
                <button data-value="{$tag}">{$tag}</button>
            {/foreach}
        </div>
    </div>
    
    <div class="filter-row filter-horizontal" id="filter-publicity">
        <span class="filter-label">{str tag=show section="artefact.studyjournal"}</span>
        <button data-value="all" class="active">{str tag=all section="artefact.studyjournal"}</button>
        <button data-value="private">{str tag=private section="artefact.studyjournal"}</button>
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

<ul id="resumes" class="gridder">

    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{str tag=newresume section="artefact.multiresume"}</span>
        </div>
    </li>

    {str tag=publicityofthisresumeis section="artefact.multiresume" assign=pubdesc}

    {foreach $resumes "resume"}
        {str tag=$resume->publicity section="artefact.multiresume" assign=pubvalue}
        {include file="gridder/item.tpl"
            author=$USER|full_name
            id=$resume->id
            extraclasses="resume-item `$resume->description`"
            author_id=$USER->id
            title=$resume->title
            url="`$WWWROOT`artefact/multiresume/edit.php?id=`$resume->id`"
            publicitydescription=$pubdesc
            publicityvalue=$pubvalue
            menuitems=$resume->menuitems
            mtime=$resume->mtime
            type="multiresume"
            view=$resume->view
            tags=$resume->jsontags
            publicity=$resume->publicity}
    {/foreach}

    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>
    
{include file="footer.tpl"}
