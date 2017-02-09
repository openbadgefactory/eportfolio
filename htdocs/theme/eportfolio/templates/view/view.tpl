{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}

{if $notrudeform}<div class="message deletemessage">{$notrudeform|safe}</div>{/if}
{* <EKAMPUS *}
<input type="hidden" id="viewid" value="{$viewid}" />
{if !$microheaders && ($mnethost || $editurl || $copyabletoskillsfolder)}
{* EKAMPUS> *}
<div class="viewrbuttons">
    {if !$showtabs}
  {if $editurl}{strip}
    {if $new}
      <a class="btn" href="{$editurl}">{str tag=back}</a>
    {else}
      <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn editview">{str tag=editthisview section=view}</a>
    {/if}
  {/strip}{/if}
  {/if}

  {if $createviewform}
      {$createviewform|safe}
  {/if}

  {* <EKAMPUS - show return view button only for non group pages/collections*}
  {if $editurl && $owner && !$learningobject}
        {include
            file="returnobjectbutton.tpl"
            collectionid=$collectionid
            instructors=$instructors
            defaultinstructors=$defaultinstructors
            prevreturndate=$prevreturndate
            viewid=$viewid}
  {/if}
  {* EKAMPUS> *}

  {if $copyabletoskillsfolder}
      <a class="btn" id="copytoskillsfolder" href="#">{str tag=copytoskillsfolder section="interaction.learningobject"}</a>
      <script type="text/javascript">
      $j('#copytoskillsfolder').click(function (evt) {
          evt.preventDefault();

          var url = window.config.wwwroot +
                  'interaction/learningobject/copytoskillsfolder.json.php';

          sendjsonrequest(url, { id: {$collectionid} }, 'post', function (resp) {
              window.location.href = window.config.wwwroot +
                      'interaction/pages/collections.php';
          });
      });
      </script>
  {/if}
  {if $assignable}
      <a class="btn btn-primary" href="{$WWWROOT}interaction/learningobject/assign.php?id={$collectionid}">{str tag=assign section="interaction.learningobject"}</a>
  {/if}
  {if $mnethost}<a href="{$mnethost.url}" class="btn">{str tag=backto arg1=$mnethost.name}</a>{/if}
</div>
{/if}

{* <EKAMPUS *}
{if $maintitle}<h1 id="viewh1"{if $showtabs} class="hastabs"{/if}><span>{$maintitle|safe}</span></h1>{/if}

{if $showtabs}
  {include file="view/editviewtabs.tpl" selected='displaypage' new=$new issiteview=$issiteview backto=$backto}
{/if}

{if $colldescription}
    <div id="collection-description">{$colldescription|clean_html|safe}</div>
{/if}
{* EKAMPUS> *}

{if !$microheaders && $collection}
    {include file=collectionnav.tpl}
{/if}

<p>{$author|safe} {if $tags}<span class="tags"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</span>{/if}</p>

<div id="view-description">{$viewdescription|clean_html|safe}</div>

<div id="view" class="cb">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent|safe}
                <div class="cb">
                </div>
            </div>
        </div>
        {if $is_public}
            <div id="share-view"></div>
        {/if}

  <div class="viewfooter">
    {if $releaseform}<div class="releaseviewform">{$releaseform|safe}</div>{/if}
    {if $view_group_submission_form}<div class="submissionform">{$view_group_submission_form|safe}</div>{/if}
    {if $feedback->position eq 'base'}
        {if $feedback->count || $enablecomments}
        <h3 class="title">{str tag="feedback" section="artefact.comment"}</h3>
        <div id="feedbacktable" class="fullwidth">
            {$feedback->tablerows|safe}
        </div>
        {$feedback->pagination|safe}
        {/if}
    {/if}
	<div id="viewmenu">
            {* <EKAMPUS *}
            {if $enablecomments}
                <p id="has_liked" class="thumbup"{if !$has_liked} style="display: none"{/if}>
                    <span id="likecount">{$thumbs}</span> <span id="likeunit">{str tag=likes section="artefact.comment" arg1=$thumbs}</span>
                </p>

                {if !$has_liked}
                    <a href="#" class="thumbup" id="like_view">{str tag=thumbsup section="artefact.comment"} ({$thumbs} {str tag=likes section="artefact.comment" arg1=$thumbs})</a>
                {/if}
            {/if}
            {* EKAMPUS> *}
        {if $feedback->position eq 'base' && $enablecomments}
            <a id="add_feedback_link" class="feedback" href="">{str tag=placefeedback section=artefact.comment}</a>
        {/if}
        {include file="view/viewmenu.tpl"}
    </div>
    {if $addfeedbackform}<div>{$addfeedbackform|safe}</div>{/if}
    {if $objectionform}<div>{$objectionform|safe}</div>{/if}
  </div>
</div>
{if $visitstring}<div class="ctime center s">{$visitstring}</div>{/if}

{* <EKAMPUS *}
{if $enablecomments}
<script type="text/javascript">
$j('#like_view').click(function (evt) {
    evt.preventDefault();

    sendjsonrequest(window.config.wwwroot + 'artefact/comment/like.json.php', { view: {$viewid} }, 'post', function (resp) {
        var likes = parseInt(resp.likes, 10);

        $j('#likecount').text(likes);
        $j('#likeunit').text(get_string('likes', likes));
        $j('#like_view').remove();
        $j('#has_liked').show();
    });
});
</script>
{/if}
{* EKAMPUS> *}

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}
