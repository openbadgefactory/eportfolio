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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'studyjournal');
require_once('pieforms/pieform.php');

if (!is_teacher()) {
    throw new AccessDeniedException();
}

$title = param_variable('title', '');
$fields = json_decode(param_variable('fields'));
$elements = array();

foreach ($fields as $index => $field) {
    $field->title = $field->value;
    $elements['field_' . $index] = PluginArtefactStudyJournal::get_template_field($field);
}

$form = pieform(array(
    'name' => 'templatepreview',
    'elements' => $elements,
    'renderer' => 'div',
    'checkdirtychange' => false
));

if (empty($title)) {
    $title = get_string('Untitled', 'view');
}

$smarty = smarty_core();
$smarty->assign('title', $title);
$smarty->assign('form', $form);

json_reply(false, array('html' => $smarty->fetch('artefact:studyjournal:preview.tpl')));