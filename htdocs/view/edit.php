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
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');

// <EKAMPUS
$backto = param_variable('backto', '');
$from = param_alpha('from', null);
// EKAMPUS>
$view = new View(param_integer('id'));

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

// Make sure we can edit view title for this type.
// If not, then we probably meant to edit blocks
if (!$view->can_edit_title()) {
    redirect('/view/blocks.php?id=' . $view->get('id'));
}

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

$group = $view->get('group');
$institution = $view->get('institution');
$view->set_edit_nav();
$view->set_user_theme();

if ($group && !group_within_edit_window($group)) {
    throw new AccessDeniedException();
}

$new = param_boolean('new', 0);
/*<EKAMPUS */
if ($view->get('type') == 'profile') {
    $profile = true;
    $title = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title);
}
else if ($view->get('type') == 'dashboard') {
    $dashboard = true;
    $title = get_string('usersdashboard', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title);
}
else if ($view->get('type') == 'grouphomepage') {
    $title = get_string('grouphomepage', 'view');
    $groupurl = group_homepage_url(get_record('group', 'id', $view->get('group')), false);
    define('TITLE', $title);
}
else if ($new) {
    define('TITLE', get_string('edittitleanddescription', 'view'));
}
else {
    define('TITLE', $view->get('title'));
}
/* EKAMPUS >*/
require_once('pieforms/pieform.php');

$formatstring = '%s (%s)';
$ownerformatoptions = array(
    FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
    FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
    FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
);

$displayname = display_name($USER);
// <EKAMPUS
// Do not show display name in owner format options.
//if ($displayname !== '') {
//    $ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('preferredname'), $displayname);
//}
// EKAMPUS>
$studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
// <EKAMPUS
// Do not show student id in owner format options.
//if ($studentid !== '') {
//    $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
//}
// EKAMPUS>

// Clean urls are only available for portfolio views owned by groups or users who already
// have their own clean profiles or group homepages.
if ($urlallowed = get_config('cleanurls') && $view->get('type') == 'portfolio' && !$institution) {
    if ($group) {
        $groupdata = get_record('group', 'id', $group);
        if ($urlallowed = !is_null($groupdata->urlid) && strlen($groupdata->urlid)) {
            $cleanurlbase = group_homepage_url($groupdata) . '/';
        }
    }
    else {
        $userurlid = $USER->get('urlid');
        if ($urlallowed = !is_null($userurlid) && strlen($userurlid)) {
            $cleanurlbase = profile_url($USER) . '/';
        }
    }
}

$editview = array(
    'name'     => 'editview',
    'method'   => 'post',
    'autofocus' => 'title',
    'autoselect' => $new ? 'title' : null,
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'id' => array(
            'type'  => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => $view->get('title'),
            'rules'        => array( 'required' => true ),
        ),
        'urlid' => array(
            'type'         => 'text',
            'title'        => get_string('viewurl', 'view'),
            'prehtml'      => '<span class="description">' . (isset($cleanurlbase) ? $cleanurlbase : '') . '</span> ',
            'description'  => get_string('viewurldescription', 'view') . ' ' . get_string('cleanurlallowedcharacters'),
            'defaultvalue' => $new ? null : $view->get('urlid'),
            'rules'        => array('maxlength' => 100, 'regex' => get_config('cleanurlvalidate')),
            'ignore'       => !$urlallowed || $new,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description','view'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => $view->get('description'),
            'rules'        => array('maxlength' => 65536),
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'defaultvalue' => $view->get('tags'),
            'help'         => true,
        ),
    ),
);

if ($group) {
    $grouproles = $USER->get('grouproles');
    if ($grouproles[$group] == 'admin') {
        $editview['elements']['locked'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('Locked', 'view'),
            'description'  => get_string('lockedgroupviewdesc', 'view'),
            'defaultvalue' => $view->get('locked'),
            'disabled'     => $view->get('type') == 'grouphomepage', // This page unreachable for grouphomepage anyway
        );
    }
}

