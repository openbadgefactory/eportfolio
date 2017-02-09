<?php
/**
 * Library file for miscellaneous local customisations.
 *
 * For simple customisation of a Mahara site, the core code will call some local_* functions
 * which may be defined in this file.
 *
 * Functions that will be called by core:
 *  - local_main_nav_update($menu):        modify the main navigation menu in the header
 *  - local_xmlrpc_services():              add custom xmlrpc functions
 *  - local_can_remove_viewtype($viewtype): stop users from deleting views of a particular type
 *  - local_progressbar_sortorder($options): Change the order of items in the profile completion progress bar
 */
defined('INTERNAL') || die();

require_once(get_config('docroot') . '/lib/view.php');

/* * * custom libraries ** */
require_once(dirname(__FILE__) . '/lib/view.php');

function local_right_nav_update(&$menu) {
    // We don't want to see Study journal in right nav.
    if (isset($menu['studyjournal'])) {
        unset($menu['studyjournal']);
    }

    if (isset($menu['assignments'])) {
        unset($menu['assignments']);
    }

    if (isset($menu['learningobject'])) {
        unset($menu['learningobject']);
    }

    if (isset($menu['ehops'])) {
        unset($menu['ehops']);
    }

    // Import/Export
    $menu['myportfolio/import'] = array(
        'path' => 'settings/import',
        'url' => 'import/index.php',
        'title' => get_string('Import', 'admin'),
        'weight' => 34,
    );
    $menu['myportfolio/export'] = array(
        'path' => 'settings/export',
        'url' => 'export/index.php',
        'title' => get_string('Export', 'export'),
        'weight' => 35,
    );

    if (get_config('cloudfile')) {
        $menu['settings/cloudfiles'] = array(
            'path' => 'settings/cloudfiles',
            'url' => 'artefact/cloudfile/',
            'title' => get_string('Cloudfiles', 'artefact.cloudfile'),
            'weight' => 71
        );
    }
}

function local_main_nav_update(&$menu) {

    // Admin
    if (isset($menu['adminhome'])) {
        return $menu;
    }

    // Institution admin
    if (isset($menu['configusers'])) {
        return $menu;
    }

    // Institution staff
    if (isset($menu['usersearch'])) {
        return $menu;
    }

    // Seinä
    $menu['home']['path'] = '';

    $menu['myportfolio/collection']['weight'] = 5;
    $menu['myportfolio/views']['url'] = 'interaction/pages/index.php';
    $menu['myportfolio']['url'] = 'interaction/pages/collections.php';
    $menu['myportfolio/collection']['url'] = 'interaction/pages/collections.php';

    $menu['content/files']['path'] = 'myportfolio/files';
    $menu['content/blogs']['path'] = 'myportfolio/blogs';
    $menu['content/resume']['path'] = 'myportfolio/resume';
    $menu['content/resume']['url'] = 'artefact/multiresume/index.php';

    unset($menu['myportfolio/share']);
    unset($menu['myportfolio/sharedviews']);
    unset($menu['myportfolio/export']);
    unset($menu['myportfolio/import']);
    unset($menu['myportfolio/skins']);
    unset($menu['content/resume']);

    // Ryhmätyötilat
    $menu['groups']['url'] = 'interaction/pages/mygroups.php';

    foreach ($menu as $key => $item) {
        if (strpos($key, "groups/") === 0) {
            unset($menu[$key]);
        }
    }

    // Galleria
    $menu['galleria'] = array(
        'path' => 'galleria',
        'url' => 'interaction/pages/sharedviews.php',
        'title' => get_string('galleria', 'interaction.pages'),
        'weight' => 80
    );
    // Ohajus
    $menu['ohjaus'] = array(
        'path' => 'ohjaus',
        'url' => 'interaction/pages/returns.php',
        'title' => get_string('feedbackandtutoring'),
        'weight' => 70
    );
    $menu['ohjaus/returns'] = array(
        'path' => 'ohjaus/returns',
        'url' => 'interaction/pages/returns.php',
        'title' => get_string('returns'),
        'weight' => 70
    );

    // Remove extras.
    unset($menu['content']);
}

