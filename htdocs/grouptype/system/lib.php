<?php
/**
 *
 * @package    mahara
 * @subpackage grouptype-system
 * @author     Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginGrouptypeSystem extends PluginGrouptype {

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            parent::installgrouptype('GroupTypeSystem');
        }
    }

    public static function can_be_disabled() {
        return true;
    }

    public static function has_config() {
        return true;
    }

    public static function validate_config_options($form, $values) {

    }

    public static function save_config_options($values) {

    }


    public static function get_config_options() {
        global $USER;
        if (!$USER->get('admin')) throw new AccessDeniedException();

        $elements = array();

        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }

    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'plugin'       => 'system',
                'event'        => 'userjoinsgroup',
                'callfunction' => 'user_joined_group',
            )
        );
    }

    public static function user_joined_group($event, $gm) {

        $group = get_record('group', 'id', $gm['group'], 'grouptype', 'system');
        if (!$group || $group->deleted) {
            // Wrong grouptype
            return;
        }

        $user_inst = get_record('usr_institution', 'usr', $gm['member'], 'institution', $group->institution);
        if ( ! $user_inst) {
            // Not a member in same institution
            return;
        }

        if ($user_inst->admin == 0 && $user_inst->staff == 0) {
            // No need to change role
            return;
        }

        error_log('DEBUG: set as group admin ' . $gm['member']);

        $where = array(
            'group'  => $gm['group'],
            'member' => $gm['member'],
        );
        $data = $where;
        $data['role'] = 'admin';
        update_record('group_member', $data, $where);
    }
}

class GroupTypeSystem extends GroupType {

    public static function allowed_join_types($all=false) {
        global $USER;
        return self::user_allowed_join_types($USER, $all);
    }

    public static function user_allowed_join_types($user, $all=false) {
        return array('controlled');
    }

    public static function get_roles() {
        return array('member', 'admin');
    }

    public static function get_view_moderating_roles() {
        return array('admin');
    }

    public static function get_view_assessing_roles() {
        return array('admin');
    }

    public static function default_role() {
        return 'member';
    }

    public static function default_artefact_rolepermissions() {
        return array(
            'member' => (object) array('view' => false, 'edit' => false, 'republish' => false),
            'admin'  => (object) array('view' => true, 'edit' => true, 'republish' => true),
        );
    }

    public static function can_be_created_by_user() {
        global $USER;
        return $USER->get('admin');
    }

    public static function can_become_admin($userid) {
        return false;
    }

    public static function get_group_artefact_plugins() {
        return array();
    }
}
