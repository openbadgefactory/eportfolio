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
 * @subpackage interaction-learningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once('collection.php');
require_once('activity.php');

class PluginInteractionLearningobject extends PluginInteraction {

    public static function instance_config_form($group, $instance = null) {

    }

    public static function instance_config_save($instance, $values) {

    }

    public static function group_menu_items($group) {
        return array();
    }

    public static function menu_items() {
        $menu = array();

        if (!in_admin_section()) {
            $menu['learningobject'] = array(
                'path' => 'learningobjects',
                'url' => 'interaction/learningobject/index.php',
                'title' => get_string('learningobjects',
                        'interaction.learningobject'),
                'weight' => 60
            );
        }

        return $menu;
    }

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'newlearningobject',
                'admin' => 0,
                'delay' => 0
            )
        );
    }

    public static function get_cron() {
        return array(
            (object) array(
                'callfunction' => 'publish_assignments',
                'hour' => '*', //'1',
                'minute' => '*'// '0'
            )
        );
    }

    /**
     * Publishes the assignments for institutions, groups and users.
     */
    public static function publish_assignments() {
        self::publish_institution_assignments();
        self::publish_group_assignments();
        self::publish_user_assignments();
    }

    /**
     * Publishes the user assignments.
     */
    private static function publish_user_assignments() {
        $user_assignments = get_records_sql_array("
            SELECT au.collection, au.user, au.assignment_date, c.owner, c.name
              FROM {interaction_learningobject_assigned_user} au
         LEFT JOIN {collection} c ON au.collection = c.id
             WHERE au.is_assigned = 0 AND DATE(au.assignment_date) <= DATE(NOW())",
                array());

        if (is_array($user_assignments)) {
            require_once('activity.php');

            foreach ($user_assignments as $assignment) {
                $learningobject = new Collection($assignment->collection);

                self::add_access_to_user($learningobject, $assignment->user);
                self::send_assignment_notification($assignment,
                        array($assignment->user));
            }

            execute_sql("
                UPDATE {interaction_learningobject_assigned_user}
                   SET is_assigned = 1
                 WHERE is_assigned = 0 AND DATE(assignment_date) <= DATE(NOW())");
        }
    }

    /**
     * Publishes the group assignments.
     */
    private static function publish_group_assignments() {
        $group_assignments = get_records_sql_array("
            SELECT ag.collection, ag.`group`, ag.assignment_date,
                c.owner, c.name
              FROM {interaction_learningobject_assigned_group} ag
         LEFT JOIN {collection} c ON ag.collection = c.id
             WHERE ag.is_assigned = 0 AND DATE(ag.assignment_date) <= DATE(NOW())",
                array());

        if (is_array($group_assignments)) {
            require_once('activity.php');

            foreach ($group_assignments as $assignment) {
                $learningobject = new Collection($assignment->collection);

                self::add_access_to_group($learningobject, $assignment->group);

                $userids = group_get_member_ids($assignment->group);
                $ownerindex = array_search($assignment->owner, $userids);

                // Remove owner from the recipients
                if ($ownerindex !== false) {
                    array_splice($userids, $ownerindex, 1);
                }

                if (count($userids) > 0) {
                    self::send_assignment_notification($assignment, $userids);
                }
            }

            execute_sql("
                UPDATE {interaction_learningobject_assigned_group}
                   SET is_assigned = 1
                 WHERE is_assigned = 0 AND DATE(assignment_date) <= DATE(NOW())");
        }
    }

    /**
     * Publishes the institution assignments.
     */
    private static function publish_institution_assignments() {

        $institution_assignments = get_records_sql_array("
            SELECT ai.collection, ai.institution, ai.assignment_date,
                c.owner, c.name
              FROM {interaction_learningobject_assigned_institution} ai
         LEFT JOIN {collection} c ON ai.collection = c.id
             WHERE ai.is_assigned = 0 AND DATE(ai.assignment_date) <= DATE(NOW())",
                array());

        if (is_array($institution_assignments)) {
            require_once('activity.php');

            foreach ($institution_assignments as $assignment) {
                $learningobject = new Collection($assignment->collection);

                self::add_access_to_institution($learningobject,
                        $assignment->institution);

                // PENDING: exclude staff & admins?
                $userids = get_column_sql("
                    SELECT DISTINCT(id)
                      FROM {usr_institution} ui
                 LEFT JOIN {usr} u ON ui.usr = u.id
                     WHERE ui.institution = ? AND u.id != 0 AND u.active = 1
                        AND u.deleted = 0 AND u.id != ?",
                        array($assignment->institution, $assignment->owner));

                if (is_array($userids) && count($userids) > 0) {
                    self::send_assignment_notification($assignment, $userids);
                }
            }

            execute_sql("
                UPDATE {interaction_learningobject_assigned_institution}
                   SET is_assigned = 1
                 WHERE is_assigned = 0 AND DATE(assignment_date) <= DATE(NOW())");
        }
    }

    /**
     * Sends a notification about a new assignment/learning object for
     * a group of users.
     *
     * @param type $assignment The assignment
     * @param array $userids An array of user ids.
     */
    private static function send_assignment_notification($assignment,
                                                         array $userids) {
        $subject = get_string('newlearningobjectassignedsubject',
                'interaction.learningobject');
        $message = get_string('newlearningobjectassignedmessage',
                'interaction.learningobject', $assignment->name);
        $notificationdata = array(
            'users' => $userids,
            'name' => $assignment->name,
//            'subject' => $subject,
//            'message' => $message,
            'url' => 'interaction/learningobject/view.php?id=' . $assignment->collection,
            'urltext' => $assignment->name,
            'fromuser' => $assignment->owner
        );

        activity_occurred('newlearningobject', $notificationdata, 'interaction',
                'learningobject');
    }

    /**
     * Returns the view access config of a collection (basically the access
     * config of the first view in the collection).
     *
     * @param Collection $learningobject The collection.
     * @return array|boolean Returns the view config array or false if no config
     *      is found.
     */
    private static function get_viewconfig(Collection $learningobject) {
        $viewids = $learningobject->get_viewids();

        if (count($viewids) > 0) {
            $firstview = new View($viewids[0]);
            $access = $firstview->get_access();
            $viewconfig = array(
                'startdate' => $firstview->get('startdate'),
                'stopdate' => $firstview->get('stopdate'),
                'template' => 1,
                'retainview' => $firstview->get('retainview'),
                'allowcomments' => $firstview->get('allowcomments'),
                'approvecomments' => (int) ($firstview->get('allowcomments') && $firstview->get('approvecomments')),
                'accesslist' => $access
            );

            return $viewconfig;
        }

        return false;
    }

    /**
     * Adds access to collection for selected user.
     *
     * @param Collection $learningobject The collection
     * @param int $userid The id of the user.
     */
    public static function add_access_to_user(Collection $learningobject,
                                              $userid) {
        $viewconfig = self::get_viewconfig($learningobject);

        if ($viewconfig !== false) {
            // Remove old access settings for the selected user (if found).
            foreach ($viewconfig['accesslist'] as $key => $item) {
                if ($item['type'] === 'user' && (int) $item['id'] === (int) $userid) {
                    unset($viewconfig['accesslist'][$key]);
                }
            }

            // Add user access to accesslist.
            $viewconfig['accesslist'][] = array(
                'type' => 'user',
                'id' => $userid,
                'startdate' => '',
                'stopdate' => ''
            );

            View::update_view_access($viewconfig, $learningobject->get_viewids());
        }
    }

    /**
     * Adds access to a collection for selected group.
     *
     * @param Collection $learningobject The collection.
     * @param type $groupid The id of the group.
     */
    private static function add_access_to_group(Collection $learningobject,
                                                $groupid) {
        $viewconfig = self::get_viewconfig($learningobject);

        if ($viewconfig !== false) {
            // Remove old access settings for the selected group (if found).
            foreach ($viewconfig['accesslist'] as $key => $item) {
                if ($item['type'] === 'group' && (int) $item['id'] === (int) $groupid) {
                    unset($viewconfig['accesslist'][$key]);
                }
            }

            // Add group access to accesslist.
            $viewconfig['accesslist'][] = array(
                'type' => 'group',
                'id' => $groupid,
                'startdate' => '',
                'stopdate' => ''
            );

            View::update_view_access($viewconfig, $learningobject->get_viewids());
        }
    }

    /**
     * Adds a access to a collection for the selected institution.
     *
     * @param Collection $learningobject The collection.
     * @param string $institution The institution id.
     */
    private static function add_access_to_institution(Collection $learningobject,
                                                      $institution) {
        $viewconfig = self::get_viewconfig($learningobject);

        if ($viewconfig !== false) {
            // Remove old access settings for the selected institution (if found).
            foreach ($viewconfig['accesslist'] as $key => $item) {
                if ($item['type'] === 'institution' && $item['id'] === $institution) {
                    unset($viewconfig['accesslist'][$key]);
                }
            }

            // Add institution access to accesslist.
            $viewconfig['accesslist'][] = array(
                'type' => 'institution',
                'id' => $institution,
                'startdate' => '',
                'stopdate' => ''
            );

            View::update_view_access($viewconfig, $learningobject->get_viewids());
        }
    }

    /**
     * Post-installation hook for this plugin.
     */
    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // Add learning object columns to collection table.
            $table = new XMLDBTable('collection');
            $typefield = new XMLDBField('type');
            $typefield->setAttributes(XMLDB_TYPE_CHAR, 255, null, null);
            $datefield = new XMLDBField('return_date');
            $datefield->setAttributes(XMLDB_TYPE_DATETIME);

            add_field($table, $typefield, false);
            add_field($table, $datefield, false);

            execute_sql("
                ALTER TABLE {interaction_learningobject_collection_parent}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_collection_parent}
             ADD CONSTRAINT FOREIGN KEY parentfk (parent)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            // Add cascading rules to user assignees.
            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_user}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_user}
             ADD CONSTRAINT FOREIGN KEY userfk (user)
                 REFERENCES {usr} (id) ON DELETE CASCADE"
            );

            // Add cascading rules to group assignees.
            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_group}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_group}
             ADD CONSTRAINT FOREIGN KEY groupfk (`group`)
                 REFERENCES {group} (id) ON DELETE CASCADE"
            );

            // Add cascading rules to institution assignees.
            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_institution}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_assigned_institution}
             ADD CONSTRAINT FOREIGN KEY institutionfk (institution)
                 REFERENCES {institution} (name) ON DELETE CASCADE"
            );

            // Add cascading rules to assignee instructors.
            execute_sql("
                ALTER TABLE {interaction_learningobject_assignment_instructor}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_assignment_instructor}
             ADD CONSTRAINT FOREIGN KEY userfk (user)
                 REFERENCES {usr} (id) ON DELETE CASCADE"
            );

            // Add cascading rules to returned views and collections.
            execute_sql(
                    "ALTER TABLE {interaction_learningobject_returned_view}
            ADD CONSTRAINT FOREIGN KEY viewfk (viewid)
            REFERENCES {view} (id) ON DELETE CASCADE"
            );

            execute_sql(
                    "ALTER TABLE {interaction_learningobject_returned_collection}
            ADD CONSTRAINT FOREIGN KEY collectionfk (collectionid)
            REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            // Add cascading rules to returned views/collections instructors.
            execute_sql("
                ALTER TABLE {interaction_learningobject_returned_view_instructor}
             ADD CONSTRAINT FOREIGN KEY viewfk (view)
                 REFERENCES {view} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_returned_view_instructor}
             ADD CONSTRAINT FOREIGN KEY userfk (user)
                 REFERENCES {usr} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_returned_collection_instructor}
             ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
                 REFERENCES {collection} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {interaction_learningobject_returned_collection_instructor}
             ADD CONSTRAINT FOREIGN KEY userfk (user)
                 REFERENCES {usr} (id) ON DELETE CASCADE"
            );
        }
    }

}

