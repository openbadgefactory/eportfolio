<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-othersrecentposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeOthersrecentposts extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/othersrecentposts');
    }


    public static function get_description() {
        return get_string('description', 'blocktype.blog/othersrecentposts');
    }

    public static function get_categories() {
        return array('blog');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $configdata = $instance->get('configdata');
        $result = '';
        $limit = isset($configdata['count']) ? (int) $configdata['count'] : 100;
        $before = 'TRUE';

        if ($instance->get_view()->is_submitted()) {
            if ($submittedtime = $instance->get_view()->get('submittedtime')) {
                // Don't display posts added after the submitted date.
                $before = "a.ctime < '$submittedtime'";
            }
        }

        // <EKAMPUS
        $share = array('user', 'friend', 'group', 'institution', 'loggedin', 'public', 'token');
        $types = array(
            'blog' => get_string('blog', 'interaction.pages'),
            'portfolio' => get_string('portfolio', 'interaction.pages'),
        );
        $data = View::shared_to_user('', '', 100, 0, 'lastchanged', 'desc', $share, array_keys($types));
        $viewids = '';

        if (is_array($data->ids)) {
            $viewids = implode(',', $data->ids);
        }

        if (!empty($viewids)) {
            if (!$mostrecent = get_records_sql_array(
            "SELECT *, " . db_format_tsfield('r.ctime', 'ctime') . ", a.title AS parenttitle, a.id AS parentid
            FROM {artefact} a
            LEFT JOIN {artefact} r ON (a.id = r.parent)
            LEFT JOIN {view_artefact} va ON (va.artefact = a.id)
            LEFT JOIN {artefact_blog_blogpost} ab ON (ab.blogpost = r.id AND ab.published = 1)
            WHERE a.artefacttype IN ('blog')
            AND va.view IN ($viewids) AND ab.published = 1 AND a.owner != ?
            GROUP BY ab.blogpost
            ORDER BY r.ctime DESC, a.id DESC
            LIMIT " . $limit, array($USER->id))) {
                $mostrecent = array();
            }
        }
        else {
            $mostrecent = array();
        }

        // format the dates
        foreach ($mostrecent as &$data) {
            $data->displaydate = format_date($data->ctime);
            $data->authorname = display_name($data->owner);
        }

        $smarty = smarty_core();
        $smarty->assign('mostrecent', $mostrecent);
        $smarty->assign('view', $instance->get('view'));
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('editing', $editing);

        $result = $smarty->fetch('blocktype:othersrecentposts:othersrecentposts.tpl');

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        safe_require('artefact', 'blog');
        $configdata = $instance->get('configdata');
        $elements = array(
            //self::artefactchooser_element((isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
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
                'value'   => 'interaction/pages/sharedviews.php',
            ),
        );

        return $elements;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('blogs', 'artefact.blog'),
            'defaultvalue' => $default,
            'blocktype' => 'othersrecentposts',
            'limit'     => 10,
            'selectone' => false,
            'artefacttypes' => array('blog'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Recentposts blocktype is only allowed in personal views, because
     * currently there's no such thing as group/site blogs
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }
    public static function get_instance_title(BlockInstance $instance) {
        return get_string('title', 'blocktype.blog/othersrecentposts');
    }
}
