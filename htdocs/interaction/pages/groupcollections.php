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
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'pages');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');

// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

$owner = null;
$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);
$urlparams = array();
if (!empty($groupid)) {
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    $group = group_current_group();
    // Check if user can edit group collections <-> user can edit group views
    $role = group_user_access($group->id);
    $canedit = $role && group_role_can_edit_views($group, $role);
    if (!$role) {
        throw new GroupAccessDeniedException(get_string('cantlistgroupcollections', 'collection'));
    }

    define('SUBTITLE', get_string('groupcollections', 'collection'));
    define('TITLE', $group->name);
    $urlparams['group'] = $groupid;
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite/collections');
        define('TITLE', get_string('sitecollections', 'collection'));
        // Check if user is a site admin
        $canedit = $USER->get('admin');
        if (!$canedit) {
            throw new AccessDeniedException(get_string('cantlistinstitutioncollections', 'collection'));
        }
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions/institutioncollections');
        define('TITLE', get_string('institutioncollections', 'collection'));
        // Check if user is a institution admin
        $canedit = $USER->get('admin') || $USER->is_institutional_admin();
        if (!$canedit) {
            throw new AccessDeniedException(get_string('cantlistinstitutioncollections', 'collection'));
        }
        require_once('institution.php');
        // Get list of availlable institutions
        $s = institution_selector_for_page($institutionname, get_config('wwwroot') . 'collection/index.php');
        $institutionname = $s['institution'];
        if ($institutionname === false) {
            $smarty = smarty();
            $smarty->display('admin/users/noinstitutions.tpl');
            exit;
        }
    }
    define('SUBTITLE', '');
    $urlparams['institution'] = $institutionname;
}
else {
    define('MENUITEM', 'myportfolio/collection');
    $owner = $USER->get('id');
    $canedit = true;
    define('SUBTITLE', '');
    define('TITLE', get_string('Collections', 'collection'));
}
$baseurl = get_config('wwwroot') . 'collection/index.php';
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

$wwwroot = get_config('wwwroot');
$data = Collection::get_mycollections_data($offset, $limit, $owner, $groupid, $institutionname);
foreach ($data->data as $value) {
    $collection = new Collection($value->id);
    $views = $collection->get('views');
    $tags = $collection->get_tags();
    if (!empty($views)) {
        $value->views = $views['views'];
    }
    
    if (!empty($views)){
        $value->tags = $tags;
    }
    
    $value->menuitems = array(
        array(
            'url' => $wwwroot . 'collection/edit.php?id=' . $value->id,
            'title' => get_string('editcontent', 'view')),
        array(
            'url' => $wwwroot . 'collection/views.php?id=' . $value->id,
            'title' => get_string('manageviews', 'collection')
        ));
    if (isset($value->views) && is_array($value->views)) {
        $value->menuitems[] = array(
            // < EKAMPUS
            'url' => $wwwroot . 'view/access.php?id=' . $value->views[0]->id .
                '&hidetabs=1&backto=interaction/pages/groupcollections.php?group=' .
                $value->views[0]->groupdata->id,
            // EKAMPUS >
            'title' => get_string('editaccess', 'view'),
            'classes' => 'editaccess',
        );
    }
     
    $value->menuitems[] = array(
        'url' => $wwwroot . 'collection/delete.php?id=' . $value->id,
        'title' => get_string('delete')
    );
}

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'));
$urlparamsstr = '';
if ($urlparams) {
    $urlparamsstr = '&' . http_build_query($urlparams);
}
if ($canedit) {
    $smarty->assign('addonelink', get_config('wwwroot') . 'collection/edit.php?new=1' . $urlparamsstr);
}

$js = '';

if (!empty($institutionname) && ($institutionname != 'mahara')) {
    $smarty->assign('institution', $institutionname);
    $smarty->assign('institutionselector', $s['institutionselector']);
    $js .= '\n' . $s['institutionselectorjs'];
//    $smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs']);
}

$fulltextsearch = 0;
$createcollection = get_config('wwwroot').'collection/edit.php?new=1&group=' . $groupid;
$js .= <<<JS
        
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/pages/js/collection'], function (c) {
        c.init({ newurl: '$createcollection', fulltextsearch: $fulltextsearch, groupid: $groupid, total: 0 });
    });
});
JS;

$pages = objectToArray($data->data);
$accesslists = View::get_accesslists(null, $groupid);
$tags = PluginInteractionPages::get_collection_tags(null, $groupid);

PluginInteractionPages::add_access_info($pages, $accesslists, !$canedit, true);

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('canedit', $canedit);
$smarty->assign('urlparamsstr', $urlparamsstr);
$smarty->assign('pages', $pages);
$smarty->assign('tags', $tags);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('PAGESUBHEADING', SUBTITLE);
$smarty->display('interaction:pages:collectionpages.tpl');