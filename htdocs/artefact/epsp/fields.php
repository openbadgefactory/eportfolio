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
define('MENUITEM', 'epsp');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'epsp');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

$id = param_integer('id');
$epsp = new ArtefactTypeEpsp($id);

// Only own epsp's can be edited.
$readonly = $epsp->get('owner') !== $USER->get('id');
$is_teacher = (int) is_teacher();
$wwwroot = get_config('wwwroot');
$fields = $epsp->get_fields(true);
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/epsp/js/template'], function (tpl) {
        tpl.init({
            is_teacher: $is_teacher
        });
    });
});
JS;

define('TITLE', $epsp->get('title'));

$calendarcss = $THEME->get_url('style/calendar.css');
$langparts = explode(".", current_language());
$smarty = smarty(array($wwwroot . 'local/js/lib/require.js', 'tinymce',
    $wwwroot . 'js/jscalendar/calendar_stripped.js',
    $wwwroot . 'js/jscalendar/lang/calendar-' . $langparts[0] . '.js',
    $wwwroot . 'js/jscalendar/calendar-setup_stripped.js'),
        array('<link rel="stylesheet" type="text/css" href="' . $calendarcss . '"></link>'),
        array(
    'artefact.epsp' => array('addnewfield', 'confirmremovefield', 'confirmremoveblock'),
    'mahara' => array('strfdaymonthyearshort')
        ));

$smarty->assign('description', $epsp->get('description'));
$smarty->assign('readonly', $readonly);
$smarty->assign('fields', $fields);
$smarty->assign('templateid', $id);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('is_teacher', is_teacher());
$smarty->display('artefact:epsp:fields.tpl');