if (!($group || $institution)) {
    $default = $view->get('ownerformat');
    // <EKAMPUS
    // If the page was saved before FORMAT_NAME_DISPLAYNAME was taken away
    // from $ownerformatoptions, pieform gives an error about invalid value.
    // Check that the saved format is still valid.
    if (!$default || !array_key_exists($default, $ownerformatoptions)) {
        $default = FORMAT_NAME_FIRSTNAMELASTNAME; //FORMAT_NAME_DISPLAYNAME;
        // EKAMPUS>
    }
    $editview['elements']['ownerformat'] = array(
        'type'         => 'select',
        'title'        => get_string('ownerformat','view'),
        'description'  => get_string('ownerformatdescription','view'),
        'options'      => $ownerformatoptions,
        'defaultvalue' => $default,
        'rules'        => array('required' => true),
    );
}

$editview['elements']['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('saveandcontinue'),
);


$editview = pieform($editview);

function editview_validate(Pieform $form, $values) {
    global $view;

    if (isset($values['urlid']) && $values['urlid'] != $view->get('urlid')) {
        if (strlen($values['urlid']) < 3) {
            $form->set_error('urlid', get_string('rule.minlength.minlength', 'pieforms', 3));
        }
        else if ($group = $view->get('group') and record_exists('view', 'group', $group, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('groupviewurltaken', 'view'));
        }
        else if ($owner = $view->get('owner') and record_exists('view', 'owner', $owner, 'urlid', $values['urlid'])) {
            $form->set_error('urlid', get_string('userviewurltaken', 'view'));
        }
    }
}

function editview_submit(Pieform $form, $values) {
    // <EKAMPUS
    global $new, $view, $SESSION, $urlallowed, $from;
    // EKAMPUS>

    $view->set('title', $values['title']);
    $view->set('description', $values['description']);
    $view->set('tags', $values['tags']);
    if (isset($values['locked'])) {
        $view->set('locked', (int)$values['locked']);
    }
    if (isset($values['ownerformat']) && $view->get('owner')) {
        $view->set('ownerformat', $values['ownerformat']);
    }
    if (isset($values['urlid'])) {
        $view->set('urlid', strlen($values['urlid']) == 0 ? null : $values['urlid']);
    }
    else if ($new && $urlallowed) {
        // Generate one automatically based on the title
        $desired = generate_urlid($values['title'], get_config('cleanurlviewdefault'), 3, 100);
        $ownerinfo = (object) array('owner' => $view->get('owner'), 'group' => $view->get('group'));
        $view->set('urlid', View::new_urlid($desired, $ownerinfo));
    }
    $SESSION->add_ok_msg(get_string('viewsavedsuccessfully', 'view'));
    $view->commit();
    // <EKAMPUS
    redirect('/view/layout.php?id=' . $view->get('id') . (!empty($from) ? '&from=' . $from : ''));
    // EKAMPUS>
}

$displaylink = $view->get_url();
if ($new) {
    $displaylink .= (strpos($displaylink, '?') === false ? '?' : '&') . 'new=1';
}

// <EKAMPUS

$stylesheets = array('<link rel="stylesheet" href="' . get_config('wwwroot') . 'local/css/bootstrap.css" />');
$smarty = smarty(array('tablerenderer', get_config('wwwroot') . 'local/js/lib/bootstrap.min.js'),
        $stylesheets, array(), array('sidebars' => false));
// EKAMPUS>
$smarty->assign('editview', $editview);
$smarty->assign('viewid', $view->get('id'));
// <EKAMPUS
//$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('viewtitle', TITLE);
// EKAMPUS>
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $displaylink);
$smarty->assign('new', $new);
// <EKAMPUS
$smarty->assign('from', $from);
$smarty->assign('backto', $backto);
if (get_config('viewmicroheaders') || !empty($from)) {
// EKAMPUS>
    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false, false));
}
$smarty->assign('issiteview', isset($institution) && ($institution == 'mahara'));

// <EKAMPUS - Add a button to return the view.
$smarty->assign('owner', $view->get('owner'));
add_learningobject_vars($view->get('collection'), $smarty, $view->get('id'));
// EKAMPUS>

$smarty->display('view/edit.tpl');
