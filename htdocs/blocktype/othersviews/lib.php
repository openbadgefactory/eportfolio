<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-othersviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeOthersViews extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.othersviews');
    }

    public static function get_description() {
        return get_string('description1', 'blocktype.othersviews');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        require_once('view.php');
        $configdata = $instance->get('configdata');
        $nviews = isset($configdata['limit']) ? intval($configdata['limit']) : 100;

        $sort = array(array('column' => 'lastchanged', 'desc' => true));
        // Uncomment the multiple-part to include views from user's groups.
        $owner = (object) array('exclude_owner' => $USER->get('id')); //, 'multiple' => true);
        $types = array('collection', 'portfolio', 'studyjournal', 'multiresume', 'blog');
        $res = View::view_search(null, null, $owner, null, $nviews, 0, true, $sort,
                $types);
        $views = $res->data;

        $smarty = smarty_core();
        $smarty->assign('loggedin', $USER->is_logged_in());
        $smarty->assign('views', $views);
        return $smarty->fetch('blocktype:othersviews:othersviews.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
        'limit' => array(
            'type' => 'text',
            'title' => get_string('viewstoshow', 'blocktype.othersviews'),
            'description' => get_string('viewstoshowdescription', 'blocktype.othersviews'),
            'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
            'size' => 3,
            'minvalue' => 1,
            'maxvalue' => 100,
        ),
        'titlelinkurl' => array(
                'type' => 'hidden',
                'value'   => 'interaction/pages/sharedviews.php',
            ),
        );
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.othersviews');
    }
}
