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

define('INTERNAL', 1);

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'collection');
define('SECTION_PAGE', 'delete');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');

$id = param_integer('id');
$collection = new Collection($id);
if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('cantdeletecollection', 'collection'));
}
// <EKAMPUS
$islearningobject = $collection->get('type') === 'learningobject';
// EKAMPUS>
$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$urlparams = array();
if (!empty($groupid)) {
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    $urlparams['group'] = $groupid;
}
else if (!empty($institutionname)) {
    if ($institutionname == 'mahara') {
        define('ADMIN', 1);
        define('MENUITEM', 'configsite/collections');
    }
    else {
        define('INSTITUTIONALADMIN', 1);
        define('MENUITEM', 'manageinstitutions/institutioncollections');
    }
    $urlparams['institution'] = $institutionname;
}
else {
    // <EKAMPUS
    $menuitem = $islearningobject ? 'learningobjects' : 'myportfolio/collection';
    define('MENUITEM', $menuitem);
    // EKAMPUS>
}
// <EKAMPUS
$module = $islearningobject ? 'interaction.learningobject' : 'collection';
define('TITLE', get_string('deletespecifiedcollection', $module, $collection->get('name')));
// EKAMPUS>

// <EKAMPUS
$baseurl = get_config('wwwroot') . ($islearningobject
        ? 'interaction/learningobject/index.php'
        : 'interaction/pages/' . (!empty($groupid) ? 'groupcollections.php' : 'collections.php'));
// EKAMPUS>
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}

if ($collection->is_submitted()) {
    $submitinfo = $collection->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'collection', $submitinfo->name));
}

$form = pieform(array(
    'name' => 'deletecollection',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => $baseurl,
        ),
    ),
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('message', get_string('collectionconfirmdelete', $module)); // EKAMPUS
$smarty->assign('form', $form);
$smarty->display('collection/delete.tpl');

function deletecollection_submit(Pieform $form, $values) {
    global $SESSION, $collection, $baseurl, $module; // EKAMPUS
    $collection->delete();
    $SESSION->add_ok_msg(get_string('collectiondeleted', $module)); // EKAMPUS
    redirect($baseurl);
}
