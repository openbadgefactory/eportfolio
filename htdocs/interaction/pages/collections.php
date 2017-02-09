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
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'pages');
define('SECTION_PAGE', 'collections');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'collection.php');
require_once('lib.php');

$owner = null;
$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);
$urlparams = array();
$wwwroot = get_config('wwwroot');
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
        $s = institution_selector_for_page($institutionname, $wwwroot . 'collection/index.php');
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
    define('TITLE', get_string('Collectionstitle', 'collection'));
}

$tags = PluginInteractionPages::get_collection_tags($USER);
$createcollection = get_config('wwwroot').'collection/edit.php?new=1';
$fulltextsearch = 1;
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/pages/js/collection'], function (c) {
        c.init({ newurl: '$createcollection', fulltextsearch: $fulltextsearch, groupid: 0, total: 0 });
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array(),
        array('view' => array('editaccess', 'deletethisview', 'editcontentandlayout')),
        array());
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('tags', $tags);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('canedit', $canedit);
$smarty->assign('fulltextsearch', $fulltextsearch);
$smarty->display('interaction:pages:collectionpages.tpl');