function is_teacher(User $user = null) {
    if (is_null($user)) {
        global $USER;
        $user = $USER;
    }

    return ($user->is_institutional_admin() || $user->is_institutional_staff());
}

function is_teacher_in($institution, User $user = null) {
    if (is_null($user)) {
        global $USER;
        $user = $USER;
    }

    return ($user->is_institutional_admin($institution) || $user->is_institutional_staff($institution));
}

function user_is_teacher($userid) {
    if ($userid) {
        $user = new User();

        try {
            $user->find_by_id($userid);
            return is_teacher($user);
        }
        catch (Exception $e) {
            return false;
        }
    }

    return false;
}

function get_students_in_groups(array $groups) {
    if (count($groups) === 0) {
        return array();
    }

    $placeholders = implode(",", array_fill(0, count($groups), '?'));
    $ids = array_map(function ($g) {
        return $g->id;
    }, $groups);

    $records = get_records_sql_array("
        SELECT u.*, gm.group
          FROM {usr} u
     LEFT JOIN {group_member} gm ON u.id = gm.member
         WHERE gm.role = 'member' AND gm.group IN ($placeholders)", $ids);

    $ret = array();

    if (is_array($records)) {
        foreach ($records as $record) {
            if (!isset($ret[$record->group])) {
                $ret[$record->group] = array();
            }

            $ret[$record->group][] = $record;
        }
    }

    return $ret;
}

/**
 * Returns all institutions in system.
 *
 * @param boolean $includedefault Whether to include the default 'Mahara'
 *      institution.
 * @param boolean $namesandidsonly Whether to fetch only the names and ids of
 *      the institutions instead of all data.
 * @return type
 */
function get_institutions($includedefault = false, $namesandidsonly = false) {
    $fields = $namesandidsonly ? 'name,displayname' : '*';
    return $includedefault ? get_records_array('institution', '', 'displayname ASC',
            $fields) : get_records_select_array('institution',
                    "name != 'mahara'", null, 'displayname ASC', $fields);
}

function get_default_institution(User $user = null) {
    if (is_null($user)) {
        global $USER;
        $user = $USER;
    }

    $owninstitutions = get_records_sql_array("
        SELECT i.name
          FROM {institution} i
     LEFT JOIN {usr_institution} ui ON i.name = ui.institution
         WHERE ui.usr = ?", array($user->get('id')));

    if ($owninstitutions) {
        return $owninstitutions[0]->name;
    }

    return false;
}

function get_groups($institution, $namesonly = true) {

    $records = get_records_select_array('group',
            'grouptype = ? AND institution = ? AND deleted = 0',
            array('system', $institution));

    if (!$namesonly) {
        return $records;
    }

    $ret = array();

    if (is_array($records)) {
        foreach ($records as $item) {
            $ret[] = (object) array('id' => $item->id, 'name' => $item->name);
        }
    }

    return $ret;
}

function get_user_groups(User $user = null) {
    if (is_null($user)) {
        global $USER;
        $user = $USER;
    }

    $groups = group_get_user_groups($user->get('id'));
    $ret = array();

    foreach ($groups as $group) {
        if ($group->grouptype === 'system') {
            $ret[] = (object) array('id' => $group->id, 'name' => $group->name);
        }
    }

    return $ret;
}

function get_workspaces($institution) {
    return get_records_select_array('group',
            'grouptype != ? AND institution = ?', array('system', $institution));
}

function find_artefact_view($artefactid, $artefactviews) {
     $artefactviewid = null;

    // Find the corresponding views for each journal.
    foreach ($artefactviews as $viewid => $obj) {
        $cfg = unserialize($obj->configdata);
        if (isset($cfg['artefactid']) && $cfg['artefactid'] == $artefactid) {
            $artefactviewid = $viewid;
            break;
        }
        if (isset($cfg['artefactids']) && in_array($artefactid, $cfg['artefactids'])){
            $artefactviewid = $viewid;
            break;
        }
    }
    return $artefactviewid;
}

function get_user_artefact_views($artefacttype, $userid = null) {
    if (is_null($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }

    $artefactviews = get_records_sql_assoc("
        SELECT v.id, b.id as 'bid', b.blocktype, b.configdata
          FROM {view} v
     LEFT JOIN {block_instance} b ON (v.id = b.view)
         WHERE v.owner = ? AND v.type = ?
      ORDER BY v.id", array($userid, $artefacttype));

    return is_array($artefactviews) ? $artefactviews : array();
}

function find_artefact_publicity($artefactid, &$artefactviews, &$accesslists) {
    $artefactviewid = null;

    // Find the corresponding views for each journal.
    foreach ($artefactviews as $viewid => $obj) {
        $cfg = unserialize($obj->configdata);
        if (isset($cfg['artefactid']) && $cfg['artefactid'] == $artefactid) {
            $artefactviewid = $viewid;
            break;
        }
        if (isset($cfg['artefactids']) && in_array($artefactid, $cfg['artefactids'])){
            $artefactviewid = $viewid;
            break;
        }
    }
    // Selected journal isn't on any page.
    if (is_null($artefactviewid)) {
        return 'private';
    }
    return get_publicity_from_accesslists($accesslists, $artefactviewid);
}

function get_publicity_from_accesslists(&$accesslists, $viewid) {
    // Try to find the page from views first..
    if (is_array($accesslists['views']) && isset($accesslists['views'][$viewid])) {
        return get_publicity_from_view_or_collection($accesslists['views'][$viewid]);
    }
    // .. and then from collections.
    else if (is_array($accesslists['collections'])) {
        foreach ($accesslists['collections'] as $collection) {
            $in_collection = in_array($viewid, array_keys($collection['views']));

            if ($in_collection) {
                return get_publicity_from_view_or_collection($collection);
            }
        }
    }
    return 'private';
}

function get_publicity_from_view_or_collection($item) {
    if (isset($item['accessgroups'])) {
        $accesstypes = array();

        foreach ($item['accessgroups'] as $group) {
            $accesstypes[] = $group['accesstype'];
        }

        if (in_array('public', $accesstypes)) {
            return 'public';
        }
        else if (count(array_intersect(array('loggedin', 'group', 'friends',
                    'institution', 'user'), $accesstypes)) > 0) {
            return 'published';
        }
        // Just in case we ever need a separate status for secret urls.
        else if (in_array('token', $accesstypes)) {
            return 'published';
        }
    }

    return 'private';
}

function objectToArray($o) {
    $a = array();
    foreach ($o as $k => $v) {
        $a[$k] = (is_array($v) || is_object($v)) ? objectToArray($v): $v;
    }
    return $a;
}

function get_group_institution($groupid) {
    return get_field('group', 'institution', 'id', $groupid);
}

function add_learningobject_vars($collection, &$smarty, $viewid) {
    global $USER;

    safe_require('interaction', 'learningobject');

    if ($collection !== false) {
        $smarty->assign('collectionid', $collection->get('id'));

        if ($instructors = InteractionLearningobjectInstance::get_returned_collection_instructors($collection->get('id'))) {
            $smarty->assign('instructors', $instructors);
            $returnedcollection = InteractionLearningobjectInstance::get_returned_collection($collection->get('id'));
            $prevreturndate = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecentfull'), strtotime($returnedcollection->prev_return_date));
            $smarty->assign('prevreturndate', $prevreturndate);
        }
        else {
            if ($parent = InteractionLearningobjectInstance::get_collection_parent($collection)){
                $defaultinstructors = InteractionLearningobjectInstance::get_instructors($parent);
                foreach($defaultinstructors as $key => $teacher){
                    if ($USER->get('id') == $teacher->user){
                        unset($defaultinstructors[$key]);
                    }
                }
                $smarty->assign('defaultinstructors', $defaultinstructors);
            }
        }
        if ($collection->get('type') === 'learningobject'){
             $smarty->assign('learningobject', true);
        }
    }
    else {
        if ($instructors = InteractionLearningobjectInstance::get_returned_view_instructors($viewid)) {
            $smarty->assign('instructors', $instructors);
            $returnedview = InteractionLearningobjectInstance::get_returned_view($viewid);
            $prevreturndate = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecentfull'), strtotime($returnedview->prev_return_date));
            $smarty->assign('prevreturndate', $prevreturndate);
        }
    }
}