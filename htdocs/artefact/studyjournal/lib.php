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
 * @subpackage artefact-studyjournal
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

class PluginArtefactStudyJournal extends PluginArtefact {

    const PUBLICITY_PRIVATE = 'private';
    const PUBLICITY_PUBLIC = 'public';
    const PUBLICITY_PUBLISHED = 'published';

    public static function get_artefact_types() {
        return array('studyjournaltemplate', 'studyjournal', 'studyjournalentry');
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'studyjournal';
    }

    public static function get_event_subscriptions() {
        $sub = new stdClass();
        $sub->plugin = 'studyjournal';
        $sub->event = 'deleteview';
        $sub->callfunction = 'delete_attached_artefacts';

        return array($sub);
    }

    public static function delete_attached_artefacts($event, $data) {
        delete_records('artefact_study_journal_entry_view', 'view', $data['id']);
        delete_records('artefact_study_journal_entry_collection', 'collection',
                $data['id']);
    }

    public static function menu_items() {
        global $USER;

        $items = array();

        // Teacher navigation.
        if (is_teacher() && !in_admin_section()) {
            $items['studyjournal'] = array(
                'path' => 'studyjournal',
                'url' => 'artefact/studyjournal/tutor/shared.php',
                'title' => get_string('studyjournal', 'artefact.studyjournal'),
                'weight' => 35
            );
            $items['studyjournal/journals'] = array(
                'path' => 'studyjournal/journals',
                'url' => 'artefact/studyjournal/student/index.php',
                'title' => get_string('studyjournals', 'artefact.studyjournal'),
                'weight' => 10
            );
            $items['studyjournal/studentjournals'] = array(
                'path' => 'studyjournal/studentjournals',
                'url' => 'artefact/studyjournal/tutor/shared.php',
                'title' => get_string('studentstudyjournals',
                        'artefact.studyjournal'),
                'weight' => 20
            );
            $items['studyjournal/templates'] = array(
                'path' => 'studyjournal/templates',
                'url' => 'artefact/studyjournal/tutor/templates.php',
                'title' => get_string('templates', 'artefact.studyjournal'),
                'weight' => 30
            );
        }

        // Student navigation.
        else if (!$USER->is_institutional_admin() && !$USER->is_institutional_staff()
                && !$USER->get('admin')) {
            $items['studyjournal'] = array(
                'path' => 'studyjournal',
                'url' => 'artefact/studyjournal/student/index.php',
                'title' => get_string('studyjournal', 'artefact.studyjournal'),
                'weight' => 35
            );
        }

        return $items;
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            // New studyjournal viewtype.
            $obj = (object) array('type' => 'studyjournal');
            ensure_record_exists('view_type', $obj, $obj);

            // Create empty default template.
            $template_exists = record_exists('artefact', 'owner', 0, 'note', 'notemplate');
            $admin_exists = record_exists('usr', 'id', 0);

            if (!$template_exists && $admin_exists) {
                $time = db_format_timestamp(time());
                $template = (object) array('artefacttype' => 'studyjournaltemplate',
                            'owner' => 0,
                            'ctime' => $time,
                            'mtime' => $time,
                            'atime' => $time,
                            'title' => 'notemplate',
                            'note' => 'notemplate',
                            'author' => 0);
                $id = insert_record('artefact', $template, 'id', true);
                insert_record('artefact_study_journal_field', (object) array(
                            'artefact' => $id,
                            'title' => '',
                            'weight' => 0,
                            'type' => 'text',
                ));
            }
        }
    }

    public static function user_is_group_tutor() {
        foreach (group_get_user_groups() as $g) {
            if ($g->role === 'tutor' || $g->role === 'admin') {
                return true;
            }
        }

        return false;
    }

    public static function get_template_field(stdClass $field, $template = null) {
        if ($field->type === "text") {
            $is_empty_template = !empty($template) && $template->get('note') === 'notemplate';

            return array(
                'type' => 'textarea',
                'rows' => $is_empty_template ? 20 : 5,
                'cols' => 100,
                'title' => $field->title,
                'resizable' => false
            );
        }
        else if ($field->type === "vibe") {
            return array(
                'type' => 'radio',
                'title' => $field->title,
                'options' => array(0, 1, 2, 3, 4),
                'class' => 'vibemeter',
                'defaultvalue' => 4
            );
        }
    }

    public static function get_journal_dateform(ArtefactTypeStudyjournal $journal,
                                                $filter) {
        $dates = array(
            'alldates' => get_string('showentriesfromalldates',
                    'artefact.studyjournal'),
            'all' => get_string('showallentries', 'artefact.studyjournal'));
        $dates = array_merge($dates, $journal->get_entry_dates());
        $dateform = pieform(array(
            'name' => 'dateselect',
            'renderer' => 'oneline',
            'successcallback' => 'PluginArtefactStudyJournal::journal_dateselect_submit',
            'elements' => array(
                'journal_id' => array(
                    'type' => 'hidden',
                    'value' => $journal->get('id')
                ),
                'filter' => array(
                    'type' => 'select',
                    'options' => $dates,
                    'defaultvalue' => $filter
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('updatejournalpage',
                            'artefact.studyjournal')
                )
            )
        ));

        return $dateform;
    }

    public static function journal_dateselect_submit(Pieform $form, $values) {
        redirect(get_config('wwwroot') . 'artefact/studyjournal/student/journal.php?id=' . $values['journal_id'] . '&filter=' . $values['filter']);
    }

    /**
     *
     * @global type $USER
     * @return type
     */
    public static function get_student_template_list() {
        global $USER;

        $templates = get_records_sql_array("
            SELECT a.id, a.title, a.note, a.owner
              FROM {artefact} a
             WHERE a.artefacttype = 'studyjournaltemplate' AND
             (
                -- Own templates
                a.owner = ?

                -- Empty template
                OR (a.owner = 0 AND a.note = 'notemplate' AND a.title = 'notemplate')

                -- Institution access
                OR a.id IN
                (
                    SELECT artefact
                      FROM {artefact_study_journal_institution}
                     WHERE institution IN
                     (
                        SELECT institution
                          FROM {usr_institution}
                         WHERE usr = ?
                     )
                )

                -- Group access
                OR a.id IN
                (
                    SELECT artefact
                      FROM {artefact_study_journal_group}
                     WHERE `group` IN
                     (
                        SELECT `group`
                          FROM {group_member}
                         WHERE member = ?
                     )
                )
             )
          ORDER BY a.note = 'notemplate' DESC, title ASC",
                array($USER->id, $USER->id, $USER->id));

        $hastemplates = false;

        if (is_array($templates)) {
            $hastemplates = true;
            $opts = array();
            foreach ($templates as $row) {
                // Empty template
                if ($row->owner == 0 && $row->note == 'notemplate') {
                    $opts[$row->id] = get_string($row->title,
                            'artefact.studyjournal');
                }
                // Regular template
                else {
                    $opts[$row->id] = $row->title;
                }
            }
        }

        return (!$hastemplates ? false : $opts);
    }

    public static function get_tutor_templates() {
        global $USER;

        $institutions = array_keys(load_user_institutions($USER->id));
        $placeholders = implode(',', array_fill(0, count($institutions), '?'));
        $instquery = count($institutions) === 0 ? "" : " OR a.id IN (
                    SELECT artefact
                      FROM {artefact_study_journal_institution}
                     WHERE institution IN ($placeholders)
                )";
        $params = $institutions;

        array_unshift($params, $USER->id);

        $result = get_records_sql_array("
            SELECT a.id, a.owner, a.title, a.mtime, a.description,
                   u.firstname, u.lastname, u.deleted,
                   (
                        SELECT COUNT(*)
                          FROM {artefact_study_journal_group}
                         WHERE artefact = a.id
                   ) AS group_shares,
                   (
                        SELECT COUNT(*)
                          FROM {artefact_study_journal_institution}
                         WHERE artefact = a.id
                   ) AS institution_shares
              FROM {artefact} a
         LEFT JOIN {usr} u ON a.owner = u.id
             WHERE a.artefacttype = 'studyjournaltemplate' AND (
                a.owner = ? $instquery)
          ORDER BY a.title ASC", $params);

        if (is_array($result)) {
            $templateids[] = array();
            $wwwroot = get_config('wwwroot');

            foreach ($result as &$row) {
                $templateids[] = $row->id;
                $isown = $row->owner == $USER->id;
                $row->isnotown = !$isown; // Seriously, how to negate a variable with Dwoo??
                $row->author = full_name((object) array(
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'deleted' => $row->deleted
                ));

                $row->extradata = array('templatetype' => ($isown ? 'own' : 'shared'));
                $row->publicity = $row->institution_shares > 0 || $row->group_shares
                        > 0 ? 'published' : 'private';
                $row->menuitems = array();

                if ($isown) {
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'artefact/studyjournal/tutor/edit.php?id=' . $row->id,
                        'title' => get_string('edit', 'artefact.studyjournal')
                    );
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'artefact/studyjournal/tutor/access.php?id=' . $row->id,
                        'title' => get_string('sharetemplate',
                                'artefact.studyjournal')
                    );
                }

                $row->menuitems[] = array(
                    'title' => get_string('copytemplate',
                            'artefact.studyjournal'),
                    'classes' => 'copy-template'
                );

                if ($isown) {
                    $row->menuitems[] = array(
                        'title' => get_string('deletetemplate',
                                'artefact.studyjournal'),
                        'classes' => 'delete-template'
                    );
                }
            }

            // Quick and dirty way to get the tags for each journal.
            $artefact_tags = array();

            if ($tags = ArtefactType::tags_from_id_list($templateids)) {
                foreach ($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($result as &$template) {
                $tags = isset($artefact_tags[$template->id]) ? $artefact_tags[$template->id]
                            : array();
                $template->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }
        }

        return $result;
    }

    public static function get_tutor_template_tags() {
        global $USER;

        $result = get_column_sql("
            SELECT DISTINCT(tag)
              FROM artefact_tag
             WHERE artefact IN
             (
                SELECT id
                  FROM artefact
                 WHERE artefacttype = 'studyjournaltemplate' AND owner = ?
             )
          ORDER BY tag COLLATE utf8_swedish_ci ASC", array($USER->id));

        return is_array($result) ? $result : array();
    }

    public static function get_journals() {
        global $USER;

        // SQL GREATEST supported in MySQL & PostgreSQL, include mtime of
        // entries when ordering results to get the latest journals to the
        // top of the list.
        $result = get_records_sql_array("
            SELECT a.id, a.owner, a.title, a.description, u.firstname,
                u.lastname, u.deleted, GREATEST(a.mtime,
                (
                    SELECT COALESCE(MAX(mtime), 0)
                      FROM {artefact}
                     WHERE artefacttype = 'studyjournalentry' AND parent = a.id
                )) AS mtime
              FROM {artefact} a
         LEFT JOIN {usr} u ON a.owner = u.id
             WHERE a.artefacttype = 'studyjournal' AND
                   a.owner = ?
          ORDER BY a.title ASC", array($USER->id));

        if (is_array($result)) {
            $wwwroot = get_config('wwwroot');
            $journalviews = self::get_user_journal_views($USER->id);
            $accesslists = View::get_accesslists($USER->id, null, null,
                            array('studyjournal'));
            $journalids = array();

            foreach ($result as &$row) {
                $journalids[] = $row->id;
                $row->author = full_name((object) array(
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'deleted' => $row->deleted
                ));

                // TODO: use find_artefact_publicity() and find_artefact_view()
                $row->publicity = self::find_journal_publicity($row->id,
                                $journalviews, $accesslists);

                foreach ($journalviews as $view) {
                    $cfg = unserialize($view->configdata);

                    if ($cfg['artefactid'] == $row->id) {
                        $row->viewid = $view->id;
                        break;
                    }
                }

                $row->menuitems = array(
                    array(
                        'url' => $wwwroot . 'artefact/studyjournal/student/edit.php?id=' . $row->id,
                        'title' => get_string('edit', 'artefact.studyjournal'))
                );

                if (isset($row->viewid)) {
                    $row->menuitems[] = array(
                        'url' => $wwwroot . 'view/access.php?id=' . $row->viewid . '&backto=artefact/studyjournal/student/',
                        // EKAMPUS >
                        'title' => get_string('editaccess',
                                'artefact.studyjournal'),
                        'classes' => 'editaccess',
                    );
                }
                // Journal hasn't been published yet, show a link which creates
                // a page and then jumps to access settings.
                else {
                    $row->menuitems[] = array(
                        'title' => get_string('editaccess',
                                'artefact.studyjournal'),
                        'url' => '#',
                        'classes' => 'create-view'
                    );
                }

                $row->menuitems[] = array(
                    'url' => '#',
                    'title' => get_string('deletejournal',
                            'artefact.studyjournal'),
                    'classes' => 'delete-journal'
                );
            }

            // Quick and dirty way to get the tags for each journal.
            $artefact_tags = array();

            if ($tags = ArtefactType::tags_from_id_list($journalids)) {
                foreach ($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($result as &$journal) {
                $tags = isset($artefact_tags[$journal->id]) ? $artefact_tags[$journal->id]
                            : array();
                $journal->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }
        }

        return $result;
    }

    public static function find_journal_publicity($journalid, &$journalviews,
                                                  &$accesslists) {
        $journalviewid = null;

        // Find the corresponding views for each journal.
        foreach ($journalviews as $viewid => $obj) {
            $cfg = unserialize($obj->configdata);

            if ($cfg['artefactid'] == $journalid) {
                $journalviewid = $viewid;
                break;
            }
        }

        // Selected journal isn't on any page.
        if (is_null($journalviewid)) {
            return self::PUBLICITY_PRIVATE;
        }

        return self::get_publicity_from_accesslists($accesslists, $journalviewid);
    }

    private static function get_publicity_from_accesslists(&$accesslists,
                                                           $viewid) {
        // Try to find the page from views first...
        if (is_array($accesslists['views']) && isset($accesslists['views'][$viewid])) {
            return self::get_publicity_from_view_or_collection($accesslists['views'][$viewid]);
        }

        // .. and then from collections.
        else if (is_array($accesslists['collections'])) {
            foreach ($accesslists['collections'] as $collection) {
                $in_collection = in_array($viewid,
                        array_keys($collection['views']));

                if ($in_collection) {
                    return self::get_publicity_from_view_or_collection($collection);
                }
            }
        }

        return self::PUBLICITY_PRIVATE;
    }

    private static function get_publicity_from_view_or_collection($item) {
        if (isset($item['accessgroups'])) {
            $accesstypes = array();

            foreach ($item['accessgroups'] as $group) {
                $accesstypes[] = $group['accesstype'];
            }

            if (in_array('public', $accesstypes)) {
                return self::PUBLICITY_PUBLIC;
            }
            else if (count(array_intersect(array('loggedin', 'group', 'friends',
                        'institution', 'user'), $accesstypes)) > 0) {
                return self::PUBLICITY_PUBLISHED;
            }
            // Just in case we ever need a separate status for secret urls.
            else if (in_array('token', $accesstypes)) {
                return self::PUBLICITY_PUBLISHED;
            }
        }

        return self::PUBLICITY_PRIVATE;
    }

    public static function get_shared_journals($query = '', $limit = null,
                                               $offset = 0,
                                               $sortby = 'lastchanged',
                                               $sortdir = 'desc', $share = null,
                                               $name = '') {
        global $USER;

        $ownedby = array();

        if (!empty($name)) {
            $users = self::find_users($name);
            $ownindex = array_search($USER->get('id'), $users);

            // Do not include own journals, remove own id from the array
            if ($ownindex !== false) {
                array_splice($users, $ownindex, 1);
            }

            // No results.
            if (count($users) === 0) {
                return (object) array('ids' => array(), 'data' => array(), 'count' => 0);
            }

            $ownedby['multiple'] = true;
            $ownedby['owner'] = $users;
        }
        else {
            $ownedby = array('exclude_owner' => $USER->get('id'));
        }

        $result = View::shared_to_user($query, null, $limit, $offset, $sortby,
                        $sortdir, $share, array('studyjournal'),
                        (object) $ownedby);

        return $result;
    }

    private static function find_users($query) {
        $like = db_ilike();
        $q = '%' . $query . '%';
        $res = get_column_sql("
            SELECT u.id
              FROM {usr} u
             WHERE u.deleted = 0 AND (
                u.firstname $like ? OR
                u.lastname $like ? OR
                u.preferredname $like ? OR
                CONCAT(u.firstname, ' ', u.lastname) $like ?
                )", array($q, $q, $q, $q));

        return (is_array($res) ? $res : array());
    }

    /**
     *
     * @global type $USER
     * @param type $title
     * @param array $fields
     * @param type $templateid
     * @param array $tags
     * @return type
     */
    public static function save_template($title, array $fields, $templateid = 0,
                                         array $tags = array()) {
        global $USER;

        $template = new ArtefactTypeStudyJournalTemplate($templateid);
        $template->set('title', $title);
        $template->set('tags', $tags);

        if ($templateid === 0) {
            $template->set('owner', $USER->id);
        }

        $template->commit();

        // TODO: Check that saving was successful.
        delete_records('artefact_study_journal_field', 'artefact',
                $template->get('id'));

        foreach ($fields as $field) {
            $template->add_field($field->value, $field->weight, $field->type);
        }

        return $template->get('id');
    }

    /**
     *
     * @global type $USER
     * @param type $id
     * @return type
     * @throws AccessDeniedException
     */
    public static function copy_template($id) {
        global $USER;

        $template = self::get_template($id);

        if (!$template->is_shared_to($USER)) {
            throw new AccessDeniedException('Cannot copy template.');
        }

        db_begin();
        $copyid = $template->copy_for_new_owner($USER->id, null, null);
        db_commit();

        return $copyid;
    }

    /**
     *
     * @param type $id
     * @return \ArtefactTypeStudyJournalTemplate
     */
    public static function get_template($id) {
        $template = new ArtefactTypeStudyJournalTemplate($id);

        return $template;
    }

    public static function get_journal($id) {
        $journal = new ArtefactTypeStudyjournal($id);
        return $journal;
    }

    /**
     *
     * @global type $USER
     * @param type $id
     * @return boolean
     * @throws AccessDeniedException
     */
    public static function delete_template($id) {
        global $USER;

        $template = new ArtefactTypeStudyJournalTemplate($id);

        if ($template->get('owner') != $USER->id) {
            throw new AccessDeniedException();
        }

        if ($template->is_shared()) {
            throw new Exception(get_string('cannotdeletetemplate', 'artefact.studyjournal'));
        }

        delete_records('artefact_study_journal_field', 'artefact',
                $template->get('id'));
        $template->delete();

        return true;
    }

    public static function delete_journal($journalid) {
        global $USER;

        $journal = self::get_journal($journalid);

        if ($journal->get('owner') != $USER->id) {
            throw new AccessDeniedException('Only own journals can be deleted');
        }

        $journalviews = self::get_journal_views($journalid);

        db_begin();

        if ($journalviews) {
            // First delete published views...
            foreach ($journalviews as $viewid) {
                $view = new View($viewid);
                $view->delete();
            }
        }

        // ... And then delete the journal itself.
        $journal->delete();

        db_commit();

        return true;
    }

    /**
     *
     * @param array $params
     */
    public static function display_edit_template(ArtefactTypeStudyJournalTemplate $template) {

        $wwwroot = get_config('wwwroot');
        $js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/studyjournal/js/templatecreator'], function (templatecreator) {
        templatecreator.init();
    });
});
JS;
        $template_exists = $template->get('id') > 0;
        $fieldhtml = '';
        $hiddentemplates = '';

        $sm = smarty_core();
        $sm2 = smarty_core();
        $sm2->assign('hidden', true);

        $hiddentemplates .= $sm2->fetch('artefact:studyjournal:templatefield/text.tpl');
        $hiddentemplates .= $sm2->fetch('artefact:studyjournal:templatefield/vibe.tpl');

        foreach ($template->get_fields() as $field) {
            $tplfile = 'artefact:studyjournal:templatefield/' . $field->type . '.tpl';
            $sm->assign('hidden', false);
            $sm->assign('value', $field->title);
            $fieldhtml .= $sm->fetch($tplfile);
        }

        $elements = array(
            'title' => array(
                'type' => 'text',
                'title' => get_string('journaltitle', 'artefact.studyjournal'),
                'rules' => array('required' => true),
                'defaultvalue' => $template->get('title')
            ),
            'fields' => array(
                'type' => 'html',
                'value' => '<div id="template-container">' . $fieldhtml . '</div>'
            ),
            'addfield' => array(
                'type' => 'button',
                'value' => '+ ' . get_string('addtemplatefield',
                        'artefact.studyjournal')
            ),
            'tags' => array(
                'type' => 'tags',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => $template->get('tags')
            )
        );

        if ($template_exists) {
            $elements['templateid'] = array(
                'type' => 'hidden',
                'value' => $template->get('id')
            );
        }

        $elements['hiddentemplates'] = array(
            'type' => 'html',
            'value' => $hiddentemplates
        );

        $buttonhtml = '<button class="btn" id="save-template">' .
                get_string('savetemplate', 'artefact.studyjournal') . '</button>';

//        if ($template_exists) {
//            $buttonhtml .= '<button class="ebtn" id="preview-template" data-toggle="modal" href="' .
//                $wwwroot . 'artefact/studyjournal/previewtemplate.php?id=' .
//                $template->get('id') . '" data-target="#template-preview-modal">' .
//                get_string('previewtemplate', 'artefact.studyjournal') . '</button>';
//        }

        $buttonhtml .= '<button class="ebtn" id="preview-template">' .
                get_string('previewtemplate', 'artefact.studyjournal') . '</button>';

        $buttonhtml .= '<a href="' . $wwwroot .
                'artefact/studyjournal/tutor/templates.php" class="btn">' .
                get_string('cancel') . '</a>';

        $elements['buttons'] = array(
            'type' => 'html',
            'value' => $buttonhtml
        );

        $form = pieform(array(
            'name' => 'studyjournaltemplate',
            'method' => 'post',
            'checkdirtychange' => false,
            'elements' => $elements
        ));

        $heading = get_string($template_exists ? 'edittemplate' : 'newtemplate',
                'artefact.studyjournal');
        $smarty = smarty(array($wwwroot . 'local/js/lib/require.js', 'lib/pieforms/static/core/pieforms.js',
            'tinymce'), array(),
                array(
            'artefact.studyjournal' => array('confirmremovefield', 'errormissingtemplatetitle',
                'templatesavingsuccessful', 'errornotemplatefieldsadded',
                'erroremptytemplatefield')
                ), array('sidebars' => false));
        $smarty->assign('PAGEHEADING', $heading);
        $smarty->assign('INLINEJAVASCRIPT', $js);
        $smarty->assign('form', $form);

        $smarty->display('artefact:studyjournal:edittemplate.tpl');
    }

    public static function publish_journal_form($journalid = null, array $config) {
        global $USER;
        $form = array(
            'name' => 'publishjournal',
            'method' => 'post',
            'plugintype' => 'core',
            'pluginname' => 'view',
            'renderer' => 'oneline',
            'successcallback' => 'PluginArtefactStudyJournal::publish_journal_submit',
            'elements' => array(
                'new' => array(
                    'type' => 'hidden',
                    'value' => true,
                ),
                'submitcollection' => array(
                    'type' => 'hidden',
                    'value' => false,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('createview', 'view'),
                ),
            )
        );
        //lets see is there any studyjournal type views which has the same studyjournal artefact on it
        $user_journal_pages = self::get_user_journal_views($USER->get('id'));

        require_once(get_config('docroot') . 'blocktype/lib.php');
        $journalviewurl = "";
        if ($user_journal_pages) {
            foreach ($user_journal_pages as $pages) {
                $configdata = unserialize($pages->configdata);
                //this shouldnt happen, but if there is more pages than 1 with this blog -> get just the latest page url
                if ($configdata['artefactid'] == $journalid) {
                    $journalviewurl = get_config('wwwroot') . "view/view.php?id=" . $pages->id;
                }
            }
        }

        $form['elements']['owner'] = array(
            'type' => 'hidden',
            'value' => $USER->get('id'),
        );
        if ($journalid !== null) {
            $form['elements']['journalid'] = array(
                'type' => 'hidden',
                'value' => $journalid,
            );
            if (!$journalviewurl) {
                $form['elements']['submit']['value'] = get_string('publishjournal',
                        'artefact.studyjournal');
                $form['name'] .= $journalid;
            }
            else {
                unset($form['elements']['submit']);
                $form['elements']['url'] = array(
                    'type' => 'markup',
                    'value' => '<a href="' . $journalviewurl . '" class="btn">' . get_string('viewjournalpage',
                            'artefact.studyjournal') . '</a>',
                );
            }
        }

        if ($config['journaltitle'] !== null) {
            $form['elements']['journaltitle'] = array(
                'type' => 'hidden',
                'value' => $config['journaltitle'],
            );
        }
        if ($config['description'] !== null) {
            $form['elements']['description'] = array(
                'type' => 'hidden',
                'value' => $config['description'],
            );
        }
        return $form;
    }

    /* creates a new page type=studyjournal and inserts the studyjournal into it like it would be from import */

    public static function publish_journal_submit(Pieform $form, $values) {
        $view = self::create_journal_view($values['journalid']);
        redirect(get_config('wwwroot') . 'view/access.php?id=' . $view->get('id') . '&backto=artefact/studyjournal/student/');
    }

    public static function create_journal_view($journalid) {
        global $USER;

        $journal = new ArtefactTypeStudyjournal($journalid);

        $userid = $journal->get('owner');

        if ($USER->get('id') !== $userid) {
            throw new AccessDeniedException('Only own journals can be published.');
        }

        if ($journalid) {
            $artefactid = $journalid;
        }

        $config = array(
            'title' => $journal->get('title'),
            'description' => $journal->get('description'),
            'type' => 'studyjournal',
            'layout' => '1',
            'approvecomments' => '1',
            'tags' => ArtefactType::artefact_get_tags($journalid),
            'numrows' => 1,
            'owner' => $userid,
            'ownerformat' => 6,
            'rows' => array(
                1 => array(
                    'columns' => array(
                        1 => array(
                            1 => array(
                                'type' => 'studyjournal',
                                'title' => '',
                                'config' => array(
                                    'artefactid' => $artefactid,
                                    'count' => '5',
                                    'copytype' => 'nocopy',
                                    'retractable' => false,
                                    'retractedonload' => false
                                )
                            )
                        )
                    )
                )
            )
        );
        $view = View::import_from_config($config, $userid, $format = '');

        return $view;
        //< EKAMPUS
        // redirect(get_config('wwwroot') . 'view/access.php?id=' . $view->get('id').'&backto=artefact/studyjournal/student/');
        // EKAMPUS >
//        PluginArtefactStudyJournal::update_journal_access($view);
    }

    public static function get_user_journal_views($userid = null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }
        if ($journalviews = get_records_sql_assoc(
                "SELECT v.id, b.id as 'bid', b.blocktype, b.configdata
            FROM {view} v
            LEFT JOIN {block_instance} b ON (v.id = b.view)
	    WHERE v.owner = ?
            AND v.type = ('studyjournal')
            ORDER BY v.id", array($userid))) {

            return $journalviews;
        }
        return array();
    }

    public static function get_journal_views($journalid) {
        global $USER;

        $ret = array();
        $journalviews = self::get_user_journal_views();

        foreach ($journalviews as $view) {
            $cfg = unserialize($view->configdata);

            if ($cfg['artefactid'] == $journalid) {
                $ret[] = $view->id;
            }
        }

        return $ret;
    }

}

