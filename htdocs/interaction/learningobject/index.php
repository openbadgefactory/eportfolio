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
 * @subpackage interaction-learningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'learningobjects');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'learningobject');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('lib.php');

define('TITLE', get_string('learningobjects', 'interaction.learningobject'));

// Institution selector.
$defaultinstitution = get_default_institution();
$institutions = get_institutions(false, true);
$instarr = array('0' => get_string('selectinstitution',
            'interaction.learningobject'));

if ($institutions) {
    foreach ($institutions as $inst) {
        $instarr[$inst->name] = $inst->displayname;
    }
}

// Create form.
$form = array(
    'name' => 'learningobjectsearch',
    'checkdirtychange' => false,
    'renderer' => 'div',
    'elements' => array(
        'institution' => array(
            'type' => 'select',
            'options' => $instarr,
            'collapseifoneoption' => false,
            'defaultvalue' => $defaultinstitution === false ? '0' : $defaultinstitution
        )
    )
);

// Teacher can change groups.
if (is_teacher()) {
    // Group selector
    $grouparr = array('0' => get_string('selectgroup',
                'interaction.learningobject'));

    if ($defaultinstitution !== false) {
        $groups = get_groups($defaultinstitution);

        if ($groups) {
            foreach ($groups as $grp) {
                $grouparr[$grp->id] = $grp->name;
            }
        }
    }

    $form['elements']['group'] = array(
        'type' => 'select',
        'options' => $grouparr,
        'collapseifoneoption' => false
    );
}

$searchform = pieform($form);
$wwwroot = get_config('wwwroot');
$newurl = $wwwroot . 'collection/edit.php?new=1&learningobject=1';
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/learningobject/js/learningobject'], function (lo) {
        lo.init({ newurl: '$newurl' });
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array());
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('is_teacher', is_teacher());
//$smarty->assign('searchform', $searchform);
$smarty->display('interaction:learningobject:index.tpl');
