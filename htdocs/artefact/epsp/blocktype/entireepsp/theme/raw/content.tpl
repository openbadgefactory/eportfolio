<div class="entire-epsp">
    {foreach from=$fields item=field}
        <div class="epsp-field epsp-field-{$field.type}" data-fieldid="{$field.id}">
            {$field.html|safe}
        </div>
    {/foreach}

    <script type="text/javascript">
        requirejs.config({ baseUrl: '{$WWWROOT}local/js' });
        requirejs(['domReady!', 'config'], function () {

        require(['../../artefact/epsp/js/view'], function (epsp) {
            epsp.init();
        });
    });
    </script>
</div>