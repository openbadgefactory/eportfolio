
/* global $j */

// Feel free to do local customizations here.
$j(document).ready(function () {

    // Show more/less -links.
    $j('.index .blockinstance .morelinkwrap a').click(function (evt) {
        evt.preventDefault();

        var rows = $j(this).parents('.blockinstance-content').find('.itemlist .extra');
        rows.toggleClass('hidden');

        $j(this).text(window.strings[rows.first().hasClass('hidden') ? 'More' : 'Less']);

        // Scroll to top of the list.
        var parent = $j(this).parents('.blockinstance');

        $j('html, body').animate({
            scrollTop: parent.offset().top
        }, 500);
    });

 /*   $j('#dropdown-nav .ohjaus a[href$="tbr"]').click(function (evt) {
        evt.preventDefault();
        alert(window.strings['modulenotimplemented']);
    });*/

    // Let teachers to mark goals etc. completed in ePSPs.
    // PENDING: This should be in artefact/epsp somehow.
    $j('#column-container').on('click', '.marked-completed-by-user.editable', function () {
        var fieldid = $j(this).parents('.epsp-field').data('fieldid');
        var viewid = parseInt($j('#viewid').val(), 10);
        var url = window.config.wwwroot + 'artefact/epsp/mark_completed.json.php';

        sendjsonrequest(url, { fieldid: fieldid, viewid: viewid }, 'post', function (resp) {
            var els = $j('.epsp-field[data-fieldid=' + fieldid + '] .marked-completed-by-user');

            if (resp.completed) {
                els.removeClass('incomplete').addClass('complete');
                els.find('span.name').hide().text('(' + resp.marked_completed_by + ', ' +
                        resp.marked_completed_at + ')').show('fast');
            }
            else {
                els.removeClass('complete').addClass('incomplete');
                els.find('span.name').hide('fast', function () { $j(this).text('').show(); });
            }
        });
    });
});