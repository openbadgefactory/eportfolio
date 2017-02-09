<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-announcement
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeAnnouncement extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.announcement');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.announcement');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function get_viewtypes() {
        return array('dashboard');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER, $THEME;
        $configdata = $instance->get('configdata');

//        $desiredtypes = array();
        $desiredtypes = array('maharamessage');
        foreach($configdata as $k => $v) {
            if (!empty($v) && $k != 'maxitems') {
                $type = preg_replace('/[^a-z]+/', '', $k);
                $desiredtypes[$type] = $type;
            }
        }

        if ($USER->get('admin') && !empty($desiredtypes['adminmessages'])) {
            unset($desiredtypes['adminmessages']);
            $desiredtypes += get_column('activity_type', 'name', 'admin', 1);
        }

        $maxitems = isset($configdata['maxitems']) ? $configdata['maxitems'] : 5;

        $records = array();
        if ($desiredtypes) {
            $sql = "
                SELECT n.id, n.subject, n.message, n.url, n.urltext, n.read, " .
                    db_format_tsfield('n.ctime', 'ctime') . ",
                    n.from, t.name AS type, u.firstname, u.lastname, u.deleted
                  FROM {notification_internal_activity} n
                  JOIN {activity_type} t ON n.type = t.id
             LEFT JOIN {usr} u ON n.from = u.id
                 WHERE n.usr = ?
                       AND t.name IN (" . join(',', array_map('db_quote', $desiredtypes)) . ")
              ORDER BY n.ctime DESC
                 LIMIT ?";

            $records = get_records_sql_array($sql, array(
                $USER->get('id'),
                $maxitems + 1 // Hack to decide whether to show the More... link
            ));
        }

        // Hack to decide whether to show the More... link
        if ($showmore = count($records) > $maxitems) {
            unset($records[$maxitems]);
        }

        if ($records) {
            require_once('activity.php');

            $teacher_data = array();

            foreach ($records as $key => &$r) {
                $r->author = full_name((object) array(
                    "firstname" => $r->firstname,
                    "lastname" => $r->lastname,
                    "deleted" => $r->deleted));
                $r->relativedate = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), null, $r->ctime);

                // Create a simple cache to reduce the amount of SQL queries.
                // Would be better to fetch all the teacher data (those
                // included in $records) once and check from there.
                if (!isset($teacher_data[$r->from])) {
                    $teacher_data[$r->from] = user_is_teacher($r->from);
                }

                if ($teacher_data[$r->from] === true) {
                    $section = empty($r->plugintype) ? 'activity' : "{$r->plugintype}.{$r->pluginname}";
                    $r->strtype = get_string('type' . $r->type, $section);
                    $r->message = format_notification_whitespace($r->message, $r->type);
                }
                else {
                    unset($records[$key]);
                }
            }
        }

        $smarty = smarty_core();
        if ($showmore) {
            $smarty->assign('desiredtypes', implode(',', $desiredtypes));
        }
        $smarty->assign('blockid', 'blockinstance_' . $instance->get('id'));
        $smarty->assign('items', $records);
        $smarty->assign('readicon', $THEME->get_url('images/readusermessage.png'));

        return $smarty->fetch('blocktype:announcement:announcement.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        $configdata = $instance->get('configdata');

        $types = get_records_array('activity_type', 'admin', 0, 'plugintype,pluginname,name', 'name,plugintype,pluginname');
        if ($USER->get('admin')) {
            $types[] = (object)array('name' => 'adminmessages');
        }

        $elements = array();
        $elements['types'] = array(
            'type' => 'fieldset',
            'legend' => get_string('messagetypes', 'blocktype.announcement'),
            'elements' => array(),
        );
        foreach($types as $type) {
            if ($type->name == 'usermessage' || $type->name == 'institutionmessage'){
                if (!empty($type->plugintype)) {
                    $title = get_string('type' . $type->name, $type->plugintype . '.' . $type->pluginname);
                }
                else {
                    $title = get_string('type' . $type->name, 'activity');
                }
                if ($type->name =='usermessage'){
                    $title = get_string('type', 'blocktype.announcement');
                }
                $elements['types']['elements'][$type->name] = array(
                    'type' => 'checkbox',
                    'title' => $title,
                    'defaultvalue' => isset($configdata[$type->name]) ? $configdata[$type->name] : 0,
                );
            }
        }
        $elements['maxitems'] = array(
            'type' => 'text',
            'title' => get_string('maxitems', 'blocktype.announcement'),
            'description' => get_string('maxitemsdescription', 'blocktype.announcement'),
            'defaultvalue' => isset($configdata['maxitems']) ? $configdata['maxitems'] : 5,
            'rules' => array(
                'minvalue' => 1,
                'maxvalue' => 100,
            ),
        );
        $elements['titlelinkurl'] =  array(
                'type' => 'hidden',
                'value'   => 'account/activity/index.php?type=usermessage',
        );

        return $elements;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Inbox only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    /**
     * We need a default title for this block, so that the announcement blocks
     * on the dashboard are translatable.
     *
     * To maintain existing behaviour, use the 'annoucnementblocktitle' string unless
     * the block has only got forum post notifications in it, in which case
     * use 'topicsimfollowing'
     */
    public static function get_instance_title(BlockInstance $instance) {
        if ($configdata = $instance->get('configdata')) {
            foreach ($configdata as $k => $v) {
                if ($v && $k != 'newpost' && $k != 'maxitems') {
                    return get_string('announcementblocktitle', 'blocktype.announcement');
                }
            }
            if (!empty($configdata['newpost'])) {
                return get_string('topicsimfollowing');
            }
        }
        return get_string('announcementblocktitle','blocktype.announcement');
    }

}
