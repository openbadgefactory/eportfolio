{include file="header.tpl"}
{* <EKAMPUS *}
{if $showtabs}
    {include file="view/editviewtabs.tpl" selected='displaypage' new=$new viewtype=$viewtype backto=$backto}
{/if}
{* EKAMPUS >*}
{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description|clean_html|safe}</div>
{/if}

<div class="grouphomepage">
{$viewcontent|safe}
</div>
<div class="cb"></div>

{include file="footer.tpl"}
