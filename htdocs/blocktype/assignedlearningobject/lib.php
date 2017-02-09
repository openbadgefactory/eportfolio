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
 * @subpackage blocktype-assignedlearningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

class PluginBlocktypeAssignedLearningObject extends SystemBlockType {

    public static function get_categories() {
        return array('internal');
    }

    public static function hide_title_on_empty_content() {
        return true;
    }

    public static function get_description() {
        return get_string('description', 'blocktype.assignedlearningobject');
    }

    public static function get_title() {
        return get_string('title', 'blocktype.assignedlearningobject');
    }

    public static function single_only() {
        return true;
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.assignedlearningobject');
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function instance_config_form(\BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $elements = array(
            'count' => array(
                'type' => 'text',
                'title' => get_string('itemstoshow',
                        'blocktype.blog/othersrecentposts'),
                'description' => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count']
                            : 10,
                'size' => 3,
                'rules' => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
            'titlelinkurl' => array(
                'type' => 'hidden',
                'value' => '',
            ),
        );

        return $elements;
    }

    public static function render_instance(\BlockInstance $instance,
                                           $editing = false) {
        global $USER;

        if (!is_teacher()) {
            return '';
        }

        $limit = 5;
        $configdata = $instance->get('configdata');
        $userid = $USER->get('id');

        if (!empty($configdata['count'])) {
            $limit = (int) $configdata['count'];
        }

        safe_require('interaction', 'learningobject');

        $smarty = smarty_core();
        $smarty->assign('learningobjects', InteractionLearningobjectInstance::get_learningobjects_assigned_by());

        return $smarty->fetch('blocktype:assignedlearningobject:learningobjects.tpl');
    }

}
