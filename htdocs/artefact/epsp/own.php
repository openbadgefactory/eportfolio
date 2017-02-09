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

define('TITLE', get_string('myplans', 'artefact.epsp'));

$plans = ArtefactTypeEpsp::get_plans();
$tags = ArtefactTypeEpsp::get_tags();
$wwwroot = get_config('wwwroot');
$total = count($plans);
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/epsp/js/plan'], function (plan) {
        plan.init({
            total: $total
        });
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array(),
        array('artefact.epsp' => array('confirmremovetemplate', 'confirmremoveplan')));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('plans', $plans);
$smarty->assign('tags', $tags);
$smarty->display('artefact:epsp:own.tpl');