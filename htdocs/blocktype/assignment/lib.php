<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-assignment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();

class PluginBlocktypeAssignment extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.assignment');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.assignment');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance,
                                           $editing = false) {
        global $USER;

        $limit = 5;
        $configdata = $instance->get('configdata');
        $userid = $USER->get('id');
        $smarty = smarty_core();

        if (!empty($configdata['count'])) {
            $limit = (int) $configdata['count'];
        }

        safe_require('interaction', 'learningobject');

        $assignments = InteractionLearningobjectInstance::get_assignments($userid, $limit);
        $smarty->assign('assignments', $assignments);

        return $smarty->fetch('blocktype:assignment:assignment.tpl');
    }

    public static function has_instance_config() {
        //< EKAMPUS
        return true;
    }

    public static function instance_config_form($instance) {
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

    // EKAMPUS >
    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.assignment');
    }

}
