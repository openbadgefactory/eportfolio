<div class="epsp-field epsp-field-{$type}" data-fieldid="{$id}">{$content|safe}</div>

    <script type="text/javascript">
        requirejs.config({ baseUrl: '{$WWWROOT}local/js' });
        requirejs(['domReady!', 'config'], function () {

        require(['../../artefact/epsp/js/view'], function (epsp) {
            epsp.init();
        });
    });
    </script>