class ArtefactTypeStudyJournalTemplate extends ArtefactType {

    protected $_field_cache = array();

    public static function get_icon($options = null) {

    }

    public static function get_links($id) {

    }

    public function get($field) {
        if ($field === 'title' && $this->get('note') === 'notemplate') {
            return get_string('notemplate', 'artefact.studyjournal');
        }

        return parent::get($field);
    }

    public function is_shared() {
        $sql = "
            SELECT SUM(cnt)
              FROM (
                SELECT COUNT(*) AS cnt
                  FROM {artefact_study_journal_group}
                 WHERE artefact = ?
                 UNION
                SELECT COUNT(*) AS cnt
                  FROM {artefact_study_journal_institution}
                 WHERE artefact = ?
                 ) tbl";

        return (count_records_sql($sql, array($this->get('id'), $this->get('id'))) > 0);
    }

    public static function is_singular() {
        return false;
    }

    public function cache_fields(array $fields) {
        $this->_field_cache = $fields;
    }

    public function copy_data() {
        $data = parent::copy_data();
        $data->title = get_string('copyoftemplate', 'artefact.studyjournal',
                $data->title);

        return $data;
    }

    public function copy_extra($new) {
        parent::copy_extra($new);

        $new->cache_fields($this->get_fields());
    }

