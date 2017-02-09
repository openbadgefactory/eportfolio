{include file="microheader.tpl"}
<script>
    function closeframe() {
        parent.document.getElementById("frame").style.display = "none";
    }
</script>
<div class="close"><img src="{theme_url filename='images/btn_close.png'}" alt="Close" class="closeframe" onclick="closeframe();" /></div>
{if $pagedescription}
  <p class="intro">{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{$form|safe}
{include file="microfooter.tpl"}
