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
 * @subpackage TODO
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('activity.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . '../interaction/learningobject/lib.php');


$message    = param_variable('message');
$subject    = param_variable('subject');
$users      = param_variable('users', '');
$viewid     = param_integer('viewid', null);
$collectionid = param_integer('collectionid', null);
$userarr    = is_array($users) ? $users : array();
$collection = $collectionid ? new Collection($collectionid) : null;
/*if (empty($message) || empty($subject)) {
    json_reply(true, get_string('nosubjectorbody', 'activity'));
}*/

// Remove duplicates & the current user from the recipients.
$userarr = array_unique($userarr);
$useridx = array_search($USER->get('id'), $userarr);

if ($useridx !== false) {
    array_splice($userarr, $useridx, 1);
}
if (count($userarr) === 0) {
    json_reply(true, get_string('noinstructorsyet', 'interaction.learningobject'));
}
// Send email
$notificationdata = array(
    'users' => $userarr,
    'subject' => $subject,
    'message' => $message,
    'fromuser' => $USER->get('id')
);

//activity_occurred('maharamessage', $notificationdata);
// Save things to database
if (!$collection){
    $view = new View($viewid);
    $collection = $view->get('collection');
}
if ($collection){
    InteractionLearningobjectInstance::return_collection($collection, $userarr);
}
else if ($view){
    InteractionLearningobjectInstance::return_view($view, $userarr);
}

json_reply(false, get_string('returnobjectsuccess', 'interaction.learningobject'));