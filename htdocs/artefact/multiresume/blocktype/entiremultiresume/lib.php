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
 * @subpackage blocktype-entiremultiresume
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact', 'file');
safe_require('artefact', 'multiresume');

class PluginBlocktypeEntireMultiresume extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.multiresume/entiremultiresume');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.multiresume/entiremultiresume');
    }

    public static function get_categories() {
        return array('resume');
    }

    public static function get_instance_title(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $lang = isset($instance->forcelang) ? $instance->forcelang : str_replace('.', '_', current_language());
        return @$configdata['title_' . $lang];
    }

    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (!empty($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] AS $a) {
                $artefacts[] = $a;
                $resume = $instance->get_artefact_instance($a);
                $artefacts = array_unique(array_merge($artefacts, $resume->get_referenced_artefacts()));
            }
        }
        return $artefacts;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;

        $configdata = $instance->get('configdata');
        $lang = isset($instance->forcelang) ? $instance->forcelang : str_replace('.', '_', current_language());

        // Somehow langmap can be an object also.
        if (isset($configdata['langmap']) && is_object($configdata['langmap'])) {
            $configdata['langmap'] = (array) $configdata['langmap'];
        }

        //Defaults to current language if forcelang is not set -> if current language has no cv specified take just the first lang which has a cv
        if (!isset($configdata['langmap'][$lang])) {
            //<EKAMPUS
            $lang = isset($configdata['langmap']) ? key($configdata['langmap']) : null;
            //there is no language which would have a cv
            if (!$lang){
                return;
            }
            /*if (isset($configdata['langmap']['en_utf8'])) {
                $lang = 'en_utf8';
            }
            else {
                return;
            }*/
             // EKAMPUS>
        }
        if (empty($configdata['artefactids'])) {
            return;
        }
        $artefactid = $configdata['artefactids'][$configdata['langmap'][$lang]];
        $rows = get_records_sql_array(
            "SELECT f.*, a.description AS lang FROM {artefact_multiresume_field} f
            INNER JOIN {artefact} a ON f.artefact = a.id
            WHERE a.id = ?  ORDER BY f.order", array($artefactid));
        if (empty($rows)) {
            return;
        }
        $lang_available = get_languages();
        $html = '<div class="multiresume">';

        if (!$editing && $USER->is_logged_in()) {
            // Show flag buttons for alternative languages.
            $html .= '<div style="float:right;">';
            $flags = array(
                'en_utf8' => 'flag_EN',
                'fi_utf8' => 'flag_FI',
                'sv_utf8' => 'flag_SE'
            );
            $seen = array();
            $keys = array_filter(array_keys($configdata['langmap']),
                function ($k) use ($lang, $configdata, &$seen) {
                    $id = $configdata['artefactids'][$configdata['langmap'][$k]];
                    if (in_array($id, $seen)) {
                        return false;
                    }
                    $seen[] = $id;
                    return $k != $lang;
                }
            );
            foreach ($keys AS $key) {
                $id    = 'multiresume_flag_' . $key . '_' . $instance->get('id');
                $class = $key == $lang ? 'multiresume_flag' : 'multiresume_flag_selector';
                $html .= ' <img src="' . get_config('wwwroot'). 'theme/raw/static/images/'. $flags[$key] .'.gif" id="'. $id .'" class="'. $class .'" alt="'. $flags[$key] .'">&nbsp;';
            }
            $html .= '</div>';
        }
        foreach ($rows AS $row) {
            $dblang = isset($lang_available[$row->lang]) ? $row->lang : 'en.utf8';
            $obj = unserialize($row->value);
            $section = $obj->to_html($dblang);
            $html .= '<h3>' . hsc($row->title) . '</h3>';
            $html .= $section . "\n<hr>\n";
        }
        $html .= '</div>';
        return $html;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        $configdata = $instance->get('configdata');

        $available = get_records_sql_array(
            "SELECT id, title, description FROM {artefact} WHERE artefacttype = 'multiresume' AND owner = ?",
            array($USER->id)
        );
        if (empty($available)) {
            return array();
        }
        $resumes = array(0 => '--');
        foreach ($available AS $av) {
            $resumes[$av->id] = $av->title;
        }

        $titlesection = array();
        $resumesection = array();
        foreach (get_languages() AS $lang => $title) {
            $lang = str_replace('.', '_', $lang);
            $titlesection['title_' . $lang] = array(
                'type' => 'text',
                'title' => $title,
                'defaultvalue' => @$configdata['title_' . $lang]
            );
            $default = 0;

            // Sometimes langmap can be an object. Don't know why.
            if (isset($configdata['langmap']) && is_object($configdata['langmap'])) {
                $configdata['langmap'] = (array) $configdata['langmap'];
            }

            if (isset($configdata['langmap'][$lang])) {
                $default = $configdata['artefactids'][$configdata['langmap'][$lang]];
            }
            $resumesection['artefactid_' . $lang] = array(
                'type' => 'select',
                'options' => $resumes,
                'defaultvalue' => $default,
                'title' => $title,
            );
        }
        return array(
            'title' => array(
                'type' => 'fieldset',
                'collapsible' => false,
                'legend' => get_string('blocktitle', 'view'),
                'elements' => $titlesection
            ),
            'resumesection' => array(
                'type' => 'fieldset',
                'collapsible' => false,
                'legend' => get_string('resume', 'artefact.resume'),
                'elements' => $resumesection
            ),
            'copysection' => array(
                'type' => 'fieldset',
                'collapsible' => true,
                'collapsed' => false,
                'legend' => get_string('moreoptions', 'artefact.blog'),
                'elements' => array(
                    'copytype' => array(
                        'type' => 'select',
                        'title' => get_string('blockcopypermission', 'view'),
                        'description' => get_string('blockcopypermissiondesc', 'view'),
                        'defaultvalue' => isset($configdata['copytype']) ? $configdata['copytype'] : 'nocopy',
                        'options' => array(
                            'nocopy' => get_string('copynocopy', 'artefact.multiresume'),
                            'full' => get_string('copyfull', 'artefact.multiresume'),
                        ),
                    ),
                ),
            )
        );
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        return array(get_config('wwwroot') . 'artefact/multiresume/js/multiresumefield.js');
    }

    /**
     * Build ordered artefactids list
     *
     */
    public static function instance_config_save($values) {
        $langs = array_keys(get_languages());
        sort($langs);
        $values['langmap']     = array();
        $values['artefactids'] = array();
        $idx = 0;
        foreach ($langs AS $lang) {
            $lang = str_replace('.', '_', $lang);
            if (isset($values['artefactid_' . $lang]) && $values['artefactid_' . $lang] > 0) {
                $values['langmap'][$lang] = $idx++;
                $values['artefactids'][] = $values['artefactid_' . $lang];
            }
            unset($values['artefactid_' . $lang]);
        }
        return $values;
    }

    /**
     * Subscribe to the blockinstancecommit event to make sure all artefacts
     * that should be in the blockinstance are
     */
    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'event'        => 'blockinstancecommit',
                'callfunction' => 'ensure_multiresume_artefacts_in_blockinstance',
            ),
        );
    }

    /**
     * Hook for making sure that all resume artefacts are associated with a
     * blockinstance at blockinstance commit time
     */
    public static function ensure_multiresume_artefacts_in_blockinstance($event, $blockinstance) {
        if ($blockinstance->get('blocktype') == 'entiremultiresume') {
            safe_require('artefact', 'multiresume');
        }
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Entireresume blocktype is only allowed in personal views, because
     * there's no such thing as group/site resumes
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}
