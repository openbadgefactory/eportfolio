<div class="mediaplayer-container center">
    <div id="vid_{$blockid}" class="mediaplayer" style="margin: 0 auto;">
        {$html|clean_html|safe}
    </div>

    <script type="text/javascript">
        // <EKAMPUS
        //  Fix videos overlapping content in IE9
        $j(function () {
            var iframe = $j('#vid_{$blockid} iframe');
            var url = iframe.attr('src');
            var param = 'wmode=transparent';

            // No need to append twice.
            if (url.indexOf(param) > 0) {
                return;
            }

            var querypos = url.indexOf('?');
            var anchorpos = url.indexOf('#');

            // Strip question mark from the end of the query.
            if (querypos === url.length - 1) {
                url = url.substring(0, querypos);
                querypos = -1;
            }

            var new_src = (anchorpos > 0 ? url.substring(0, anchorpos) : url) +
                    (querypos > 0 ? '&' + param : '?' + param) +
                    (anchorpos > 0 ? url.substring(anchorpos) : '');

            iframe.attr('src', new_src);
            iframe.attr('wmode', 'Opaque');
        });
        // EKAMPUS>
    </script>
</div>
