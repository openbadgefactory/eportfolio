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
define('SECTION_PAGE', 'views');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('collection.php');

$id = param_integer('id');

// view addition/displayorder values
$view = param_integer('view',0);
$direction = param_variable('direction','');

$collection = new Collection($id);
if (!$USER->can_edit_collection($collection)) {
    throw new AccessDeniedException(get_string('canteditcollection', 'collection'));
}
// <EKAMPUS
$is_learningobject = $collection->get('type') === 'learningobject';
$module = $is_learningobject ? 'interaction.learningobject' : 'collection';
// EKAMPUS>
$owner = $collection->get('owner');
$groupid = $collection->get('group');
$institutionname = $collection->get('institution');
$urlparams = array();
if (!empty($groupid)) {
    define('MENUITEM', 'groups/collections');
    define('GROUP', $groupid);
    $group = group_current_group();
    define('TITLE', $group->name . ' - ' . get_string('editcollection', 'collection'));
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
    define('TITLE', get_string('editcollection', 'collection'));
    $urlparams['institution'] = $institutionname;
}
else {
    // <EKAMPUS
    define('MENUITEM', $is_learningobject ? 'learningobjects' : 'myportfolio/collection');
    define('TITLE', get_string('editcollection', $module));
    // EKAMPUS>
}
// <EKAMPUS
define('SUBTITLE', $collection->get('name'). ': ' . get_string('editviews', $module));
$baseurl = get_config('wwwroot') . ($is_learningobject ? 'interaction/learningobject/index.php' :
        'interaction/pages/' .
        (!empty($groupid) ? 'groupcollections.php' : 'collections.php'));
$createviewform = pieform(create_view_form($groupid, $institutionname, null, null, $id));
// EKAMPUS>
if ($urlparams) {
    $baseurl .= '?' . http_build_query($urlparams);
}
if ($collection->is_submitted()) {
    $submitinfo = $collection->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'collection', $submitinfo->name));
}

if ($view AND !empty($direction)) {
    $collection->set_viewdisplayorder($view,$direction);
    redirect('/collection/views.php?id='.$id);
}

$views = $collection->views();

if ($views) {
    foreach ($views['views'] as &$v) {
        $v->remove = pieform(array(
            'name' => 'removeview_' . $v->view,
            'successcallback' => 'removeview_submit',
            'elements' => array(
                'view' => array(
                    'type' => 'hidden',
                    'value' => $v->view,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'confirm' => get_string('viewconfirmremove', 'collection'),
                    'value' => get_string('remove'),
                ),
            ),
        ));
    }
}

$elements = array();
$viewsform = null;
if ($available = Collection::available_views($owner, $groupid, $institutionname)) {
    foreach ($available as $a) {
        $elements['view_'.$a->id] = array(
            'type'      => 'checkbox',
            'title'     => $a->title,
        );
    }
    $elements['submit'] = array(
        'type' => 'submit',
        'value' => get_string('addviews','collection'),
        'goto' => get_config('wwwroot') . 'collection/views.php?id='.$id,
    );

    $viewsform = pieform(array(
        'name' => 'addviews',
        'plugintype' => 'core',
        'pluginname' => 'collection',
        'autofocus' => false,
        'method'   => 'post',
        'elements' => $elements,
    ));
}

// <EKAMPUS
$extraconfig = $is_learningobject ? array('bodyclasses' => array('learningobject')) : array();

$javascript[] = 'tablerenderer';
$javascript[] = get_config('wwwroot') . 'local/js/lib/bootstrap.min.js';
$headers = array('<link rel="stylesheet" href="' . get_config('wwwroot') . 'local/css/bootstrap.css" />');
//$smarty = smarty(array(), array(), array(), $extraconfig);
$smarty = smarty($javascript, $headers, array(), $extraconfig);
$smarty->assign('owner', $owner);
$smarty->assign('collectionid', $collection->get('id'));
if ($owner){
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
}
// EKAMPUS>
if (!empty($groupid)) {
    $smarty->assign('PAGESUBHEADING', SUBTITLE);
    $smarty->assign('PAGEHELPNAME', '0');
    $smarty->assign('SUBPAGEHELPNAME', '1');
}
else {
    $smarty->assign('PAGEHEADING', SUBTITLE);
}

// <EKAMPUS
$smarty->assign('createviewform', $createviewform);
$smarty->assign('is_learningobject', $is_learningobject);
// EKAMPUS>

$smarty->assign('baseurl', $baseurl);
$smarty->assign('displayurl',get_config('wwwroot').'collection/views.php?id='.$id);
$smarty->assign('removeurl',get_config('wwwroot').'collection/deleteview.php?id='.$id);
$smarty->assign_by_ref('views', $views);
$smarty->assign_by_ref('viewsform', $viewsform);
$smarty->display('collection/views.tpl');

function addviews_submit(Pieform $form, $values) {
    // <EKAMPUS
    global $SESSION, $collection, $is_learningobject;
    $module = $is_learningobject ? 'interaction.learningobject' : 'collection';
    // EKAMPUS>
    $count = $collection->add_views($values);
    if ($count > 1) {
        $SESSION->add_ok_msg(get_string('viewsaddedtocollection', $module)); // EKAMPUS
    }
    else {
        $SESSION->add_ok_msg(get_string('viewaddedtocollection', $module)); // EKAMPUS
    }
    redirect('/collection/views.php?id='.$collection->get('id'));

}

function removeview_submit(Pieform $form, $values) {
    global $SESSION, $collection;
    $collection->remove_view((int)$values['view']);
    $SESSION->add_ok_msg(get_string('viewremovedsuccessfully','collection'));
    redirect('/collection/views.php?id='.$collection->get('id'));
}
