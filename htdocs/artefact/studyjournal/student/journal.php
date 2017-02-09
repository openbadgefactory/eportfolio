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

$offset = param_integer('offset', null);
$journalid = param_integer('id');
$filter = param_variable('filter', 'alldates');

$perpage = ($filter == 'alldates' ? 5 : null);
$journal = PluginArtefactStudyJournal::get_journal($journalid);
$totalentries = $journal->get_total_entries();
$entries = $filter == 'alldates' || $filter == 'all'
        ? $journal->get_entries($perpage, $offset)
        : $journal->get_entries_by_date($filter);

$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/studyjournal/js/studyjournal'], function (studyjournal) {
        studyjournal.init_journal();
    });
});
JS;

$dateform = PluginArtefactStudyJournal::get_journal_dateform($journal, $filter);
$smarty = smarty(array($wwwroot . 'local/js/lib/require.js', 'lib/pieforms/static/core/pieforms.js',
        'paginator', 'expandable'),
        array(), array(), array('sidebars' => false));
$config = array('journaltitle' => $journal->get('title'), 'description' => $journal->get('description'));

if (!$journal->is_published()) {
    $publishjournalform = pieform(PluginArtefactStudyJournal::publish_journal_form($journal->get('id'), $config));
    $smarty->assign('publishjournalform', $publishjournalform);
}

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('journal', $journal);
$smarty->assign('entries', $entries);
$smarty->assign('dateform', $dateform);

if (!is_null($perpage)) {
    $smarty->assign('pagination',
            build_pagination(array(
        'url' => $wwwroot . 'artefact/studyjournal/student/journal.php?id=' . $journalid,
        'count' => $totalentries,
        'limit' => $perpage,
        'resultcounttextsingular' => get_string('resultcounttextsingular', 'artefact.studyjournal'),
        'resultcounttextplural' => get_string('resultcounttextplural', 'artefact.studyjournal'),
        'offset' => is_null($offset) ? 0 : $offset
    )));
}

$smarty->display('artefact:studyjournal:journal.tpl');
