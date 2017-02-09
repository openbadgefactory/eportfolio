function multiresumeLanguageChanged(a, lang) {

    $j('.artefactchooser-tabs.multiresume li').removeClass('current');
    a.parentNode.className = 'current';

    $j('.multiresume_lang_container').hide();
    $j('#multiresume_lang_container_' + lang).show();

    return false;
}

function multiresumeArtefactChanged(elm, lang) {
    var artefact = elm.options[elm.selectedIndex].value;
    var fields = $j('#multiresume_artefact_' + artefact + '_fields_' + lang);
    $j('#multiresume_lang_container_' + lang + ' .multiresume_artefact_fields').hide();
    fields.show();
    multiresumeArtefactFieldChanged(fields.get(0));
}

function multiresumeArtefactFieldChanged(elm, lang) {
    var field = elm.options[elm.selectedIndex].value;
    var rows = $j('#multiresume_artefactfield_' + field + '_rows_container_' + lang);
    $j('#multiresume_lang_container_' + lang + ' .multiresume_artefactfield_rows_container').hide();
    rows.show();
}

(function ($) {
    $(function () {
        $('img.multiresume_flag_selector').live('click', function () {
            var m = this.id.match(/multiresume_flag_(.+)_(\d+)$/);

            var parent = this.parentNode.parentNode;
            $.get(config.wwwroot + 'artefact/multiresume/blocktype/entiremultiresume/content.php', {id: m[2], lang: m[1]}, function (data) {
                if (!data) {
                    return;
                }
                var title = $(parent.parentNode).prev('div').children('h4');
                title.text(data.title);

                $(parent).replaceWith(data.content);
                $(".expandable-body").hide();
                $(".toggle").addClass('expandable');
                $(".expandable-head").click(function(event) {
                    $(this).next('.expandable-body').toggle();
                    $(this).children(".toggle.expandable").toggleClass('expanded');
                });
            });
        });
    });
})(jQuery);
