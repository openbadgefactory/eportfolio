<div class="template-field listrow template-field-vibe{if $hidden} template-field-template{/if}"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    <div class="template-field-name">{str tag=templatevibefield section="artefact.studyjournal"}</div>
    <input type="text" placeholder="{str tag="vibedescription" section="artefact.studyjournal"}" value="{$value|safe}" />
    <div class="fr btns2">
        <a href="#" class="remove-field"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
    </div>
</div>