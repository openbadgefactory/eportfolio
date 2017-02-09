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
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'learningobject');
define('SECTION_PAGE', 'assign');
define('MENUITEM', 'learningobjects');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require('collection.php');
safe_require('interaction', 'learningobject');

define('TITLE', get_string('assignlearningobject', 'interaction.learningobject'));

$id = param_integer('id');
$collection = InteractionLearningobjectInstance::get_instance($id);
$owner = $collection->get('owner');

if (!is_teacher()) {
    throw new AccessDeniedException('Only teachers can assign learning objects.');
}

if ($owner !== $USER->get('id')) {
    throw new AccessDeniedException('Only own learning objects can be assigned.');
}

if ($collection->get('type') !== 'learningobject') {
    throw new AccessDeniedException('Only learning objects can be asssigned.');
}

// Form elements
$wwwroot = get_config('wwwroot');
$defaultinstitution = get_default_institution();
$url = $wwwroot . 'interaction/learningobject/assign.php?id=' . $id;
$formparams = array('renderer' => 'div', 'checkdirtychange' => false);
$institutionselector = institution_selector_for_page($defaultinstitution, $url,
        $formparams, true, array('collapseifoneoption' => false));
$institutiongroups = get_groups($institutionselector['institution'], true);
$mygroups = get_user_groups();
$calendar_icon_url = $THEME->get_url('images/btn_calendar.png');

// Scripts
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/learningobject/js/assignment'], function (a) {
        a.init({
            learningobjectid: $id,
            calendar_icon: '$calendar_icon_url'
        });
    });
});
JS;

$langparts = explode(".", current_language());
$calendarcss = $THEME->get_url('style/calendar.css');

$smarty = smarty(array(
    'tablerenderer',
    $wwwroot . 'js/jscalendar/calendar_stripped.js',
    $wwwroot . 'js/jscalendar/lang/calendar-' . $langparts[0] . '.js',
    $wwwroot . 'js/jscalendar/calendar-setup_stripped.js',
    $wwwroot . 'local/js/lib/require.js'),
        array('<link rel="stylesheet" type="text/css" href="' . $calendarcss . '"></link>'),
        array('mahara' => array('add', 'noresultsfound', 'strfdaymonthyearshort', 'remove'),
            'interaction.learningobject' => array('selectgroup')));

$smarty->assign('date_format', get_string('strfdaymonthyearshort'));
$smarty->assign('learningobject', $collection);
$smarty->assign('assignees', InteractionLearningobjectInstance::get_assignees($collection));
$smarty->assign('instructors', InteractionLearningobjectInstance::get_instructors($collection));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('groups', $institutiongroups);
$smarty->assign('mygroups', $mygroups);
$smarty->assign('institutionselector', $institutionselector['institutionselector']);
$smarty->display('interaction:learningobject:assign.tpl');