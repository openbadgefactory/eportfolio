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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'ohjaus/progression');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'epsp');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

define('TITLE', get_string('sharedprogression', 'artefact.epsp'));

$default_institution = get_default_institution();
$institutions = get_institutions();
$instarray = array();
$grouparray = array();

// Data for institution selector.
if (count($institutions) > 0) {
    $instarray['0'] = get_string('selectinstitution', 'artefact.epsp');

    foreach ($institutions as $inst) {
        $instarray[$inst->name] = $inst->displayname;
    }
}
else {
    $instarray['0'] = get_string('noinstitution', 'artefact.epsp');
}

$selectform = array(
    'name' => 'search',
    'checkdirtychange' => false,
    'dieaftersubmit' => false,
    'renderer' => 'div',
    'class' => 'search',
    'elements' => array(
        'institution' => array(
            'type' => 'select',
            'options' => $instarray,
            'collapseifoneoption' => false,
            'defaultvalue' => $default_institution === false ? '0' : $default_institution
        )
    )
);

// Data for group selector.
if (is_teacher()) {
    $groups = array();

    if ($default_institution !== false) {
        $groups = get_groups($default_institution, true);
    }

    if (count($groups) > 0) {
        $grouparray['0'] = get_string('selectgroup', 'artefact.epsp');

        foreach ($groups as $group) {
            $grouparray[$group->id] = $group->name;
        }
    }
    else {
        $grouparray['0'] = get_string('nogroups', 'artefact.epsp');
    }

    $selectform['elements']['group'] = array(
                'type' => 'select',
                'options' => $grouparray,
                'collapseifoneoption' => false,
    );
    $selectform['elements']['student'] = array(
                'type' => 'select',
                'options' => array('0' => get_string('selectstudent', 'artefact.epsp')),
                'collapseifoneoption' => false,
    );
}
$progress = 1;
$opentext = get_string('openall', 'artefact.epsp');
$closetext = get_string('closeall', 'artefact.epsp');
$searchform = pieform($selectform);
$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/epsp/js/sharedprogress'], function (plan) {
        plan.init({progress: $progress,opentext:'$opentext',closetext:'$closetext'});
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array());
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('progress', $progress);
$smarty->assign('searchform', $searchform);
$smarty->display('artefact:epsp:shared.tpl');
