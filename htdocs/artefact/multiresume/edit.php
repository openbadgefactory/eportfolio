<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-multiresume
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012- Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'multiresume');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'multiresume');
//< EKAMPUS
$resumeid = param_integer('id');
$resume = new ArtefactTypeMultiResume($resumeid);
$publish = param_integer('publish', null);
// EKAMPUS >
$available = get_languages();
$lang = $resume->get('description');
if (!isset($available[$lang])) {
    $lang = 'en.utf8';
}

if ($resume->get('owner') != $USER->id) {
    throw new AccessDeniedException('');
}

define('TITLE', get_string('editresume','artefact.multiresume') . ': ' . $resume->get('title'));

list($form, $js) = $resume->edit_form();

$js .= <<<'JS'

$j(document).ready(function () {

    $j('#main-column form.pieform').submit(function () { return this.id != 'new_field';});

    var timeout;
    $j('#main-column input.submit').not('.multirow, #new_field_submit').click(function () {
        var that, form, id, editor, i = 0;
        that = this;
        that.disabled = true;

        if (timeout) {
            clearTimeout(timeout);
            timeout = null;
        }
        $j('.multiresume_saved, .multiresume_failed').remove();

        if (typeof tinyMCE != 'undefined'){
            while (editor = tinyMCE.get(i++)) {
            editor.save();
            }
        }
        id = that.id.replace(/_submit/, '');
        form = $j('#' + id);
        form.find('fieldset legend a').text($j('#' + id + '_title').val());

        $j.post('update.json.php?action=save', form.serialize(), function (result) {
            that.disabled = false;
            if (result.success) {
                $j(that).parent().append('<span class="multiresume_saved">- OK -');
                timeout = window.setTimeout(function () { $j('.multiresume_saved').remove(); }, 5000);
            }
            else {
                $j(that).parent().append('<span class="multiresume_failed">SAVE FAILED. Please try again.');
            }
        });

        return false;
    });
    $j('.expandable-body').hide();
    $j('.toggle').addClass('expandable');

    $j('.expandable-head td').not('.buttonscell').click(function(event) {
        $j(this).parent().next('.expandable-body').toggle();
        $j(this).parent().children(".toggle.expandable").toggleClass('expanded');
    });

    $j('form .expandable-head .uparrow').click(function(event) {
        var tr = $j(this).parents('tr').get(0);
        var idx = Math.floor(tr.rowIndex / 2);
        var id = $j(tr).parents('table').get(0).className.match(/multi(\d+)/);

        var form = $j(this).parents('form').get(0);
        var sesskey = form.sesskey.value;
        //var sesskey = $j('#education_sesskey').val();
        $j.post('update.json.php?action=reorderrows', {sesskey:sesskey, id:id[1], row:idx, direction:'up'}, function (result) {
            var prev = $j(tr).prev('tr').prev('tr');
            if (result.success && prev.get(0)) {
                var desc = $j(tr).next('tr').detach();
                $j(tr).detach();
                prev.before(desc);
                prev.prev('tr').before($j(tr));
            }
        });
    });

    $j('form .expandable-head .downarrow').click(function(event) {
        var tr = $j(this).parents('tr').get(0);
        var idx = Math.floor(tr.rowIndex / 2);
        var id = $j(tr).parents('table').get(0).className.match(/multi(\d+)/);

         var form = $j(this).parents('form').get(0);
        var sesskey = form.sesskey.value;
        //var sesskey = $j('#education_sesskey').val();
        $j.post('update.json.php?action=reorderrows', {sesskey:sesskey, id:id[1], row:idx, direction:'down'}, function (result) {
            var next = $j(tr).next('tr').next('tr');
            if (result.success && next.get(0)) {
                var desc = $j(tr).next('tr').detach();
                $j(tr).detach();
                next.next('tr').after($j(tr));
                $j(tr).after(desc);
            }
        });
    });

    $j('form .expandable-head .delete_row').click(function(event) {
        if (!window.confirm(strings.deleteconfirm)) {
            return false;
        }
        var tr = $j(this).parents('tr').get(0);
        var idx = Math.floor(tr.rowIndex / 2);
        var id = $j(tr).parents('table').get(0).className.match(/multi(\d+)/);
        var form = $j(this).parents('form').get(0);
        var sesskey = form.sesskey.value;
        //var sesskey = $j('#education_sesskey').val();
        $j.post('update.json.php?action=deleterow', {sesskey:sesskey, id:id[1], row:idx}, function (result) {
            if (result.success) {
                tr = $j(tr);
                if (tr.siblings().length == 1) {
                    $j(tr.parents('table').get(0)).remove();
                } else {
                    tr.next().remove();
                    tr.remove();
                }
            }
        });
    });


    $j('.pieform fieldset').not('.new_field').children('table').prepend(
        '<tr><th></th><td><span class="reorder"> <span class="remove">&nbsp;</span></span></td></tr>'
    );

    $j('.pieform span.reorder span.remove').click(function(event) {
        if (!window.confirm(strings.deleteconfirm)) {
            return false;
        }
        var form = $j(this).parents('form').get(0);
        var id = (form.id.value.match(/(\d+)/))[1];
        var sesskey = form.sesskey.value;

        $j.post('update.json.php?action=deletefield', {sesskey:sesskey, id:id}, function (result) {
            if (result.success) {
                $j(form).remove();
            }
        });
        return false;
    });

    //displays the edit row form in an iframe instead of a new page
    // <EKAMPUS
    $j('.multiresumeform a.btn, img.edit_row').click(function (event) {
    // EKAMPUS >
        event.preventDefault();
        $j("#frame").height(0);
        var href = $j(this).attr('href');

        if (href) {
            $j("#frame").attr('src',href);
        }
        else {
            var tr = $j(this).parents('tr').get(0);
            var idx = Math.floor(tr.rowIndex / 2);
            var id = $j(tr).parents('table').get(0).className.match(/multi(\d+)/);
            var cv = window.location.search.match(/id=(\d+)/);
            var url = 'edit_row.php?cv=' + cv[1] + '&id=' + id[1] + '&row=' + idx;
            $j("#frame").attr('src', url);
        }

        $j("#frame").css('display', 'block');
        return false;

    });

    //set height of iframe to avoid scrollbars
    $j("#frame").load(function() {
        var that = $j(this);

        that.contents().find("body").addClass('multiresumeframe').css('background', 'white');

        // delay height calculation for TinyMCE
        window.setTimeout(function () {
            that.height( that.contents().find("body").height() +40);
            center();
            $j('#frame img.closeframe').click(function (event) {
               $j("#frame").css('display','none');
               $j("#frame").height(0);
            });
        }, 250);
    });

    //hide the iframe if we click to the parent body
    $j('body.js').click(function(event){
       $j("#frame").css('display','none');
       $j("#frame").height(0);

    });

    function center () {
        var top, left;

        top = Math.max($j(window).height() - $j("#frame").outerHeight(), 0) / 2;
        left = Math.max($j(window).width() - $j("#frame").outerWidth(), 0) / 2;

        $j("#frame").css({
            top:top + $j(window).scrollTop(),
            left:left + $j(window).scrollLeft()
        });
    };
    // add handler for sortable and add row colors
    $j('.pieform-fieldset').each(function(index){
        $j(this).before("<span class='handler'></span>");
    });
    function update_row_indices() {
        $j('.ui-sortable').find('.pieform').each(function(index) {
            $j(this).removeClass('r0 r1').addClass('r' + (index % 2));
        });
    };

    //sort cv fields by dragging
    $j('#main-column-container .multiresumeform').sortable({
    update: function( event, ui ) {},
    items: "form",
    handle: ".handler",
    axis: "y",
    start: function(e, ui){
        if (typeof tinyMCE != 'undefined'){
            ui.item.find('textarea.wysiwyg').each(function(){
                tinyMCE.execCommand( 'mceRemoveControl', false, $j(this).attr('id') );
            });
        }
    },
    stop: function(e,ui) {
        if (typeof tinyMCE != 'undefined'){
            ui.item.find('textarea.wysiwyg').each(function(){
                tinyMCE.execCommand( 'mceAddControl', true, $j(this).attr('id') );
            });
        }
    },
    update: update_row_indices
    });

    //save sort order
   $j('#main-column-container .multiresumeform').on( "sortupdate", function( event, ui ) {

        var newOrder = $j(this).sortable('toArray').toString();

        var formid = $j(this).children('form:first').attr('id');
        var sessid = formid + '_sesskey';
        var sesskey = $j('input#' + sessid).val();

        $j.post('update.json.php?action=orderfields', {sesskey:sesskey, order:newOrder}, function (result) {

            if (result.success ) {

            }
        });
    });

    // Move title submit buttons inline. TODO use oneline pieform renderer instead.
    $j('.multiresumeform tr.submit input').each(function (i, elm) {
        if (elm.id.match(/education|employment|certification|book|membership/)) {
            var moveTo = $j(elm).parents('tr').prev().find('td');
            $j(elm).detach().appendTo(moveTo).before(' ');
        }
    });

    //< EKAMPUS -->add support for mobile devices drag and drop
     var touchHandler = function(event) {
        var touch = event.changedTouches[0];
        var simulatedEvent = document.createEvent("MouseEvent");
            simulatedEvent.initMouseEvent({
            touchstart: "mousedown",
            touchmove: "mousemove",
            touchend: "mouseup"
        }[event.type], true, true, window, 1,
            touch.screenX, touch.screenY,
            touch.clientX, touch.clientY, false,
            false, false, false, 0, null);
        touch.target.dispatchEvent(simulatedEvent);

        var $target = $j(event.target);

        if( $target.hasClass('handler')) {
            event.preventDefault();
            $j(':focus').trigger('blur');
        }
    }

    var init_touchHandler = function() {
        document.addEventListener('touchstart', touchHandler, true);
        document.addEventListener('touchmove', touchHandler, true);
        document.addEventListener('touchend', touchHandler, true);
        document.addEventListener('touchcancel', touchHandler, true);
    }

    if (window.config.handheld_device){
        init_touchHandler();
    }
    update_row_indices();
    // EKAMPUS >
});

