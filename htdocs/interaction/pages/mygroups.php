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
 * @subpackage interaction-pages
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'groups');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('groups'));
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'pages');
define('SECTION_PAGE', 'mygroups');
require_once('group.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/lib/resourcetree.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/lib/institution.php');

$fulltextsearch = (int) (get_config('searchplugin') === 'sphinx');

global $USER;
$tags = get_group_tags($USER->id);

$extraconfig = array();
$wwwroot = get_config('wwwroot');
$jsroot = $wwwroot . 'interaction/pages/js/';
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/pages/js/group'], function (g) {
        g.init({ fulltextsearch: $fulltextsearch });
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array(), array(), $extraconfig);

$smarty->assign('tags', $tags);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('fulltextsearch', $fulltextsearch);
$smarty->assign('cancreate', group_can_create_groups());
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('strcreategroup', get_string('creategroup', 'group'));
$smarty->display('interaction:pages:groups.tpl');
