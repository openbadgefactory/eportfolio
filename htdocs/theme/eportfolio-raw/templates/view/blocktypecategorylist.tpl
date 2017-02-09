{foreach from=$categories key=name item=category}
    <div id="blocktype-common" class="blocktype-list">
    {if $category.name == 'studyjournal'}
            <div class="blocktype">
                <a class="blocktypelink" href="#">
                    <input type="radio" class="blocktype-radio" id="blocktype-radio-studyjournal" name="blocktype" value="studyjournal">
                    <img width="24" height="24" title="{str tag=description section=blocktype.studyjournal/studyjournal}" alt="{str tag=description section=blocktype.studyjournal/studyjournal}" src="{$WWWROOT}thumb.php?type=blocktype&bt=studyjournal&ap=studyjournal">
                    <label for="blocktype-radio-studyjournal" class="blocktypetitle">{str tag='studyjournal' section='artefact.studyjournal'}</label>
                </a>
            </div>
    {elseif $category.name == 'internal'}
            <div class="blocktype lastrow">
                <a class="blocktypelink" href="#">
                    <input type="radio" id="blocktype-radio-profileinfo" class="blocktype-radio" name="blocktype" value="profileinfo">
                    <img width="24" height="24" title="{str tag=description section=blocktype.internal/profileinfo}" alt="{str tag=description section=blocktype.internal/profileinfo}" src="{$WWWROOT}thumb.php?type=blocktype&bt=profileinfo&ap=internal">
                    <label for="blocktype-radio-profileinfo" class="blocktypetitle">{str tag='blocktypecategory.internal' section='view'}</label>
                </a>
            </div>
    {/if}
    </div>
{/foreach}
<div id="accordion">
{foreach from=$categories key=name item=category}
    {if $category.name != 'studyjournal' && $category.name != 'internal'}
        <div id="block-category-{$category.name}" class="block-category-title collapsed">
            <div class="withjs" style="display: none" title="{$category.description}">{$category.title}</div>
            <a class="nonjs" href="{$WWWROOT}view/blocks.php?id={$viewid}&c={$category.name}&new=1" title="{$category.description}">{$category.title}</a>
        </div>
        {if $selectedcategory == $category.name}
            <div id="{$category.name}">
                {$blocktypelist|safe}
            </div>
        {else}
            <div id="{$category.name}" class="hidden">{str tag=loading section=mahara}</div>
        {/if}
    {/if}
{/foreach}
</div>