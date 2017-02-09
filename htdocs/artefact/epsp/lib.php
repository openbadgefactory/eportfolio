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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

class PluginArtefactEpsp extends PluginArtefact {

    public static function get_artefact_types() {
        return array('epsp', 'epspfield');
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'ePSP';
    }

    public static function menu_items() {
        $menu = array();

        if (in_admin_section()) {
            return $menu;
        }

        if (is_teacher()) {
            $menu['ehops'] = array(
                'path' => 'epsp',
                'weight' => 15,
                'title' => get_string('ehops', 'artefact.epsp'),
                'url' => 'artefact/epsp/'
            );

            $menu['ehops/shared'] = array(
                'path' => 'epsp/shared',
                'weight' => 10,
                'title' => get_string('sharedbystudents', 'artefact.epsp'),
                'url' => 'artefact/epsp/shared.php'
            );
            $menu['ohjaus/progression'] = array(
                'path' => 'ohjaus/progression',
                'url' => 'artefact/epsp/sharedprogress.php',
                'title' => get_string('progression'),
                'weight' => 70
            );
        }
        else {
            $menu['ehops'] = array(
                'path' => 'epsp',
                'weight' => 15,
                'title' => get_string('ehops', 'artefact.epsp'),
                'url' => 'artefact/epsp/own.php'
            );

            $menu['ehops/own'] = array(
                'path' => 'epsp/own',
                'weight' => 10,
                'title' => get_string('myplans', 'artefact.epsp'),
                'url' => 'artefact/epsp/own.php'
            );
            $menu['ohjaus/progression'] = array(
                'path' => 'ohjaus/progression',
                'url' => 'artefact/epsp/ownprogress.php',
                'title' => get_string('progression'),
                'weight' => 70
            );
        }

        $menu['ehops/templates'] = array(
            'path' => 'epsp/templates',
            'weight' => 20,
            'title' => get_string('templates', 'artefact.epsp'),
            'url' => 'artefact/epsp/'
        );

        return $menu;
    }

    public static function postinst($fromversion) {
        if ($fromversion === 0) {
            // Add cascading rules to our tables.
            execute_sql("
                ALTER TABLE {artefact_epsp_field}
             ADD CONSTRAINT FOREIGN KEY artefactfk (artefact)
                 REFERENCES {artefact} (id) ON DELETE CASCADE"
            );

            execute_sql("
                ALTER TABLE {artefact_epsp_field}
             ADD CONSTRAINT FOREIGN KEY usrfk (marked_completed_by_user)
                 REFERENCES {usr} (id) ON DELETE SET NULL"
            );

            // New view type.
            $obj = (object) array('type' => 'epsp');

            ensure_record_exists('view_type', $obj, $obj);
        }
    }

}

class ArtefactTypeEpsp extends ArtefactType {

    public static function get_icon($options = null) {

    }

    public static function get_links($id) {

    }

    public static function is_singular() {
        return false;
    }

