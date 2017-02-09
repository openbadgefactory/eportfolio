{* <EKAMPUS *}
{if $microheaders}
    {include file="viewmicroheader.tpl"}
    {include file="view/editviewtabs.tpl" selected='share' new=$new issiteview=$issiteview from=$from}
{else}
    {include file="header.tpl"}
    {* <EKAMPUS *}
    {if $owner && !$learningobject}
        <div class="viewrbuttons">
        {include
            file="returnobjectbutton.tpl"
            collectionid=$collectionid
            instructors=$instructors
            defaultinstructors=$defaultinstructors
            prevreturndate=$prevreturndate
            viewid=$viewid}
        </div>
    {/if}
    {* EKAMPUS> *}
{/if}
{* EKAMPUS> *}
{*<KYVYT*}
{if $writeaccess}
	<p>{str tag=writeaccesspagedescription section=view}</p>
{else}
{*KYVYT>*}
    {if ($group)}
        <p>{str tag=editaccessgrouppagedescription section=view}</p>
    {else}
        {if ($institution)}
            {if ($institution == 'mahara')}
                <p>{str tag=editaccesssitepagedescription section=view}</p>
            {else}
                <p>{str tag=editaccessinstitutionpagedescription section=view}</p>
            {/if}
        {else}
            <p>{str tag=editaccesspagedescription3 section=view}</p>
        {/if}
    {/if}
{*<KYVYT*}
{/if}
{*KYVYT>*}
{* <EKAMPUS *}
{if $showtabs}
    {include file="view/editviewtabs.tpl" selected='share' new=$new issiteview=$issiteview backto=$backto viewtype=$viewtype}
{/if}

{if $is_collection}
    <div class="message">
        <p>{str tag=collectionaccesswarning section=view}</p>
    </div>
{/if}
<div class="subpage">
{$form|safe}
</div>
{* EKAMPUS> *}
{include file="footer.tpl"}