    public function commit() {
        parent::commit();

        foreach ($this->_field_cache as $field) {
            $this->add_field($field->title, $field->weight, $field->type);
        }

        $this->_field_cache = array();
    }

    public function get_fields() {
        $fields = get_records_array('artefact_study_journal_field', 'artefact',
                $this->get('id'), 'weight ASC');
        return (is_array($fields) ? $fields : array());
    }

    public function add_field($title, $weight, $type) {
        insert_record('artefact_study_journal_field',
                (object) array(
                    'artefact' => $this->get('id'),
                    'title' => $title,
                    'weight' => $weight,
                    'type' => $type
        ));
    }

    public function is_copyable_by(User $user) {
        if ($this->owner == $user->id) {
            return true;
        }

        $is_copyable = record_exists_sql("
            SELECT a.id
              FROM {artefact} a
             WHERE a.id = ? AND a.artefacttype = 'studyjournaltemplate' AND a.owner IN (
                SELECT member
                  FROM {group_member}
                 WHERE role IN ('tutor', 'admin') AND `group` IN (
                    SELECT `group`
                      FROM {group_member}
                     WHERE role = 'member' AND member = ?
                )
            )", array($this->get('id'), $user->id));

        return $is_copyable;
    }

    public function give_access(array $groups, array $institutions) {
        db_begin();

        delete_records('artefact_study_journal_group', 'artefact', $this->id);
        delete_records('artefact_study_journal_institution', 'artefact',
                $this->id);

        // Add groups.
        foreach ($groups as $groupid) {
            insert_record('artefact_study_journal_group',
                    (object) array(
                        'artefact' => $this->id,
                        'group' => $groupid
            ));
        }

        // Add institutions.
        foreach ($institutions as $institution) {
            insert_record('artefact_study_journal_institution',
                    (object) array(
                        'artefact' => $this->id,
                        'institution' => $institution
            ));
        }

        db_commit();
    }

    public function get_group_access() {
        return get_records_sql_array("
                SELECT a.group, g.name
                  FROM {artefact_study_journal_group} a
             LEFT JOIN {group} g ON a.group = g.id
                 WHERE a.artefact = ?
              ORDER BY g.name ASC", array($this->id));
    }

    public function get_institution_access() {
        return get_records_sql_array("
            SELECT a.institution, i.displayname
              FROM {artefact_study_journal_institution} a
         LEFT JOIN {institution} i ON a.institution = i.name
             WHERE a.artefact = ?
          ORDER BY i.displayname ASC", array($this->id));
    }

    /**
     * Checks whether this template is shared with the selected user.
     *
     * @param User $user The user to test.
     * @return boolean Returns true if the template is shared to selected
     *      user, false otherwise.
     */
    public function is_shared_to(User $user) {
        // Empty template or owned by the user.
        if ($this->owner == 0 || $this->owner == $user->get('id')) {
            return true;
        }

        // TODO: DRY with get_student_template_list()
        return record_exists_sql("
            SELECT id
              FROM {artefact}
             WHERE artefacttype = 'studyjournaltemplate' AND
             (
                -- Institution access
                id IN
                (
                    SELECT artefact
                      FROM {artefact_study_journal_institution}
                     WHERE institution IN
                     (
                        SELECT institution
                          FROM {usr_institution}
                         WHERE usr = ?
                     )
                )

                -- Group access
                OR id IN
                (
                    SELECT artefact
                      FROM {artefact_study_journal_group}
                     WHERE `group` IN
                     (
                        SELECT `group`
                          FROM {group_member}
                         WHERE member = ?
                     )
                )
             )", array($user->get('id'), $user->get('id')));
    }

}

class ArtefactTypeStudyjournal extends ArtefactTypeStudyJournalTemplate {

    public function is_published() {
        $journalviews = PluginArtefactStudyJournal::get_journal_views($this->id);

        return (count($journalviews) > 0);
    }

    public function get_total_entries() {
        return count_records('artefact', 'artefacttype', 'studyjournalentry',
                'parent', $this->get('id'));
    }

    /**
     *
     * @param type $limit
     * @param type $offset
     * @param type $date
     * @return ArtefactTypeStudyJournalEntry[]
     */
    public function get_entries($limit = 5, $offset = 0, $date = null,
                                $viewid = null) {
        $sql = "
            SELECT a.*
              FROM {artefact} a
             WHERE a.artefacttype = 'studyjournalentry' AND a.parent = ?";
        $params = array($this->get('id'));

        // TODO: validate date!
        if (!is_null($date)) {
            $sql .= " AND DATE(ctime) = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY a.ctime DESC";
        $result = get_records_sql_array($sql, $params, $offset, $limit);

        $ret = array();

        if (is_array($result)) {
            // Get the attached files.
            $entryids = array_map(create_function('$a', 'return $a->id;'),
                    $result);
            $files = ArtefactType::attachments_from_id_list($entryids);
            $filearr = array();

            if ($files) {
                safe_require('artefact', 'file');
                foreach ($files as &$file) {
                    $options = array('id' => $file->attachment);

                    if (!is_null($viewid)) {
                        $options['viewid'] = $viewid;
                    }

                    $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype),
                            'get_icon', $options);


                    if (!isset($filearr[$file->artefact])) {
                        $filearr[$file->artefact] = array();
                    }

                    $filearr[$file->artefact][] = $file;
                }
            }

            foreach ($result as &$item) {
                $entry = new ArtefactTypeStudyJournalEntry($item->id, $item);

                if (isset($filearr[$item->id])) {
                    $entry->set_files($filearr[$item->id]);
                }

                $ret[] = $entry;
            }
        }

        return $ret;
    }

    public function get_entries_by_date($date, $viewid = null) {
        return $this->get_entries(null, null, $date, $viewid);
    }

    public function get_entry_dates() {
        $result = get_records_sql_array("
            SELECT COUNT(*) AS cnt, DATE(ctime) AS date
              FROM {artefact}
             WHERE artefacttype = 'studyjournalentry' AND parent = ?
          GROUP BY DATE(ctime)
          ORDER BY DATE(ctime) DESC", array($this->get('id')));

        $ret = array();

        if (is_array($result)) {
            foreach ($result as $row) {
                $ret[$row->date] = format_date(strtotime($row->date),
                                'strftimedate') . ' (' . $row->cnt . ')';
            }
        }

        return $ret;
    }

    public function delete() {
        // Rows from artefact_study_journal_field need to be removed after the
        // entry rows are deleted but BEFORE the artefact is removed. Thus this
        // closure.
        parent::delete(function ($artefact) {
            delete_records('artefact_study_journal_field', 'artefact',
                    $artefact->get('id'));
        });
    }

    public static function get_journal_tags() {
        global $USER;

        return get_column_sql("
            SELECT DISTINCT(tag)
              FROM {artefact_tag}
             WHERE artefact IN (
                SELECT id
                  FROM {artefact}
                 WHERE owner = ? AND artefacttype = ?
             )
          ORDER BY tag COLLATE utf8_swedish_ci ASC", array($USER->id, 'studyjournal'));
    }

}

class ArtefactTypeStudyJournalEntry extends ArtefactType {

    private $artefact_cache = null;
    private $file_cache = null;

    public function display_title($maxlen = null) {
        $title = parent::display_title($maxlen);

        if (empty($title)) {
            $title = get_string('noentrytitle', 'artefact.studyjournal');
        }

        return $title;
    }

    public function delete() {
        delete_records('artefact_study_journal_entry_collection', 'artefact',
                $this->get('id'));
        delete_records('artefact_study_journal_entry_view', 'artefact',
                $this->get('id'));
        delete_records('artefact_study_journal_entry_value', 'artefact',
                $this->get('id'));

        parent::delete();
    }

    public function add_field_value($fieldid, $value) {
        insert_record('artefact_study_journal_entry_value',
                (object) array(
                    'artefact' => $this->get('id'),
                    'study_journal_field' => $fieldid,
                    'value' => $value
        ));
    }

    public function get_postdate($format = 'strftimedaydatetime') {
        return format_date($this->get('ctime', $format));
    }

    public function get_fields($viewid = null) {
        $fields = $this->get_parent_instance()->get_fields();
        $values = get_records_sql_array("
            SELECT study_journal_field, value
              FROM {artefact_study_journal_entry_value}
             WHERE artefact = ?", array($this->get('id')));

        safe_require('artefact', 'file');

        foreach ($fields as &$field) {
            foreach ($values as $val) {
                if ($val->study_journal_field == $field->id) {
                    $field->value = $val->value;
                    break;
                }
            }

            if (!empty($viewid)) {
                $field->value = ArtefactTypeFolder::append_view_url($field->value,
                                $viewid);
            }
        }

        return $fields;
    }

    public function attach_collection($collectionid) {
        insert_record('artefact_study_journal_entry_collection',
                (object) array(
                    'artefact' => $this->get('id'),
                    'collection' => $collectionid
        ));
    }

    public function attach_view($viewid) {
        insert_record('artefact_study_journal_entry_view',
                (object) array(
                    'artefact' => $this->get('id'),
                    'view' => $viewid
        ));
    }

    public function has_artefacts() {
        if (is_null($this->artefact_cache)) {
            $this->refresh_artefact_cache();
        }

        return (count($this->artefact_cache['collections']) > 0 || count($this->artefact_cache['views'])
                > 0);
    }

    private function refresh_artefact_cache() {
        require_once('collection.php');

        $res = get_records_sql_array("
            SELECT ac.collection AS id, 'collections' AS type, c.name AS name
              FROM {artefact_study_journal_entry_collection} ac
         LEFT JOIN {collection} c ON ac.collection = c.id
             WHERE ac.artefact = ?
             UNION
            SELECT av.view AS id, 'views' AS type, v.title AS name
              FROM {artefact_study_journal_entry_view} av
         LEFT JOIN {view} v ON av.view = v.id
             WHERE av.artefact = ?", array($this->get('id'), $this->get('id')));

        $this->artefact_cache = array('collections' => array(), 'views' => array());

        if (is_array($res)) {
            foreach ($res as $item) {
                $this->artefact_cache[$item->type][$item->id] = array('id' => $item->id,
                    'name' => $item->name);

                // PENDING: No db queries in a loop.
                if ($item->type === 'collections') {
                    $coll = new Collection($item->id);
                    $this->artefact_cache[$item->type][$item->id]['url'] = $coll->get_url();
                }
                else {
                    $this->artefact_cache[$item->type][$item->id]['url'] = get_config('wwwroot') . 'view/view.php?id=' . $item->id;
                }
            }
        }
    }

    public function has_collections() {
        if (is_null($this->artefact_cache)) {
            $this->refresh_artefact_cache();
        }

        return count($this->artefact_cache['collections']) > 0;
    }

    public function has_views() {
        if (is_null($this->artefact_cache)) {
            $this->refresh_artefact_cache();
        }

        return count($this->artefact_cache['views']) > 0;
    }

    public function has_files() {
        if (is_null($this->file_cache)) {
            $this->refresh_file_cache();
        }

        return count($this->file_cache) > 0;
    }

    public function refresh_file_cache() {
        $this->file_cache = $this->get_attachments();
    }

    public function get_collections() {
        if (is_null($this->artefact_cache)) {
            $this->refresh_artefact_cache();
        }

        return $this->artefact_cache['collections'];
    }

    public function get_views() {
        if (is_null($this->artefact_cache)) {
            $this->refresh_artefact_cache();
        }

        return $this->artefact_cache['views'];
    }

    public function render_self($options) {
        $smarty = smarty_core();
        $smarty->assign('entry', $this);

        return array('html' => $smarty->fetch('artefact:studyjournal:entry.tpl'),
            'javascript' => '');
    }

    public function get_artefact_stylesheets() {
        return array('artefact/studyjournal/theme/raw/static/style/style.css');
    }

    public static function get_icon($options = null) {

    }

    public static function get_links($id) {

    }

    public static function is_singular() {
        return false;
    }

    public function set_files(array $files) {
        $this->file_cache = $files;
    }

    public function get_files() {
        return $this->file_cache;
    }

}
