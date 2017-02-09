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

class PluginBlocktypeSingleEpspField extends PluginBlocktype {

    public static function artefactchooser_element($default = null) {

    }

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
        return get_string('description', 'blocktype.epsp/singleepspfield');
    }

    public static function get_title() {
        return get_string('title', 'blocktype.epsp/singleepspfield');
    }

    public static function render_instance(\BlockInstance $instance,
                                           $editing = false) {
        $configdata = $instance->get('configdata');

        if (!isset($configdata['artefactid'])) {
            return '';
        }

        $viewid = $instance->get_view()->get('id');
        $fieldid = (int) $configdata['artefactid'];
        $field = ArtefactTypeEpsp::get_field($fieldid);

        $smarty = smarty_core();
        $smarty->assign('type', $field->get('field')->get_type());
        $smarty->assign('id', $field->get('id'));
        $smarty->assign('content', $field->to_html($viewid));

        return $smarty->fetch('blocktype:singleepspfield:content.tpl');
    }

    public static function get_instance_config_javascript(\BlockInstance $instance) {
        $wwwroot = get_config('wwwroot');

        return array(
            $wwwroot . 'artefact/epsp/blocktype/singleepspfield/js/singleepspfield.js');
    }


    public static function instance_config_form(\BlockInstance $instance) {
        global $USER;

        $plans = ArtefactTypeEpsp::get_plans();
        $elements = array();

        if (count($plans) > 0) {
            $planopts = array();
            $fieldopts = array();
            $configdata = $instance->get('configdata');

            foreach ($plans as $plan) {
                $planopts[$plan->id] = $plan->title;
            }

            $selected_epsp = isset($configdata['epsp']) ? $configdata['epsp'] : array_shift(array_keys($planopts));

            $elements['epsp'] = array(
                'type' => 'select',
                'title' => get_string('selectepspfield', 'blocktype.epsp/singleepspfield'),
                'collapseifoneoption' => false,
                'options' => $planopts,
                'defaultvalue' => $selected_epsp
            );

            $epsp = new ArtefactTypeEpsp($selected_epsp);
            $fields = $epsp->get_fields(true);

            foreach ($fields as $field) {
                $prefix = $field->field->type === 'subtitle' ? '--' : ($field->field->type === 'goal' || $field->field->type === 'textfield' ? '----' : '');
                $fieldopts[$field->id] = $prefix . ' ' . $field->title;
            }

            $elements['artefactid'] = array(
                'type' => 'select',
                'allowempty' => true,
                'collapseifoneoption' => false,
                'options' => $fieldopts,
                'donotvalidateoptions' => true,
            );

            if (isset($configdata['artefactid'])) {
                $elements['artefactid']['defaultvalue'] = $configdata['artefactid'];
            }
        }
        else {
            $elements['notice'] = array(
                'type' => 'html',
                'value' => '<div class="message">' .
                get_string('noepsps', 'blocktype.epsp/singleepspfield') . '</div>',
            );
        }

        return $elements;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function allowed_in_view(\View $view) {
        return !is_teacher();
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