class InteractionLearningobjectInstance extends InteractionInstance {

    public function interaction_remove_user($userid) {

    }

    public static function get_plugin() {
        return 'learningobject';
    }

    public static function get_instance($id) {
        return new Collection($id);
    }

    public static function add_assignation_status(User $user,
                                                  array &$learningobjects) {
        $ids = array();
        $userid = $user->get('id');

        foreach ($learningobjects as $lo) {
            $ids[] = $lo['id'];
        }

        $idstr = implode(',', array_map('db_quote', $ids));
        $cols = get_column_sql("
            SELECT b.collection
              FROM (
                SELECT collection
                  FROM {interaction_learningobject_assigned_user}
                 WHERE is_assigned = 1 AND user = ?
                    UNION
                SELECT collection
                  FROM {interaction_learningobject_assigned_group}
                 WHERE is_assigned = 1 AND `group` IN (
                    SELECT `group`
                      FROM {group_member}
                     WHERE member = ?
                 )
                    UNION
                SELECT collection
                  FROM {interaction_learningobject_assigned_institution}
                 WHERE is_assigned = 1 AND institution IN (
                    SELECT institution
                      FROM {usr_institution}
                     WHERE usr = ?
                )
            ) b
         LEFT JOIN collection c ON b.collection = c.id
             WHERE c.owner != ? AND b.collection IN ($idstr)",
                array($userid,
            $userid, $userid, $userid));

        $cols = is_array($cols) ? $cols : array();

        foreach ($learningobjects as &$lo) {
            $lo['is_assigned'] = in_array($lo['id'], $cols);
        }

        return true;
    }

