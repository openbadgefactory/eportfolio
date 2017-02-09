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
 * @subpackage core
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editwriteaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'group.php');

$viewid = param_integer('id');

$view = new custom\View($viewid);

$collection = $view->get_collection();


define('TITLE', get_string('editwriteaccess', 'view') . ' / ' . ($collection ? $collection->get('name') : $view->get('title')));

$group = $view->get('group');

define('MENUITEM', 'groups/share');
define('GROUP', $group);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$form = array(
    'name' => 'editaccess',
    'renderer' => 'div',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'viewid' => $view->get('id'),
    'groupid'  => $group,
    'userview' => (int) $view->get('owner'),
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
    )
);

$form['elements']['more'] = array(
    'type' => 'fieldset',
    'collapsible' => false,
    'collapsed' => false,
    'elements' => array(),
);


$form['elements']['accesslist'] = array(
    'type'          => 'modifyacl',
    'defaultvalue'  => $view->get_writeaccesslist(),
);


$js = "function update_loggedin_access() {}\n";

$js .= <<<EOF

var allowcomments = 0;
var moderatecomments = 0;

function update_comment_options() { }

function update_approve_options() { }

EOF;

$form['elements']['more']['elements']['overrides'] = array(
    'type' => 'html',
    'value' => '<strong>' . get_string('overridingstartstopdate', 'view') . '</strong>',
    'description' => get_string('overridingwritestartstopdatesdescription', 'view'),
);
$form['elements']['more']['elements']['startdate'] = array(
    'type'         => 'calendar',
    'title'        => get_string('startdate','view'),
    'description'  => get_string('datetimeformatguide'),
    'defaultvalue' => isset($view) ? strtotime($view->get('startdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
        'ifFormat'       => get_string('strftimedatetimeshort'),
    ),
);
$form['elements']['more']['elements']['stopdate'] = array(
    'type'         => 'calendar',
    'title'        => get_string('stopdate','view'),
    'description'  => get_string('datetimeformatguide'),
    'defaultvalue' => isset($view) ? strtotime($view->get('stopdate')) : null,
    'caloptions'   => array(
        'showsTime'      => true,
        'ifFormat'       => get_string('strftimedatetimeshort'),
    ),
);

$form['elements']['submit'] = array(
    'type'  => 'submitcancel',
    'value' => array(get_string('save'), get_string('cancel')),
);


/*
 * Converts parsed time array to unix timestamp.
 * @param array // date parsed using strptime()
 * @return int  // Unix timestamp
 */
function ptimetotime($ptime) {
    return mktime(
        $ptime['tm_hour'],
        $ptime['tm_min'],
        $ptime['tm_sec'],
        1,
        $ptime['tm_yday'] + 1,
        $ptime['tm_year'] + 1900
    );
}

function editaccess_validate(Pieform $form, $values) {
    global $SESSION, $group;

    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('newstartdatemustbebeforestopdate', 'view', 'Overriding'));
    }
    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {
            if (empty($item['startdate'])) {
                $item['startdate'] = null;
            }
            else if (!$item['startdate'] = strptime($item['startdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('unrecogniseddateformat', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
            if (empty($item['stopdate'])) {
                $item['stopdate'] = null;
            }
            else if (!$item['stopdate'] = strptime($item['stopdate'], $dateformat)) {
                $SESSION->add_error_msg(get_string('unrecogniseddateformat', 'view'));
                $form->set_error('accesslist', '');
                break;
            }
            $now = strptime(date('Y/m/d H:i'), $dateformat);
            if ($item['stopdate'] && ptimetotime($now) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('newstopdatecannotbeinpast', 'view', get_string($item['type'], 'view')));
                $form->set_error('accesslist', '');
                break;
            }
            if ($item['startdate'] && $item['stopdate'] && ptimetotime($item['startdate']) > ptimetotime($item['stopdate'])) {
                $SESSION->add_error_msg(get_string('newstartdatemustbebeforestopdate', 'view', get_string($item['type'], 'view')));
                $form->set_error('accesslist', '');
                break;
            }
        }
    }
}

function editaccess_cancel_submit() {
    global $group;
    if (!empty($group)) {
        $redirecturl = '/group/shareviews.php?group=' . $group;
    }
    else {
        $redirecturl = '/view/share.php';
    }
    redirect($redirecturl);
}


function editaccess_submit(Pieform $form, $values) {
    global $SESSION, $view, $collection;

    if ($values['accesslist']) {
        $dateformat = get_string('strftimedatetimeshort');
        foreach ($values['accesslist'] as &$item) {
            if (!empty($item['startdate'])) {
                $item['startdate'] = ptimetotime(strptime($item['startdate'], $dateformat));
            }
            if (!empty($item['stopdate'])) {
                $item['stopdate'] = ptimetotime(strptime($item['stopdate'], $dateformat));
            }
            if ($item['type'] === 'group') {
                $item['id'] = $view->get('group');
                $item['role'] = 'member';
            }
        }
    }

    $viewconfig = array(
        'startdate'       => $values['startdate'],
        'stopdate'        => $values['stopdate'],
        'accesslist'      => $values['accesslist'],
    );

    $toupdate = array();
        
    if ($collection) {
        $coll = $collection->views();
        foreach ($coll['views'] as $v) {
            $toupdate[] = $v->view;
        }
    } else {
        $toupdate[] = $view->get('id');
    }

    foreach ($toupdate AS $vid) {
        $v = new custom\View($vid);
        $v->update_writeaccess($viewconfig);
    }

    $SESSION->add_ok_msg(get_string('updatedaccessfornumviews', 'view', count($toupdate)));

    if ($view->get('owner')) {
        redirect('/view/share.php');
    }
    $view->post_edit_redirect();
}

$form = pieform($form);

$smarty = smarty(
    array('tablerenderer'),
    array(),
    array(
        'mahara' => array('From', 'To', 'datetimeformatguide'),
        'artefact.comment' => array('Comments', 'Allow', 'Moderate')
    ),
    array('sidebars' => false)
);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->assign('writeaccess', true);
$smarty->display('view/access.tpl');
