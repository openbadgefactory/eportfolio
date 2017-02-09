<div id="home-info-container">
    {if $USER->is_logged_in()}
        <div id="hideinfo" class="nojs-hidden-block">
            <a href="#" title="{str tag=Hide2}">
                <img src="{theme_url filename='images/btn_close.png'}" alt="{str tag=Close}" />
            </a>
        </div>
    {/if}
    	   <!--videonlataus-->
   <script type="text/javascript">
{*$j(document).ready(function() {
    projekktor('.projekktor2', {
	volume: 0.8,
	autoplay:true,
	playerFlashMP4: "{$WWWROOT}js/projekktor/swf/StrobeMediaPlayback/StrobeMediaPlayback.swf",
	playerFlashMP3: "{$WWWROOT}js/projekktor/swf/StrobeMediaPlayback/StrobeMediaPlayback.swf"
    });
});*}
</script> 
<div id="etusivun_tausta" class="lang_{$LANGUAGE}">
<div id="videoruutu">
<table id="etusivun_taulu" width="200" border="0">
  <tr>
    <td class="transparent">&nbsp;</td>
    <td colspan="3">
	<iframe width="336" height="189" src="//www.youtube.com/embed/xOzdc9wURbY?rel=0" frameborder="0" allowfullscreen></iframe>
	
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td colspan="3"><img src="{theme_url filename='images/viiva.png'}" width="373" height="26" alt="" /></td>
  </tr>
  <tr>
    <td><img src="{theme_url filename='images/kayttoohjeet.png'}" width="56" height="61" alt="Ohjeet" /></td>
    <td valign="middle"><h3>{str tag=ekampusmanual}</h3></td>
    <td valign="middle"><a href="http://eportfolio.meke.wikispaces.net/ePortfolion+k%C3%A4ytt%C3%B6ohje" target="_blank">{str tag=forteachers}</a></td>
    <td valign="middle"><a href="http://eportfolio.meke.wikispaces.net/ePortfolion+k%C3%A4ytt%C3%B6ohje" target="_blank">{str tag=forstudent}</a></td>
  </tr>
</table>

</div><!--videoruutuloppuu-->
</div>
    
    <!--videonlataus-->
</div>
