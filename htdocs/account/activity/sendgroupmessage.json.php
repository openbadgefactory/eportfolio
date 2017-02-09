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

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('activity.php');

if (!is_teacher()) {
    json_reply(true, get_string('accessdeniedexception', 'error'));
}

$message    = param_variable('message');
$subject    = param_variable('subject');
$users      = param_variable('users', '');
$groups     = param_variable('groups', '');
$userarr    = is_array($users) ? $users : array();

if (empty($message) || empty($subject)) {
    json_reply(true, get_string('nosubjectorbody', 'activity'));
}

if (is_array($groups)) {
    foreach ($groups as $groupid) {
        $groupmembers = group_get_member_ids($groupid);

        if (is_array($groupmembers)) {
            $userarr = array_merge($userarr, $groupmembers);
        }
    }
}

// Remove duplicates & the current user from the recipients.
$userarr = array_unique($userarr);
$useridx = array_search($USER->get('id'), $userarr);

if ($useridx !== false) {
    array_splice($userarr, $useridx, 1);
}

if (count($userarr) === 0) {
    json_reply(true, get_string('norecipientsselected', 'activity'));
}

$notificationdata = array(
    'users' => $userarr,
    'subject' => $subject,
    'message' => $message,
    'fromuser' => $USER->get('id')
);

activity_occurred('maharamessage', $notificationdata);

json_reply(false, get_string('groupmessagesuccess', 'activity'));
