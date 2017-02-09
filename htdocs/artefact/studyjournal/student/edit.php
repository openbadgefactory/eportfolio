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
 * @subpackage artefact-studyjournal
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
require_once('pieforms/pieform.php');
safe_require('artefact', 'studyjournal');

define('TITLE', get_string('studyjournal', 'artefact.studyjournal'));

$journalid = param_integer('id', null);
$journal = !is_null($journalid) ? PluginArtefactStudyJournal::get_journal($journalid)
            : new ArtefactTypeStudyjournal();

if (!is_null($journalid)) {
    if ($journal->get('owner') !== $USER->id) {
        throw new AccessDeniedException('Only own journals can be edited.');
    }
}

$templates = PluginArtefactStudyJournal::get_student_template_list();

if ($templates !== false) {
    $savetext = get_string(!is_null($journalid) ? 'savejournal' : 'savenewjournal', 'artefact.studyjournal');
    $form = array(
        'name' => 'studyjournal',
        'method' => 'post',
        'elements' => array(
            'title' => array(
                'type' => 'text',
                'title' => get_string('journaltitle', 'artefact.studyjournal'),
                'rules' => array('required' => true),
                'defaultvalue' => $journal->get('title')
            ),
            'description' => array(
                'type' => 'wysiwyg',
                'rows' => 10,
                'cols' => 70,
                'title' => get_string('journaldescription',
                        'artefact.studyjournal'),
                'rules' => array(),
                'defaultvalue' => $journal->get('description')
            ),
            'tags' => array(
                'type' => 'tags',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => $journal->get('tags')
            ),
            'template' => array(
                'type' => 'select',
                'collapseifoneoption' => false,
                'title' => get_string('journaltemplate', 'artefact.studyjournal'),
                'options' => $templates
            ),
            'submit' => array(
                'type' => 'submitcancel',
                'value' => array($savetext, get_string('cancel')),
                'goto' => get_config('wwwroot') . 'artefact/studyjournal/student/index.php'
            )
        )
    );

    if (!is_null($journalid)) {
        unset($form['elements']['template']);
    }

    $form = pieform($form);
}

$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/studyjournal/js/studyjournal'], function (studyjournal) {
        studyjournal.init_new_journal();
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array
        (), array('artefact.studyjournal' => array('previewtemplate')),
        array('sidebars' => false));

if ($templates !== false) {
    $smarty->assign('form', $form);
}

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING',
        get_string((is_null($journalid) ? 'create' : 'edit') . 'journal',
                'artefact.studyjournal'));
$smarty->display(
        'artefact:studyjournal:editjournal.tpl');

function studyjournal_submit(Pieform $form, $values) {
    global $SESSION, $journal;

    $journalid = $journal->get('id');

    try {
        // New journal.
        if (empty($journalid)) {
            $copyid = PluginArtefactStudyJournal::copy_template($values['template']);
            $journal = PluginArtefactStudyJournal::get_template($copyid);
            $journal->set('artefacttype', 'studyjournal');
        }

        $journal->set('title', $values['title']);
        $journal->set('description', $values['description']);
        $journal->set('tags', $values['tags']);
        $journal->set('note', ''); // "notemplate" may be stored here, causes issues later.
        $journal->commit();

        // PENDING: Set copy's parent to original template's id, so we know
        // from whom the template was copied?
        $SESSION->add_ok_msg(get_string('journalsavedsuccessfully',
                        'artefact.studyjournal'));

        if (empty($journalid)) {
            redirect('/artefact/studyjournal/student/post.php?id=' . $journal->get('id'));
        }
        else {
            redirect('/artefact/studyjournal/student/edit.php?id=' . $journal->get('id'));
        }
    } catch (Exception $ex) {
        $SESSION->add_error_msg($ex->getMessage());
        redirect('/artefact/studyjournal/student/edit.php');
    }
}
