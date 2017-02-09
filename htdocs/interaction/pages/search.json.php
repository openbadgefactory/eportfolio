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
require_once('pieforms/pieform.php');

safe_require('interaction', 'pages');

$query = param_variable('query', '');
$groupid = param_integer('group', null);
$plugin = get_config('searchplugin');
$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);
$tagparam = param_variable('tags', '');
$sortby = param_variable('sortby', 'modified') === 'modified' ? 'lastchanged' : 'title';
$sortdir = $sortby === 'lastchanged' ? 'desc' : 'asc';
$publicity = param_variable('shared', 'all');
$tags = is_array($tagparam) ? $tagparam : (empty($tagparam) ? array() : explode(',', $tagparam));

// Access settings
$getpublic = $publicity === 'all' || $publicity === 'public';
$getpublished = $publicity === 'all' || $publicity === 'published';
$getprivate = $publicity === 'all' || $publicity === 'private';
$access = array();

if ($getpublic) {
    $access[] = 'public';
}
if ($getpublished) {
    $access = array_merge($access,
            array('user', 'friend', 'group', 'institution', 'loggedin', 'token'));
}
if ($getprivate) {
    $access[] = 'private';
}

// Check group privileges.
if (!is_null($groupid)) {
    define('GROUP', $groupid);
    $group = group_current_group();

    if (!is_logged_in() && !$group->public) {
        throw new AccessDeniedException();
    }
}
safe_require('search', $plugin);
$sort = array(array('column' => $sortby, 'desc' => ($sortdir === 'desc')));

if (defined('GROUP')) {
    $results = View::view_search($query, null,
                    (object) array('group' => $group->id), null, $limit,
                    $offset, true, $sort, array('portfolio', 'grouphomepage'),
                    false, $access, $tags);
    $accesslists = View::get_accesslists(null, $group->id);
    $role = group_user_access($group->id);
    $can_edit = $role && group_role_can_edit_views($group, $role);
    $editlocked = ($role != 'admin');
    $access[] = 'owngroup';
}
else {
    $access[] = 'own';
    $results = View::view_search($query, null,
                    (object) array('owner' => $USER->get('id')), null, $limit,
                    $offset, true, $sort, array(), false, $access, $tags);
    $accesslists = View::get_accesslists($USER->get('id'));
    $can_edit = true;
    $editlocked = false;
}

$count = $results->count;
$pages = $results->data;
$ret = array('total' => $count, 'html' => '');

if (is_array($pages)) {

    $sm = smarty_core();
    $pubdesc = get_string('statusofthispageis', 'interaction.pages');
    $wwwroot = get_config('wwwroot');

    PluginInteractionPages::add_access_info($pages, $accesslists, $editlocked);

    if (defined('GROUP')) {
        $backto = 'interaction/pages/grouppages.php?group=' . $group->id;
    }
    else {
        $backto = 'interaction/pages/';
    }

    foreach ($pages as &$page) {
        $page['menuitems'] = array();

        if ($page['is_editable'] && $can_edit) {
            $page['menuitems'][] = array(
                'title' => get_string('edittitleanddescription', 'view'),
                'url' => $wwwroot . 'view/edit.php?id=' . $page['id']
            );

            $page['menuitems'][] = array(
                'title' => get_string('editcontentandlayout', 'view'),
                'url' => $wwwroot . 'view/blocks.php?id=' . $page['id']
            );

            $page['menuitems'][] = array(
                'title' => get_string('editaccess', 'view'),
                'url' => $wwwroot . 'view/access.php?id=' . $page['id'] . '&backto=' . $backto,
                'classes' => 'editaccess'
            );

            // Do not allow copying of group homepage.
            if ($page['type'] !== 'grouphomepage') {
                $page['menuitems'][] = array(
                    'title' => get_string('copyview', 'view'),
                    'url' => '#',
                    'classes' => 'copypage'
                );
            }

            if (defined('GROUP')) {
                // Do not allow copying of group homepage.
                if ($page['type'] !== 'grouphomepage') {
                    $page['menuitems'][] = array(
                        'title' => get_string('copyforme', 'view'),
                        'url' => '#',
                        'classes' => 'copypage forme'
                    );
                }
            }

            if ($page['is_removable']) {
                $page['menuitems'][] = array(
                    'title' => get_string('delete'),
                    'url' => $wwwroot . 'view/delete.php?id=' . $page['id']
                );
            }
        }

        // Do not have edit access to the pages but can copy it for myself
        else if (isset($page['template']) && $page['template'] == 1) {
            // Do not allow copying of group homepage.
            if ($page['type'] !== 'grouphomepage') {
                $page['menuitems'][] = array(
                    'title' => get_string('copyforme', 'view'),
                    'url' => '#',
                    'classes' => 'copypage forme'
                );
            }
        }

        $type = $page['type'] === 'portfolio' ? 'page' : $page['type'];
        $author = !empty($page['group']) ? $page['sharedby'] : full_name($USER);

        $sm->assign('id', $page['id']);
        $sm->assign('group', $page['group']);
        $sm->assign('group_id', $page['group']);
        $sm->assign('author_id', $USER->get('id'));
        $sm->assign('author', $author);
        $sm->assign('title', $page['displaytitle']);
        $sm->assign('url', $page['fullurl']);
        $sm->assign('publicity', $page['shared_to']);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue',
                get_string('sharedto_' . $page['shared_to'], 'interaction.pages'));
        $sm->assign('menuitems', $page['menuitems']);
        $sm->assign('mtime', $page['mtime']);
        $sm->assign('tags', $page['jsontags']);
        $sm->assign('type', $type);
        $sm->assign('extraclasses', $type . '-item');
        $sm->assign('view', $page['id']);

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

json_reply(false, $ret);
