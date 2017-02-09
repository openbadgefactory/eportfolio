<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-studymaterial
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeStudyMaterial extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.studymaterial');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.studymaterial');
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

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        $smarty = smarty_core();
//        safe_require('artefact', 'assignment');

        //$assignments = ArtefactTypeAssignment::get_assignments($USER);

        //$smarty->assign('studymaterial', $studymaterial);
        return $smarty->fetch('blocktype:studymaterial:studymaterial.tpl');
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
                'title' => get_string('itemstoshow', 'blocktype.blog/othersrecentposts'),
                'description'   => get_string('betweenxandy', 'mahara', 1, 100),
                'defaultvalue' => isset($configdata['count']) ? $configdata['count'] : 10,
                'size' => 3,
                'rules' => array('integer' => true, 'minvalue' => 1, 'maxvalue' => 100),
            ),
        'titlelinkurl' => array(
                'type' => 'hidden',
                'value'   => '',
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
        return get_string('title', 'blocktype.studymaterial');
    }

}
