{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row search-filter">
        <input type="search" id="template-filter" placeholder="{str tag=filter section="artefact.blog"}" />
    </div>

    <div class="filter-row filter-horizontal">
        <span class="filter-label">{str tag="tags"}</span>
        <div class="filter-buttons tag-buttons">
            <button data-value="all" id="show-all" class="all active">{str tag="all" section="interaction.pages"}</button>
            {foreach from=$tags item=tag}
                <button data-value="{$tag}">{$tag}</button>
            {/foreach}
        </div>
    </div>
    
    <div class="filter-row filter-horizontal" id="filter-publicity">
        <span class="filter-label">{str tag="show" section="interaction.pages"}</span>
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
    
<ul id="blogs" class="gridder">

    <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
        <div class="gridder-new-icon">
            <span></span>
        </div>
        <div class="gridder-new-text">
            <span>{str tag=addblog section="artefact.blog"}</span>
        </div>
    </li>

    {if $blogs}
        {str tag=publicityofthisblogis section="artefact.blog" assign=pubdesc}

        {foreach $blogs "blog"}
            {str tag=$blog->publicity section="artefact.blog" assign=pubvalue}
            {include file="gridder/item.tpl"
                author=$USER|full_name
                id=$blog->id
                extraclasses="blog-item"
                author_id=$USER->id
                title=$blog->title
                url="`$WWWROOT`artefact/blog/view/index.php?id=`$blog->id`"
                publicitydescription=$pubdesc
                publicityvalue=$pubvalue
                menuitems=$blog->menuitems
                description=$blog->description|safe|strip_tags
                mtime=$blog->mtime
                tags=$blog->jsontags
                type="blog"
                view=$blog->view
                publicity=$blog->publicity}   
        {/foreach}
    {/if}

    <li class="shuffle-sizer"></li>
</ul>
    
<div id="gridder-pagination"></div>

{include file="footer.tpl"}