    public static function is_allowed_to_toggle_field_completion(EpspFieldTitle $field,
                                                                 $viewid) {
        // User is allowed to change field completion status if the user is
        // a teacher and the user has access to a view that includes either
        // the field or the whole ePSP that contains the field.
        if (!is_teacher()) {
            throw new AccessDeniedException();
        }

        $view = new View($viewid);
        $fieldid = $field->get_artefactid();

        if (!can_view_view($view)) {
            throw new AccessDeniedException();
        }

        $inblock = false;
        $artefacts = array();
        $blocks = get_records_sql_array("
            SELECT *
              FROM {block_instance}
             WHERE view = ? AND blocktype IN ('entireepsp', 'singleepspfield')",
                array($viewid));

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $configdata = unserialize($block->configdata);

                // Single field.
                if ($block->blocktype === 'singleepspfield') {
                    if ($configdata['artefactid'] == $fieldid) {
                        $inblock = true;
                        break;
                    }
                }

                // The whole ePSP, store the identifier for later use.
                else {
                    $artefacts[] = $configdata['artefactid'];
                }
            }
        }

        // Did not found single field from blocks, let's try the whole ePSP's.
        if (!$inblock && count($artefacts) > 0) {
            $artefactids = implode(',', array_map('intval', $artefacts));
            return count_records_select('artefact',
                    "id = ? AND parent IN ($artefactids)", array($fieldid));
        }

        return $inblock;
    }

    public static function toggle_field_completion_status($fieldid, $viewid) {
        global $USER;

        $artefact = self::get_field($fieldid);
        $field = $artefact->get('field');

        if (!$field->is_completable()) {
            throw new Exception('This field cannot be completed.');
        }

        if (!self::is_allowed_to_toggle_field_completion($field, $viewid)) {
            throw new AccessDeniedException();
        }

        if ($field->is_completed() && $field->get_marked_completed_by() != $USER->get('id')) {
            throw new AccessDeniedException();
        }

        $field->toggle_completion_status();

        return $field;
    }

    public function save_fields(array $fields) {
        global $USER;

        db_begin();

        // Delete removed fields from database.
        $existing_fields = array();

        foreach ($fields as $field) {
            if (!empty($field->data->fieldid)) {
                $existing_fields[] = (int) $field->data->fieldid;
            }
        }

        if (count($existing_fields) > 0) {
            $idstr = join(',', array_map('db_quote', $existing_fields));
            $ids = get_column_sql("
                SELECT id
                  FROM {artefact}
                 WHERE artefacttype = ? AND parent = ? AND locked = 0 AND id NOT IN ($idstr)",
                    array('epspfield', $this->get('id')));

            if (count($ids) > 0) {
                ArtefactType::bulk_delete($ids);
            }
        }

        // Add/update fields.
        foreach ($fields as $field) {
            $type = $field->data->type;
            $artefactid = $field->data->fieldid;
            $classname = 'EpspField' . $type;
            $instance = new $classname();
            $instance->initialize_from_formvalues($field->data);

            // New field -> insert.
            if (empty($artefactid)) {
                $artefact = new ArtefactTypeEpspField();
                $artefact->set('owner', $USER->get('id'));
                $artefact->set('parent', $this->id);
                $artefact->set('allowcomments', 1);
            }

            // Existing field -> update.
            else {
                $artefact = new ArtefactTypeEpspField($artefactid);
            }

            $artefact->set('field', $instance);

            // User is not allowed to update locked field title.
            if (!$artefact->get('locked')) {
                $artefact->set('title', $field->data->title);
            }

            $artefact->commit();

//            $this->field->set_artefactid($this->get('id'));
            $instance->set_artefactid($artefact->get('id'));
            $obj = $instance->to_database_object();

            if (empty($artefactid)) {
                insert_record('artefact_epsp_field', $obj);
            }
            else {
                update_record('artefact_epsp_field', $obj,
                        array('artefact' => $artefactid));
            }
        }

        $this->set('mtime', time());
        $this->commit();

        db_commit();
    }

    /**
     *
     * @param type $fieldid
     * @return \ArtefactTypeEpspField|boolean
     */
    public static function get_field($fieldid) {
        // PENDING: Do we have to check access rights here (the viewer can see
        // the ePSP that includes this field)? Or can we assume that if the user
        // has shared a view with this field included, the view access rights
        // are used?
        $record = get_record_sql("
            SELECT a.*, f.value, f.completable, f.completed,
                   f.marked_completed_by_user, `order`, type, artefact, " .
                db_format_tsfield('f.marked_completed_at', 'marked_completed_at') .
                ", u.firstname, u.lastname
              FROM {artefact} a
         LEFT JOIN {artefact_epsp_field} f ON a.id = f.artefact
         LEFT JOIN {usr} u ON f.marked_completed_by_user = u.id
             WHERE a.id = ?", array($fieldid));

        if (!$record) {
            return false;
        }

        $classname = 'EpspField' . $record->type;
        $instance = new $classname();
        $instance->from_database_object($record);

        $artefact = new ArtefactTypeEpspField($fieldid, $record);
        $artefact->set('field', $instance);

        return $artefact;
    }

    /**
     *
     * @param type $get_as_stdclass
     * @return \EpspFieldTitle[]
     */
    public function get_fields($get_as_stdclass = false) {
        $rows = get_records_sql_array("
            SELECT a.*, f.*, " . db_format_tsfield('f.marked_completed_at',
                        'marked_completed_at') . ", u.firstname, u.lastname
              FROM {artefact} a
         LEFT JOIN {artefact_epsp_field} f ON a.id = f.artefact
         LEFT JOIN {usr} u ON f.marked_completed_by_user = u.id
             WHERE a.parent = ?
          ORDER BY f.order ASC", array($this->get('id')));

        if (!is_array($rows)) {
            return array();
        }

        $fields = array();

        foreach ($rows as $row) {
            $epspfield = new ArtefactTypeEpspField($row->id, $row);

            if (!empty($row->type)) {
                $classname = 'EpspField' . $row->type;
                $instance = new $classname();
                $instance->from_database_object($row);
                $epspfield->set_field($instance);
            }

            $fields[] = $get_as_stdclass ? $epspfield->to_stdclass() : $epspfield;
        }

        return $fields;
    }

    public static function get_templates($query = '', $offset = 0, $limit = 10,
                                         $sortby = 'modified',
                                         $publicity = null, $institution = null,
                                         $authorname = '') {
        global $USER;

        $userid = $USER->get('id');
        $query = trim($query);
        $orderby = $sortby === 'modified' ? 'a.mtime DESC' : 'TRIM(a.title) ASC';
        $having = "";
        $queryparams = array();
        $fromparams = array();
        $ret = array('items' => array(), 'count' => 0);
        $from = "
            FROM {artefact} a
       LEFT JOIN {usr} u ON a.owner = u.id";

        $where = "
            WHERE a.artefacttype = ?";
        $queryparams[] = 'epsp';

        // Get own stuff only
        if ($publicity === 'private' || $publicity === 'published' ||
                $publicity === 'own') {
            $where .= "
                AND a.owner = ?";
            $queryparams[] = $userid;
        }

        // Get own private stuff
        if ($publicity === 'private') {
            $having .= "
                HAVING sharecount = 0";
        }
        // Get own shared templates.
        else if ($publicity === 'published') {
            $having .= "
                HAVING sharecount > 0";
        }
        // Others' templates shared to current user.
        else if ($publicity === 'others') {
            $subquery = self::get_shared_subquery();
            $where .= "
                AND (a.owner != ? AND a.id IN
                (
                    $subquery
                ))";
            array_push($queryparams, $userid, $userid, $userid, $userid);
        }
        // Get own templates + others' templates shared to current user.
        else {
            $subquery = self::get_shared_subquery();
            $where .= "
                AND
                (
                    a.owner = ? OR (a.id IN
                    (
                        $subquery
                    ))
                )";
            array_push($queryparams, $userid, $userid, $userid, $userid);
        }

        $like = db_ilike();
        $collate = "COLLATE utf8_swedish_ci";

        if (!empty($query)) {
            $from .= "
                LEFT JOIN {artefact_tag} at ON (at.artefact = a.id AND at.tag = ?)";
            $where .= "
                AND (a.title $like '%' || ? || '%' $collate
                    OR a.description $like '%' || ? || '%' $collate
                    OR at.tag = ? $collate)";

            array_push($fromparams, $query);
            array_push($queryparams, $query, $query, $query);
        }

        // Filter by author's institution.
        if (!empty($institution)) {
            $where .= "
                AND (a.owner IN
                (
                    SELECT usr
                      FROM {usr_institution}
                     WHERE institution = ?
                ))";
            $queryparams[] = $institution;
        }

        // Filter by author's name.
        if (!empty($authorname)) {
            $where .= "
                AND
                (
                    u.preferredname $like '%' || ? || '%' OR
                    u.firstname $like '%' || ? || '%' OR
                    u.lastname $like '%' || ? || '%' OR
                    CONCAT(u.firstname, ' ', u.lastname) $like '%' || ? || '%'
                )";

            array_push($queryparams, $authorname, $authorname, $authorname, $authorname);
        }

        $params = array_merge($fromparams, $queryparams);
        $sql = "
            SELECT a.id, a.owner, a.title, a.description, u.firstname,
                u.lastname, u.deleted, a.mtime, (
                    (SELECT COUNT(*) FROM {artefact_epsp_user} WHERE artefact = a.id) +
                    (SELECT COUNT(*) FROM {artefact_epsp_group} WHERE artefact = a.id) +
                    (SELECT COUNT(*) FROM {artefact_epsp_institution} WHERE artefact = a.id)
                ) AS sharecount
            $from
            $where
            $having";

        $result = get_records_sql_array("$sql ORDER BY $orderby", $params,
                $offset, $limit);
        $count = count_records_sql("SELECT COUNT(*) FROM ($sql) AS tbl", $params);

        if (is_array($result)) {
            $wwwroot = get_config('wwwroot');
            $studyids = array();
            $is_teacher = is_teacher();

            foreach ($result as &$row) {
                $studyids[] = $row->id;
                $isown = $row->owner == $USER->id;

                $row->author = full_name((object) array(
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'deleted' => $row->deleted
                ));

                $row->publicity = $row->sharecount == 0 ? 'private' : 'published';
                $row->menuitems = array();

                if ($isown) {
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'artefact/epsp/edit.php?id=' . $row->id,
                        'title' => get_string('edit', 'artefact.epsp')
                    );
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'artefact/epsp/fields.php?id=' . $row->id,
                        'title' => get_string('edittemplatefields', 'artefact.epsp')
                    );
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'artefact/epsp/access.php?id=' . $row->id,
                        'title' => get_string('sharetemplate', 'artefact.epsp')
                    );
                }

                $translationkey = $is_teacher ? 'copytemplate' : 'copytemplatetoplans';
                $row->menuitems[] = array(
                    'title' => get_string($translationkey, 'artefact.epsp'),
                    'classes' => 'copy-template'
                );

                if ($isown) {
                    $row->menuitems[] = array(
                        'title' => get_string('delete'),
                        'classes' => 'delete-template'
                    );
                }
            }

            $artefact_tags = array();
            $tags = ArtefactType::tags_from_id_list($studyids);

            if (is_array($tags)) {
                foreach ($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($result as &$study) {
                $tags = isset($artefact_tags[$study->id]) ? $artefact_tags[$study->id]
                            : array();
                $study->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }

            $ret['items'] = $result;
            $ret['count'] = $count;
        }

        return $ret;
    }

    public static function get_user_plan_views($userid) {
        $views = get_records_sql_assoc("
            SELECT v.id, b.id AS bid, b.blocktype, b.configdata
              FROM {view} v
         LEFT JOIN {block_instance} b ON v.id = b.view
             WHERE v.owner = ? AND v.type = ?
          ORDER BY v.id", array($userid, 'epsp'));

        return (is_array($views) ? $views : array());
    }

    public static function get_tags() {
        global $USER;

        return get_column_sql("
            SELECT DISTINCT(tag)
              FROM {artefact_tag}
             WHERE artefact IN (
                SELECT id
                  FROM {artefact}
                 WHERE owner = ? AND artefacttype = ?
             )
          ORDER BY tag COLLATE utf8_swedish_ci ASC", array($USER->id, "epsp"));
    }

    public function share(array $users, array $groups, array $institutions) {
        db_begin();

        $artefactid = $this->get('id');

        delete_records('artefact_epsp_user', 'artefact', $artefactid);
        delete_records('artefact_epsp_group', 'artefact', $artefactid);
        delete_records('artefact_epsp_institution', 'artefact', $artefactid);

        // Share to users.
        foreach ($users as $userid) {
            insert_record('artefact_epsp_user',
                    (object) array(
                        'artefact' => $artefactid,
                        'user' => $userid
            ));
        }

        // Share to groups.
        foreach ($groups as $groupid) {
            insert_record('artefact_epsp_group',
                    (object) array(
                        'artefact' => $artefactid,
                        'group' => $groupid
            ));
        }

        // Share to institutions.
        foreach ($institutions as $institution) {
            insert_record('artefact_epsp_institution',
                    (object) array(
                        'artefact' => $artefactid,
                        'institution' => $institution
            ));
        }

        db_commit();
    }

    public function get_user_access() {
        $records = get_records_sql_array("
                SELECT a.user, u.firstname, u.lastname, u.deleted
                  FROM {artefact_epsp_user} a
             LEFT JOIN {usr} u ON a.user = u.id
                 WHERE a.artefact = ?
              ORDER BY u.lastname ASC, u.firstname ASC",
                array($this->get('id')));

        if (!is_array($records)) {
            return array();
        }

        foreach ($records as &$row) {
            $row->name = full_name((object) array(
                        'firstname' => $row->firstname,
                        'lastname' => $row->lastname,
                        'deleted' => $row->deleted
            ));
        }

        return $records;
    }

    public function get_group_access() {
        return get_records_sql_array("
                SELECT a.group, g.name
                  FROM {artefact_epsp_group} a
             LEFT JOIN {group} g ON a.group = g.id
                 WHERE a.artefact = ?
              ORDER BY g.name ASC", array($this->get('id')));
    }

    public function get_institution_access() {
        return get_records_sql_array("
            SELECT a.institution, i.displayname
              FROM {artefact_epsp_institution} a
         LEFT JOIN {institution} i ON a.institution = i.name
             WHERE a.artefact = ?
          ORDER BY i.displayname ASC", array($this->get('id')));
    }

    public static function copy_template($id) {
        global $USER;

        $template = new ArtefactTypeEpsp($id);

        if (!$template->is_shared_to($USER)) {
            throw new AccessDeniedException('Cannot copy template which isn\'t shared to you.');
        }

        db_begin();
        $copyid = $template->copy_for_new_owner($USER->get('id'), null, null);
        db_commit();

        return $copyid;
    }

    public function copy_extra($new) {
        global $USER;

        parent::copy_extra($new);

        $new->set('mtime', time());

        // ArtefactType::copy_for_new_owner calls copy_extra BEFORE the copy
        // is saved, so we don't know the copy's id yet. So let's commit the
        // changes now to get the id.
        $new->commit();
        $id = $new->get('id');
        $fields = $this->get_children_instances();

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $fieldcopyid = $field->copy_for_new_owner($USER->get('id'), null, null);
                $fieldcopy = new ArtefactTypeEpspField($fieldcopyid);
                $fieldcopy->set('parent', $id);
                $fieldcopy->commit();
            }
        }
    }

    public function copy_data() {
        global $USER;

        $data = parent::copy_data();

        if ($data->owner == $USER->get('id')) {
            $data->title = get_string('copyofepsp', 'artefact.epsp', $data->title);
        }

        return $data;
    }

    public function is_shared_to(User $user) {
        // User owns the template.
        if ($this->owner == $user->get('id')) {
            return true;
        }

        return record_exists_sql(self::get_shared_subquery(),
                array($user->get('id'), $user->get('id'), $user->get('id')));
    }

    private static function get_shared_subquery() {
        $sql = "
            SELECT id
              FROM {artefact}
             WHERE
             (
                -- User access
                id IN
                (
                    SELECT artefact
                      FROM {artefact_epsp_user}
                     WHERE user = ?
                )

                -- Group access
                OR id IN
                (
                    SELECT artefact
                      FROM {artefact_epsp_group}
                     WHERE `group` IN
                     (
                        SELECT `group`
                          FROM {group_member}
                         WHERE member = ?
                     )
                )

                -- Institution access
                OR id IN
                (
                    SELECT artefact
                      FROM {artefact_epsp_institution}
                     WHERE institution IN
                     (
                        SELECT institution
                          FROM {usr_institution}
                         WHERE usr = ?
                     )
                )
             )";

        return $sql;
    }

    public static function delete_epsp($id) {
        global $USER;

        $epsp = new ArtefactTypeEpsp($id);

        if ($epsp->get('owner') != $USER->get('id')) {
            throw new AccessDeniedException();
        }

        $epsp->delete();

        return true;
    }

    public static function get_shared_plans($query, $offset, $limit, $sortby,
                                            $publicity, $ownerquery, $owners) {
        safe_require('interaction', 'pages');

        // Sorting.
        if ($sortby === 'author'){
            $sorting = 'ownername';
        }
        else {
            $sorting = $sortby === 'modified' ? 'lastchanged' : 'title';
        }

        $sortdir = $sorting === 'lastchanged' ? 'desc' : 'asc';

        // Access settings.
        $access = array();
        $getpublic = $publicity === 'all' || $publicity === 'public';
        $getpublished = $publicity === 'all' || $publicity === 'published';

        if ($getpublic) {
            $access[] = 'public';
        }
        if ($getpublished) {
            $access = array_merge($access,
                    array('user', 'friend', 'group', 'institution',
                'loggedin', 'token'));
        }

        // Author settings.
        $ownedby = array();

        if ($owners['student']) {
            $ownedby['owner'] = $owners['student'];
        }
        else if ($owners['groups']) {
            $ownedby['group'] = $owners['groups'];
            $ownedby['multiple'] = true;
        }
        else if ($owners['institution']) {
            $ownedby['institution'] = $owners['institution'];
            $ownedby['multiple'] = true;
        }
        else {
            // PENDING: Copied from interaction/pages/sharedsearch.json.php, is
            // this still relevant?
            $owned['multiple'] = true;
        }

        $ownedby = count($ownedby) === 0 ? null : (object) $ownedby;



        $results = View::shared_to_user($query, null, $limit, $offset, $sorting,
                        $sortdir, $access, array('epsp'), $ownedby, $ownerquery);

        
        PluginInteractionPages::get_sharedview_accessrecord($results->data);

        return $results;
    }

    public static function get_plans() {
        global $USER;

        $userid = $USER->get('id');
        $sql = "
            SELECT a.id, a.owner, a.title, a.description, u.firstname,
                u.lastname, u.deleted, a.mtime
              FROM {artefact} a
         LEFT JOIN {usr} u ON a.owner = u.id
             WHERE a.artefacttype = ? AND a.owner = ?
          ORDER BY a.mtime DESC";

        $result = get_records_sql_array($sql, array('epsp', $userid));

        if (is_array($result)) {
            $wwwroot = get_config('wwwroot');
            $views = self::get_user_plan_views($USER->get('id'));
            $acls = View::get_accesslists($USER->get('id'), null, null,
                            array('epsp'));
            $studyids = array();

            foreach ($result as &$row) {
                $studyids[] = $row->id;

                $row->author = full_name((object) array(
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'deleted' => $row->deleted
                ));

                $row->publicity = find_artefact_publicity($row->id, $views,
                        $acls);
                $row->view = find_artefact_view($row->id, $views);
                $row->menuitems = array();

                $row->menuitems[] = array(
                    'url' => $wwwroot . 'artefact/epsp/edit.php?id=' . $row->id,
                    'title' => get_string('edit', 'artefact.epsp')
                );

                $row->menuitems[] = array(
                    'url' => $wwwroot . 'artefact/epsp/fields.php?id=' . $row->id,
                    'title' => get_string('edittemplatefields', 'artefact.epsp')
                );

                if ($row->view) {
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'view/access.php?id=' . $row->view .
                        '&backto=artefact/epsp/own.php',
                        'classes' => 'editaccess',
                        'title' => get_string('editaccess', 'view')
                    );
                }
                else {
                    $row->menuitems[] = array(
                        'title' => get_string('editaccess', 'view'),
                        'url' => '#',
                        'classes' => 'create-view'
                    );
                }

                $row->menuitems[] = array(
                    'title' => get_string('delete'),
                    'classes' => 'delete-plan'
                );
            }

            $artefact_tags = array();
            $tags = ArtefactType::tags_from_id_list($studyids);

            if (is_array($tags)) {
                foreach ($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($result as &$study) {
                $tags = isset($artefact_tags[$study->id]) ? $artefact_tags[$study->id]
                            : array();
                $study->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }
        }

        return (is_array($result) ? $result : array());
    }
    public static function searchLatest($array){
        $currentMax = NULL;
        $currentOrder = NULL;
        $sums = array();
        foreach($array as $arr){
            $value = $arr->sums->lastmodifiedpart;

                if (($value >= $currentMax)){
                    //if we have equal values
                    if ($value == $currentMax){
                        // if we have empty strings, so no times, but we have goals
                        if (!$value && $arr->sums->order){
                            if (!$currentOrder){ //we are at first goal choose it
                                    $currentMax = $value;
                                    $currentOrder = $arr->sums->order;
                                    $sums = $arr->sums;
                                    $sums->id = $arr->id;
                                    $sums->title = $arr->title;
                            }
                            elseif ($arr->sums->order < $currentOrder){ //otherwise chose the one with smaller order
                                    $currentMax = $value;
                                    $currentOrder = $arr->sums->order;
                                    $sums = $arr->sums;
                                    $sums->id = $arr->id;
                                    $sums->title = $arr->title;
                            }
                        }
                        //if we have the same time choose the one with the bigger order
                        elseif ($arr->sums->order > $currentOrder){
                            $currentMax = $value;
                            $currentOrder = $arr->sums->order;
                            $sums = $arr->sums;
                            $sums->id = $arr->id;
                            $sums->title = $arr->title;
                        }
                    }
                    else {
                        $currentMax = $value;
                        $currentOrder = $arr->sums->order;
                        $sums = $arr->sums;
                        $sums->id = $arr->id;
                        $sums->title = $arr->title;
                    }
                }
        }
        return $sums;
    }
    public static function get_shared_progression($query, $offset, $limit, $sortby, $publicity, $ownerquery, $owners){

        $results = self::get_shared_plans($query, $offset, $limit, $sortby, $publicity, $ownerquery, $owners);

        $plans = $results->data;

        $plansbyowner = array();
        foreach($plans as $key => &$plan){
            $plan['epspid'] = self::get_planid_from_view($plan['viewid']);
            $plan['fields'] = self::get_plan_fields_bytype($plan['epspid'], 'subtitle');
            // no subtitles in the plan dont print it
            if (!count($plan['fields'])){
                unset($plans[$key]);
            }
            $plan['latest'] = self::searchLatest($plan['fields']);
            //if there is no goals in the plan dont print it
            if (!count($plan['latest'])){
                unset($plans[$key]);
            }
        }
        $plans = array_values($plans);
        reset($plans);
        $author = NULL;

        if ($sortby == "author"){
            foreach ($plans as $authorplan){
                $author = $authorplan['owner'];
                $plansbyowner[$author][] = $authorplan;
            }

            foreach ($plansbyowner as $key => $byowner){
                usort($plansbyowner[$key],
                      function ($plan1, $plan2){
                        return $plan1['mtime'] < $plan2['mtime'];
                });
            }
            //if sorting by author group plans by author
            $plans = $plansbyowner;
        }

        $countres = count($plans);

        return (object) array(
            'ids' => $results->ids,
            'data'  => $plans,
            'count' => $countres,
        );
    }

    public static function get_planid_from_view($viewid){
        $configdata = array();

        $epsp = get_record_sql("
            SELECT v.id, b.id AS bid, b.blocktype, b.configdata
              FROM {view} v
         LEFT JOIN {block_instance} b ON v.id = b.view
             WHERE v.id = ? AND v.type = ?
          ORDER BY v.id", array($viewid, 'epsp'));

        if ($epsp){
            $configdata = unserialize($epsp->configdata);
        }

        return (isset($configdata['artefactid']) && $configdata['artefactid']) ? $configdata['artefactid'] : '';
    }

    public static function get_own_progression() {
        global $USER;

        $userid = $USER->get('id');

        $plans = self::get_plans();
        if (is_array($plans)){

            foreach($plans as $num => &$plan){
                $plan->fields = self::get_plan_fields_bytype($plan->id, 'subtitle');
                // unset plans which are templates or not 'shared' (no view found) this will show private views though
                // ? should we remove private plans $plan->publicity == 'private' ?
                if (!$plan->view){
                    unset($plans[$num]);
                }
            }
        }
        return $plans;
    }
    public static function get_plan_fields_bytype($planid, $fieldtype){

        $result = get_records_sql_array("
            SELECT a.*, f.value, f.completable, f.completed,
                   f.marked_completed_by_user, `order`, type, artefact, " .
                db_format_tsfield('f.marked_completed_at', 'marked_completed_at') .
                ", u.firstname, u.lastname
              FROM {artefact} a
            LEFT JOIN {artefact_epsp_field} f ON a.id = f.artefact
            LEFT JOIN {usr} u ON f.marked_completed_by_user = u.id
             WHERE a.parent = ? AND f.type = ?
             ORDER BY f.order", array($planid, $fieldtype));

        if ($result){
            $length = count($result);

            for($i = 0; $i < $length; ++$i) {
                $nextsubtitleorder = isset($result[$i+1]) ? $result[$i+1]->order : 999;
                $result[$i]->sums = self::count_goals($planid, $result[$i]->order, $nextsubtitleorder);
            }
        }
        return (is_array($result) ? $result : array());
    }
    public static function count_goals($planid, $thissubtitleorder, $nextsubtitleorder){
         $results = get_record_sql(
                "SELECT COUNT(f.artefact) as totalgoals, SUM(f.completed) as bystudent, COUNT(f.marked_completed_by_user) as byteacher, MAX(f.marked_completed_at) as lastmodifiedpart, {order}
                 FROM {artefact_epsp_field} f
                 LEFT JOIN {artefact} a ON a.id = f.artefact
                 WHERE f.type = ? AND a.parent = ? AND f.order BETWEEN ? AND ?
                ", array('goal', $planid, $thissubtitleorder, $nextsubtitleorder));

        if (is_object($results)){
            $results->bystudent = ($results->bystudent) ? $results->bystudent : '0';
            //no division by zero pls
            if ($results->totalgoals){
                $results->bystudentprog = str_replace(",", ".", round($results->bystudent * 100 / $results->totalgoals, 1));
                $results->byteacherprog = str_replace(",", ".", round($results->byteacher * 100 / $results->totalgoals, 1));
            }
            else {
                $results->bystudentprog = 0;
                $results->byteacherprog = 0;
            }
            return $results;
        }

        return array();
    }

    public static function create_view_from_template($id) {
        global $USER;

        $epsp = new ArtefactTypeEpsp($id);
        $owner = $epsp->get('owner');

        if ($USER->get('id') != $owner) {
            throw new AccessDeniedException('Only own templates can be published.');
        }

        $config = array();
        $config['copytype'] = 'nocopy';
        $config['artefactid'] = $id;

        $configs = array(
            'title' => $epsp->get('title'),
            'description' => $epsp->get('description'),
            'type' => 'epsp',
            'layout' => '1',
            'approvecomments' => '1',
            'tags' => array(),
            'numrows' => 1,
            'owner' => $owner,
            'ownerformat' => 6,
            'rows' => array(
                1 => array(
                    'columns' => array(
                        1 => array(
                            1 => array(
                                'type' => 'entireepsp',
                                'title' => '',
                                'config' => $config,
                            )
                        )
                    )
                )
            )
        );

        $view = View::import_from_config($configs, $owner);

        return $view;
    }
}

class ArtefactTypeEpspField extends ArtefactType {

    /**
     * @var EpspFieldTitle
     */
    protected $field = null;

    public static function get_icon($options = null) {

    }

    public static function get_links($id) {

    }

    public static function is_singular() {
        return false;
    }

    public function set_field(EpspFieldTitle $field) {
        $this->field = $field;
    }

    public function to_stdclass() {
        $obj = parent::to_stdclass();

        if ($this->field instanceof EpspFieldTitle) {
            $obj->field = $this->field->to_stdclass();
        }

        return $obj;
    }

    public function copy_data() {
        global $USER;

        $data = parent::copy_data();

        // If copying field from someone else as a student, lock the field.
        if ($data->owner != $USER->get('id') && !is_teacher()) {
            $data->locked = 1;
        }

        return $data;
    }

    public function copy_extra($new) {
        parent::copy_extra($new);

        $new->commit();

        $copyid = $new->get('id');
        $record = get_record('artefact_epsp_field', 'artefact', $this->get('id'));
        $record->artefact = $copyid;

        insert_record('artefact_epsp_field', $record);
    }

    public function to_html($viewid = null) {
        return $this->field->to_html($this, $viewid);
    }

    public function add_comment($comment, $viewid) {
        global $USER;

        safe_require('artefact', 'comment');

        if (!is_teacher()) {
            throw new AccessDeniedException('Only teachers are allowed to comment fields.');
        }

        if ($USER->get('id') == $this->get('owner')) {
            throw new AccessDeniedException('Owner cannot comment own fields.');
        }

        if (!can_view_view($viewid)) {
            throw new AccessDeniedException('Cannot comment a field without access privileges.');
        }

        $data = (object) array(
            'title' => get_string('Comment', 'artefact.comment'),
            'description' => $comment,
            'onartefact' => $this->get('id'),
            'owner' => $this->get('owner'),
            'author' => $USER->get('id')
        );

        db_begin();

        $commentobj = new ArtefactTypeComment(0, $data);
        $commentobj->commit();

        require_once('activity.php');

        $activitydata = (object) array(
            'commentid' => $commentobj->get('id'),
            'viewid' => $viewid,
            'linktoview' => $viewid
        );

        activity_occurred('feedback', $activitydata, 'artefact', 'comment');

        db_commit();

        return $commentobj;
    }

    public function get_comments() {
        global $USER;

        $ctime = db_format_tsfield('a.ctime', 'ctime');
        $comments = get_records_sql_assoc("
            SELECT a.id, a.author, $ctime, a.description, u.username, u.firstname,
                u.lastname, u.deleted
              FROM {artefact} a
        INNER JOIN {artefact_comment_comment} c ON a.id = c.artefact
         LEFT JOIN {usr} u ON a.author = u.id
             WHERE c.onartefact = ?
          ORDER BY a.ctime DESC", array($this->get('id')));

        $html = '';

        if (is_array($comments)) {
            $smarty = smarty_core();

            foreach ($comments as $comment) {
                $deletable = $comment->author == $USER->get('id');

                $user = display_name((object) $comment);
                $smarty->assign('comment', $comment);
                $smarty->assign('authorname', $user);
                $smarty->assign('deletable', $deletable);

                $html .= $smarty->fetch('artefact:epsp:comment.tpl');
            }
        }

        return $html;
    }
}

class EpspFieldTitle {

    protected $artefactid = null;
    protected $value = '';
    protected $completed = false;
    protected $marked_completed_by_user = null;
    protected $marked_completed_at = null;
    protected $marked_completed_by_user_name = '';
    protected $index = -1;

    public function get_artefactid() {
        return $this->artefactid;
    }

    public function to_database_object() {
        return (object) array(
                    'completable' => $this->is_completable(),
                    'value' => $this->value,
                    'completed' => (int) $this->completed,
//                    'marked_completed_by_user' => $this->marked_completed_by_user,
//                    'marked_completed_at' => $this->marked_completed_at,
                    'artefact' => $this->artefactid,
                    'type' => $this->get_type(),
                    'order' => $this->index
        );
    }

    public function initialize_from_formvalues(stdClass $values) {
        $this->artefactid = $values->fieldid;
        $this->index = $values->index;
    }

    public function from_database_object(stdClass $values) {
        $this->artefactid = $values->artefact;
        $this->value = $values->value;
        $this->completed = $values->completed;
        $this->marked_completed_at = $values->marked_completed_at;
        $this->marked_completed_by_user = $values->marked_completed_by_user;
        $this->index = $values->order;

        if (!empty($values->firstname)) {
            $this->marked_completed_by_user_name = full_name((object) array(
                        'firstname' => $values->firstname,
                        'lastname' => $values->lastname
            ));
        }
    }

    public function to_stdclass() {
        return (object) array(
                    'completable' => $this->is_completable(),
                    'value' => $this->value,
                    'completed' => (int) $this->completed,
                    'marked_completed_by_user' => $this->marked_completed_by_user,
                    'marked_completed_at' => $this->marked_completed_at,
                    'artefact' => $this->artefactid,
                    'type' => $this->get_type(),
                    'order' => $this->index,
                    'marked_completed_by_user_name' => $this->marked_completed_by_user_name
        );
    }

    public function to_html(ArtefactTypeEpspField $field, $viewid) {
        global $USER;

        $is_teacher = is_teacher();
        $can_see_comments = $is_teacher || $field->get('owner') == $USER->get('id');
        $view = new View($viewid);
        $can_comment = $is_teacher && $view->user_comments_allowed($USER);

        $sm = smarty_core();
        $sm->assign('viewid', $viewid);
        $sm->assign('artefact', $field->to_stdclass());
        $sm->assign('field', $this->to_stdclass());
        $sm->assign('userid', $USER->get('id'));
        $sm->assign('is_teacher', $is_teacher);
        $sm->assign('can_comment', $can_comment);
        $sm->assign('can_see_comments', $can_see_comments);

        return $sm->fetch('artefact:epsp:fields/' . $this->get_type() . '_display.tpl');
    }

    public function get_type() {
        return strtolower(str_replace('EpspField', '', get_class($this)));
    }

    public function is_completable() {
        return false;
    }

    public function is_completed() {
        return !empty($this->marked_completed_by_user);
    }

    public function get_completion_date() {
        return $this->marked_completed_at;
    }

    public function get_marked_completed_by() {
        return $this->marked_completed_by_user;
    }

    public function toggle_completion_status() {
        global $USER;

        $is_completed = $this->is_completed();

        $this->marked_completed_by_user = $is_completed ? null : $USER->get('id');
        $this->marked_completed_at = $is_completed ? null : time();

        $obj = (object) array(
            'marked_completed_by_user' => $this->marked_completed_by_user,
            'marked_completed_at' => $this->marked_completed_at,
        );

        if (!empty($this->marked_completed_at)) {
            $obj->marked_completed_at = db_format_timestamp($this->marked_completed_at);
        }

        update_record('artefact_epsp_field', $obj, array('artefact' => $this->artefactid));
    }

    public function set_artefactid($id) {
        $this->artefactid = $id;
    }

}

class EpspFieldCompletabletitle extends EpspFieldTitle {

    public function initialize_from_formvalues(stdClass $values) {
        parent::initialize_from_formvalues($values);

        $this->completed = isset($values->markedcomplete) && $values->markedcomplete
                === 'on';
    }

    public function is_completable() {
        return true;
    }

}

class EpspFieldSubtitle extends EpspFieldCompletabletitle {

}

class EpspFieldTextfield extends EpspFieldTitle {

    public function initialize_from_formvalues(stdClass $values) {
        parent::initialize_from_formvalues($values);
        $this->value = $values->text;
    }

}

class EpspFieldGoal extends EpspFieldCompletabletitle {

    protected $startdate = null;
    protected $enddate = null;
    protected $demonstrationdate = null;
    protected $recognition = '';
    protected $where = '';
    protected $methods = '';

    public function to_database_object() {
        $obj = parent::to_database_object();
        $obj->value = serialize(array(
            'start' => $this->startdate,
            'end' => $this->enddate,
            'demo' => $this->demonstrationdate,
            'rpl' => $this->recognition,
            'where' => $this->where,
            'methods' => $this->methods
        ));

        return $obj;
    }

    public function initialize_from_formvalues(stdClass $values) {
        parent::initialize_from_formvalues($values);

        $this->startdate = $values->startdate;
        $this->enddate = $values->enddate;
        $this->demonstrationdate = $values->demonstrationdate;
        $this->recognition = $values->recognition;
        $this->where = $values->wherelearned;
        $this->methods = $values->methods;
    }

    public function from_database_object(stdClass $values) {
        parent::from_database_object($values);

        $valueobj = unserialize($this->value);
        $this->startdate = $valueobj['start'];
        $this->enddate = $valueobj['end'];
        $this->demonstrationdate = $valueobj['demo'];
        $this->recognition = $valueobj['rpl'];
        $this->where = $valueobj['where'];
        $this->methods = $valueobj['methods'];
    }

    public function to_stdclass() {
        $obj = parent::to_stdclass();
        $obj->start = $this->startdate;
        $obj->end = $this->enddate;
        $obj->demo = $this->demonstrationdate;
        $obj->rpl = $this->recognition;
        $obj->where = $this->where;
        $obj->methods = $this->methods;

        return $obj;
    }
}
