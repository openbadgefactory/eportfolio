{include file="header.tpl"}

<h1>{str tag=studyjournals section="artefact.studyjournal"}</h1>

<div class="gridder-filters">

    <div class="filter-row search-filter">
        <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.studyjournal"}" />
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

    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{str tag=createjournal section="artefact.studyjournal"}</span>
        </div>
    </li>
    
    {str tag=publicityofthisjournalis section="artefact.studyjournal" assign=pubdesc}

    {foreach from=$journals item=journal}
        {str tag=$journal->publicity section="artefact.studyjournal" assign=pubvalue}
        {include file="gridder/item.tpl"
            author=$journal->author
            id=$journal->id
            extraclasses="journal-item"
            type="studyjournal"
            view=$journal->viewid
            author_id=$journal->owner
            title=$journal->title
            url="`$WWWROOT`artefact/studyjournal/student/journal.php?id=`$journal->id`"
            publicitydescription=$pubdesc
            publicityvalue=$pubvalue
            menuitems=$journal->menuitems
            tags=$journal->jsontags
            mtime=$journal->mtime
            description=$journal->description|safe|strip_tags
            publicity=$journal->publicity}
    {/foreach}

    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>
    
{include file="footer.tpl"}