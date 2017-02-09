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
 * @subpackage blocktype-resumefield
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

require_once(get_config('docroot') . 'artefact/lib.php');
safe_require('artefact', 'multiresume');

class PluginBlocktypeSingleResumefield extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.multiresume/singleresumefield');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.multiresume/singleresumefield');
    }

    public static function get_categories() {
        return array('resume');
    }

    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['multiresumefield']['singular']['artefact'])) {
            $artefacts[] = $configdata['multiresumefield']['singular']['artefact'];

            $resume = $instance->get_artefact_instance($configdata['multiresumefield']['singular']['artefact']);
            $artefacts = array_unique(array_merge($artefacts, $resume->get_referenced_artefacts($configdata['multiresumefield']['singular']['field'])));
        }
        return $artefacts;
    }

    public static function get_instance_title(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['title'])) {
            return $configdata['title'];
        }
        if (!empty($configdata['multiresumefield'])) {
            return get_field('artefact_multiresume_field', 'title', 'id', $configdata['multiresumefield']['singular']['field']);
        }
        return '';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['multiresumefield'])) {
            $field = get_record('artefact_multiresume_field', 'id', $configdata['multiresumefield']['singular']['field']);
            if ($field) {
                $lang_available = get_languages();
                $lang = get_field('artefact', 'description', 'id', $field->artefact);
                if (!isset($lang_available[$lang])) {
                    $lang = 'en.utf8';
                }
                $obj = unserialize($field->value);
                return $obj->to_html($lang, $configdata['multiresumefield']['singular']['rows']);
            }
        }
        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        $configdata = $instance->get('configdata');
        $instance->set('artefactplugin', 'multiresume');

        $form = array();

        $form['message'] = array(
            'type' => 'html',
            'value' => get_string('singularfielddesc', 'artefact.multiresume', '<a href="' . get_config('wwwroot') . 'artefact/multiresume/">', '</a>'),
        );

        $form['multiresumefield']  = array(
            'type' => 'multiresumefield',
            'singular' => true,
            'defaultvalue' => @$configdata['multiresumefield'],
        );
        return $form;
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        return array(get_config('wwwroot') . 'artefact/multiresume/js/multiresumefield.js');
    }

    public static function instance_config_save($values) {
        unset($values['message']);
        return $values;
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function default_copy_type() {
        return 'nocopy';
    }

    /**
     * Resumefield blocktype is only allowed in personal views, because 
     * there's no such thing as group/site resumes
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

}

