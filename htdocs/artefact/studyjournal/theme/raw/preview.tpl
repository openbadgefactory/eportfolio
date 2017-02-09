<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h1>{$title}</h1>
    </div>
    <div class="modal-body">
        <div class="template-preview">
            {$form|safe}
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{str tag=closepreview section="artefact.studyjournal"}</button>
    </div>
</div>