    public static function get_learningobjects_assigned_by(User $user = null) {
        if (is_null($user)) {
            global $USER;
            $user = $USER;
        }

        $rtime = db_format_tsfield('return_date', 'rtime');
        $records = get_records_sql_array("
            SELECT *, $rtime
              FROM {collection}
             WHERE owner = ? AND (DATE(return_date) >= DATE(NOW()) OR return_date IS NULL) AND id IN (
                SELECT collection FROM {interaction_learningobject_assigned_institution}
                UNION
                SELECT collection FROM {interaction_learningobject_assigned_group}
                UNION
                SELECT collection FROM {interaction_learningobject_assigned_user}
             )
          ORDER BY COALESCE(return_date, '9999-99-99'), mtime DESC",
                array($user->get('id')));

        if (is_array($records)) {
            foreach ($records as &$record) {
                $record->relativedate = empty($record->rtime) ? '' : relative_date('%v',
                                null, $record->rtime, null, 'strftimedate');
            }
        }

        return (is_array($records) ? $records : array());
    }

    public static function is_assigned(Collection $learningobject) {
        $id = $learningobject->get('id');
        $count = count_records_sql("
            SELECT COUNT(*)
              FROM (
                SELECT collection, is_assigned
                  FROM {interaction_learningobject_assigned_institution}
                 WHERE collection = ? AND is_assigned = 1
                 UNION
                SELECT collection, is_assigned
                  FROM {interaction_learningobject_assigned_group}
                 WHERE collection = ? AND is_assigned = 1
                 UNION
                SELECT collection, is_assigned
                  FROM {interaction_learningobject_assigned_user}
                 WHERE collection = ? AND is_assigned = 1) a",
                array($id, $id, $id));

        return ($count > 0);
    }

