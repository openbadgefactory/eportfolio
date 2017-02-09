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
define('MENUITEM', 'ohjaus/returns');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'pages');
define('SECTION_PAGE', 'returns');


require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('lib.php');
require_once('pieforms/pieform.php');

define('TITLE', get_string('returns'));

$wwwroot = get_config('wwwroot');

//default is the users own institution (the first of them)
if ($defaultinstitutions = get_records_sql_array('SELECT i.name from {institution} i LEFT JOIN {usr_institution} ui ON i.name = ui.institution WHERE ui.usr = ?',
        array($USER->get('id')))) {
    $defaultinstitution = $defaultinstitutions[0]->name;
}
else {
    $defaultinstitution = '0';
}

//institution select (all institutions)
if ($institutions = get_records_sql_array('SELECT i.name, i.displayname from {institution} i WHERE name != ?',
        array('mahara'))) {
    $insti = array('0' => get_string('selectinstitution', 'interaction.pages'));
    foreach ($institutions as $inst) {
        $insti[$inst->name] = $inst->displayname;
    }
}
else {
    $insti = array('0' => get_string('noinstitution', 'interaction.pages'));
}

//group select (no default here)
if ($defaultinstitution != '0') {
    $groups = get_records_sql_array('SELECT id, name FROM {group} g WHERE g.deleted = 0 AND hidden = 0 AND institution = ?',
            array($defaultinstitution));
}
else {
    $groups = get_records_sql_array('SELECT id, name FROM {group} g WHERE g.deleted = 0 AND hidden = 0',
            array());
}
if ($groups) {
    $group = array('0' => get_string('selectgroup', 'interaction.pages'));
    foreach ($groups as $value) {
        $group[$value->id] = $value->name;
    }
}
else {
    $group = array('0' => get_string('nogroup', 'interaction.pages'));
}

$students = array('0' => get_string('selectstudent', 'interaction.pages'));

$searchform = "";
if (is_teacher($USER)) {
    $selectform = array(
        'name' => 'search',
        'checkdirtychange' => false,
        'dieaftersubmit' => false,
        'renderer' => 'div',
        'class' => 'search',
        'elements' => array(
            'institution' => array(
                'type' => 'select',
                'options' => $insti,
                'collapseifoneoption' => false,
                'defaultvalue' => $defaultinstitution,
            ),
        )
    );
    $selectform['elements']['group'] = array(
                'type' => 'select',
                'options' => $group,
                'collapseifoneoption' => false,
    );
    $selectform['elements']['student'] = array(
                'type' => 'select',
                'options' => $students,
                'collapseifoneoption' => false,
    );
    $searchform = pieform($selectform);
}

$types = array(
    'collection' => get_string('Collections', 'collection'),
    'portfolio' => get_string('Views', 'view'),
    'studyjournal' => get_string('studyjournal', 'interaction.pages'),
    'multiresume' => get_string('multiresume', 'interaction.pages'),
    'blog' => get_string('blog', 'interaction.pages'),
//    'learningobject' => ucfirst(strtolower(get_string('learningobjects',
//                            'interaction.learningobject')))
);

$fulltextsearch = 1;
$groupid = -1;
$returns = 1;
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/pages/js/gallery'], function (g) {
        g.init({ fulltextsearch: $fulltextsearch, groupid: $groupid, returns: $returns });
    });
});
JS;
$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array(),
        array('view' => array('editaccess', 'deletethisview', 'editcontentandlayout')));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('types', $types);
$smarty->assign('searchform', $searchform);
$smarty->assign('returns', 1);
$smarty->assign('teacher', is_teacher($USER));
$smarty->display('interaction:pages:sharedviews.tpl');
