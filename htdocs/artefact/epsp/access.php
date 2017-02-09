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
 * @subpackage TODO
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

define('TITLE', get_string('ehops', 'artefact.epsp'));

if (!is_teacher()) {
    throw new AccessDeniedException();
}

$id = param_integer('id');
$template = new ArtefactTypeEpsp($id);

if ($template->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException();
}

$useraccess = $template->get_user_access();
$groupaccess = $template->get_group_access();
$institutionaccess = $template->get_institution_access();
$wwwroot = get_config('wwwroot');

$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/epsp/js/accessmanager'], function (a) {
        a.init($id);
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js', 'tablerenderer'),
        array(), array('mahara' => array('add', 'remove')));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', get_string('sharetemplatetitle', 'artefact.epsp', $template->get('title')));
$smarty->assign('template', $template);
$smarty->assign('useraccess', $useraccess);
$smarty->assign('groupaccess', $groupaccess);
$smarty->assign('institutionaccess', $institutionaccess);
$smarty->display('artefact:epsp:access.tpl');