{include file="header.tpl"}

<div id="messages">
    {if $saved}
        <div class="ok">{str tag=templatesavingsuccessful section="artefact.studyjournal"}</div>
    {/if}
</div>

<h1>{str tag=studyjournaltemplates section="artefact.studyjournal"}</h1>

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
    
    <div class="filter-row filter-horizontal" id="template-type">
        <span class="filter-label">{str tag=show section="artefact.studyjournal"}</span>
        <button data-value="all" class="active">{str tag=all section="artefact.studyjournal"}</button>
        <button data-value="shared">{str tag=institutiontemplates section="artefact.studyjournal"}</button>
        <button data-value="own">{str tag=owntemplates section="artefact.studyjournal"}</button>
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

<ul id="templates" class="gridder">

    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{str tag=createtemplate section="artefact.studyjournal"}</span>
        </div>
    </li>

    {str tag=publicityofthistemplateis section="artefact.studyjournal" assign=pubdesc}

    {foreach from=$templates item=template}
        {str tag=$template->publicity section="artefact.studyjournal" assign=pubvalue}
        {include file="gridder/item.tpl"
            author=$template->author
            id=$template->id
            extraclasses="journal-template journal-item"
            type="studyjournaltemplate"
            author_id=$template->owner
            title=$template->title
            url="`$WWWROOT`artefact/studyjournal/previewtemplate.php?id=`$template->id`&showheader=1"
            publicitydescription=$pubdesc
            publicityvalue=$pubvalue
            menuitems=$template->menuitems
            tags=$template->jsontags
            mtime=$template->mtime
            extradata=$template->extradata
            cannoteditaccess=$template->isnotown
            publicity=$template->publicity}
    {/foreach}

    <li class="shuffle-sizer"></li>
</ul>
    
<div id="gridder-pagination"></div>

{include file="footer.tpl"}