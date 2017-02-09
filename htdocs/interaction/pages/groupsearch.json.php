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
// Stupid group.php uses pieform, but does not include the lib.
require_once('pieforms/pieform.php');
require_once('group.php');
require_once('searchlib.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/lib/institution.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/lib/resourcetree.php');

$query = param_variable('query', '');
$filter = param_variable('filter', 'all');
$category = param_signed_integer('category', -1);
$owninstitution = param_integer('institution', 0);
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 5);
$tagparam = param_variable('tags', '');
$sortparam = param_variable('sort', 'name');
$tags = empty($tagparam) ? array() : explode(',', $tagparam);
$sort = ($sortparam == 'mtime') ? 'mtime DESC' : $sortparam;
$type = '';
$groups = array();

if (in_array($filter,
                array('allmygroups', 'admin', 'invite', 'notmember',
            'allgroups'))) {
    $type = $filter;
}
else {
    $type = 'allmygroups';
}

if (in_array($type, array('allmygroups', 'admin', 'invite'))) {
    $type = $type == 'allmygroups' ? 'all' : $type;
    $results = group_get_associated_groups($USER->get('id'), $type,
            $limit, $offset, $category, false, $tags, $query, $sort);
    $groups = $results['groups'];
}
else {
    $type = $type == 'allgroups' ? 'all' : $type;
    $results = search_group($query, $limit, $offset, $type, $category, false);
    $groups = $results['data'];

    // Gets more data about the groups found by search_group
    // including type if the user is associated with the group in some way.
    //
    // NB: Copied from group/find.php
    if ($groups) {
        $groupids = array();
        foreach ($groups as $group) {
            $groupids[] = $group->id;
        }
        $groups = get_records_sql_array("
        SELECT g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.request, g1.grouptype, g1.submittableto,
            g1.hidemembers, g1.hidemembersfrommembers, g1.urlid, g1.role, g1.membershiptype, g1.membercount, COUNT(gmr.member) AS requests,
            g1.editwindowstart, g1.editwindowend" . /* <KYVYT */", g1.mtime, g1.ctime, g1.institution AS inst " . /* KYVYT> */"
        FROM (
            SELECT g.id, g.name, g.description, g.public, g.jointype, g.request, g.grouptype, g.submittableto,
                g.hidemembers, g.hidemembersfrommembers, g.urlid, t.role, t.membershiptype, COUNT(gm.member) AS membercount,
                g.editwindowstart, g.editwindowend". /* <KYVYT */", g.mtime, g.ctime, g.institution " . /* KYVYT> */"
            FROM {group} g
            LEFT JOIN {group_member} gm ON (gm.group = g.id)
            LEFT JOIN (
                SELECT g.id, 'admin' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ? AND gm.role = 'admin')
                UNION
                SELECT g.id, 'member' AS membershiptype, gm.role AS role
                FROM {group} g
                INNER JOIN {group_member} gm ON (g.id = gm.group AND gm.member = ? AND gm.role != 'admin')
                UNION
                SELECT g.id, 'invite' AS membershiptype, gmi.role
                FROM {group} g
                INNER JOIN {group_member_invite} gmi ON (gmi.group = g.id AND gmi.member = ?)
                UNION
                SELECT g.id, 'request' AS membershiptype, NULL as role
                FROM {group} g
                INNER JOIN {group_member_request} gmr ON (gmr.group = g.id AND gmr.member = ?)
            ) t ON t.id = g.id
            WHERE g.id IN (" . implode($groupids, ',') . ')
            GROUP BY g.id, g.name, g.description, g.public, g.jointype, g.request, g.grouptype, g.submittableto,
                g.hidemembers, g.hidemembersfrommembers, g.urlid, t.role, t.membershiptype, g.editwindowstart, g.editwindowend
        ) g1
        LEFT JOIN {group_member_request} gmr ON (gmr.group = g1.id) ' .
                // <KYVYT
//                'LEFT JOIN {our_group_config} c ON (g1.id = c.group AND c.field = \'institutionmembersonly\') ' .
                // KYVYT>
                'GROUP BY g1.id, g1.name, g1.description, g1.public, g1.jointype, g1.request, g1.grouptype, g1.submittableto,
            g1.hidemembers, g1.hidemembersfrommembers, g1.urlid, g1.role, g1.membershiptype, g1.membercount, g1.editwindowstart, g1.editwindowend
        ORDER BY g1.'.$sort,
                array($USER->get('id'), $USER->get('id'), $USER->get('id'), $USER->get('id'))
        );

        // <KYVYT
//        $institutions = custom\Institution::mynames();
//        foreach ($groups AS &$d) {
//            if (!empty($d->institution) && !in_array($d->institution,
//                            $institutions)) {
//                $d->jointype = 'invite';
//            }
//        }
        // KYVYT>
    }
}
// <EKAMPUS
$institutions = custom\Institution::mynames();
foreach($groups AS &$d) {
    if (in_array($d->inst, $institutions)) {
        $d->myinst = 1;
    }
    else {
        $d->myinst = 0;
    }
}
 // EKAMPUS>
group_prepare_usergroups_for_display($groups, 'mygroups', custom\resourcetree_data('group', $USER->id));

$html = '';
$smarty = smarty_core();
$pubdesc = get_string('statusofthisgroupis', 'interaction.pages');
//$smartygroup = smarty_core();
foreach ($groups as $group) {
    $key = $group->grouptype == 'institution'
            ? 'grouptypeinstitution'
            : ($group->jointype == 'open'
                ? 'jointypeopen'
                : ($group->jointype == 'approve'  && $group->request == '1'
                    ? 'jointyperequest'
                    : ($group->jointype == 'approve' && $group->request == '0'
                        ? 'jointypeinvite'
                        : ($group->jointype == 'controlled'
                            ? 'jointypecontrolled'
                            : '-'))));

    $smarty->assign('id',$group->id);
    $smarty->assign('author_id', $group->admin_id);
    $smarty->assign('author', $group->admin_name);
    $smarty->assign('title', $group->name);
    $smarty->assign('url', group_homepage_url($group));
    $smarty->assign('publicity', '');
    $smarty->assign('publicitydescription', $pubdesc);
    $smarty->assign('publicityvalue', get_string($key, 'interaction.pages'));
    $smarty->assign('menuitems', $group->menuitems);
    $smarty->assign('mtime', $group->ctime);
    if (isset($group->tags)){
        $smarty->assign('tags', json_encode($group->tags));
    }
    $smarty->assign('extraclasses', 'group-item' . ($group->myinst ? ' myinst' : ''));
    $smarty->assign('type', 'group');
    $smarty->assign('cannoteditaccess', true);

    $html .= $smarty->fetch('gridder/item.tpl');
}
json_reply(false, array('html' => $html, 'total' => (int) $results['count']));
