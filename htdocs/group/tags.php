<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
// <EKAMPUS
// To prevent exception about storing institution info in non-api controlled
// groups when saving group data.
define('API_GROUP', 0);
// EKAMPUS>

define('INTERNAL', 1);
define('MENUITEM', 'groups/tags');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('group.php');
require_once(get_config('libroot') . 'antispam.php');

if ($id = param_integer('id', null)) {
    
    define('GROUP', $id);

    if (!group_user_access($id)) {
        $SESSION->add_error_msg(get_string('canteditdontown', 'group'));
        redirect('/interaction/pages/mygroups.php');
    }

    $group_data = group_get_groups_for_editing(array($id));
   
    if (count($group_data) != 1) {
        throw new GroupNotFoundException(get_string('groupnotfound', 'group', $id));
    }

    $group_data = $group_data[0];
    define('TITLE', get_string('editgrouptags', 'group', $group_data->name));
    // Fix dates to unix timestamps instead of formatted timestamps.
    $group_data->editwindowstart = isset($group_data->editwindowstart) ? strtotime($group_data->editwindowstart) : null;
    $group_data->editwindowend = isset($group_data->editwindowend) ? strtotime($group_data->editwindowend) : null;
}
else {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    
$oldtags = group_get_tags($id);

$form = array(
    'name'       => 'editgrouptags',
    'plugintype' => 'core',
    'pluginname' => 'groups',
    'elements'   => array(
        'tags' => array(
                'type' => 'tags',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => $oldtags,
        ),
        'submit' => array(
            'type'         => 'submitcancel',
            'value'        => array(get_string('savegrouptags', 'group'), get_string('cancel')),
            'goto'         => get_config('wwwroot') . 'interaction/pages/mygroups.php',
        ),
    ),
);

$editgrouptags = pieform($form);


function editgrouptags_cancel_submit() {
    redirect('/interaction/pages/mygroups.php');
}

function editgrouptags_submit(Pieform $form, $values) {
    global $USER, $SESSION, $group_data;


    $newvalues = array(
        'tags'    => $values['tags'],
    );

    db_begin();

    if ($group_data->id) {
        group_update_tags($group_data->id, $newvalues);
    }
    

    $SESSION->add_ok_msg(get_string('groupsaved', 'group'));

    db_commit();

    // Reload $group_data->urlid or else the redirect will fail
    if (get_config('cleanurls') && (!isset($values['urlid']) || $group_data->urlid != $values['urlid'])) {
        $group_data->urlid = get_field('group', 'urlid', 'id', $group_data->id);
    }

    redirect(group_homepage_url($group_data));
}


$smarty = smarty();
$smarty->assign('form', $editgrouptags);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('pagedescription', get_string('edittagsdescription', 'group'));
$smarty->display('form.tpl');
