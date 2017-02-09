<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-recentforumposts
 * @author     Nigel McNie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Nigel McNie http://nigel.mcnie.name/
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeRecentForumPosts extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.recentforumposts');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.recentforumposts');
    }

    public static function get_categories() {
        return array('general');
    }

    private static function get_group(BlockInstance $instance) {
        static $groups = array();
        global $USER;

        $block = $instance->get('id');

        if (!isset($groups[$block])) {

            // When this block is in a group view it should always display the
            // forum posts from that group

            $groupid = $instance->get_view()->get('group');
            $configdata = $instance->get('configdata');
            if (!$groupid && !empty($configdata['groupid'])) {
        //< EKAMPUS
                $groupid = ($configdata['groupid']);

            }

            if (is_array($groupid)){
                $groupid = implode(',',$groupid);
            }

            if ($groupid) {
                $sql = "SELECT *, ". db_format_tsfield('ctime')." FROM {group} WHERE id IN ($groupid) AND deleted = 0";
                $groups[$block] = get_records_sql_array($sql, array());
            }
            //if no group selected -> display all groupforums of the user
            else {
                 $usergroups = get_records_sql_array(
                "SELECT g.*
                FROM {group} g
                JOIN {group_member} gm ON (gm.group = g.id)
                WHERE gm.member = ? AND g.grouptype != ?
                AND g.deleted = 0", array($USER->get('id'), 'system'));

                if ($usergroups) {
                    foreach($usergroups as &$usergroup){
                        $usergroup->ctime = strtotime($usergroup->ctime);
                    }
                    $groups[$block] = $usergroups;
                }
                else {
                    $groups[$block] = false;
                }
            }
        // EKAMPUS >
        }

        return $groups[$block];

    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $forumposts = array();
        $groups = self::get_group($instance);

        if ($groups) {
            require_once('group.php');
            safe_require('interaction' ,'forum'); // < EKAMPUS

            $validgroups = array();

            foreach($groups as &$group) {
                $role = group_user_access($group->id);

                if ($role || $group->public) {
                    $validgroups[] = $group->id;
                }
            }

            if (count($validgroups) > 0) {
                $limit = 5;
                $configdata = $instance->get('configdata');

                if (!empty($configdata['limit'])) {
                    $limit = intval($configdata['limit']);
                }

                $groupids = implode(',', array_map('intval', $validgroups));
                $forumposts = get_records_sql_array("
                    SELECT
                        p.id, p.subject, p.body, p.poster, p.topic, t.forum, pt.subject AS topicname, " .
                        db_format_tsfield('p.ctime', 'ctime') . ", i.group, g.name AS groupname, u.firstname, u.lastname, u.username,
                        u.preferredname, u.email, u.profileicon, u.admin, u.staff, u.deleted, u.urlid
                    FROM
                        {interaction_forum_post} p
                        INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
                        INNER JOIN {interaction_instance} i ON (i.id = t.forum)
                        INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
                        INNER JOIN {usr} u ON p.poster = u.id
                        INNER JOIN {group} g ON i.group = g.id
                    WHERE
                        i.group IN ($groupids)
                        AND i.deleted = 0
                        AND t.deleted = 0
                        AND p.deleted = 0
                    ORDER BY
                        p.ctime DESC", array(), 0, $limit
                );

                if ($forumposts) {
                    $userfields = array(
                        'firstname', 'lastname', 'username', 'preferredname', 'email', 'profileicon',
                        'admin', 'staff', 'deleted', 'urlid',
                    );

                    foreach ($forumposts as &$f) {
                        $f->author = (object) array('id' => $f->poster);
                        $f->relativedate = relative_date(get_string('strftimerecentrelative', 'interaction.forum'), get_string('strftimerecentfull'), $f->ctime);
                        foreach ($userfields as $uf) {
                            $f->author->$uf = $f->$uf;
                            unset($f->$uf);
                        }
                    }
                }
            }
        }

        $smarty = smarty_core();
        $smarty->assign('posts', $forumposts);

        if ($instance->get_view()->get('type') == 'grouphomepage') {
            // <EKAMPUS
            if (count($groups) === 1) {
                $smarty->assign('group', $groups[0]);
            }
            // EKAMPUS>
            return $smarty->fetch('blocktype:recentforumposts:latestforumposts.tpl');
        }

        return $smarty->fetch('blocktype:recentforumposts:recentforumposts.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;

        $elements   = array();
        $groupid    = $instance->get_view()->get('group');
        $configdata = $instance->get('configdata');

        if ($groupid || $instance->get_view()->get('institution')) {
            // This block will show recent forum posts from this group
            $elements['groupid'] = array(
                'type' => 'hidden',
                'value' => $groupid,
            );
        }
        else {
            // Allow the user to choose a group they're in to show posts for
            if (!empty($configdata['groupid'])) {
            //< EKAMPUS
                $groupid = $configdata['groupid'];
                //$group = get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
            // EKAMPUS >
            }

            $usergroups = get_records_sql_array(
                "SELECT g.id, g.name
                FROM {group} g
                JOIN {group_member} gm ON (gm.group = g.id)
                WHERE gm.member = ? AND g.grouptype != ?
                AND g.deleted = 0
                ORDER BY g.name", array($USER->get('id'), 'system'));

            if ($usergroups) {
                $choosablegroups = array();
                foreach ($usergroups as $group) {
                    // <EKAMPUS
                    //$choosablegroups[$group->id] = $group->name;
                    $choosablegroups[] = array(
                        'title'        => $group->name,
                        'value'        => $group->id,
                        'defaultvalue' => is_array($groupid) && in_array($group->id, $groupid)
                    );
                    // EKAMPUS>
                }

                // <EKAMPUS
                $elements['groupid'] =  array(
                    'type'  => 'checkboxes',
                    'title' => get_string('group', 'blocktype.recentforumposts'),
                    'elements' => $choosablegroups,
                );
                // EKAMPUS>
            }
        }

        if (isset($elements['groupid'])) {
            $elements['limit'] = array(
                'type' => 'text',
                'title' => get_string('poststoshow', 'blocktype.recentforumposts'),
                'description' => get_string('poststoshowdescription', 'blocktype.recentforumposts'),
                'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
                'size' => 3,
                'minvalue' => 1,
                'maxvalue' => 100,
            );
        }
        else {
            $elements = array(
                'whoops' => array(
                    'type' => 'html',
                    'value' => '<p class="noartefacts">' . get_string('nogroupstochoosefrom', 'blocktype.recentforumposts') . '</p>',
                ),
            );
        }
        //< EKAMPUS
        $elements['titlelinkurl'] = array(
                'type' => 'hidden',
                'value'   => 'group/topics.php',
            );
        // EKAMPUS >
        return $elements;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function feed_url(BlockInstance $instance) {
        //< EKAMPUS
        if ($groups = self::get_group($instance)) {
            foreach ($groups as $group){
                if ($group->public) {
                    return get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
                }
            }
        }
         // EKAMPUS >
    }

    public static function get_instance_title(BlockInstance $instance) {
        if ($instance->get_view()->get('type') == 'grouphomepage') {
            return get_string('latestforumposts', 'interaction.forum');
        }
        return get_string('title', 'blocktype.recentforumposts');
    }
}
