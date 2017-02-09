<div class="template-field listrow template-field-text{if $hidden} template-field-template{/if}"{if $hidden} style="display: none"{/if}>
    <span class="handle"></span>
    <div class="template-field-name">{str tag=templatetextfield section="artefact.studyjournal"}</div>
    <textarea rows="1" cols="60" placeholder="{str tag=templatefieldtext section='artefact.studyjournal'}">{$value|safe}</textarea>
    <div class="fr btns2">
        <a href="#" class="remove-field"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
    </div>
</div>