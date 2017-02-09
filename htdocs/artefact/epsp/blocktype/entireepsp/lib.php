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
defined('INTERNAL') || die();

safe_require('artefact', 'epsp');

class PluginBlocktypeEntireEpsp extends PluginBlocktype {

    public static function get_categories() {
        return array('epsp');
    }

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // In clean install this postinst is called before the core
            // blocktype categories are installed and the order goes wrong.
            // So let's do not insert our new category here in clean install,
            // instead we do it in local postinst.
            if (count_records('blocktype_category') > 0) {
                $obj = (object) array(
                            'name' => 'epsp',
                            'sort' => 7
                );
                ensure_record_exists('blocktype_category', $obj, $obj);
            }
        }
    }

    public static function get_description() {
        return get_string('description', 'blocktype.epsp/entireepsp');
    }

    public static function get_title() {
        return get_string('title', 'blocktype.epsp/entireepsp');
    }

    public static function render_instance(\BlockInstance $instance,
                                           $editing = false) {
        $configdata = $instance->get('configdata');

        if (empty($configdata['artefactid'])) {
            return '';
        }

        $artefactid = $configdata['artefactid'];

        try {
            $epsp = new ArtefactTypeEpsp($artefactid);
        } catch (ArtefactNotFoundException $e) {
            return '';
        }

        $fields = array();
        $smarty = smarty_core();

        foreach ($epsp->get_fields() as $field) {
            $fields[] = array(
                'id' => $field->get('id'),
                'html' => $field->to_html($instance->get_view()->get('id')),
                'type' => $field->get('field')->get_type());
        }

        $smarty->assign('fields', $fields);

        return $smarty->fetch('blocktype:entireepsp:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    public static function allowed_in_view(View $view) {
        return !is_teacher();
    }

    public static function instance_config_form(\BlockInstance $instance) {
        global $USER;

        $configdata = $instance->get('configdata');

        if (!empty($configdata['artefactid'])) {
            $epsp = $instance->get_artefact_instance($configdata['artefactid']);
        }

        $elements = array();

        if (empty($configdata['artefactid']) || $epsp->get('owner') == $USER->get('id')) {
            $id = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;
            $elements[] = self::artefactchooser_element($id);
        }
        else {
            $elements['notice'] = array(
                'type' => 'html',
                'value' => '<div class="message">' .
                get_string('noepsp', 'blocktype.epsp/entireepsp') . '</div>'
            );
        }

        return $elements;
    }

    public static function artefactchooser_element($default = null) {
        return array(
            'name' => 'artefactid',
            'type' => 'artefactchooser',
            'title' => get_string('selectepsp', 'blocktype.epsp/entireepsp'),
            'defaultvalue' => $default,
            'blocktype' => 'entireepsp',
            'limit' => 10,
            'selectone' => true,
            'artefacttypes' => array('epsp'),
            'template' => 'blocktype:entireepsp:artefactchooser-element.tpl'
        );
    }

    public static function get_instance_javascript(\BlockInstance $instance) {
        $wwwroot = get_config('wwwroot');

        return array(
            $wwwroot . 'local/js/lib/require.js');
    }

    public static function has_title_link() {
        return false;
    }
}
