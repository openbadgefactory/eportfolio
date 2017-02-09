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
 * @subpackage interaction-pages
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require('collection.php');

safe_require('interaction', 'pages');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$sortby = param_variable('sortby', 'modified');
$sortdesc = $sortby === 'modified';
$publicity = param_variable('shared', 'all');
$tagparam = param_variable('tags', array());
$groupid = param_integer('groupid', 0);

$tags = is_array($tagparam) ? $tagparam : (empty($tagparam) ? array() : explode(',', $tagparam));
$ownedby = array();
$access = array();
$sorting = array();
$canedit = true;

// Check group access.
if (!empty($groupid)) {
    define('GROUP', $groupid);
    $group = group_current_group();
    $role = group_user_access($groupid);
    $canedit = $role && group_role_can_edit_views($group, $role);
    $ownedby['group'] = $groupid;
    $access[] = 'owngroup';

    if (!$role) {
        throw new GroupAccessDeniedException(get_string('canlistgroupcollections', 'collection'));
    }
}
// User access
else {
    $ownedby['owner'] = $USER->get('id');
    $access[] = 'own';
}

// Sorting settings.
if ($sortby === 'modified') {
    $sorting[] = array('column' => 'lastchanged', 'desc' => $sortdesc);
}
else {
    $sorting[] = array('column' => 'name', 'desc' => $sortdesc, 'tablealias' => 'c');
}

// Access settings
$getpublic = $publicity === 'all' || $publicity === 'public';
$getpublished = $publicity === 'all' || $publicity === 'published';
$getprivate = $publicity === 'all' || $publicity === 'private';

if ($getpublic) {
    $access[] = 'public';
}
if ($getpublished) {
    $access = array_merge($access, array('user', 'friend', 'group', 'institution', 'loggedin', 'token'));
}
if ($getprivate) {
    $access[] = 'private';
}

$wwwroot = get_config('wwwroot');
$collectiondata = Collection::collection_search($query, (object) $ownedby,
        $limit, $offset, $sorting, $access, $tags);
$collectiontags = array();

if (count($collectiondata->ids) > 0) {
    $collectiontags = Collection::get_collections_tags($collectiondata->ids);
}

$backto = !empty($groupid) ? 'groupcollections.php?group=' . $groupid : 'collections.php';

$collectionarr = objectToArray($collectiondata->data);

// PENDING: This should be done in SQL with one query.
$accesslists = !empty($groupid) ? View::get_accesslists(null, $groupid) : View::get_accesslists($USER->get('id'));
$editlocked = ($canedit) ? false : true;
PluginInteractionPages::add_access_info($collectionarr, $accesslists, $editlocked, true);

$is_teacher = is_teacher();

foreach ($collectionarr as &$value) {
    $collection = new Collection($value['id']);
    $value['tags'] = isset($collectiontags[$value['id']]) ? $collectiontags[$value['id']] : array();
    if (isset($value['is_editable']) && $value['is_editable']){
        $value['menuitems'] = array(
            array(
                'url' => $wwwroot . 'collection/edit.php?id=' . $value['id'],
                'title' => get_string('edittitleanddesc', 'collection')),
            array(
                'url' => $wwwroot . 'collection/views.php?id=' . $value['id'],
                'title' => get_string('manageviews', 'collection')
        ));

        if (!empty($value['first_view_id'])) {
            $value['menuitems'][] = array(
                'url' => $wwwroot . 'view/access.php?id=' . $value['first_view_id'] .
                    '&hidetabs=1&backto=interaction/pages/' . $backto,
                'classes' => 'editaccess',
                'title' => get_string('editaccess', 'view')
            );
            $value['menuitems'][] = array(
                'title' => get_string('copycollection', 'collection'),
                'url' => '#',
                'classes' => 'copypage'
            );
            //If we are in group -> new menu item copy it for myself
            if (!empty($groupid)){
                $value['menuitems'][] = array(
                    'title' => get_string('copyforme', 'view'),
                    'url' => '#',
                    'classes' => 'copypage forme'
                );
            }

            if ($is_teacher) {
                $value['menuitems'][] = array(
                    'title' => get_string('copytolearningobject', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copytolearningobject'
                );
            }
        }
        if (isset($value['is_removable']) && $value['is_removable']){
            $value['menuitems'][] = array(
            'url' => $wwwroot . 'collection/delete.php?id=' . $value['id'],
            'title' => get_string('delete')
            );
        }

    }
    else {
        // do not have edit access to the pages but can copy it for myself
        if(isset($value['view_template']) && $value['view_template'] && !empty($groupid) && !empty($value['first_view_id'])) {
            $value['menuitems'] = array(
                array(
                    'title' => get_string('copyforme', 'view'),
                    'url' => '#',
                    'classes' => 'copypage forme'
                )
            );

            if ($is_teacher) {
                $value['menuitems'][] = array(
                    'title' => get_string('copytolearningobject', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copytolearningobject'
                );
            }
        }
        else {
            $value['menuitems'] = array();
        }
    }
}

$html = '';
$smarty = smarty_core();
$pubdesc = get_string('statusofthispageis', 'interaction.pages');
$author = !empty($groupid) ? $group->name : full_name($USER);

foreach ($collectionarr as $collection) {
    $smarty->assign('id', $collection['id']);
    $smarty->assign('author_id', $USER->get('id'));
    $smarty->assign('author', $author);
    $smarty->assign('title', $collection['name']);
    $smarty->assign('publicity', $collection['shared_to']);
    $smarty->assign('publicitydescription', $pubdesc);
    $smarty->assign('publicityvalue', get_string('sharedto_' . $collection['shared_to'], 'interaction.pages'));
    if (isset($collection['menuitems'])){
        $smarty->assign('menuitems', $collection['menuitems']);
    }
    $smarty->assign('mtime', $collection['modtime']);
    $smarty->assign('tags', $collection['jsontags']);
    $smarty->assign('type', 'portfolio');
    $smarty->assign('group', $collection['group']);

    $view = !empty($collection['first_view_id']) ? $collection['first_view_id'] : null;
    $url = !empty($collection['first_view_id'])
            ? $wwwroot . 'view/view.php?id=' . $collection['first_view_id']
            : $wwwroot . 'collection/views.php?id=' . $collection['id'];

    $smarty->assign('cannoteditaccess', is_null($view));
    $smarty->assign('view', $view);
    $smarty->assign('url', $url);
    $smarty->assign('extraclasses', 'collection-item');

    $html .= $smarty->fetch('gridder/item.tpl');
}

json_reply(false, array('html' => $html, 'total' => $collectiondata->count));