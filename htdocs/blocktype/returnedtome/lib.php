<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-returnedtome
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeReturnedToMe extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.returnedtome');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.returnedtome');
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
    public static function hide_title_on_empty_content() {
        return true;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            return '';
        }

        if (!is_teacher()) {
            return '';
        }

        $smarty = smarty_core();
        safe_require('interaction', 'learningobject');

        $returnedtomeviews = InteractionLearningobjectInstance::get_returned_tome_views($USER);
        $returnedtomecollections = InteractionLearningobjectInstance::get_returned_tome_collections($USER);

        $returnedtomeobjects = array_merge(array_values($returnedtomeviews), array_values($returnedtomecollections));

        uasort($returnedtomeobjects, 'InteractionLearningobjectInstance::sort_by_date');
        $returnedtomeobjects = array_slice($returnedtomeobjects, 0, 99);
        foreach ($returnedtomeobjects as &$object){
            $object->prev_date = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecentfull'), strtotime($object->prev_return_date));
        }
        $smarty->assign('returns', $returnedtomeobjects);
        return $smarty->fetch('blocktype:returnedtome:returnedtome.tpl');
    }

    public static function has_instance_config() {
        return true;
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
        return get_string('title', 'blocktype.returnedtome');
    }

}