JS;
//< EKAMPUS
if ($publish) {
    try {
        $view = ArtefactTypeMultiResume::create_cv_view($resumeid);
        redirect(get_config('wwwroot') . 'view/access.php?new=1&id=' . $view->get('id') . '&backto=artefact/multiresume');
    }
    catch (Exception $e) {
        // TODO: Publishing failed, show an error.
    }
}
$resumeviews = ArtefactTypeMultiResume::get_user_resume_views($USER->id);
$resumeview = find_artefact_view($resumeid, $resumeviews);
// EKAMPUS >
$form .= "<hr>\n\n";
$obj = new MultiResumeField();
$new_form = $obj->edit_form(param_integer('id'), '', 0, $lang);
$new_form['name'] = 'new_field';
$new_form['elements']['section']['class'] = 'new_field';
$new_form['elements']['section']['legend'] = get_string('newresumefield', 'artefact.multiresume');

$form .= pieform($new_form);

$smarty = smarty(array('jquery', get_config('wwwroot')."artefact/multiresume/js/jquery-ui-1.10.4.custom.js"), array(), array('artefact.multiresume' => array('deleteconfirm')));
//< EKAMPUS
$smarty->assign('resumeid', $resumeid);
$smarty->assign('resumeview', $resumeview);
// EKAMPUS >
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescription', get_string('editpagedescription', 'artefact.multiresume'));
//$smarty->display('form.tpl');
$smarty->display('artefact:multiresume:edit.tpl');

function new_field_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $parent = $values['id'];
    $order = get_field_sql("SELECT MAX(`order`) FROM {artefact_multiresume_field} WHERE artefact = ?", array($parent));

    $rec = new stdClass();
    $rec->artefact = $parent;
    $rec->order = $order + 1;
    $rec->title = $values['title'];
    $obj = new MultiResumeField();
    $obj->update_self($values);
    $rec->value = $obj;

    insert_record('artefact_multiresume_field', $rec);

    $SESSION->add_ok_msg('OK');
    redirect('/artefact/multiresume/edit.php?id=' . $parent);
}

