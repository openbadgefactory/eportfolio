<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
defined('INTERNAL') || die();

class Collection {

    private $id;
    private $name;
    private $description;
    private $owner;
    private $group;
    private $institution;
    private $mtime;
    private $ctime;
    private $navigation;
    private $submittedgroup;
    private $submittedhost;
    private $submittedtime;
    private $views;
    private $tags;
    private $type;
    private $return_date;

    public function __construct($id = 0, $data = null) {

        if (!empty($id)) {
            $tempdata = get_record('collection', 'id', $id);
            if (empty($tempdata)) {
                throw new CollectionNotFoundException("Collection with id $id not found");
            }
            if (!empty($data)) {
                $data = array_merge((array) $tempdata, $data);
            }
            else {
                $data = $tempdata; // use what the database has
            }
            $this->id = $id;
        }
        else {
            $this->ctime = time();
            $this->mtime = time();
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array) $data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
        if (empty($this->group) && empty($this->institution) && empty($this->owner)) {
            global $USER;
            $this->owner = $USER->get('id');
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'tags') {
            return $this->get_tags();
        }
        if ($field == 'views') {
            return $this->views();
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            $this->{$field} = $value;
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Creates a new Collection for the given user.
     *
     * @param array $data
     * @return collection           The newly created Collection
     */
    public static function save($data) {
        $collection = new Collection(0, $data);
        $collection->commit();

        $values = is_array($data) ? $data : (array) $data;

        // <EKAMPUS
        // If learning object, add the current user as an instructor.
        if (!isset($values['id']) && $collection->get('type') === 'learningobject') {
            insert_record('interaction_learningobject_assignment_instructor',
                    (object) array(
                        'collection' => $collection->get('id'),
                        'user' => $collection->get('owner')));
        }
        // EKAMPUS>

        return $collection; // return newly created Collections id
    }

    /**
     * Deletes a Collection
     *
     */
    public function delete() {
        $viewids = get_column('collection_view', 'view', 'collection', $this->id);
        db_begin();

        // Delete navigation blocks within the collection's views which point at this collection.
        if ($viewids) {
            $values = $viewids;
            $values[] = 'navigation';
            $navigationblocks = get_records_select_assoc(
                    'block_instance',
                    'view IN (' . join(',', array_fill(0, count($viewids), '?')) . ') AND blocktype = ?',
                    $values
            );
            if ($navigationblocks) {
                safe_require('blocktype', 'navigation');
                foreach ($navigationblocks as $b) {
                    $bi = new BlockInstance($b->id, $b);
                    $configdata = $bi->get('configdata');
                    if (isset($configdata['collection']) && $configdata['collection']
                            == $this->id) {
                        $bi->delete();
                    }
                }
            }
        }

        delete_records('collection_view', 'collection', $this->id);
        delete_records('collection_tag', 'collection', $this->id);
        delete_records('collection', 'id', $this->id);

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        if ($viewids) {
            delete_records_select('view_access',
                    'view IN (' . join(',', $viewids) . ') AND token IS NOT NULL');
        }

        db_commit();
    }

    /**
     * This method updates the contents of the collection table only.
     */
    public function commit() {

        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            // <EKAMPUS
            // Added field return_date.
            if (in_array($k, array('mtime', 'ctime', 'submittedtime', 'return_date')) && !empty($v)) {
            // EKAMPUS>
                $fordb->{$k} = db_format_timestamp($v);
            }
        }

        db_begin();

        // if id is not empty we are editing an existing collection
        if (!empty($this->id)) {
            update_record('collection', $fordb, 'id');
        }
        else {
            $id = insert_record('collection', $fordb, 'id', true);
            if ($id) {
                $this->set('id', $id);
            }
        }

        if (isset($this->tags)) {
            delete_records('collection_tag', 'collection', $this->get('id'));
            $tags = check_case_sensitive($this->get_tags(), 'collection_tag');
            foreach ($tags as $tag) {
                //truncate the tag before insert it into the database
                $tag = substr($tag, 0, 128);
                insert_record('collection_tag',
                        (object) array('collection' => $this->get('id'), 'tag' => $tag));
            }
        }

        db_commit();
    }

    /**
     * Generates a name for a newly created Collection
     */
    private static function new_name($name, $ownerdata) {
        $taken = get_column_sql('
            SELECT name
            FROM {collection}
            WHERE ' . self::owner_sql($ownerdata) . "
                AND name LIKE ? || '%'", array($name));
        $ext = '';
        $i = 0;
        if ($taken) {
            while (in_array($name . $ext, $taken)) {
                $ext = ' (' . ++$i . ')';
            }
        }
        return $name . $ext;
    }

    /**
     * Creates a Collection for the given user, based off a given template and other
     * Collection information supplied.
     *
     * Will set a default name of 'Copy of $collectiontitle' if name is not
     * specified in $collectiondata and $titlefromtemplate == false.
     *
     * @param array $collectiondata Contains information of the old collection, submitted in form
     * @param int $templateid The ID of the Collection to copy
     * @param int $userid     The user who has issued the command to create the
     *                        collection.
     * @param int $checkaccess Whether to check that the user can see the collection before copying it
     * @return array A list consisting of the new collection, the template collection and
     *               information about the copy - i.e. how many blocks and
     *               artefacts were copied
     * @throws SystemException under various circumstances, see the source for
     *                         more information
     */
    public static function create_from_template($collectiondata, $templateid,
                                                $userid = null,
                                                $checkaccess = true,
                                                $titlefromtemplate = false) {
        require_once(get_config('libroot') . 'view.php');
        global $SESSION, $CFG; // EKAMPUS

        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        db_begin();

        $colltemplate = new Collection($templateid);
        // <EKAMPUS
        // Do not rename views in learning objects.
        $islearningobject = $colltemplate->get('type') === 'learningobject';
        $renamecopiesdefault = get_config('renamecopies');

        if ($islearningobject) {
            $CFG->renamecopies = false;
        }
        // EKAMPUS>

        $data = new StdClass;
        // Set a default name if one wasn't set in $collectiondata
        if ($titlefromtemplate) {
            $data->name = $colltemplate->get('name');
        }
        else if (!isset($collectiondata['name'])) {
            $desiredname = $colltemplate->get('name');
            if (get_config('renamecopies')) {
                $desiredname = get_string('Copyofcollection', 'mahara', $desiredname);
            }
            $data->name = self::new_name($desiredname, (object) $collectiondata);
        }
        else {
            $data->name = $collectiondata['name'];
        }
        $data->description = $colltemplate->get('description');
        $data->tags = $colltemplate->get('tags');
        $data->navigation = $colltemplate->get('navigation');
        if (!empty($collectiondata['group'])) {
            $data->group = $collectiondata['group'];
        }
        else if (!empty($collectiondata['institution'])) {
            $data->institution = $collectiondata['institution'];
        }
        else if (!empty($collectiondata['owner'])) {
            $data->owner = $collectiondata['owner'];
        }
        else {
            $data->owner = $userid;
        }

        // <EKAMPUS
        $data->type = array_key_exists('type', $collectiondata)
                ? $collectiondata['type']
                : $colltemplate->get('type');
        // EKAMPUS>

        $collection = self::save($data);

        // <EKAMPUS
        // Update learningobject -> collection -relationship in database if
        // we have copied the learning object to skills folder.
        if ($islearningobject && is_null($collection->get('type'))) {
            insert_record('interaction_learningobject_collection_parent',
                    (object) array(
                        'collection' => $collection->get('id'),
                        'parent' => $templateid));
        }
        // EKAMPUS>

        $numcopied = array('pages' => 0, 'blocks' => 0, 'artefacts' => 0);

        $views = $colltemplate->get('views');
        $copyviews = array();

        foreach ($views['views'] as $v) {
            $values = array(
                'new' => true,
                'owner' => isset($data->owner) ? $data->owner : null,
                'group' => isset($data->group) ? $data->group : null,
                'institution' => isset($data->institution) ? $data->institution : null,
                'usetemplate' => $v->view
            );
            list($view, $template, $copystatus) = View::create_from_template($values,
                            $v->view, $userid, $checkaccess, $titlefromtemplate);
            if (isset($copystatus['quotaexceeded'])) {
                $SESSION->clear('messages');
                return array(null, $colltemplate, array('quotaexceeded' => true));
            }
            $copyviews['view_' . $view->get('id')] = true;
            $numcopied['blocks'] += $copystatus['blocks'];
            $numcopied['artefacts'] += $copystatus['artefacts'];
        }

        // <EKAMPUS
        // Restore default setting.
        if ($islearningobject) {
            $CFG->renamecopies = $renamecopiesdefault;
        }
        // EKAMPUS>

        $numcopied['pages'] = count($views['views']);

        $collection->add_views($copyviews);

        // Update all the navigation blocks referring to this collection
        if ($viewids = get_column('collection_view', 'view', 'collection',
                $collection->get('id'))) {
            $navblocks = get_records_select_array(
                    'block_instance',
                    'view IN (' . join(',', array_fill(0, count($viewids), '?')) . ") AND blocktype = 'navigation'",
                    $viewids
            );

            if ($navblocks) {
                safe_require('blocktype', 'navigation');
                foreach ($navblocks as $b) {
                    $bi = new BlockInstance($b->id, $b);
                    $configdata = $bi->get('configdata');
                    if (isset($configdata['collection']) && $configdata['collection']
                            == $templateid) {
                        $bi->set('configdata',
                                array('collection' => $collection->get('id')));
                        $bi->commit();
                    }
                }
            }
        }

        db_commit();

        return array(
            $collection,
            $colltemplate,
            $numcopied,
        );
    }

    /**
     * Returns a list of the current user, group, or institution collections
     *
     * @param offset current page to display
     * @param limit how many collections to display per page
     * @param groupid current group ID
     * @param institutionname current institution name
     * @return array (count: integer, data: array, offset: integer, limit: integer)
     */
    // <EKAMPUS
    public static function get_mycollections_data($offset = 0, $limit = 10,
                                                  $owner = null, $groupid = null,
                                                  $institutionname = null) {
        // EKAMPUS>

        if (!empty($groupid)) {
            $wherestm = '"group" = ?';
            $values = array($groupid);
            // <EKAMPUS
            $count = count_records('collection', 'group', $groupid);
            // EKAMPUS>
        }
        else if (!empty($institutionname)) {
            $wherestm = 'institution = ?';
            $values = array($institutionname);
            // <EKAMPUS
            $count = count_records('collection', 'institution',
                    $institutionname);
            // EKAMPUS>
        }
        else if (!empty($owner)) {
            $wherestm = 'owner = ?';
            $values = array($owner);
            // <EKAMPUS
            $count = count_records('collection', 'owner', $owner);
            // EKAMPUS>
        }
        else {
            $count = 0;
        }

        $data = array();
        if ($count > 0) {
            // <EKAMPUS
            $data = get_records_sql_assoc("
                SELECT c.id, c.description, c.name, c.mtime
                FROM {collection} c
                WHERE " . $wherestm .
                    " ORDER BY c.name, c.ctime, c.id ASC
                ", $values, $offset, $limit);
            // EKAMPUS>
        }

        self::add_submission_info($data);

        $result = (object) array(
                    'count' => $count,
                    'data' => $data,
                    'offset' => $offset,
                    'limit' => $limit,
        );
        return $result;
    }

    private static function add_submission_info(&$data) {
        if (empty($data)) {
            return;
        }

        $records = get_records_sql_assoc('
            SELECT c.id, c.submittedgroup, c.submittedhost, ' . db_format_tsfield('submittedtime') . ',
                   sg.name AS groupname, sg.urlid, sh.name AS hostname
              FROM {collection} c
         LEFT JOIN {group} sg ON c.submittedgroup = sg.id
         LEFT JOIN {host} sh ON c.submittedhost = sh.wwwroot
             WHERE c.id IN (' . join(',', array_fill(0, count($data), '?')) . ')
               AND (c.submittedgroup IS NOT NULL OR c.submittedhost IS NOT NULL)',
                array_keys($data)
        );

        if (empty($records)) {
            return;
        }

        foreach ($records as $r) {
            if (!empty($r->submittedgroup)) {
                $groupdata = (object) array(
                            'id' => $r->submittedgroup,
                            'name' => $r->groupname,
                            'urlid' => $r->urlid,
                            'time' => $r->submittedtime,
                );
                $groupdata->url = group_homepage_url($groupdata);
                $data[$r->id]->submitinfo = $groupdata;
            }
            else if (!empty($r->submittedhost)) {
                $data[$r->id]->submitinfo = (object) array(
                            'name' => $r->hostname,
                            'url' => $r->submittedhost,
                            'time' => $r->submittedtime,
                );
            }
        }
    }

    /**
     * Gets the fields for the new/edit collection form
     * - populates the fields with collection data if it is an edit
     *
     * @param array $collection
     * @return array $elements
     */
    // <EKAMPUS
    public function get_collectionform_elements($is_learningobject = false) {
        $module = $is_learningobject ? 'interaction.learningobject' : 'collection';
        // EKAMPUS>
        $elements = array(
            'name' => array(
                'type' => 'text',
                'defaultvalue' => null,
                'title' => get_string('name', $module), // EKAMPUS
                'size' => 30,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'type' => 'textarea',
                'rows' => 10,
                'cols' => 50,
                'resizable' => false,
                'defaultvalue' => null,
                'title' => get_string('description', $module), // EKAMPUS
            ),
            'tags' => array(
                'type' => 'tags',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => null,
                'help' => true,
            ),
            'navigation' => array(
                'type' => 'checkbox',
                'title' => get_string('viewnavigation', $module), // EKAMPUS
                'description' => get_string('viewnavigationdesc', $module), // EKAMPUS
                'defaultvalue' => 1,
            ),
        );

        // populate the fields with the existing values if any
        if (!empty($this->id)) {
            foreach ($elements as $k => $element) {
                if ($k === 'tags') {
                    $elements[$k]['defaultvalue'] = $this->get_tags();
                }
                else {
                    $elements[$k]['defaultvalue'] = $this->$k;
                }
            }
            $elements['id'] = array(
                'type' => 'hidden',
                'value' => $this->id,
            );
        }
        if (!empty($this->group)) {
            $elements['group'] = array(
                'type' => 'hidden',
                'value' => $this->group,
            );
        }
        else if (!empty($this->institution)) {
            $elements['institution'] = array(
                'type' => 'hidden',
                'value' => $this->institution,
            );
        }
        else if (!empty($this->owner)) {
            $elements['owner'] = array(
                'type' => 'hidden',
                'value' => $this->owner,
            );
        }

        return $elements;
    }

    /**
     * Returns array of views in the current collection
     *
     * @return array views
     */
    public function views() {

        if (!isset($this->views)) {

            $sql = "SELECT v.id, cv.*, v.title, v.owner, v.group, v.institution, v.ownerformat, v.urlid
                FROM {collection_view} cv JOIN {view} v ON cv.view = v.id
                WHERE cv.collection = ?
                ORDER BY cv.displayorder, v.title, v.ctime ASC";

            $result = get_records_sql_assoc($sql, array($this->get('id')));

            if (!empty($result)) {
                require_once('view.php');
                View::get_extra_view_info($result, false, false);
                $result = array_values($result);
                $max = $min = $result[0]['displayorder'];
                foreach ($result as &$r) {
                    $max = max($max, $r['displayorder']);
                    $min = min($min, $r['displayorder']);
                    $r = (object) $r;
                }
                $this->views = array(
                    'views' => array_values($result),
                    'count' => count($result),
                    'max' => $max,
                    'min' => $min,
                );
            }
            else {
                $this->views = array();
            }
        }

        return $this->views;
    }

    /**
     * Get the available views the current user can choose from
     * - currently dashboard, group and profile views are ignored to solve access issues
     * - each view can only belong to one collection
     *
     * @return array $views
     */
    public static function available_views($owner = null, $groupid = null,
                                           $institutionname = null) {
        if (!empty($groupid)) {
            $wherestm = '"group" = ?';
            $values = array($groupid);
        }
        else if (!empty($institutionname)) {
            $wherestm = 'institution = ?';
            $values = array($institutionname);
        }
        else if (!empty($owner)) {
            $wherestm = 'owner = ?';
            $values = array($owner);
        }
        else {
            return array();
        }
        ($views = get_records_sql_array("SELECT v.id, v.title
            FROM {view} v
            LEFT JOIN {collection_view} cv ON cv.view = v.id
            WHERE " . $wherestm .
                "   AND cv.view IS NULL
                AND v.type NOT IN ('dashboard','grouphomepage','profile')
            GROUP BY v.id, v.title
            ORDER BY v.title ASC
            ", $values)) || ($views = array());
        return $views;
    }

    /**
     * Submits the selected views to the collection
     *
     * @param array values selected views
     * @return integer count so we know what SESSION message to display
     */
    public function add_views($values) {
        require_once(get_config('libroot') . 'view.php');

        $count = 0; // how many views we are adding
        db_begin();

        // each view was marked with a key of view_<id> in order to identify the correct items
        // from the form values
        foreach ($values as $key => $value) {
            if (substr($key, 0, 5) === 'view_' AND $value == true) {
                $cv = array();
                $cv['view'] = substr($key, 5);
                $cv['collection'] = $this->get('id');

                // set displayorder value
                $max = get_field('collection_view', 'MAX(displayorder)',
                        'collection', $this->get('id'));
                $cv['displayorder'] = is_numeric($max) ? $max + 1 : 0;

                insert_record('collection_view', (object) $cv);
                $count++;
            }
        }

        $viewids = get_column('collection_view', 'view', 'collection', $this->id);

        // Set the most permissive access records on all views
        View::combine_access($viewids, true);

        // Copy the whole view config from the first view to all the others
        if (count($viewids)) {
            $firstview = new View($viewids[0]);
            $viewconfig = array(
                'startdate' => $firstview->get('startdate'),
                'stopdate' => $firstview->get('stopdate'),
                'template' => $firstview->get('template'),
                'retainview' => $firstview->get('retainview'),
                'allowcomments' => $firstview->get('allowcomments'),
                'approvecomments' => (int) ($firstview->get('allowcomments') && $firstview->get('approvecomments')),
                'accesslist' => $firstview->get_access(),
            );
            View::update_view_access($viewconfig, $viewids);
        }

        db_commit();

        return $count;
    }

    /**
     * Removes the selected views from the collection
     *
     * @param integer $view the view to remove
     */
    public function remove_view($view) {
        db_begin();
        delete_records('collection_view', 'view', $view, 'collection',
                $this->get('id'));

        // Secret url records belong to the collection, so remove them from the view.
        // @todo: add user message to whatever calls this.
        delete_records_select('view_access', 'view = ? AND token IS NOT NULL',
                array($view));

        db_commit();
    }

    /**
     * Sets the displayorder for a view
     *
     * @param integer view
     * @param string direction
     *
     */
    public function set_viewdisplayorder($id, $direction) {

        $ids = get_column_sql('
            SELECT view FROM {collection_view}
            WHERE collection = ?
            ORDER BY displayorder', array($this->get('id')));

        foreach ($ids as $k => $v) {
            if ($v == $id) {
                $oldorder = $k;
                break;
            }
        }

        if ($direction == 'up' && $oldorder > 0) {
            $neworder = array_merge(array_slice($ids, 0, $oldorder - 1),
                    array($id, $ids[$oldorder - 1]),
                    array_slice($ids, $oldorder + 1));
        }
        else if ($direction == 'down' && ($oldorder + 1 < count($ids))) {
            $neworder = array_merge(array_slice($ids, 0, $oldorder),
                    array($ids[$oldorder + 1], $id),
                    array_slice($ids, $oldorder + 2));
        }

        if (isset($neworder)) {
            foreach ($neworder as $k => $v) {
                set_field('collection_view', 'displayorder', $k, 'view', $v,
                        'collection', $this->get('id'));
            }
            $this->set('mtime', time());
            $this->commit();
        }
    }

    /**
     * after editing the collection, redirect back to the appropriate place
     */
    public function post_edit_redirect($new = false, $copy = false,
                                       $urlparams = null) {
        if ($new || $copy) {
            $urlparams['id'] = $this->get('id');
            $redirecturl = '/collection/views.php';
        }
        else {
            // <EKAMPUS
            $groupid = $this->get('group');
            $redirecturl = $this->get('type') === 'learningobject'
                    ? '/interaction/learningobject/index.php'
                    : '/interaction/pages/' . (!empty($groupid) ? 'group' : '') . 'collections.php';
            // EKAMPUS>
        }
        if ($urlparams) {
            $redirecturl .= '?' . http_build_query($urlparams);
        }
        redirect($redirecturl);
    }

    public static function search_by_view_id($viewid) {
        $record = get_record_sql('
            SELECT c.*
            FROM {collection} c JOIN {collection_view} cv ON c.id = cv.collection
            WHERE cv.view = ?', array($viewid)
        );
        if ($record) {
            return new Collection(0, $record);
        }
        return false;
    }

    /**
     * Returns an SQL snippet that can be used in a where clause to get views
     * with the given owner.
     *
     * @param object $ownerobj An object that has view ownership information -
     *                         either the institution, group or owner fields set
     * @return string
     */
    private static function owner_sql($ownerobj) {
        // Multiple owners.
        if (isset($ownerobj->multiple)) {
            if (isset($ownerobj->owner)) {
                foreach (get_object_vars($ownerobj) as $column => $values) {
                    if (is_array($values) && !empty($values)) {
                        return '"' . $column . '" IN (' . join(',', $values) . ')';
                    }
                }
            }
            else {
                // Check if user is admin in any institution + all groups
                return "
                    owner IN
                    (
                        SELECT id
                          FROM {usr} u
                     LEFT JOIN {usr_institution} ui ON u.id = ui.usr
                    ) OR c.group IS NOT NULL";
            }
        }
        else {
            if (isset($ownerobj->institution)) {
                return 'institution = ' . db_quote($ownerobj->institution);
            }
            if (isset($ownerobj->group) && is_numeric($ownerobj->group)) {
                return '"group" = ' . (int) $ownerobj->group;
            }
            if (isset($ownerobj->owner) && is_numeric($ownerobj->owner)) {
                return 'owner = ' . (int) $ownerobj->owner;
            }
            throw new SystemException("View::owner_sql: Passed object did not have an institution, group or owner field");
        }
    }

    /**
     * Makes a URL for a collection
     *
     * @param bool $full return a full url
     * @param bool $useid ignore clean url settings and always return a url with an id in it
     *
     * @return string
     */
    public function get_url($full = true, $useid = false) {
        global $USER;

        $views = $this->views();
        if (!empty($views)) {
            $v = new View(0, $views['views'][0]);
            $v->set('dirty', false);
            return $v->get_url($full, $useid);
        }

//        log_warn("Attempting to get url for an empty collection");

        if ($this->owner === $USER->get('id')) {
            $url = 'collection/views.php?id=' . $this->id;
        }
        else {
            $url = '';
        }

        if ($full) {
            $url = get_config('wwwroot') . $url;
        }

        return $url;
    }

    // <EKAMPUS
    public function get_publicity() {
        global $USER;

        // TODO: DRY with PluginArtefactStudyJournal::get_publicity_from_*
        static $accesslists = array();

        if (empty($accesslists)) {
            $accesslists = View::get_accesslists($USER->id);
        }

        $id = $this->get('id');

        if (isset($accesslists[$id]) && isset($accesslists[$id]['accessgroups'])) {
            $accesstypes = array();

            foreach ($accesslists[$id]['accessgroups'] as $group) {
                $accesstypes[] = $group['accesstype'];
            }

            if (in_array('public', $accesstypes)) {
                return 'public';
            }
            else if (count(array_intersect(array('loggedin', 'group', 'friends',
                'institution', 'user'), $accesstypes)) > 0) {
                return 'published';
            }
            else if (in_array('token', $accesstypes)) {
                return 'published';
            }
        }

        return 'private';
    }

    // EKAMPUS>

    /**
     * Release a submitted collection
     *
     * @param object $releaseuser The user releasing the collection
     */
    public function release($releaseuser = null) {

        if (!$this->is_submitted()) {
            throw new ParameterException("Collection with id " . $this->id . " has not been submitted");
        }

        // One day there might be group and institution collections, so be safe
        if (empty($this->owner)) {
            throw new ParameterException("Collection with id " . $this->id . " has no owner");
        }

        $viewids = $this->get_viewids();

        db_begin();
        execute_sql('
            UPDATE {collection}
            SET submittedgroup = NULL, submittedhost = NULL, submittedtime = NULL
            WHERE id = ?', array($this->id)
        );
        View::_db_release($viewids, $this->owner, $this->submittedgroup);
        db_commit();

        $releaseuser = optional_userobj($releaseuser);
        $releaseuserdisplay = display_name($releaseuser, $this->owner);
        $submitinfo = $this->submitted_to();

        require_once('activity.php');
        activity_occurred(
                'maharamessage',
                array(
            'users' => array($this->get('owner')),
            'strings' => (object) array(
                'subject' => (object) array(
                    'key' => 'collectionreleasedsubject',
                    'section' => 'group',
                    'args' => array($this->name, $submitinfo->name, $releaseuserdisplay),
                ),
                'message' => (object) array(
                    'key' => 'collectionreleasedmessage',
                    'section' => 'group',
                    'args' => array($this->name, $submitinfo->name, $releaseuserdisplay),
                ),
            ),
            'url' => $this->get_url(false),
            'urltext' => $this->name,
                )
        );
    }

    public function get_viewids() {
        $ids = array();
        $viewdata = $this->views();

        if (!empty($viewdata['views'])) {
            foreach ($viewdata['views'] as $v) {
                $ids[] = $v->id;
            }
        }

        return $ids;
    }

    public function is_submitted() {
        return $this->submittedgroup || $this->submittedhost;
    }

    public function submitted_to() {
        if ($this->submittedgroup) {
            $record = get_record('group', 'id', $this->submittedgroup, null,
                    null, null, null, 'id, name, urlid');
            $record->url = group_homepage_url($record);
        }
        else if ($this->submittedhost) {
            $record = get_record('host', 'wwwroot', $this->submittedhost, null,
                    null, null, null, 'wwwroot, name');
            $record->url = $record->wwwroot;
        }
        else {
            throw new SystemException("Collection with id " . $this->id . " has not been submitted");
        }

        return $record;
    }

    public function submit($group) {
        global $USER;

        if ($this->is_submitted()) {
            throw new SystemException('Attempting to submit a submitted collection');
        }

        $viewids = $this->get_viewids();
        $idstr = join(',', array_map('intval', $viewids));

        // Check that none of the views is submitted to some other group.  This is bound to happen to someone,
        // because collection submission is being introduced at a time when it is still possible to submit
        // individual views in a collection.
        $submittedtitles = get_column_sql("
            SELECT title FROM {view}
            WHERE id IN ($idstr) AND (submittedhost IS NOT NULL OR (submittedgroup IS NOT NULL AND submittedgroup != ?))",
                array($group->id)
        );

        if (!empty($submittedtitles)) {
            die_info(get_string('viewsalreadysubmitted', 'view',
                            implode('<br>', $submittedtitles)));
        }

        $group->roles = get_column('grouptype_roles', 'role', 'grouptype',
                $group->grouptype, 'see_submitted_views', 1);

        db_begin();
        View::_db_submit($viewids, $group);
        $this->set('submittedgroup', $group->id);
        $this->set('submittedhost', null);
        $this->set('submittedtime', time());
        $this->commit();
        db_commit();

        activity_occurred(
                'groupmessage',
                array(
            'group' => $group->id,
            'roles' => $group->roles,
            'url' => $this->get_url(false),
            'strings' => (object) array(
                'urltext' => (object) array(
                    'key' => 'Collection',
                    'section' => 'collection',
                ),
                'subject' => (object) array(
                    'key' => 'viewsubmittedsubject1',
                    'section' => 'activity',
                    'args' => array($group->name),
                ),
                'message' => (object) array(
                    'key' => 'viewsubmittedmessage1',
                    'section' => 'activity',
                    'args' => array(
                        display_name($USER, null, false, true),
                        $this->name,
                        $group->name,
                    ),
                ),
            ),
                )
        );
    }

    /**
     * Returns the collection tags
     *
     * @return mixed
     */
    public function get_tags() {
        if (!isset($this->tags)) {
            $this->tags = get_column('collection_tag', 'tag', 'collection',
                    $this->get('id'));
        }
        return $this->tags;
    }

    // <EKAMPUS

    public static function get_collections_tags(array $ids) {
        $idstr = implode(', ', array_map('intval', $ids));
        $ret = array();
        $tags = get_records_sql_array("
            SELECT collection, tag
              FROM {collection_tag}
             WHERE collection IN ($idstr)", array());

        if (is_array($tags)) {
            foreach ($tags as $arr) {
                if (!isset($ret[$arr->collection])) {
                    $ret[$arr->collection] = array();
                }

                $ret[$arr->collection][] = $arr->tag;
            }
        }

        return $ret;
    }

    /**
     * Find collections. Lots of code from View::view_search()
     */
    public static function collection_search($query = '', $ownedby = null,
            $limit = null, $offset = null, $sortby = array(), $accesstypes = array(),
            $tags = array(), $ownerquery = null, array $types = array()) {

        global $USER;

        $loggedin = $USER->is_logged_in();
        $viewerid = (int) $USER->get('id');
        $fromparams = array();
        $whereparams = array();
        $ownersql = '';

        $from = "
            FROM {collection} c
            LEFT JOIN (
                SELECT v.*, MAX(v.mtime) AS max_time, cw.collection
                FROM {view} v
                LEFT JOIN {collection_view} cw ON v.id = cw.view
                GROUP BY cw.collection
            ) v ON c.id = v.collection
            LEFT OUTER JOIN {usr} qu ON (c.owner = qu.id)
            ";
        $where = "
            WHERE (c.owner IS NULL OR c.owner > 0)
                AND (c.group IS NULL OR c.group NOT IN (SELECT id FROM {group} WHERE deleted = 1))
                AND (qu.suspendedctime is null OR c.owner = ?)";

        $whereparams[] = $viewerid;

        if (in_array('learningobject', $types)) {
            $where .= " AND (c.type = ?)";
            $whereparams[] = 'learningobject';
        }
        else {
            $where .= " AND (c.type IS NULL)";
        }

        if ($ownedby) {
            $ownersql = ' AND (c.' . self::owner_sql($ownedby) . ')';
            $where .= $ownersql;
        }

        $like = db_ilike();

        if (!empty($query)) {
            $collate = "COLLATE utf8_swedish_ci";
            $from .= "
                LEFT JOIN {collection_tag} ct ON (ct.collection = c.id)";
            $where .= "
                AND (c.name $like '%' || ? || '%' $collate OR c.description $like '%' || ? || '%' $collate OR ct.tag $like '%' || ? || '%' $collate ";

            array_push($whereparams, $query, $query, $query);

            $where .= ")";
        }

        if (count($tags) > 0) {
            $tagcount = count($tags);
            $tagstr = implode(', ', array_map('db_quote', $tags));
            $where .= "
                AND c.id IN (
                    SELECT collection
                    FROM {collection_tag}
                    WHERE tag IN ($tagstr)
                    GROUP BY collection
                    HAVING COUNT(tag) = $tagcount)
                ";
        }

        $editableviews = false;
        $editablesql = '';

        if (is_array($accesstypes)) {
            $editableviews = in_array('editable', $accesstypes);
        }
        else if ($loggedin) {
            $editableviews = true;
            $accesstypes = array('public', 'loggedin', 'friend', 'user', 'group', 'institution');
        }
        // If not logged in, show only public collections.
        else if (!$loggedin) {
            $accesstypes = array('public');
        }

        if ($editableviews) {
            $editablesql = "c.owner = ?      -- user owns the view
                    OR c.group IN (  -- group view, editable by the user
                        SELECT m.group
                        FROM {group_member} m JOIN {group} g ON m.member = ? AND m.group = g.id
                        WHERE m.role = 'admin' OR g.editroles = 'all' OR (g.editroles != 'admin' AND m.role != 'member')
                    )";
            $whereparams[] = $viewerid;
            $whereparams[] = $viewerid;
        }
        else {
            $editablesql = 'FALSE';
        }

        $accesssql = array();

        //<EKAMPUS
        $isownersql =  " (v.owner = $viewerid OR v.group IN (  -- group view, editable by the user
                        SELECT m.group
                        FROM {group_member} m JOIN {group} g ON m.member = $viewerid AND m.group = g.id
                        WHERE m.role = 'admin' OR g.editroles = 'all' OR (g.editroles != 'admin' AND m.role != 'member')
                        ))";
        $currently_shared = "(
            (va.startdate IS NULL OR va.startdate < current_timestamp)
            AND (va.stopdate IS NULL OR va.stopdate > current_timestamp)
        )";

        $published_accesstypes = array('user', 'friend', 'group', 'institution',
            'loggedin', 'token');
        $getpublic = in_array('public', $accesstypes);
        $getpublished = count(array_intersect($published_accesstypes, $accesstypes)) > 0;
        $getprivate = in_array('private', $accesstypes);
        $group = group_current_group();
        //EKAMPUS >

        foreach ($accesstypes as $t) {
            if ($t === 'own' || ($t === 'owngroup' && !is_null($group))) {
                $ownsql = "
                    -- User or user's group is the owner
                    (
                        $isownersql";

                $sharedsql = array();

                // Get own (or current group's) public collections.
                if ($getpublic) {
                    $sharedsql[] = "
                        v.id IN (
                            SELECT va.view
                              FROM {view_access} va
                             WHERE va.accesstype = 'public'
                                AND $currently_shared
                        )";
                }

                // Get own (or current group's) published collections.
                if ($getpublished) {
                    $sharedsql[] = "
                        v.id IN (
                            SELECT va.view
                              FROM {view_access} va
                             WHERE
                             (
                                va.accesstype = 'loggedin'
                                OR
                                (
                                    va.accesstype IS NULL AND
                                    (
                                        va.`group` IS NOT NULL
                                        OR va.usr IS NOT NULL
                                        OR va.institution IS NOT NULL
                                        OR va.token IS NOT NULL
                                    )
                                )
                                AND $currently_shared
                             )
                        )";
                }

                // Get own (or current group's) private collections.
                if ($getprivate) {
                    // Sweet baby Jesus the hacks.
                    $ownersqlviews = str_replace('c.', 'v.', $ownersql);
                    $sharedsql[] = "
                        (
                            v.id IS NULL $ownersql
                        )
                        OR
                        (
                            v.id NOT IN (
                                SELECT va.view
                                  FROM {view_access} va
                            ) $ownersqlviews
                        )";
                }

                if (count($sharedsql) > 0) {
                    $ownsql .= "
                        AND (" . implode(" OR ", $sharedsql) . ")";
                }

                $ownsql .= ")";
                $accesssql[] = $ownsql;
            }
            else if ($t == 'private') {
                $accesssql[] = "
                    ( -- private access, no views in collection
                        v.id IS NULL AND c.owner = ?
                    )
                    OR
                    ( -- private access, views exist, but no access given.
                        v.id NOT IN (
                            SELECT va.view
                            FROM {view_access} va
                        ) AND (v.owner = ? "./*<EKAMPUS -private group views*/"OR v.group IN (
                        SELECT m.group
                        FROM {group_member} m JOIN {group} g ON m.member = $viewerid AND m.group = g.id
                        WHERE m.role = 'admin' OR g.editroles = 'all' OR (g.editroles != 'admin' AND m.role != 'member')
                        )) ". /*EKAMPUS>*/"
                    )";
                $whereparams[] = $viewerid;
                $whereparams[] = $viewerid;

            }
            else if ($t == 'public') {
                $accesssql[] = "v.id IN ( -- public access
                                SELECT va.view
                                FROM {view_access} va
                                WHERE va.accesstype = 'public'"
                                  /*<EKAMPUS*/
                                ." AND ( $isownersql OR (".
                                /*EKAMPUS>*/
                                    " $currently_shared))
                            )";
            }
            else if ($t == 'loggedin') {
                $accesssql[] = "v.id IN ( -- loggedin access
                                SELECT va.view
                                FROM {view_access} va
                                WHERE va.accesstype = 'loggedin'"
                                 /*<EKAMPUS*/
                                ." AND ( $isownersql OR (".
                                /*<EKAMPUS*/
                                    " $currently_shared))
                            )";
            }
            else if ($t == 'user') {
                $accesssql[] = "v.id IN ( -- user access
                                SELECT va.view
                                FROM {view_access} va "
                                ./*<EKAMPUS this makes u see your own shares to individual users*/
                                    "JOIN {view} vf ON va.view = vf.id AND vf.owner IS NOT NULL
                                WHERE ((va.usr = ?) OR (va.usr IS NOT NULL AND vf.owner = ?))
                                 AND ( $isownersql OR (
                                ".
                                 /*<EKAMPUS*/
                                    " $currently_shared))
                            )";
                $whereparams[] = $viewerid;
                $whereparams[] = $viewerid;
            }
            else if ($t == 'group') {
                $accesssql[] = "v.id IN ( -- group access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {group_member} m ON va.group = m.group AND (va.role = m.role OR va.role IS NULL)
                                WHERE
                                    m.member = ?"
                                /*<EKAMPUS*/
                                ." AND ( $isownersql OR (".
                                /*<EKAMPUS*/
                                    " $currently_shared))
                            )";
                $whereparams[] = $viewerid;
            }
            else if ($t == 'institution') {
                $accesssql[] = "v.id IN ( -- institution access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {usr_institution} ui ON va.institution = ui.institution
                                WHERE
                                    ui.usr = ?"
                                /*<EKAMPUS*/
                                ." AND ( $isownersql OR (".
                                /*<EKAMPUS*/
                                    " $currently_shared))
                            )";
                $whereparams[] = $viewerid;
            }
            //< EKAMPUS
            else if ($t == 'token') {
                $accesssql[] = "v.id IN ( -- token access
                                SELECT va.view
                                FROM {view_access} va
                                    JOIN {view} vf ON va.view = vf.id
                                WHERE (va.token IS NOT NULL AND vf.owner = ?)"
                                 /*<EKAMPUS*/
                                ." AND ( $isownersql OR (".
                                /*<EKAMPUS*/
                                    " $currently_shared))
                            )";
                $whereparams[] = $viewerid;
            }
            // EKAMPUS >
        }

        $accesssqlstr = '';

        if (count($accesssql) > 0) {
            $accesssqlstr = "( -- user has permission to see the view
                                (
                                    $isownersql OR
                                    (
                                        (v.startdate IS NULL OR v.startdate < current_timestamp)
                                        AND (v.stopdate IS NULL OR v.stopdate > current_timestamp)
                                    )
                                ) AND (" . join(' OR ', $accesssql) . "))";
        }
        else {
            $accesssqlstr = 'FALSE';
        }

        $where .= "
                AND ($editablesql
                    OR $accesssqlstr)";
        $orderby = 'name ASC';

        if (!empty($ownerquery)) {
            $from .= "
                LEFT OUTER JOIN {group} qg ON (c.group = qg.id)
                LEFT OUTER JOIN {institution} qi ON (c.institution = qi.name)";

            if (strpos(strtolower(get_config('sitename')), strtolower($ownerquery)) !== false) {
                $sitequery = " OR qi.name = 'mahara'";
            }
            else {
                $sitequery = '';
            }

            $ownerwhere = "
                AND (
                    qu.preferredname $like '%' || ? || '%' OR
                    qu.firstname $like '%' || ? '%' OR
                    qu.lastname $like '%' || ? || '%' OR
                    qg.name $like '%' || ? || '%' OR
                    qi.displayname $like '%' || ? || '%'
                    OR CONCAT(qu.firstname, ' ', qu.lastname) $like '%' || ? || '%'
                    $sitequery
                )";
            $where .= $ownerwhere;
            $whereparams = array_merge($whereparams, array($ownerquery,
                $ownerquery, $ownerquery, $ownerquery, $ownerquery, $ownerquery));
        }

        if (count($sortby) > 0) {
            $orderby = '';

            foreach ($sortby as $item) {
                if (!preg_match('/^[a-zA-Z_0-9\'="]+$/', $item['column'])) {
                    continue; // skip this item (it fails validation)
                }

                if (!empty($orderby)) {
                    $orderby .= ', ';
                }

                if ($item['column'] == 'lastchanged') {
                    $orderby .= 'GREATEST(c.mtime, COALESCE(v.max_time, c.mtime))';
                }
                else {
                    $orderby .= (!empty($item['tablealias']) ? $item['tablealias'] . '.' : '') . $item['column'];
                }

                $orderby .= $item['desc'] ? ' DESC' : ' ASC';
            }
        }

        $ph = array_merge($fromparams, $whereparams);
        $count = count_records_sql('SELECT COUNT(*) ' . $from . $where, $ph);
        // <EKAMPUS
        $rtime = db_format_tsfield('c.return_date', 'rtime');
        // EKAMPUS>
        $data = get_records_sql_assoc("
            SELECT c.id, c.name, c.description, c.owner, c.group, c.institution,
                (
                    SELECT view
                    FROM {collection_view}
                    WHERE collection = c.id
                    ORDER BY displayorder ASC
                    LIMIT 1
                ) AS first_view_id,
                c.mtime, c.ctime, c.type, v.id AS view_id, v.mtime AS view_mtime,
                v.ctime AS view_ctime,".
                /*<EKAMPUS*/" v.template AS view_template, $rtime, "./*EKAMPUS>*/
                " GREATEST(c.mtime, COALESCE(v.max_time, c.mtime)) AS modtime
            $from
            $where
            ORDER BY $orderby, c.id ASC", $ph, $offset, $limit);

        if (!$data) {
            $data = array();
        }
        // Get owner names.
        else {
            Collection::get_extra_collection_info($data);
        }

        return (object) array(
            'ids' => array_keys($data),
            'data' => array_values($data),
            'count' => $count
        );
    }
    // EKAMPUS>

    // <EKAMPUS
    public static function get_extra_collection_info(&$collectiondata) {
        $owners = array();
        $groups = array();
        $institutions = array();

        foreach ($collectiondata as $c) {
            if (!empty($c->owner) && !isset($owners[$c->owner])) {
                $owners[$c->owner] = (int) $c->owner;
            }
            else if (!empty($c->group) && !isset($groups[$c->group])) {
                $groups[$c->group] = (int) $c->group;
            }
            else if (!empty($c->institution) && !isset($institutions[$c->institution])) {
                $institutions[$c->institution] = $c->institution;
            }
        }

        if (!empty($owners)) {
            global $USER;
            $userid = $USER->get('id');
            $fields = array(
                'id', 'username', 'firstname', 'lastname', 'preferredname',
                'admin', 'staff', 'studentid', 'email', 'profileicon', 'urlid',
                'suspendedctime',
            );

            if (count($owners) == 1 && isset($owners[$userid])) {
                $owners = array($userid => new stdClass());

                foreach ($fields as $f) {
                    $owners[$userid]->$f = $USER->get($f);
                }
            }
            else {
                $owners = get_records_select_assoc(
                        'usr',
                        'id IN (' . join(',', array_fill(0, count($owners), '?')) . ')',
                        $owners, '', join(',', $fields)
                );
            }
        }

        if (!empty($groups)) {
            require_once('group.php');
            $groups = get_records_select_assoc('group',
                    'id IN (' . join(',', $groups) . ')', null, '',
                    'id,name,urlid');
        }

        if (!empty($institutions)) {
            $institutions = get_records_assoc('institution', '', '', '',
                    'name,displayname');
            $institutions['mahara']->displayname = get_config('sitename');
        }

        foreach ($collectiondata as &$c) {
            if (!empty($c->owner)) {
                $c->sharedby = View::owner_name(null, $owners[$c->owner]);
                $c->user = $owners[$c->owner];
            }
            else if (!empty($c->group)) {
                $c->sharedby = $groups[$c->group]->name;
                $c->groupdata = $groups[$c->group];
            }
            else if (!empty($c->institution)) {
                $c->sharedby = $institutions[$c->institution]->displayname;
            }
            $c = (array) $c;
        }
    }
    // EKAMPUS>
}
