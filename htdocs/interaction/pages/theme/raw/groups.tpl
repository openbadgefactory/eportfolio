{include file="header.tpl"}

<div class="gridder-filters">

    <div class="filter-row search-filter filter-horizontal">
        <!--label class="filter-label" for="group-search">{str tag=search}</label -->
        <input type="search" id="group-search" placeholder="{str tag=groupsearchhelp section="interaction.pages"}" />
        <span id="search-spinner"></span>
    </div>
    
    <div class="filter-row group-tags filter-horizontal">
        <span class="filter-label">{str tag="tags"}</span>
        <div class="filter-buttons tag-buttons">
            <button data-value="all" id="show-all" class="all active">{str tag="all" section="interaction.pages"}</button>
            {foreach from=$tags item=tag}
                <button data-value="{$tag}">{$tag}</button>
            {/foreach}
        </div>
    </div>

    <div class="filter-row share-filter filter-horizontal">
        <label class="filter-label" for="group-type">{str tag=show section="interaction.pages"}</label>
        <select id="group-type">
            <option value="allmygroups">{str tag=groupsimin section=group}</option>
            <option value="admin">{str tag=groupsiown section=group}</option>
            <option value="invite">{str tag=groupsiminvitedto section=group}</option>
            <option value="notmember">{str tag=groupsnotin section=group}</option>
            <option value="allgroups">{str tag=allgroups section=group}</option>
        </select>
    </div>
    
    <div class="filter-row sort-by filter-horizontal">
        <div class="filter-sorting">
            <span class="filter-label">{str tag="sortby" section="interaction.pages"}</span>
            <input type="radio" id="sort-by-modified" name="sortpagesby" checked="checked" value="mtime" />
            <label for="sort-by-modified">{str tag="sortbymodified" section="interaction.pages"}</label>
            <input type="radio" id="sort-by-title" name="sortpagesby" value="name" />
            <label for="sort-by-title">{str tag="sortbygrouptitle" section="interaction.pages"}</label>
        </div>
    </div>

    <div class="filter-row institution-selector filter-horizontal" style="display: none">
        <label class="filter-label" for="group-institution">{str tag=institution}</label>
        <select id="group-institution">
            <option value="1">{str tag=myinstitutions section="interaction.pages"}</option>
            <option value="0" selected="selected">{str tag=allinstitutions section="interaction.pages"}</option> 
        </select>
    </div>


</div>
<ul id="pages" class="gridder">

    {if $cancreate}
        <li class="gridder-item gridder-new" data-title="" data-mtime="9999-99-99">
            <div class="gridder-new-icon">
                <span></span>
            </div>
            <div class="gridder-new-text">
                <span>{$strcreategroup}</span>
            </div>
        </li>
    {/if}

    <li class="shuffle-sizer"></li>
</ul>

<div id="gridder-pagination"></div>

{include file="tageditor.tpl"}
{include file="footer.tpl"}
