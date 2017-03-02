{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row search-filter">
        <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.epsp"}" />
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
        <button data-value="private" class="active">{str tag=private section="artefact.studyjournal"}</button>
        <button data-value="published">{str tag=shared section="artefact.studyjournal"}</button>
        <button data-value="public">{str tag=public section="artefact.studyjournal"}</button>
        <button data-value="all">{str tag=all section="artefact.studyjournal"}</button>
    </div>

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
    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{str tag=createnewplan section="artefact.epsp"}</span>
        </div>
    </li>

    {str tag=publicityofthisplanis section="artefact.epsp" assign=pubdesc}

    {foreach $plans "plan"}
        {str tag=$plan->publicity section="artefact.epsp" assign=pubvalue}
        {include file="gridder/item.tpl"
            author=$USER|full_name
            id=$plan->id
            extraclasses="epsp-item"
            author_id=$USER->id
            title=$plan->title
            url="`$WWWROOT`artefact/epsp/fields.php?id=`$plan->id`"
            publicitydescription=$pubdesc
            publicityvalue=$pubvalue
            menuitems=$plan->menuitems
            mtime=$plan->mtime
            type="epsp"
            view=$plan->view
            tags=$plan->jsontags
            description=$plan->description
            publicity=$plan->publicity}
    {/foreach}

    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="footer.tpl"}