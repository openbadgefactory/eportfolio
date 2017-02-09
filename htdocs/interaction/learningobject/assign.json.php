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
 * @subpackage interaction-learningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('collection.php');
safe_require('interaction', 'learningobject');

if (!is_teacher()) {
    throw new AccessDeniedException('Only teachers are allowed to assign learning objects.');
}

$id = param_integer('id');
$assignees = json_decode(param_variable('assignees'));
$instructors = json_decode(param_variable('instructors'));
$due_date = param_variable('due_date', '');

try {
    $learningobject = InteractionLearningobjectInstance::get_instance($id);
    InteractionLearningobjectInstance::assign($learningobject, $assignees,
            $instructors);

    $due = empty($due_date) ? null : InteractionLearningobjectInstance::formatted_date_to_timestamp($due_date);

    $learningobject->set('return_date', $due);
    $learningobject->commit();

    $okmsg = get_string('learningobjectassignedsuccessfully', 'interaction.learningobject');
    $SESSION->add_ok_msg($okmsg);

    json_reply(false, $okmsg);
}
catch (Exception $e) {
    json_reply('local', $e->getMessage());
}