    /**
     * Returns the learning objects that are assigned to user and are not
     * returned yet.
     *
     * @param type $userid
     * @return type
     */
    public static function get_assignments($userid, $limit = 100) {
        $atime = db_format_tsfield('a.assignment_date', 'atime');
        $rtime = db_format_tsfield('c.return_date', 'rtime');
        $records = get_records_sql_array("
            SELECT a.*, $atime, c.owner, c.name, c.description, $rtime,
                CONCAT(u.firstname, ' ', u.lastname) AS author
              FROM (
                SELECT au.collection, au.assignment_date
                  FROM {interaction_learningobject_assigned_user} au
                 WHERE au.user = ? AND au.is_assigned = 1

                 UNION
                SELECT ag.collection, ag.assignment_date
                  FROM {interaction_learningobject_assigned_group} ag
                 WHERE ag.`group` IN (
                    SELECT gm.`group`
                      FROM group_member gm
                 LEFT JOIN `group` g ON gm.`group` = g.id
                     WHERE gm.member = ? AND g.deleted = 0
                ) AND ag.is_assigned = 1

                 UNION
                SELECT ai.collection, ai.assignment_date
                  FROM {interaction_learningobject_assigned_institution} ai
                 WHERE ai.institution IN (
                    SELECT institution
                      FROM {usr_institution}
                     WHERE usr = ?
                ) AND ai.is_assigned = 1
            ) a
         LEFT JOIN {collection} c ON a.collection = c.id
         LEFT JOIN {usr} u ON c.owner = u.id
             WHERE a.collection NOT IN (
                SELECT parent
                  FROM {interaction_learningobject_collection_parent}
                 WHERE collection IN (
                    SELECT collectionid
                      FROM {interaction_learningobject_returned_collection}
                )
            ) AND c.owner != ?
          ORDER BY a.assignment_date DESC
             LIMIT ?", array($userid, $userid, $userid, $userid, $limit));

        if (is_array($records)) {
            foreach ($records as &$record) {
                $record->from = format_date($record->atime,
                        'strfdaymonthyearshort');
                $record->to = format_date($record->rtime,
                        'strfdaymonthyearshort');
            }
        }

        return (is_array($records) ? $records : array());
    }

    public static function assign(Collection $learningobject, array $assignees,
                                  array $instructors) {
        global $USER;

        // Trying to assign someone else's learning object.
        if ($learningobject->get('owner') !== $USER->get('id')) {
            throw new AccessDeniedException('Only own learning objects can be assigned.');
        }

        db_begin();

        self::save_assignees($learningobject, $assignees);
        self::save_instructors($learningobject, $instructors);

        db_commit();
    }

    private static function save_assignees(Collection $learningobject,
                                           array $assignees) {
        $existing_assignees = self::get_assignees($learningobject);

        // Delete assignees that were previously saved but not anymore.
        self::delete_old_assignees($learningobject, $existing_assignees,
                $assignees);

        // Add or update new/existing assignees.
        foreach ($assignees as $assignee) {
            if (empty($assignee->date)) {
                throw new ParameterException(get_string('assignationdatemissing',
                        'interaction.learningobject'));
            }

            $exists = false;
            $needsupdate = false;

            foreach ($existing_assignees as $existing) {
                $typematches = $existing->type === $assignee->type;
                $idmatches = (int) $existing->id === (int) $assignee->id;

                if ($typematches && $idmatches) {
                    $exists = true;
                    $datematches = strtotime($existing->assignment_date) === strtotime($assignee->date);

                    if (!$datematches) {
                        $needsupdate = true;
                    }
                }
            }

            // TODO: Check the assignee's institution?
            $table = 'interaction_learningobject_assigned_' . $assignee->type;
            $params = (object) array(
                        'collection' => $learningobject->get('id'),
                        $assignee->type => $assignee->id,
                        'assignment_date' => db_format_timestamp(self::formatted_date_to_timestamp($assignee->date))
            );

            if (!$exists) {
                insert_record($table, $params);
            }
            else if ($needsupdate) {
                update_record($table, $params,
                        array(
                    'collection' => $learningobject->get('id'),
                    $assignee->type => $assignee->id
                ));
            }
        }
    }

    /**
     * Converts parsed time array to unix timestamp.
     * @param array // date parsed using strptime()
     * @return int  // Unix timestamp
     *
     * Copied from view/access.php
     */
    public static function formatted_date_to_timestamp($date, $format = null) {
        if (is_null($format)) {
            $format = get_string('strfdaymonthyearshort');
        }

        $timeparts = strptime($date, $format);

        return mktime(
                $timeparts['tm_hour'], $timeparts['tm_min'],
                $timeparts['tm_sec'], 1, $timeparts['tm_yday'] + 1,
                $timeparts['tm_year'] + 1900
        );
    }

    private static function delete_old_assignees(Collection $learningobject,
                                                 array $existing_assignees,
                                                 array $new_assignees) {
        $todelete = array('user' => array(), 'group' => array(), 'institution' => array());

        // Check for assignees that should be deleted.
        foreach ($existing_assignees as $existing) {
            $found = false;

            foreach ($new_assignees as $assignee) {
                if ($existing->type === $assignee->type &&
                        (int) $existing->id === (int) $assignee->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $todelete[$existing->type][] = $existing->id;
            }
        }

        foreach ($todelete as $type => $deleted_assignees) {
            if (count($deleted_assignees) === 0) {
                continue;
            }

            $table = 'interaction_learningobject_assigned_' . $type;
            $mapfunc = $type === 'institution' ? 'db_quote' : 'intval';
            $ids = implode(',', array_map($mapfunc, $deleted_assignees));

            delete_records_select($table,
                    'collection = ? AND `' . $type .
                    '` IN (' . $ids . ')', array($learningobject->get('id')));
        }
    }

    private static function delete_old_instructors(Collection $learningobject,
                                                   $existing_instructors,
                                                   $new_instructors) {
        $todelete = array();

        foreach ($existing_instructors as $existing) {
            $found = false;

            foreach ($new_instructors as $instructor) {
                if ((int) $existing->user === (int) $instructor->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $todelete[] = $existing->user;
            }
        }

        if (count($todelete) > 0) {
            $ids = implode(',', array_map('intval', $todelete));
            delete_records_select('interaction_learningobject_assignment_instructor',
                    'collection = ? AND user IN (' . $ids . ')',
                    array($learningobject->get('id')));
        }
    }

    private static function save_instructors(Collection $learningobject,
                                             array $instructors) {
        $existing_instructors = self::get_instructors($learningobject);
        self::delete_old_instructors($learningobject, $existing_instructors,
                $instructors);

        foreach ($instructors as $instructor) {
            $user = new User();
            $user->find_by_id($instructor->id);

            if (!is_teacher($user)) {
                throw new ParameterException('Only teachers can be instructors.');
            }

            $found = false;

            foreach ($existing_instructors as $existing) {
                if ((int) $existing->user === (int) $instructor->id) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                insert_record('interaction_learningobject_assignment_instructor',
                        (object) array(
                            'collection' => $learningobject->get('id'),
                            'user' => $instructor->id));
            }
        }
    }

    public static function get_assignees(Collection $learningobject) {
        $id = $learningobject->get('id');
        $records = get_records_sql_array("
            SELECT *
              FROM (
                SELECT au.collection, au.user AS id, au.assignment_date, au.is_assigned,
                    'user' AS type, CONCAT(u.firstname, ' ', u.lastname) AS name
                  FROM {interaction_learningobject_assigned_user} au
             LEFT JOIN {usr} u ON au.user = u.id
                 WHERE collection = ?

                 UNION

                SELECT ag.collection, ag.`group` AS id, ag.assignment_date, ag.is_assigned,
                    'group' AS type, g.name AS name
                 FROM {interaction_learningobject_assigned_group} ag
            LEFT JOIN {group} g ON ag.`group` = g.id
                WHERE collection = ?

                 UNION

                SELECT ai.collection, ai.institution AS id, ai.assignment_date, ai.is_assigned,
                    'institution' AS type, i.displayname AS name
                  FROM {interaction_learningobject_assigned_institution} ai
             LEFT JOIN {institution} i ON ai.institution = i.name
                 WHERE collection = ?
                ) a
          ORDER BY assignment_date ASC, type ASC", array($id, $id, $id));

        if (is_array($records)) {
            $dateformat = get_string('strfdaymonthyearshort');
            foreach ($records as &$record) {
                $record->formatted_assignment_date = strftime($dateformat,
                        strtotime($record->assignment_date));
            }
        }

        return is_array($records) ? $records : array();
    }

    public static function get_instructors(Collection $learningobject) {
        $records = get_records_sql_array("
            SELECT ai.collection, ai.user, CONCAT(u.firstname, ' ', u.lastname) AS name
              FROM {interaction_learningobject_assignment_instructor} ai
         LEFT JOIN {usr} u ON ai.user = u.id
             WHERE ai.collection = ?", array($learningobject->get('id')));

        return is_array($records) ? $records : array();
    }

    public static function get_collection_parent(Collection $collection) {
        $result = get_record_sql("SELECT cp.parent FROM {interaction_learningobject_collection_parent} cp
                                  LEFT JOIN {collection} c ON cp.parent = c.id WHERE c.type = ? AND cp.collection = ?",
                array('learningobject', $collection->get('id')));
        if ($result) {
            return new Collection($result->parent);
        }
        else {
            return $result;
        }
    }

    public static function return_view(View $view, $instructors) {
        global $USER;
        if ($view->get('owner') !== $USER->get('id')) {
            throw new AccessDeniedException('Only own views can be returned.');
        }

        if ($view && is_array($instructors)) {
            self::save_returned_view($view, $instructors);
        }
        else {
            throw new ParameterException('Instructors must be an array.');
        }
    }

    /**
     * Saves view return dates and instructors (only for the first time)
     * - View $view is the view object
     * - $instructor is array of user ids
     * */
    public static function save_returned_view(View $view, $instructors) {
        $viewid = $view->get('id');
        $table = 'interaction_learningobject_returned_view';
        if (!self::get_returned_view($viewid)) {
            $params = (object) array(
                        'viewid' => $viewid,
                        'first_return_date' => db_format_timestamp(time()),
                        'prev_return_date' => db_format_timestamp(time())
            );
            insert_record($table, $params);

            $tablei = 'interaction_learningobject_returned_view_instructor';
            foreach ($instructors as $instructor) {
                $user = new User();
                $user->find_by_id($instructor);

                if (!is_teacher($user)) {
                    throw new ParameterException('Only teachers can be instructors.');
                }
                self::add_access_to_user_view($view, $instructor, 0);
                $paramsi = (object) array(
                            'view' => $viewid,
                            'user' => $instructor,
                );
                insert_record($tablei, $paramsi);
            }
        }
        else {
            $paramsu = (object) array(
                        'prev_return_date' => db_format_timestamp(time()),
            );
            update_record($table, $paramsu, array('viewid' => $viewid));
        }
    }

    public static function get_returned_view($viewid) {
        $records = get_record_sql('SELECT *
                                    FROM {interaction_learningobject_returned_view} rv
                                    WHERE rv.viewid = ?', array($viewid));
        return $records;
    }

    public static function get_returned_view_instructors($viewid) {
        $records = get_records_sql_array('SELECT vi.user, u.username, u.firstname, u.lastname, u.preferredname, u.profileicon, u.email, u.urlid
                                         FROM {interaction_learningobject_returned_view_instructor} vi
                                         LEFT JOIN {usr} u ON vi.user = u.id
                                         WHERE vi.view = ?', array($viewid));
        return $records;
    }

    public static function return_collection(Collection $collection,
                                             $instructors) {
        global $USER;
        if ($collection->get('owner') !== $USER->get('id')) {
            throw new AccessDeniedException('Only own collections can be returned.');
        }

        if ($collection && is_array($instructors)) {
            self::save_returned_collection($collection, $instructors);
        }
        else {
            throw new ParameterException('Instructors must be an array.');
        }
    }

    /**
     * Saves collection return dates and instructors (only for the first time)
     * - Collection $collection is the collection object
     * - $instructor is array of user ids
     * */
    public static function save_returned_collection(Collection $collection,
                                                    $instructors) {
        $collectionid = $collection->get('id');
        $table = 'interaction_learningobject_returned_collection';
        if (!self::get_returned_collection($collectionid)) {
            $params = (object) array(
                        'collectionid' => $collectionid,
                        'first_return_date' => db_format_timestamp(time()),
                        'prev_return_date' => db_format_timestamp(time())
            );
            insert_record($table, $params);

            $tablei = 'interaction_learningobject_returned_collection_instructor';

            foreach ($instructors as $instructor) {
                $user = new User();
                $user->find_by_id($instructor);

                if (!is_teacher($user)) {
                    throw new ParameterException('Only teachers can be instructors.');
                }
                PluginInteractionLearningobject::add_access_to_user($collection,
                        $instructor);
                $paramsi = (object) array(
                            'collection' => $collectionid,
                            'user' => $instructor,
                );
                insert_record($tablei, $paramsi);
            }
        }
        else {
            $paramsu = (object) array(
                        'prev_return_date' => db_format_timestamp(time()),
            );
            update_record($table, $paramsu,
                    array('collectionid' => $collectionid));
        }
    }

    public static function get_returned_collection($collectionid) {
        $records = get_record_sql('SELECT *
                                    FROM {interaction_learningobject_returned_collection} rc
                                    WHERE rc.collectionid = ?',
                array($collectionid));
        return $records;
    }

    public static function get_returned_collection_instructors($collectionid) {
        $records = get_records_sql_array('SELECT ci.user, u.username, u.firstname, u.lastname, u.preferredname, u.profileicon, u.email, u.urlid
                                         FROM {interaction_learningobject_returned_collection_instructor} ci
                                         LEFT JOIN {usr} u ON ci.user = u.id
                                         WHERE ci.collection = ?',
                array($collectionid));
        return $records;
    }

    /**
     * Returns the previous return date of the collection and false if the
     * collection hasn't been returned yet.
     *
     * @param Collection $collection The collection
     * @param boolean $format Whether to format the date according to user's
     *      locale settings.
     * @return string|boolean The previous return date or false if not returned
     *      yet.
     */
    public static function get_previous_collection_return_date(Collection $collection,
                                                               $format = true) {
        $record = self::get_returned_collection($collection->get('id'));
        $date = false;

        if ($record !== false) {
            $date = $format ? format_date(strtotime($record->prev_return_date)) : $record->prev_return_date;
        }

        return $date;
    }

    /**
     * Returns the previous return date of the view and false if the view hasn't
     * been returned yet.
     *
     * @param View $view The view
     * @param boolean $format Whether to format the date according to user's
     *      locale settings.
     * @return string|boolean The previous return date or false if not returned
     *      yet.
     */
    public static function get_previous_view_return_date(View $view,
                                                         $format = true) {
        $record = self::get_returned_view($view->get('id'));
        $date = false;

        if ($record !== false) {
            $date = $format ? format_date(strtotime($record->prev_return_date)) : $record->prev_return_date;
        }

        return $date;
    }

    public static function get_my_returned_collections(User $user) {
        $records = get_records_sql_array('SELECT *,
                                         (
                                            SELECT view
                                            FROM {collection_view}
                                            WHERE collection = c.id
                                            ORDER BY displayorder ASC
                                            LIMIT 1
                                        ) AS first_view_id
                                         FROM {interaction_learningobject_returned_collection} rc
                                         LEFT JOIN {collection} c ON rc.collectionid = c.id
                                         WHERE c.owner = ? ORDER BY rc.prev_return_date DESC LIMIT 100',
                array($user->get('id')));
        return is_array($records) ? $records : array();
    }

    public static function get_my_returned_views(User $user) {
        $records = get_records_sql_array('SELECT * FROM {interaction_learningobject_returned_view} rv
                                         LEFT JOIN view v ON rv.viewid = v.id
                                         WHERE v.owner = ? ORDER BY rv.prev_return_date DESC LIMIT 100',
                array($user->get('id')));
        return is_array($records) ? $records : array();
    }

    public static function search_all_returns($instructor = null, $ownedby = null, $ownerquery = null, $query=null, $limit=null, $offset=0, $sortby = null, $types = null){
        $sort = ($sortby == "lastchanged") ? "prev_return_date DESC" : "title ASC";

        $fromviewparams = array();
        $fromcollectionparams = array();
        $viewparams = array();
        $collectionparams = array();
        $whereview = " v.locked = 0 ";
        $wherecollection = " u.deleted = 0 ";

        if ($instructor){
            $whereview .= " AND vi.user = ? ";
            $wherecollection .= " AND ci.user = ? ";
            array_push($viewparams, $instructor);
            array_push($collectionparams, $instructor);
        }
        if ($ownedby) {
            $whereview .= ' AND (v.' . View::owner_sql($ownedby).')';
            $wherecollection .= ' AND (c.' . View::owner_sql($ownedby).')';
        }
        $like = db_ilike();
        if ($ownerquery){
            $whereview .=  "
             AND (
                u.preferredname $like '%' || ? || '%'
                OR u.firstname $like '%' || ? || '%'
                    OR u.lastname $like '%' || ? || '%'
                    OR CONCAT(u.firstname, ' ', u.lastname) $like '%' || ? || '%'
            )";
            $wherecollection .=  "
             AND (
                u.preferredname $like '%' || ? || '%'
                OR u.firstname $like '%' || ? || '%'
                    OR u.lastname $like '%' || ? || '%'
                    OR CONCAT(u.firstname, ' ', u.lastname) $like '%' || ? || '%'
            )";
            array_push($viewparams, $ownerquery, $ownerquery, $ownerquery, $ownerquery);
            array_push($collectionparams, $ownerquery, $ownerquery, $ownerquery, $ownerquery);
        }

        $tagquery_view = "";
        $tagquery_collection = "";
        if ($query) {

            $collate = "COLLATE utf8_swedish_ci ";
            $whereview .= "
                AND (v.title $like '%' || ? || '%' $collate OR description $like '%' || ? || '%' $collate OR tag = ? $collate ) ";
            $wherecollection .= "
                AND (c.name $like '%' || ? || '%' $collate OR description $like '%' || ? || '%' $collate OR tag = ? $collate ) ";
            $tagquery_view =  " LEFT JOIN {view_tag} vt ON (vt.view = v.id AND vt.tag = ?) ";
            $tagquery_collection = " LEFT JOIN {collection_tag} ct ON (ct.collection = c.id AND ct.tag = ?) ";
            array_push($fromviewparams, $query);
            array_push($fromcollectionparams, $query);
            array_push($viewparams, $query, $query, $query);
            array_push($collectionparams, $query, $query, $query);
        }
        $noview = 0;
        $nocollection = 0;
        // type buttons
        if ($types){
            if (is_array($types) && !empty($types)) {
                if (in_array('collection', $types)) {
                    //only search collections and remove first part of union
                    if (count($types) == 1){
                        $noview = 1;
                    }
                    else {
                        $whereview .= ' AND v.type IN (';
                        $whereview .= join(',', array_map('db_quote', $types)) . ')';
                    }
                }
                else {
                    //if we dont have to search in collections remove second part of union
                    $nocollection = 1;
                    $whereview .= ' AND v.type IN (';
                    $whereview .= join(',', array_map('db_quote', $types)) . ')';
                }
            }
        }

        $viewselect = "SELECT v.id, v.title, v.description, v.owner, v.ctime, v.mtime, v.type, v.locked, v.urlid, v.template, rv.viewid, '0' as collid,
                                         rv.first_return_date, rv.prev_return_date,
                                         u.username, u.lastname, u.firstname, u.deleted, u.email, u.profileicon,
                                         'cfirstviewid' as first_view_id ";
        $viewfromwhere = " FROM {interaction_learningobject_returned_view} rv
                                         LEFT JOIN {view} v ON rv.viewid = v.id
                                         LEFT JOIN {usr} u ON v.owner = u.id
                                         LEFT JOIN {interaction_learningobject_returned_view_instructor} vi ON rv.viewid = vi.view
                                         ". $tagquery_view ."
                                         WHERE ". $whereview;

        $union = " UNION ALL ";
        $collectionselect = "SELECT c.id, c.name as title, c.description, c.owner, c.ctime, c.mtime, c.type, '0' as locked, '0' as urlid,
                                (
                                    SELECT v.template
                                    FROM {collection_view} cv
                                    LEFT JOIN {view} v ON v.id = cv.view
                                    WHERE collection = c.id
                                    ORDER BY displayorder ASC
                                    LIMIT 1
                                ) as template,
                                '0' as viewid, rc.collectionid as collid,
                                         rc.first_return_date, rc.prev_return_date,
                                         u.username, u.lastname, u.firstname, u.deleted, u.email, u.profileicon,
                                         (
                                            SELECT view
                                            FROM {collection_view}
                                            WHERE collection = c.id
                                            ORDER BY displayorder ASC
                                            LIMIT 1
                                        ) AS first_view_id";
        $collectionfromwhere = " FROM {interaction_learningobject_returned_collection} rc
                                         LEFT JOIN {collection} c ON rc.collectionid = c.id
                                         LEFT JOIN {usr} u ON c.owner = u.id
                                         LEFT JOIN {interaction_learningobject_returned_collection_instructor} ci ON rc.collectionid = ci.collection
                                         ". $tagquery_collection ."
                                         WHERE ". $wherecollection;

        $total = $totalcollection = $totalview = 0;
        $ids = $idscoll = $idsview = array();

        //count totals for student view or get id-s for access check
        if ($noview){
            $union = $viewselect =  $viewfromwhere = "";
            $fromviewparams = $viewparams = array();
        }
        else {
            if ($instructor){
                $idsview = get_records_sql_array('SELECT v.id '. $viewfromwhere, array_merge($fromviewparams, $viewparams));
                $idsview = is_array($idsview) ? $idsview : array();
            }
            else {
                $totalview = count_records_sql('SELECT COUNT(*) '. $viewfromwhere, array_merge($fromviewparams, $viewparams));
            }
        }

        if ($nocollection){
            $union = $collectionselect = $collectionfromwhere = "";
            $fromcollectionparams = $collectionparams = array();
        }
        else {
            if ($instructor){
                //we need first view_id for user_access_records
                $idscoll  = get_records_sql_array('SELECT c.id AS collid, (
                                            SELECT view
                                            FROM {collection_view}
                                            WHERE collection = c.id
                                            ORDER BY displayorder ASC
                                            LIMIT 1
                                        ) AS id '. $collectionfromwhere, array_merge($fromcollectionparams, $collectionparams));
                $idscoll = is_array($idscoll) ? $idscoll : array();
            }
            else {
                $totalcollection = count_records_sql('SELECT COUNT(*) '. $collectionfromwhere, array_merge($fromcollectionparams, $collectionparams));
            }
        }

        if ($instructor){
            $ids = array_merge($idsview, $idscoll);
        }
        else {
            $total = $totalcollection + $totalview;
        }

        // now check that if the user is teacher has actually access to the view / collection
        if ($instructor){
            global $USER;
            $collids = $viewids = array();

            foreach($ids as $ke => $id){
                $access = View::user_access_records($id->id, $USER->id);

                if (!$access){
                    //unset id-s with no access
                    unset($ids[$ke]);
                }
                else {
                    //put rest to separate arrays
                    if (isset($id->collid)){
                        $collids[] = $id->collid;
                    }
                    else {
                        $viewids[] = $id->id;
                    }
                }
            }
            //count total after unsets
            $total = count($ids);
            if (count($collids)){
                $cidstr = implode(',', array_map('db_quote', $collids));
                $collectionfromwhere .= ($collectionfromwhere != '') ?  ' AND c.id IN ('.$cidstr .') ' : '';
            }
            if (count($viewids)){
                $vidstr = implode(',', array_map('db_quote', $viewids));
                $viewfromwhere .= ($viewfromwhere != '') ? ' AND v.id IN ('.$vidstr .') ' : '';
            }
        }

        $params = array_merge($fromviewparams, $viewparams, $fromcollectionparams, $collectionparams);

        /*union all faster than union but does not remove duplicates*/
        $records = get_records_sql_array($viewselect .$viewfromwhere .
                                         $union .
                                         $collectionselect . $collectionfromwhere .
                                          "ORDER BY " .$sort,
                                         $params, $offset, $limit);

        $rec = is_array($records) ? $records : array();

        return (object) array(
            'data'  => $rec,
            'count' => $total,
        );

    }

    public static function get_returned_tome_views(User $user) {
        $records = get_records_sql_array('SELECT v.*, rv.*, u.username, u.lastname, u.firstname, u.preferredname, u.email, u.profileicon
                                         FROM {interaction_learningobject_returned_view} rv
                                         LEFT JOIN {interaction_learningobject_returned_view_instructor} vi ON rv.viewid = vi.view
                                         LEFT JOIN {view} v ON rv.viewid = v.id
                                         LEFT JOIN {usr} u ON v.owner = u.id
                                         WHERE vi.user = ? ORDER BY rv.prev_return_date DESC LIMIT 100',
                array($user->get('id')));
        return is_array($records) ? $records : array();
    }

    public static function get_returned_tome_collections(User $user) {
        $records = get_records_sql_array('SELECT rc.*, c.*, u.username, u.lastname, u.firstname, u.preferredname, u.email, u.profileicon,
                                         (
                                            SELECT view
                                            FROM {collection_view}
                                            WHERE collection = c.id
                                            ORDER BY displayorder ASC
                                            LIMIT 1
                                        ) AS first_view_id
                                         FROM {interaction_learningobject_returned_collection} rc
                                         LEFT JOIN {interaction_learningobject_returned_collection_instructor} ci ON rc.collectionid = ci.collection
                                         LEFT JOIN {collection} c ON rc.collectionid = c.id
                                         LEFT JOIN {usr} u ON c.owner = u.id
                                         WHERE ci.user = ?
                                         HAVING first_view_id IS NOT NULL
                                         ORDER BY rc.prev_return_date DESC LIMIT 100',
                array($user->get('id')));
        return is_array($records) ? $records : array();
    }

    // <EKAMPUS - template-parameter
    public static function add_access_to_user_view(View $view, $userid, $template = 1) {
    // EKAMPUS>
        if ($view) {
            $access = $view->get_access();
            $viewconfig = array(
                'startdate' => $view->get('startdate'),
                'stopdate' => $view->get('stopdate'),
                'template' => $template, // EKAMPUS
                'retainview' => $view->get('retainview'),
                'allowcomments' => $view->get('allowcomments'),
                'approvecomments' => (int) ($view->get('allowcomments') && $view->get('approvecomments')),
                'accesslist' => $access
            );
        }

        if ($viewconfig !== false) {
            // Remove old access settings for the selected user (if found).
            foreach ($viewconfig['accesslist'] as $key => $item) {
                if ($item['type'] === 'user' && (int) $item['id'] === (int) $userid) {
                    unset($viewconfig['accesslist'][$key]);
                }
            }

            // Add user access to accesslist.
            $viewconfig['accesslist'][] = array(
                'type' => 'user',
                'id' => $userid,
                'startdate' => '',
                'stopdate' => ''
            );
            View::update_view_access($viewconfig, array($view->get('id')));
        }
    }

    public static function sort_by_date($a, $b) {
        return strtotime($b->prev_return_date) - strtotime($a->prev_return_date);
    }
}

class ActivityTypeInteractionLearningobjectNewLearningobject extends ActivityTypePlugin {
    protected $name;

    public function __construct($data, $cron = false) {
        parent::__construct($data, $cron);
        $this->users = activity_get_users($this->get_id(), $this->users);
    }

    public function get_pluginname() {
        return 'learningobject';
    }

    public function get_plugintype() {
        return 'interaction';
    }

    public function get_required_parameters() {
        return array('users', 'name');
    }

    public function get_message($user) {
        return get_string_from_language($user->lang, 'newlearningobjectassignedmessage',
                'interaction.learningobject', $this->name);
    }

    public function get_subject($user) {
        return get_string_from_language($user->lang, 'newlearningobjectassignedsubject',
                'interaction.learningobject');
    }

    public function get_string_for_user($user, $string) {
        parent::get_string_for_user($user, $string);
    }
}
