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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

if (!is_teacher()) {
    throw new AccessDeniedException();
}

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$sortby = param_variable('sortby', 'modified');
$publicity = param_variable('shared', 'all');

// Author filters.
$ownerquery = param_variable('ownerquery', '');
$owners = array('institution' => param_variable('institution', '0'));
$owners['groups'] = param_integer('groups', 0);
$owners['student'] = param_integer('student', 0);

$results = ArtefactTypeEpsp::get_shared_plans($query, $offset, $limit, $sortby,
                $publicity, $ownerquery, $owners);
$plans = $results->data;
$ret = array('total' => $results->count, 'html' => '');

if ($results->count > 0) {
    $sm = smarty_core();
    $pubdesc = get_string('publicityofthisplanis', 'artefact.epsp');
    $wwwroot = get_config('wwwroot');

    foreach ($plans as $plan) {
        $shared = explode(' ', $plan['shared_to']);
        $author = isset($plan['user']) ? full_name($plan['user']) : '';

        // Create HTML markup.
        $sm->assign('author', $author);
        $sm->assign('id', $plan['id']);
        $sm->assign('extraclasses', 'epsp-item');
        $sm->assign('type', 'epsp');
        $sm->assign('title', $plan['title']);
        $sm->assign('url', $plan['fullurl']);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue', get_string($shared[0], 'artefact.epsp'));
        $sm->assign('menuitems', array());
        $sm->assign('mtime', $plan['mtime']);
        $sm->assign('description', $plan['description']);
        $sm->assign('publicity', $plan['shared_to']);

        if (!empty($plan['owner'])) {
            $sm->assign('author_id', $plan['owner']);
        }

        if ($plan['owner'] != $USER->get('id')) {
            $sm->assign('cannoteditaccess', true);
        }

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

// Data for student select.
$ret['students'] = array('0' => get_string('selectstudent', 'artefact.epsp'));

if ($owners['groups'] != 0) {
    $students = get_students_in_groups(array((object) array('id' => $owners['groups'])));

    if (isset($students[$owners['groups']])) {
        foreach ($students[$owners['groups']] as $student) {
            $ret['students'][$student->id] = display_name($student);
        }
    }
}

// Data for group select.
$ret['groups'] = array('0' => get_string('selectgroup', 'artefact.epsp'));

if ($owners['institution'] !== '0') {
    $groups = get_groups($owners['institution'], true);

    foreach ($groups as $group) {
        $ret['groups'][$group->id] = $group->name;
    }
}

json_reply(false, $ret);
