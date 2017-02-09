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
 * @subpackage interaction-pages
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'pages');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$returns = param_integer('returns', 0);
$owners = array('institution' => param_variable('institution', '0'));
$owners['groups'] = param_integer('groups', 0);
$owners['student']  = param_integer('student', 0);
$ownerquery = param_variable('ownerquery', null);
$sharedparam = param_variable('shared', null);
$typesparam = param_variable('types', null);
$sortby = param_variable('sortby', 'modified') === 'modified' ? 'lastchanged' : 'title';
$sortdir = $sortby === 'lastchanged' ? 'desc' : 'asc';
$types = is_array($typesparam) ? $typesparam : (!empty($typesparam) ? explode(',', $typesparam) : null);
$shared = is_array($sharedparam) ? $sharedparam : (!empty($sharedparam) ? explode(',', $sharedparam) : array());

//data for search
$owned = array();

//if there is no such group in selected institution reset group
if ($owners['groups'] AND $owners['institution']){
    if (!$result = get_records_sql_array('
            SELECT g.id, g.name  FROM {group} g LEFT JOIN {institution} i ON g.institution = i.name
            WHERE g.deleted = 0 AND g.hidden = 0 AND i.name = ? AND g.id = ?', array($owners['institution'], $owners['groups']))) {
                $owners['groups'] = 0;
            }
}

$share = array();
$getpublic = in_array('public', $shared);
$getpublished = in_array('published', $shared);

if ($getpublic || !$getpublished) {
    $share[] = 'public';
}
if ($getpublished || !$getpublic) {
    $share = array_merge($share, array('user', 'friend', 'group', 'institution', 'loggedin', 'token'));
}
$is_teacher = is_teacher();
// $owned['multiple'] is to hijack view::owner_sql function
if (!$is_teacher) {
    $owned['owner'] = $USER->id;
}
elseif ($owners['student']){
    $owned['owner'] = $owners['student'];
}
elseif ($owners['groups']) {
    $owned['group'] = $owners['groups'];
    $owned['multiple'] = true;
}
elseif ($owners['institution']){
    $owned['institution'] = $owners['institution'];
    $owned['multiple'] = true;
}
else {
    //if all groups and all institutions are selected -> we still need to filter out staff and admin users
    $owned['multiple'] = true;
}
// dont want group pages here
$owned['nogroup'] = true;

$ownedby = (empty($owned)) ? null : (object)$owned;


$wwwroot = get_config('wwwroot');
// Returned views/collections
if ($returns){
    safe_require('interaction', 'learningobject');

        $instructor = $is_teacher ? $USER->id : null;

        $results = InteractionLearningobjectInstance::search_all_returns($instructor, $ownedby, $ownerquery, $query, $limit, $offset, $sortby, $types);

        $returnedobjects = $results->data;

        foreach ($returnedobjects as &$object){

            $object = (array)$object;
            $object["fullurl"] = $wwwroot.'view/view.php?id='.$object['id'];
            if ($object["collid"]){
                $object["type"] = 'collection';
                $object["fullurl"] = $wwwroot.'view/view.php?id='.$object['first_view_id'];
                $object["id"] = $object['first_view_id'];
            }
        }

        $pages = $returnedobjects;

    PluginInteractionPages::get_sharedview_accessrecord($pages);

    $total = $results->count;

}


$ret = array('total' => $total, 'html' => '');

if ($pages) {
    $pubdesc = get_string('thepublicityofthispageis', 'interaction.pages');
    $sm = smarty_core();

    //$is_teacher = is_teacher();

    foreach ($pages as &$page) {
        $page['data'] = array('type' => $page['type']);
        $page['menuitems'] = array();

        if (isset($page['is_editable']) && $page['is_editable']) {

            //if this is a collection
            if ($page['type'] == 'collection'){

                $page['menuitems'] = array(
                    array(
                        'url' => $wwwroot . 'collection/edit.php?id=' . $page['collid'],
                        'title' => get_string('edittitleanddesc', 'collection')),
                    array(
                        'url' => $wwwroot . 'collection/views.php?id=' . $page['collid'],
                        'title' => get_string('manageviews', 'collection')
                ));

            }
            // this is a page
            else {
                $page['menuitems'][] = array(
                'title' => get_string('edittitleanddescription', 'view'),
                'url' => $wwwroot . 'view/edit.php?id=' . $page['id']
                );

                $page['menuitems'][] = array(
                'title' => get_string('editcontentandlayout', 'view'),
                'url' => $wwwroot . 'view/blocks.php?id=' . $page['id']
                );
            }
            // access is the same for both (first view id)
            $page['menuitems'][] = array(
                'url' => $wwwroot . 'view/access.php?id=' . $page['id'] . '&backto=interaction/pages/returns.php',
                'title' => get_string('editaccess', 'view'),
                'classes' => 'editaccess'
            );

                $page['menuitems'][] = array(
                'title' => $page['type'] == 'collection' ? get_string('copycollection', 'collection') : get_string('copyview', 'view'),
                'url' => '#',
                'classes' => 'copypage'
                );


            // Copy own collection to a learning object.
            if ($is_teacher && $page['type'] === 'collection') {
                $page['menuitems'][] = array(
                    'title' => get_string('copytolearningobject', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copytolearningobject'
                );
            }
        }
        else if (isset($page['template']) &&  $page['template'] == 1 ) {
            if (in_array($page['type'], array('portfolio', 'collection'))){
                $page['menuitems'][] = array(
                'title' => $page['type'] == 'collection' ? get_string('copycollection', 'collection') : get_string('copyview', 'view'),
                'url' => '#',
                'classes' => 'copypage'
                );
            }

            // Copy another user's collection to learning object.
            if ($is_teacher && $page['type'] === 'collection') {
                $page['menuitems'][] = array(
                    'title' => get_string('copytolearningobject', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copytolearningobject'
                );
            }
        }

        if (isset($page['is_removable']) && $page['is_removable']) {
            if ($page['type'] == 'collection'){
                $page['menuitems'][] = array(
                'url' => $wwwroot . 'collection/delete.php?id=' . $page['collid'],
                'title' => get_string('delete')
                );
            }
            else {
                $page['menuitems'][] = array(
                    'url' => $wwwroot . 'view/delete.php?id=' . $page['id'],
                    'title' => get_string('deletethisview', 'view')
                );
            }
        }

        $shared = explode(' ', $page['shared_to']);
        $type = $page['type'] == 'portfolio' ? 'page' : $page['type'];

        $author = isset($page['user']) ? full_name($page['user']) : '';
        if ($author == ''){
            $author = (isset($page['firstname']) && isset($page['lastname'])) ? $page['firstname'] . ' ' . $page['lastname'] : '';
        }

        if ($page['type'] == 'collection'){
            $sm->assign('id', $page['collid']);
        }
        else {
            $sm->assign('id', $page['id']);
        }

        $sm->assign('author', $author);

        if (($page['owner'])) {
            $sm->assign('author_id', $page['owner']);
            $sm->assign('group', null);
        }

        $sm->assign('uniqueid', $page['type'] . '-' . $page['id']);
        $sm->assign('url', $page['fullurl']);
        if (isset($page['displaytitle'])){
            $sm->assign('title', $page['displaytitle']);
        }
        else if (isset($page['title'])){
            $sm->assign('title', $page['title']);
        }
        else {
            $sm->assign('title', $page['name']);
        }
        //$sm->assign('title', $page['displaytitle']);
        $sm->assign('publicity', $page['shared_to']);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue', get_string($shared[0], 'interaction.pages'));
        $sm->assign('menuitems', $page['menuitems']);
        $sm->assign('extraclasses', $type . '-item');
        // this is not mtime but previous return date
        $sm->assign('mtime', $page['prev_return_date']);
        $sm->assign('extradata', $page['data']);
        $sm->assign('type', 'gallery');

        if (!empty($page['prev_return_date'])) {
            $formatteddate = format_date(strtotime($page['prev_return_date']), 'strftimedateshort');
            $sm->assign('headdata', '<span class="return_date" title="' .
                    get_string('returneddate', 'interaction.pages', $formatteddate) .
                    '">' . $formatteddate . '</span>');
        }
        else {
            $sm->assign('headdata', null);
        }

        // Make box bottoms unclickable for others' views.
        if (!in_array('own', $shared)) {
            $sm->assign('cannoteditaccess', true);
        }

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

if (is_teacher()) {
    $ret['students'] = array('0' => get_string('selectstudent', 'interaction.pages'));

    // Get students only if there's at least one group selected. Otherwise it
    // might get messy (thousands of students returned).
    if ($owners['groups'] != 0) {
        //$where = ' AND g.role = ? AND g.group = ?';
        $where = ' AND g.group = ?';
        //$pram = array('member', (int)$owners['groups']);
        $pram = array((int)$owners['groups']);

        if ($result = get_records_sql_array("
            SELECT u.id, u.firstname, u.lastname
              FROM {usr} u
         LEFT JOIN {usr_institution} i ON u.id = i.usr
         LEFT JOIN {group_member} g ON u.id = g.member
             WHERE u.deleted = 0 $where", $pram)) {
                foreach ($result as $student) {
                    $ret['students'][$student->id] = $student->firstname .' '. $student->lastname;
                }
                $result = NULL;
        }
    }

    //data for group selection
    $ret['groups'] = array('0' => get_string('selectgroup', 'interaction.pages'));

    if ($owners['institution'] != '0') {
        $where = ' AND i.name = ? AND g.grouptype = ?';
        $pram = array($owners['institution'], 'system');

        if ($result = get_records_sql_array('
                SELECT g.id, g.name  FROM {group} g LEFT JOIN {institution} i ON g.institution = i.name
                WHERE g.deleted = 0 AND g.hidden = 0'.$where, $pram)) {
                foreach ($result as $group) {
                    $ret['groups'][$group->id] = $group->name;
                }
        }
    }
}

// Do not show students & groups to other students. Only teachers can see them.
else {
    $ret['students'] = array();
    $ret['groups'] = array();
}

unset($owners);
json_reply(false, $ret);