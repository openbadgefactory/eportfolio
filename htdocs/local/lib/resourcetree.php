<?php
namespace custom;
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
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


function resourcetree_data($type, $userid, $groupid=0) {

    $userid =  (int) $userid;
    $groupid = (int) $groupid;

    $data = array();

    $sql = '';
    $param = array();

    $sql = "SELECT * FROM {our_resourcetree} WHERE type LIKE ? AND usr = ?";
    $param = array($type . '%', $userid);
    if ($groupid != 0) {
        $sql = "SELECT * FROM {our_resourcetree} WHERE type LIKE ? AND `group` = ?";
        $param = array($type, $groupid);
    }
    $data['tree'] = \get_records_sql_array($sql, $param);

    $sql = "SELECT * FROM {our_resourcetree_folder} WHERE type = ? AND usr = ? ORDER BY parent, title";
    $param = array($type, $userid);
    if ($groupid != 0) {
        $sql = "SELECT * FROM {our_resourcetree_folder} WHERE type = ? AND `group` = ? ORDER BY parent, title";
        $param = array($type, $groupid);
    }
    $data['folder'] = \get_records_sql_assoc($sql, $param);


    if ($type === 'view') {
        $sql = "SELECT id, title, description, submittedgroup, submittedtime FROM {view} WHERE type = 'portfolio' AND owner = ? ORDER BY title";
        $param = array($userid);
        if ($groupid != 0) {
            $sql = "SELECT id, title, description, submittedgroup, submittedtime  FROM {view} WHERE type = 'portfolio' AND `group` = ? ORDER BY title";
            $param = array($groupid);
        }
        $data['full'] = \get_records_sql_assoc($sql, $param);
        if (!empty($data['full'])) {
            foreach ($data['full'] AS &$d) {
                if ($d->submittedgroup) {
                    $group_name = get_field('group', 'name', 'id', $d->submittedgroup);
                    $d->description = '<em>' . get_string( 'viewsubmittedtogroupon', 'view', get_config('wwwroot') . 'group/view.php?id=' . $d->submittedgroup, hsc($group_name), format_date(strtotime($d->submittedtime))) . '</em>';
                }
                else {
                    $d->description = str_shorten_html($d->description, 75, true);
                }
                $d->kind = 'view';
            }
        }
    }


    if ($type === 'watchlist') {
        $data['full'] = array();
        $views = \PluginArtefactWatchlist::views();
        if (!empty($views)) foreach ($views AS &$v) {
            if (empty($v->owner_name)) {
                $v->owner_name = display_name($v);
            }
            $data['full'][$v->vid]['view'] = $v;
        }

        $subs = \PluginArtefactWatchlist::subscriptions();
        if (!empty($subs)) foreach ($subs AS &$s) {
            if ($s->kind == 'forum') {
                $data['full'][$s->fid]['forum'] = $s;
            } else {
                $data['full'][$s->id]['topic'] = $s;
            }
        }
    }


    if ($type === 'group') {
        $results = \group_get_associated_groups($userid, 'all', null, null);
        \group_prepare_usergroups_for_display($results['groups'], 'mygroups');

        $data['full'] = array();
        if (!empty($results['groups'])) {
            foreach ($results['groups'] AS &$g) {
                $g->settingsdescription = \group_display_settings($g);
                $smarty = \smarty_core();
                $smarty->assign('group', $g);
                $smarty->assign('returnto', 'mygroups');
                $g->details = $smarty->fetch("group/group.tpl");

                $g->kind = 'group';
                $data['full'][$g->id] = $g;
            }
        }

    }

    foreach (array('full', 'tree', 'folder') AS $key) {
        if (empty($data[$key])) {
            $data[$key] = new \stdClass();
        }
    }

    return $data;
}

function resourcetree($type, $userid=null, $groupid=null) {
    global $USER;
    if (is_null($userid) && is_null($groupid)) {
        $userid = $USER->get('id');
    }

    $userid =  (int) $userid;
    $groupid = (int) $groupid;

    $smarty = \smarty_core();

    $jsdata = json_encode(resourcetree_data($type, $userid, $groupid));

    $wwwroot = get_config('wwwroot');
    $initjs = "strings.dosubmit = '" . get_string('save') . "'; ";
    $initjs .= "strings.docancel = '" . get_string('cancel') . "'; ";
    $initjs .= " var tree = new ResourceTree('{$type}', '{$userid}', '{$groupid}', '{$wwwroot}'); addLoadEvent(function () {tree.build({$jsdata}); });";

    $smarty->assign('initjs', $initjs);

    if ($type == 'view') {
        $smarty->assign('rootname', get_string('allviews', 'view'));
    }
    else if ($type == 'group') {
        $smarty->assign('rootname', get_string('allgroups', 'group'));
    }
    else if ($type == 'watchlist') {
        $smarty->assign('rootname', get_string('mywatchlist', 'artefact.watchlist'));
    }

    return $smarty->fetch('form/resourcetree.tpl');
}

