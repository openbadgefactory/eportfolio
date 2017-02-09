{include file="header.tpl"}

{if $changepassword}
  {if $changeusername}
            <h1>{str tag="chooseusernamepassword"}</h1>
            <p>{str tag="chooseusernamepasswordinfo" arg1=$sitename}</p>
  {else}
            <h1>{str tag="changepassword"}</h1>
            <p>{str tag="changepasswordinfo"}</p>
  {/if}
            {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange|safe}</div>{/if}
{else}
			<h1>{str tag='requiredfields' section='auth'}</h1>
{/if}

			{$form|safe}
{if $page_content}
    <div id="acceptterms">
        {$page_content|clean_html|safe}
    </div>
{/if}
{include file="footer.tpl"}
<script>
    jQuery(document).ready(function() {
 // Scroll down!
        $j('.accepttermslink').on('click', function(evt){
                if ($j('#acceptterms').length > 0) {
                    evt.preventDefault();
                    $j('html, body').animate({
                        scrollTop: $j('#acceptterms').offset().top - 30
                    }, 500);
                }
                else {
                    return true;
                }
            });
    });
</script>