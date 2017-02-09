{include file="header.tpl"}

{$form|safe}

<div class="modal fade" id="attach-from-portfolio-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">{str tag="linktoportfolio" section="artefact.studyjournal"}</h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="attach-items">{str tag=attach section="artefact.studyjournal"}</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">{str tag=cancel}</button>
      </div>
    </div>
  </div>
</div>

{include file="footer.tpl"}
