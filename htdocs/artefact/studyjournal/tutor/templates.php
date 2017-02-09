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
 * @subpackage artefact-studyjournal
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'studyjournal');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'studyjournal');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'studyjournal');

define('TITLE', get_string('studyjournal', 'artefact.studyjournal'));

if (!is_teacher()) {
    throw new AccessDeniedException();
}

$templates = PluginArtefactStudyJournal::get_tutor_templates();
$tags = PluginArtefactStudyJournal::get_tutor_template_tags();
$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/studyjournal/js/studyjournal'], function (studyjournal) {
        studyjournal.init_teacher_template_list();
    });
});
JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js', 'lib/pieforms/static/core/pieforms.js',
    'tinymce'), array(), array('artefact.studyjournal' => array('confirmremovetemplate')), array('sidebars' => false));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('templates', $templates);
$smarty->assign('tags', $tags);
$smarty->assign('saved', param_integer('saved', 0));
$smarty->display('artefact:studyjournal:tutortemplates.tpl');
