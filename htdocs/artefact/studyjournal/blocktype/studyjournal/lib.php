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
 * @subpackage blocktype-studyjournal
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

class PluginBlocktypeStudyjournal extends PluginBlocktype {

    /**
     * Studyjournal blocktype is only allowed in personal views.
     */
    public static function allowed_in_view(\View $view) {
        return $view->get('owner') != null;
    }

    public static function artefactchooser_element($default = null) {
        return array(
            'name' => 'artefactid',
            'type' => 'artefactchooser',
            'title' => get_string('studyjournals', 'artefact.studyjournal'),
            'defaultvalue' => $default,
            'blocktype' => 'studyjournal',
            'limit' => 10,
            'selectone' => true,
            'artefacttypes' => array('studyjournal'),
            'template' => 'artefact:studyjournal:artefactchooser-element.tpl'
        );
    }

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // In clean install this postinst is called before the core
            // blocktype categories are installed and the order goes wrong.
            // So let's do not insert our new category here in clean install,
            // instead we do it in local postinst.
            if (count_records('blocktype_category') > 0) {
                $obj = (object) array(
                            'name' => 'studyjournal',
                            'sort' => 6
                );
                ensure_record_exists('blocktype_category', $obj, $obj);
            }
        }
    }
    public static function get_categories() {
        return array('studyjournal');
    }

    public static function get_viewtypes() {
        return array('portfolio', 'studyjournal');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.studyjournal/studyjournal');
    }

    public static function get_title() {
        return get_string('title', 'blocktype.studyjournal/studyjournal');
    }

    public static function render_instance(\BlockInstance $instance,
                                           $editing = false) {
        $configdata = $instance->get('configdata');
        $result = '';

        if (!empty($configdata['artefactid'])) {
            safe_require('artefact', 'studyjournal');
            $journal = PluginArtefactStudyJournal::get_journal($configdata['artefactid']);

            $dates = array(
                'alldates' => get_string('showentriesfromalldates',
                        'artefact.studyjournal'),
                'all' => get_string('showallentries', 'artefact.studyjournal'));
            $dates = array_merge($dates, $journal->get_entry_dates());
            $baseurl = $instance->get_view()->get_url();
            $baseurl .= (strpos($baseurl, '?') === false ? '?' : '&') . 'block=' . $instance->get('id');
            $pagination = array(
                'url' => $baseurl,
                'count' => $journal->get_total_entries(),
                'limit' => 5,
                'offset' => 0,
                'resultcounttextplural' => get_string('resultcounttextplural', 'artefact.studyjournal'),
                'resultcounttextsingular' => get_string('resultcounttextsingular', 'artefact.studyjournal'),
                'id' => 'studyjournal-pagination-' . $instance->get('id'),
                'datatable' => 'journal-entries', // PENDING: fails if multiple journals on same page?
                'jsonscript' => 'artefact/studyjournal/student/entries.json.php'
            );

            $viewid = $instance->get_view()->get('id');

            $smarty = smarty_core();
            $smarty->assign('view', $viewid);
            $smarty->assign('block', $instance->get('id'));
            $smarty->assign('journal', $journal);
            $smarty->assign('entries', $journal->get_entries(5, 0, null, $viewid));
            $smarty->assign('plain', true);
            $smarty->assign('dates', $dates);
            $smarty->assign('pagination', build_pagination($pagination));

            $result = $smarty->fetch('artefact:studyjournal:journal.tpl');
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;

        safe_require('artefact', 'studyjournal');

        $configdata = $instance->get('configdata');

        if (!empty($configdata['artefactid'])) {
            $journal = $instance->get_artefact_instance($configdata['artefactid']);
        }

        $elements = array();

        if (empty($configdata['artefactid']) || $journal->get('owner') == $USER->get('id')) {
            $elements[] = self::artefactchooser_element((isset($configdata['artefactid'])
                                        ? $configdata['artefactid'] : null));
        }
        else {
            $elements[] = array(
                'type' => 'html',
                'name' => 'notice',
                'value' => '<div class="message">' . get_string('journalcopiedfromanotherview',
                        'artefact.studyjournal') . '</div>',
            );
        }

        return $elements;
    }

    public static function single_only() {
        return true;
    }

}
