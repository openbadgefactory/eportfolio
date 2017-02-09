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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require('lib.php');
require_once('pieforms/pieform.php');

define('TITLE', get_string('studyjournal', 'artefact.studyjournal'));

safe_require('artefact', 'studyjournal');

$templateid = param_integer('id');
$showheader = param_boolean('showheader', false);
$template = PluginArtefactStudyJournal::get_template($templateid);
$elements = array();

if (!$template->is_shared_to($USER)) {
    throw new AccessDeniedException();
}

foreach ($template->get_fields() as $index => $field) {
    $elements['field_' . $index] = PluginArtefactStudyJournal::get_template_field($field, $template);
}

$form = pieform(array(
    'name' => 'templatepreview',
    'elements' => $elements,
    'renderer' => 'div',
    'checkdirtychange' => false
        ));

$tpl = $showheader ? 'previewfull.tpl' : 'preview.tpl';
$smarty = smarty();

if ($showheader) {
    $smarty->assign('is_owner', $template->get('owner') == $USER->get('id'));
    $smarty->assign('id', $template->get('id'));
    $smarty->assign('PAGEHEADING', $template->get('title'));
    $smarty->assign('PAGETITLE', get_string('previewtemplatex', 'artefact.studyjournal', $template->get('title')));
    $smarty->assign('editurl', 'tutor/edit.php');
}

$smarty->assign('title', $template->get('title'));
$smarty->assign('form', $form);
$smarty->display('artefact:studyjournal:' . $tpl);
