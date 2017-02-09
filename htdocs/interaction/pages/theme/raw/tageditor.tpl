<div class="modal fade" id="edit-item-tags">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">{str tag=tags}</h4>
      </div>
      <div class="modal-body">
          <div class="info">{str tag=addtaghelp section="interaction.pages"}</div>
          <div id="tags-container"></div>
          <input type="text" name="item-tags" id="item-tags-input" placeholder="{str tag=tags}" class="tm-input item-tags" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{str tag=cancel}</button>
        <button type="button" class="btn btn-primary" id="save-tags">{str tag=save}</button>
      </div>
    </div>
  </div>
</div>