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
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('docroot') . 'blocktype/lib.php');

safe_require('artefact', 'studyjournal');

$filter = param_variable('filter', 'alldates');
$offset = param_integer('offset', 0);
$blockid = param_integer('block', null);
$block = new BlockInstance($blockid);
$cfg = $block->get('configdata');
$artefactid = $cfg['artefactid'];
$viewid = $block->get_view()->get('id');

if (!can_view_view($block->get('view'))) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$perpage = ($filter == 'alldates' ? 5 : null);
$journal = PluginArtefactStudyJournal::get_journal($artefactid);
$entries = $filter === 'alldates' || $filter === 'all'
        ? $journal->get_entries($perpage, $offset, null, $viewid)
        : $journal->get_entries_by_date($filter, $viewid);
$smarty = smarty_core();
$smarty->assign('entries', $entries);
$smarty->assign('view', $block->get_view()->get('id'));
$rows = $smarty->fetch('artefact:studyjournal:entries.tpl');

$baseurl = $block->get_view()->get_url();
$baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $block->get('id');
$pagination = build_pagination(array(
        'url' => $baseurl,
        'count' => $journal->get_total_entries(),
        'limit' => 5,
        'offset' => $offset,
        'id' => 'studyjournal-pagination-' . $block->get('id'),
        'datatable' => 'journal-entries', // PENDING: fails if multiple journals on same page?
        'jsonscript' => 'artefact/studyjournal/student/entries.json.php'
    ));

$data = array(
    'tablerows' => $rows,
    'pagination' => $pagination['html'],
    'pagination_js' => $pagination['javascript']
);

json_reply(false, array('data' => $data));
