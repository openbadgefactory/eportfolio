<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage artefact.studyjournal
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'studyjournal');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'studyjournal');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'studyjournal');
safe_require('artefact', 'file');

define('TITLE', get_string('studyjournal', 'artefact.studyjournal'));

$journalid = param_integer('id');
$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$file = param_integer('file', 0);
$highlight = $file ? array($file) : null;
$elements = array();
$journal = PluginArtefactStudyJournal::get_journal($journalid);

$elements['title'] = array(
    'type' => 'text',
    'title' => get_string('entrytitle', 'artefact.studyjournal'),
    'rules' => array(
        'required' => true
    )
);

foreach ($journal->get_fields() as $field) {
    $elements['field_' . $field->id] = PluginArtefactStudyJournal::get_template_field($field, $journal);
}

$elements['attachedpreview'] = array(
    'type' => 'html',
    'value' => '<h3 class="attached-title">' . get_string('attachedviewsandcollections', 'artefact.studyjournal') .
        '</h3><div class="attachedpages">' . get_string('noattachedpages',
            'artefact.studyjournal') . '</div>'
);

// Normally we'd use a hidden field here, but it doesn't work in Pieforms if the
// value is changed via JavaScript.
$elements['attached'] = array(
    'type' => 'text',
    'name' => 'attached'
);

$elements['portfoliolink'] = array(
    'type' => 'button',
    'value' => get_string('linktoportfolio', 'artefact.studyjournal')
);

$elements['filebrowser'] = array(
    'type'         => 'filebrowser',
    'title'        => get_string('attachments', 'artefact.blog'),
    'folder'       => $folder,
    'highlight'    => $highlight,
    'browse'       => $browse,
    'page'         => get_config('wwwroot') . 'artefact/studyjournal/student/post.php?id=' . $journalid . '&browse=1',
//    'browsehelp'   => 'browsemyfiles',
    'config'       => array(
        'upload'          => true,
        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
        'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
        'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
        'createfolder'    => false,
        'edit'            => false,
        'select'          => true,
    ),
    'defaultvalue'       => array(),
    'selectlistcallback' => 'artefact_get_records_by_id'
);

$elements['submitcancel'] = array(
    'type' => 'submitcancel',
    'value' => array(get_string('saveentry', 'artefact.studyjournal'), get_string('cancel')),
    'goto' => get_config('wwwroot') . 'artefact/studyjournal/student/journal.php?id=' . $journalid
);

$form = pieform(array(
    'name'              => 'studyjournalentry',
    'method'            => 'post',
    'renderer'          => 'div',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'studyjournalentry_callback',
    'jserrorcallback'   => 'studyjournalentry_error_callback',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements'          => $elements
        ));

$wwwroot = get_config('wwwroot');
$js = <<<JS
sjentry = null;

requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/studyjournal/js/studyjournal'], function (studyjournal) {
        sjentry = studyjournal;
        sjentry.init_journal_post();
    });
});

function imageSrcFromId(imageid) {
    return window.config.wwwroot + 'artefact/file/download.php?file=' + imageid;
}

function imageIdFromSrc(src) {
    var artefactstring = 'download.php?file=';
    var ind = src.indexOf(artefactstring);
    if (ind != -1) {
        return src.substring(ind+artefactstring.length, src.length);
    }
    return '';
}

function studyjournalentry_callback(form, data) {
    studyjournalentry_filebrowser.callback(form, data);
};

function studyjournalentry_error_callback(form, data) {
    // HACK: When the form is submitted and results to an error, the form is
    // somehow reloaded and the event observers don't work anymore. So let's
    // attach those bastards once again.
    if (sjentry !== null) {
        sjentry.init_journal_post();
    }
}

JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js',
    'tinymce'), array(),
        array('artefact.studyjournal' => array('noattachedpages')),
        array('sidebars' => false, 'tinymcesetup' => "ed.addCommand('mceImage', studyjournalImageWindow);"));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING',
        get_string('createjournalentry', 'artefact.studyjournal'));
$smarty->display('artefact:studyjournal:post.tpl');

function studyjournalentry_submit(Pieform $form, $values) {
    global $journal, $USER, $SESSION;

    require_once('collection.php');
    require_once('view.php');

    $attached = json_decode($values['attached']);

    db_begin();

    $entry = new ArtefactTypeStudyJournalEntry();
    $entry->set('parent', $journal->get('id'));
    $entry->set('owner', $USER->id);
    $entry->set('title', $values['title']);
    $entry->set('allowcomments', true);
    $entry->commit();

    foreach ($values as $key => $value) {
        // Hacky hackerson here.
        if (strpos($key, 'field_') === 0) {
            // TODO; validate field id.
            $id = (int) str_replace('field_', '', $key);
            $entry->add_field_value($id, $value);
        }
    }

    if (!is_null($attached)) {
        foreach ($attached->c as $collectionid) {
            $coll = new Collection($collectionid);

            if ($coll->get('owner') == $USER->id) {
                $entry->attach_collection($collectionid);
            }
        }

        foreach ($attached->v as $viewid) {
            $view = new View($viewid);

            if ($view->get('owner') == $USER->id) {
                $entry->attach_view($viewid);
            }
        }
    }

    // Attached files
    $files = is_array($values['filebrowser']) ? $values['filebrowser'] : array();

    // Only allow the attaching of files that exist and are editable by user.
    foreach ($files as $key => $fileid) {
        $file = artefact_instance_from_id($fileid);

        if (!($file instanceof ArtefactTypeFile) || !$USER->can_publish_artefact($file)) {
            unset($files[$key]);
        }
    }

    if (!empty($files)) {
        foreach ($files as $f) {
            try {
                $entry->attach($f);
            }
            catch (ArtefactNotFoundException $e) {

            }
        }
    }

    db_commit();

    $result = array(
        'error' => false,
        'message' => get_string('journalentrysavedsuccessfully',
                    'artefact.studyjournal'),
        'goto' => get_config('wwwroot') . 'artefact/studyjournal/student/journal.php?id=' . $journal->get('id')
    );

    if ($form->submitted_by_js()) {
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }

    redirect(PIEFORM_OK, $result);
}