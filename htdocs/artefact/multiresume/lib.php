<?php

defined('INTERNAL') || die();

require_once('activity.php');

class PluginArtefactMultiResume extends PluginArtefact {

    public static function get_artefact_types() {
        return array('multiresume');
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'MultiResume';
    }

    public static function menu_items() {
        return array(
            'multiresume' => array(
                'path' => 'myportfolio/multiresume',
                'url' => 'artefact/multiresume/',
                'title' => get_string('multiresume', 'artefact.multiresume'),
                'weight' => 50,
            ),
        );
    }

    public static function postinst($fromversion) {
        if ($fromversion == 0) {
            // New view type (multiresume).
	  $vtobj = (object) array('type' => 'multiresume');

	  ensure_record_exists('view_type', $vtobj, $vtobj);

//	    $obj = (object) array(
//				  'blocktype' => 'entiremultiresume',
//				  'viewtype' => 'multiresume'
//				  );

//            ensure_record_exists('blocktype_installed_viewtype', $obj, $obj);

            // Allow badges in multiresume view.
//	    $obj->blocktype = 'openbadgedisplayer';
//            ensure_record_exists('blocktype_installed_viewtype', $obj, $obj);
        }
    }

}


class ArtefactTypeMultiResume extends ArtefactType {

    public function render_self($options) {
        return '';
    }

    public static function get_icon($options=null) { }

    public static function is_singular() {
        return false;
    }

    public static function get_links($id) { }

    public static function get_tags() {
        global $USER;

        $res = get_column_sql("
            SELECT DISTINCT(tag)
              FROM {artefact_tag}
             WHERE artefact IN (
                SELECT id
                  FROM {artefact}
                 WHERE owner = ? AND artefacttype = ?
             )
          ORDER BY tag COLLATE utf8_swedish_ci ASC", array($USER->id, 'multiresume'));

        return is_array($res) ? $res : array();
    }

    public function delete() {
        if (empty($this->id)) {
            return;
        }
        delete_records('artefact_multiresume_field', 'artefact', $this->id);
        parent::delete();
    }

    public function copy_extra($new) {
        $new->set('title', get_string('Copyof', 'mahara', $this->get('title')));
        $new->commit();
        $id = $new->get('id');
        $source = get_records_array('artefact_multiresume_field', 'artefact', $this->id);
        foreach ($source AS $rec) {
            unset($rec->id);
            $rec->artefact = $id;
            insert_record('artefact_multiresume_field', $rec);
        }
    }

    public function get_referenced_artefacts($fieldid=null) {
        $artefacts = array();
        if (is_null($fieldid)) {
            $content = get_column('artefact_multiresume_field', 'value', 'artefact', $this->id);
        }
        else {
            $content = get_column('artefact_multiresume_field', 'value', 'id', $fieldid);
        }
        foreach ($content AS $data) {
            $artefacts = array_merge($artefacts, artefact_get_references_in_html($data));
        }
        return $artefacts;
    }

    public static function new_resume($user, $values) {
        db_begin();

        $language = $dblang = $values['language'];
        if ($language == 'other') {
            $language = $values['otherlanguage'];
            $dblang = 'en.utf8';
        }

        $artefact = new ArtefactTypeMultiResume();
        $artefact->set('title', $values['title']);
        $artefact->set('description', $language);
        $artefact->set('tags', $values['tags']);
        $artefact->set('owner', $user->get('id'));
        $artefact->commit();

        $aid = $artefact->id;

        if (@$values['copyresume'] && isset($values['copyable'])) {
            if ($values['copyable'] == 'oldresume') {
                self::copy_old_resume($aid, $dblang);
            }
            else {
                self::copy_multiresume($values['copyable'], $aid, $dblang);
            }
        }
        else {
            // Generate new
            $pos = 0;
            $rec = new stdClass();
            $rec->artefact = $aid;

            $rec->title = get_string_from_language($dblang, 'coverletter', 'artefact.resume');
            $rec->value = new MultiResumeField();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'personalinformation', 'artefact.resume');
            $rec->value = new MultiResumePersonalInformation();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'educationhistory', 'artefact.resume');
            $rec->value = new MultiResumeEducation();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'employmenthistory', 'artefact.resume');
            $rec->value = new MultiResumeEmployment();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'certification', 'artefact.resume');
            $rec->value = new MultiResumeCertification();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'book', 'artefact.resume');
            $rec->value = new MultiResumeBook();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $rec->title = get_string_from_language($dblang, 'membership', 'artefact.resume');
            $rec->value = new MultiResumeMembership();
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);

            $titles = array(
                'personalgoal', 'academicgoal', 'careergoal',
                'personalskill', 'academicskill', 'workskill',
                'interests'
            );
            foreach ($titles AS $title) {
                $rec->title = get_string_from_language($dblang, $title, 'artefact.resume');
                $rec->value = new MultiResumeField();
                $rec->order = $pos++;
                insert_record('artefact_multiresume_field', $rec);
            }
        }

        db_commit();
        return $aid;
    }

    public static function copy_multiresume($from, $to, $dblang) {
        // If source cv has default field titles, we can try to translate them.
        $available = get_languages();
        $fromlang = get_field('artefact', 'description', 'id', $from);
        $langmap = array();
        if (isset($available[$fromlang])) {
            $titles = array(
                'personalgoal', 'academicgoal', 'careergoal', 'personalskill', 'academicskill', 'workskill',
                'interests', 'coverletter', 'personalinformation', 'educationhistory', 'employmenthistory',
                'certification', 'book', 'membership'
            );
            foreach ($titles AS $t) {
                $langmap[ get_string_from_language($fromlang, $t, 'artefact.resume') ] = get_string_from_language($dblang, $t, 'artefact.resume');
            }
        }

        $source = get_records_array('artefact_multiresume_field', 'artefact', $from);
        foreach ($source AS $rec) {
            unset($rec->id);
            $rec->artefact = $to;
            if (isset($langmap[$rec->title])) {
                $rec->title = $langmap[$rec->title];
            }
            insert_record('artefact_multiresume_field', $rec);
        }
    }

    public static function copy_old_resume($target, $lang) {
        global $USER;

        $pos = 0;
        $rec = new stdClass();
        $rec->artefact = $target;

        db_begin();

        $rec->title = get_string_from_language($lang, 'coverletter', 'artefact.resume');
        $rec->value = new MultiResumeField();
        $rec->value->content = get_field('artefact', 'description', 'artefacttype', 'coverletter', 'owner', $USER->id);
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'personalinformation', 'artefact.resume');
        $personal = get_record_sql(
            "SELECT i.* FROM {artefact_resume_personal_information} i
            INNER JOIN {artefact} a ON i.artefact = a.id WHERE a.owner = ?", array($USER->id)
        );
        $rec->title = get_string_from_language($lang, 'personalinformation', 'artefact.resume');
        $rec->value = new MultiResumePersonalInformation();
        if (!empty($personal)) {
            unset($personal->artefact);
            foreach ($personal AS $key => $value) {
                if ($key == 'gender') {
                    $value = get_string_from_language($lang, $value, 'artefact.resume');
                }
                $rec->value->{$key} = $value;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'educationhistory', 'artefact.resume');
        $rec->value = new MultiResumeEducation();
        $rows = get_records_sql_array(
            "SELECT e.startdate, e.enddate, e.qualtype, e.qualname, e.institution, e.qualdescription AS qualdesc, e.institutionaddress
            FROM {artefact_resume_educationhistory} e INNER JOIN {artefact} a ON e.artefact = a.id
            WHERE a.owner = ? ORDER BY e.displayorder", array($USER->id)
        );
        if (!empty($rows)) {
            foreach ($rows AS $r) {
                $row = new MultiResumeEducationRow();
                foreach ($r AS $key => $value) {
                    $row->{$key} = $value;
                }
                $rec->value->rows[] = $row;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'employmenthistory', 'artefact.resume');
        $rec->value = new MultiResumeEmployment();
        $rows = get_records_sql_array(
            "SELECT e.startdate, e.enddate, e.employer, e.jobtitle, e.positiondescription AS jobdesc, e.employeraddress
            FROM {artefact_resume_employmenthistory} e INNER JOIN {artefact} a ON e.artefact = a.id
            WHERE a.owner = ? ORDER BY e.displayorder", array($USER->id)
        );
        if (!empty($rows)) {
            foreach ($rows AS $r) {
                $row = new MultiResumeEmploymentRow();
                foreach ($r AS $key => $value) {
                    $row->{$key} = $value;
                }
                $rec->value->rows[] = $row;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'certification', 'artefact.resume');
        $rec->value = new MultiResumeCertification();
        $rows = get_records_sql_array(
            "SELECT c.date, c.title, c.description
            FROM {artefact_resume_certification} c INNER JOIN {artefact} a ON c.artefact = a.id
            WHERE a.owner = ? ORDER BY c.displayorder", array($USER->id)
        );
        if (!empty($rows)) {
            foreach ($rows AS $r) {
                $row = new MultiResumeCertificationRow();
                foreach ($r AS $key => $value) {
                    $row->{$key} = $value;
                }
                $rec->value->rows[] = $row;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'book', 'artefact.resume');
        $rec->value = new MultiResumeBook();
        $rows = get_records_sql_array(
            "SELECT b.date, b.title, b.description, b.contribution
            FROM {artefact_resume_book} b INNER JOIN {artefact} a ON b.artefact = a.id
            WHERE a.owner = ? ORDER BY b.displayorder", array($USER->id)
        );
        if (!empty($rows)) {
            foreach ($rows AS $r) {
                $row = new MultiResumeBookRow();
                foreach ($r AS $key => $value) {
                    $row->{$key} = $value;
                }
                $rec->value->rows[] = $row;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $rec->title = get_string_from_language($lang, 'membership', 'artefact.resume');
        $rec->value = new MultiResumeMembership();
        $rows = get_records_sql_array(
            "SELECT m.startdate, m.enddate, m.title, m.description
            FROM {artefact_resume_membership} m INNER JOIN {artefact} a ON m.artefact = a.id
            WHERE a.owner = ? ORDER BY m.displayorder", array($USER->id)
        );
        if (!empty($rows)) {
            foreach ($rows AS $r) {
                $row = new MultiResumeMembershipRow();
                foreach ($r AS $key => $value) {
                    $row->{$key} = $value;
                }
                $rec->value->rows[] = $row;
            }
        }
        $rec->order = $pos++;
        insert_record('artefact_multiresume_field', $rec);

        /* * * * */

        $titles = array(
            'personalgoal', 'academicgoal', 'careergoal',
            'personalskill', 'academicskill', 'workskill',
            'interests'
        );
        foreach ($titles AS $title) {
            $rec->title = get_string_from_language($lang, $title, 'artefact.resume');
            $rec->value = new MultiResumeField();
            $title = str_replace('interests', 'interest', $title);
            $rec->value->content = get_field('artefact', 'description', 'artefacttype', $title, 'owner', $USER->id);
            $rec->order = $pos++;
            insert_record('artefact_multiresume_field', $rec);
        }

        /* * * * */

        $userdefined = get_records_sql_array(
            "SELECT title, description FROM {artefact} WHERE artefacttype = 'userdefined' AND owner = ?", array($USER->id)
        );
        if (!empty($userdefined)) {
            foreach ($userdefined AS $from) {
                $rec->title = $from->title;
                $rec->value = new MultiResumeField();
                $rec->value->content = $from->description;
                $rec->order = $pos++;
                insert_record('artefact_multiresume_field', $rec);
            }
        }

        db_commit();
    }

    public function edit_form() {
        global $USER;

        $lang = $this->description;
        $available = get_languages();
        if (!isset($available[$lang])) {
            $lang = 'en.utf8';
        }

        $rows = get_records_sql_array(
            "SELECT mr.* FROM {artefact_multiresume_field} mr
            INNER JOIN {artefact} a ON mr.artefact = a.id
            WHERE a.id = ? AND a.owner = ? ORDER BY mr.order",
            array($this->id, $USER->id)
        );
        $form = $js = '';
        if (!empty($rows)) {
            foreach ($rows AS $row) {
                $obj = unserialize($row->value);
                if (is_object($obj)) {
                    $form .= pieform($obj->edit_form($row->id, $row->title, $row->order, $lang)) . "\n\n";
                    $js   .= $obj->edit_js();
                }
            }
        }
        return array($form, $js);
    }

    public static function get_resumes(User $user = null) {
        if (empty($user)) {
            global $USER;
            $user = $USER;
        }

        $resumes = get_records_sql_array("
                SELECT *
                  FROM {artefact}
                 WHERE artefacttype = ? AND owner = ?
              ORDER BY title ASC", array('multiresume', $user->id));

        if (!empty($resumes)) {

            $langs = get_languages();
            $wwwroot = get_config('wwwroot');
            $resumeviews = self::get_user_resume_views($user->id);
            $accesslists = View::get_accesslists($user->id, null, null, array('multiresume'));
            $resumeids = array();

            foreach ($resumes AS &$r) {
                $resumeids[] = $r->id;

                if (isset($langs[$r->description])) {
                    $r->description = $langs[$r->description];
                }

                $r->publicity = find_artefact_publicity($r->id, $resumeviews, $accesslists);
                $r->view = find_artefact_view($r->id, $resumeviews);
                $r->menuitems = array(
                    array(
                        'title' => get_string('edittitleandlang', 'artefact.multiresume'),
                        'url' => $wwwroot . 'artefact/multiresume/settings.php?id=' . $r->id,
                    ));

                // CV is published (page has been created), show link to
                // access settings.
                if ($r->view) {
                    $r->menuitems[] = array(
                        'title' => get_string('editaccess', 'view'),
                        'url' => $wwwroot . 'view/access.php?id=' . $r->view.'&backto=artefact/multiresume',
                        'classes' => 'editaccess',
                    );
                }
                // CV hasn't been published yet, show a link which creates
                // a page and then jumps to access settings.
                else {
                    $r->menuitems[] = array(
                        'title' => get_string('editaccess', 'view'),
                        'url' => '#',
                        'classes' => 'create-view'
                    );
                }

                $r->menuitems[] =
                    array(
                        'title' => get_string('delete'),
                        'url' => $wwwroot . 'artefact/multiresume/delete.php?id=' . $r->id
                    );
            }

            // Quick and dirty way to get the tags for each resume.
            $artefact_tags = array();

            if ($tags = ArtefactType::tags_from_id_list($resumeids)) {
                foreach($tags as $at) {
                    if (!isset($artefact_tags[$at->artefact])) {
                        $artefact_tags[$at->artefact] = array();
                    }

                    $artefact_tags[$at->artefact][] = $at->tag;
                }
            }

            foreach ($resumes as &$resume) {
                $tags = isset($artefact_tags[$resume->id]) ? $artefact_tags[$resume->id] : array();
                $resume->jsontags = json_encode($tags, JSON_HEX_QUOT);
            }
        }

        return $resumes;
    }

    public static function get_user_resume_views($userid = null) {
        if (is_null($userid)) {
            global $USER;
            $userid = $USER->get('id');
        }

        $resumeviews = get_records_sql_assoc("
            SELECT v.id, b.id as 'bid', b.blocktype, b.configdata
              FROM {view} v
         LEFT JOIN {block_instance} b ON (v.id = b.view)
	     WHERE v.owner = ? AND v.type = ?
          ORDER BY v.id", array($userid, 'multiresume'));

        return is_array($resumeviews) ? $resumeviews : array();
    }

    // <EKAMPUS
    /**
     * Creates a new page type=multiresume and inserts the entireresumefield
     * into it like it would be from import, then opens the blog page for
     * editing
     */
    public static function create_cv_view($resumeid) {
        global $USER;

        $multiresume = new ArtefactTypeMultiResume($resumeid);
        $owner = $multiresume->get('owner');

        if ($USER->get('id') !== $owner) {
            throw new AccessDeniedException('Only own resumes can be published.');
        }

        $titlem = isset($multiresume->title) ? $multiresume->title : get_string('multiresume',
                        'artefact.multiresume');
        $config = array();

        foreach (get_languages() AS $lang => $title) {
            $langrep = str_replace('.', '_', $lang);
            if ($multiresume->description == $lang) {
                $config['title_' . $langrep] = '';
                $config['langmap'][$langrep] = 0;
                $config['artefactids'][0] = $multiresume->id;
            }
            else {
                $config['title_' . $langrep] = '';
            }
        }

        // This is some special language CV -> Put it under english cv.
        if (!isset($configdata['langmap'])) {
            $config['langmap']['en_utf8'] = 0;
            $config['artefactids'][0] = $multiresume->id;
        }

        $config['copytype'] = 'nocopy';
        $configs = array(
            'title' => $titlem,
            'description' => '',
            'type' => 'multiresume',
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
                                'type' => 'entiremultiresume',
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

    // EKAMPUS >
}


class MultiResumeField {

    public $content;

    public function edit_form($id, $title, $order, $lang) {
        $elem = array();

        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );

        $elem['content'] = array(
            'type' => 'wysiwyg',
            'cols' => 60,
            'rows' => 10,
            //'height' => '500px',
            //'width' => '100%',
            'defaultvalue' => $this->content,
        );

        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));

        return array(
            'name' => 'resumefield_' . $id,
            'plugintype' => 'artefact',
            'pluginname' => 'multiresume',
            'jsform' => false,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'section' => array(
                    'type'         => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => true,
                    'legend' => $title,
                    'elements' => $elem
                )
            )
        );
    }

    public function edit_js() { }

    public function update_self($values) {
        $this->content = $values['content'];
    }

    public function to_html() {
        return clean_html($this->content);
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumePersonalInformation {
    public $dateofbirth;
    public $placeofbirth;
    public $citizenship;
    public $visastatus;
    public $gender;
    public $maritalstatus;

    public function edit_form($id, $title, $order, $lang) {

        return array(
            'name'        => 'personalinformation_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'resume',
            'jsform'      => false,
            'elements'    => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'personalinfomation' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => true,
                    'legend' => $title,
                    'elements' => array(
                        'title' => array(
                            'type' => 'text',
                            'title' => get_string('title', 'group'),
                            'defaultvalue' => $title
                        ),
                        'dateofbirth' => array(
                            'type'       => 'text',
                            'defaultvalue' => $this->dateofbirth,
                            'title' => get_string('dateofbirth', 'artefact.resume'),
                        ),
                        'placeofbirth' => array(
                            'type' => 'text',
                            'defaultvalue' => $this->placeofbirth,
                            'title' => get_string('placeofbirth', 'artefact.resume'),
                            'size' => 30,
                        ),
                        'citizenship' => array(
                            'type' => 'text',
                            'defaultvalue' => $this->citizenship,
                            'title' => get_string('citizenship', 'artefact.resume'),
                            'size' => 30,
                        ),
                        'visastatus' => array(
                            'type' => 'text',
                            'defaultvalue' => $this->visastatus,
                            'title' => get_string('visastatus', 'artefact.resume'),
                            'size' => 30,
                        ),
                        'gender' => array(
                            'type' => 'text',
                            'defaultvalue' => $this->gender,
                            'title' => get_string('gender', 'artefact.resume'),
                            'size' => 30,
                        ),
                        'maritalstatus' => array(
                            'type' => 'text',
                            'defaultvalue' => $this->maritalstatus,
                            'title' => get_string('maritalstatus', 'artefact.resume'),
                            'size' => 30,
                        ),
                        'submit' => array('type' => 'submit', 'value' => get_string('save'))
                    ),
                ),
            ),
        );
    }

    public function edit_js() { }

    public function update_self($values) {

        $this->dateofbirth   = $values['dateofbirth'];
        $this->placeofbirth  = $values['placeofbirth'];
        $this->citizenship   = $values['citizenship'];
        $this->visastatus    = $values['visastatus'];
        $this->gender        = $values['gender'];
        $this->maritalstatus = $values['maritalstatus'];
    }

    public function to_html($lang) {
        $fields = array();
        foreach ($this AS $key => $value) {
            $fields[get_string_from_language($lang, $key, 'artefact.resume')] = $value;
        }
        $smarty = smarty_core();
        $smarty->assign('fields', $fields);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/personalinformation.tpl');
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumeEducation {
    public $rows;

    public function edit_form($id, $title, $order, $lang) {

        $elem = array();

        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('edittitle', 'artefact.multiresume'));

        $smarty = smarty_core();
        $smarty->assign('rows', $this->rows);
        $smarty->assign('cv', param_integer('id'));
        $smarty->assign('id', $id);
//        $smarty->assign('lang', $lang);
        $smarty->assign('lang', current_language());
        $elem['rowsection'] = array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:multiresume:educationrows.tpl')
        );


        return array(
            'name'        => 'education_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'multiresume',
            'jsform'      => false,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'educationfs' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => param_integer('open', 0) != $id,
                    'legend' => $title,
                    'elements' => $elem,
                ),
            ),
        );
    }

    public function edit_row_form($row_id, $lang) {
        $row = new MultiResumeEducationRow();
        if (!is_null($row_id)) {
            $row = $this->rows[$row_id];
        }

        $elem['section'] = array('type' => 'hidden', 'value' => 'education');
        $elem['row_id'] = array('type' => 'hidden', 'value' => $row_id);

        $elem['startdate'] = array(
            'type'  => 'text',
            'title' => get_string('startdate', 'artefact.resume'),
            'defaultvalue' => $row->startdate,
        );
        $elem['enddate'] = array(
            'type'  => 'text',
            'title' => get_string('enddate', 'artefact.resume'),
            'defaultvalue' => $row->enddate,
        );
        $elem['institution'] = array(
            'type'  => 'text',
            'title' => get_string('institution', 'artefact.resume'),
            'defaultvalue' => $row->institution,
        );
        /*
        $elem['institution_address'] = array(
            'type'  => 'text',
            'title' => get_string_from_language($lang, 'institutionaddress', 'artefact.resume'),
            'defaultvalue' => $row->institution_address,
        );
         */
        $elem['qualtype'] = array(
            'type'  => 'text',
            'title' => get_string('qualtype', 'artefact.resume'),
            'defaultvalue' => $row->qualtype,
        );
        $elem['qualname'] = array(
            'type'  => 'text',
            'title' => get_string('qualname', 'artefact.resume'),
            'defaultvalue' => $row->qualname,
        );
        $elem['qualdesc'] = array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 60,
            'title' => get_string('qualdescription', 'artefact.resume'),
            'defaultvalue' => $row->qualdesc,
        );

        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));

        return $elem;
    }

    public function edit_js() { }

    public function update_self($values) {

        if (isset($values['title'])) {
            return; // not for us
        }

        if (isset($values['row_id'])) {
            $order = $values['row_id'];
            $row = $this->rows[$order];
        }
        else {
            $order = count($this->rows);
            $row = new MultiResumeEducationRow();
        }
        $row->startdate = $values['startdate'];
        $row->enddate = $values['enddate'];
        $row->institution = $values['institution'];
        //$row->institution_address = $values['institution_address'];
        $row->qualtype = $values['qualtype'];
        $row->qualname = $values['qualname'];
        $row->qualdesc = $values['qualdesc'];

        $this->rows[$order] = $row;
    }

    public function to_html($lang, $rows=null) {
        if (empty($this->rows)) {
            return;
        }
        $smarty = smarty_core();
        $smarty->assign('startdate', get_string_from_language($lang, 'startdate', 'artefact.resume'));
        $smarty->assign('enddate', get_string_from_language($lang, 'enddate', 'artefact.resume'));
        $smarty->assign('qualification', get_string_from_language($lang, 'qualification', 'artefact.resume'));

        $showrows = $this->rows;
        if (!is_null($rows)) {
            $showrows = array();
            foreach ($rows AS $r) {
                $showrows[] = $this->rows[$r];
            }
        }
        $smarty->assign('rows', $showrows);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/educationhistory.tpl');
    }

    public function __toString() { return serialize($this); }
}

class MultiResumeEducationRow {

    public $startdate;
    public $enddate;
    public $institution;
    public $qualtype;
    public $qualname;
    public $qualdesc;
    public $institutionaddress;

    public function qualification() {
        $qualification = '';
        if (strlen($this->qualname) && strlen($this->qualtype)) {
            $qualification = $this->qualname. ' (' . $this->qualtype . ') - ';
        }
        else if (strlen($this->qualtype)) {
            $qualification = $this->qualtype . ' '; // . $at . ' ';
        }
        else if (strlen($this->qualname)) {
            $qualification = $this->qualname . ' '; // . $at . ' ';
        }
        $qualification .= $this->institution;
        return $qualification;
    }

    public function rowtitle() {
        return $this->startdate . ' - ' . $this->enddate . ' ' . $this->qualification();
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumeEmployment {
    public $rows;

    public function edit_form($id, $title, $order, $lang) {
        $elem = array();
        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('edittitle', 'artefact.multiresume'));

        $smarty = smarty_core();
        $smarty->assign('rows', $this->rows);
        $smarty->assign('cv', param_integer('id'));
        $smarty->assign('id', $id);
        $smarty->assign('lang', current_language());
        $elem['rowsection'] = array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:multiresume:employmentrows.tpl')
        );

        return array(
            'name'        => 'employment_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'multiresume',
            'jsform'      => false,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'employmentfs' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => param_integer('open', 0) != $id,
                    'legend' => $title,
                    'elements' => $elem,
                ),
            ),
        );
    }

    public function edit_js() { }

    public function edit_row_form($row_id, $lang) {
        $row = new MultiResumeEmploymentRow();
        if (!is_null($row_id)) {
            $row = $this->rows[$row_id];
        }

        $elem['section'] = array('type' => 'hidden', 'value' => 'employment');
        $elem['row_id'] = array('type' => 'hidden', 'value' => $row_id);

        $elem['startdate'] = array(
            'type'  => 'text',
            'title' => get_string('startdate', 'artefact.resume'),
            'defaultvalue' => $row->startdate,
        );
        $elem['enddate'] = array(
            'type'  => 'text',
            'title' => get_string('enddate', 'artefact.resume'),
            'defaultvalue' => $row->enddate,
        );
        $elem['employer'] = array(
            'type'  => 'text',
            'title' => get_string('employer', 'artefact.resume'),
            'defaultvalue' => $row->employer,
        );
        /*
        $elem['employeraddress'] = array(
            'type'  => 'text',
            'title' => get_string_from_language($lang, 'employeraddress', 'artefact.resume'),
            'defaultvalue' => $row->employeraddress,
        );
         */
        $elem['jobtitle'] = array(
            'type'  => 'text',
            'title' => get_string('jobtitle', 'artefact.resume'),
            'defaultvalue' => $row->jobtitle,
        );
        $elem['jobdesc'] = array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 60,
            'title' => get_string('positiondescription', 'artefact.resume'),
            'defaultvalue' => $row->jobdesc,
        );

        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));


        return $elem;
    }

    public function update_self($values) {
        if (isset($values['title'])) {
            return; // not for us
        }

        if (isset($values['row_id'])) {
            $order = $values['row_id'];
            $row = $this->rows[$order];
        }
        else {
            $order = count($this->rows);
            $row = new MultiResumeEmploymentRow();
        }
        $row->startdate = $values['startdate'];
        $row->enddate = $values['enddate'];
        $row->employer = $values['employer'];
        //$row->employeraddress = $values['employer_address'];
        $row->jobtitle = $values['jobtitle'];
        $row->jobdesc = $values['jobdesc'];

        $this->rows[$order] = $row;
    }

    public function to_html($lang, $rows=null) {
        if (empty($this->rows)) {
            return;
        }
        $smarty = smarty_core();
        $smarty->assign('startdate', get_string_from_language($lang, 'startdate', 'artefact.resume'));
        $smarty->assign('enddate', get_string_from_language($lang, 'enddate', 'artefact.resume'));
        $smarty->assign('position', get_string_from_language($lang, 'position', 'artefact.resume'));

        $showrows = $this->rows;
        if (!is_null($rows)) {
            $showrows = array();
            foreach ($rows AS $r) {
                $showrows[] = $this->rows[$r];
            }
        }
        $smarty->assign('rows', $showrows);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/employmenthistory.tpl');
    }

    public function __toString() { return serialize($this); }
}

class MultiResumeEmploymentRow {

    public $startdate;
    public $enddate;
    public $employer;
    public $employeraddress;
    public $jobtitle;
    public $jobdesc;



    public function rowtitle() {
        return $this->startdate . ' - ' . $this->enddate . ' ' . $this->jobtitle;
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumeCertification {
    public $rows;

    public function edit_form($id, $title, $order, $lang) {
        $elem = array();
        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('edittitle', 'artefact.multiresume'));

        $smarty = smarty_core();
        $smarty->assign('rows', $this->rows);
        $smarty->assign('cv', param_integer('id'));
        $smarty->assign('id', $id);
        $smarty->assign('lang', $lang);
        $elem['rowsection'] = array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:multiresume:certificationrows.tpl')
        );

        return array(
            'name'        => 'certification_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'multiresume',
            'jsform'      => false,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'certfs' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => param_integer('open', 0) != $id,
                    'legend' => $title,
                    'elements' => $elem,
                ),
            ),
        );

    }

    public function edit_row_form($row_id, $lang) {
        $row = new MultiResumeCertificationRow();
        if (!is_null($row_id)) {
            $row = $this->rows[$row_id];
        }

        $elem['section'] = array('type' => 'hidden', 'value' => 'certification');
        $elem['row_id'] = array('type' => 'hidden', 'value' => $row_id);

        $elem['date'] = array(
            'type'  => 'text',
            'title' => get_string('date', 'artefact.resume'),
            'defaultvalue' => $row->date,
        );
        $elem['rowtitle'] = array(
            'type'  => 'text',
            'title' => get_string('title', 'artefact.resume'),
            'defaultvalue' => $row->title,
        );
        $elem['description'] = array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 60,
            'title' => get_string('description', 'artefact.resume'),
            'defaultvalue' => $row->description,
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));

        return $elem;
    }

    public function edit_js() { }

    public function update_self($values) {
        if (isset($values['title'])) {
            return; // not for us
        }

        if (isset($values['row_id'])) {
            $order = $values['row_id'];
            $row = $this->rows[$order];
        }
        else {
            $order = count($this->rows);
            $row = new MultiResumeCertificationRow();
        }
        $row->date        = $values['date'];
        $row->title       = $values['rowtitle'];
        $row->description = $values['description'];

        $this->rows[$order] = $row;
    }

    public function to_html($lang, $rows=null) {
        if (empty($this->rows)) {
            return;
        }
        $smarty = smarty_core();
        $smarty->assign('date', get_string_from_language($lang, 'date', 'artefact.resume'));
        $smarty->assign('title', get_string_from_language($lang, 'title', 'artefact.resume'));

        $showrows = $this->rows;
        if (!is_null($rows)) {
            $showrows = array();
            foreach ($rows AS $r) {
                $showrows[] = $this->rows[$r];
            }
        }
        $smarty->assign('rows', $showrows);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/certification.tpl');
    }

    public function __toString() { return serialize($this); }
}

class MultiResumeCertificationRow {

    public $date;
    public $title;
    public $description;

    public function rowtitle() {
        return $this->date . ' ' . $this->title;
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumeBook {
    public $rows;

    public function edit_form($id, $title, $order, $lang) {
        $elem = array();
        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('edittitle', 'artefact.multiresume'));

        $smarty = smarty_core();
        $smarty->assign('rows', $this->rows);
        $smarty->assign('cv', param_integer('id'));
        $smarty->assign('id', $id);
        $smarty->assign('lang', $lang);
        $elem['rowsection'] = array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:multiresume:bookrows.tpl')
        );

        return array(
            'name'        => 'book_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'multiresume',
            'jsform'      => false,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'bookfs' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => param_integer('open', 0) != $id,
                    'legend' => $title,
                    'elements' => $elem,
                ),
            ),
        );
    }

    public function edit_row_form($row_id, $lang) {
        $row = new MultiResumeBookRow();
        if (!is_null($row_id)) {
            $row = $this->rows[$row_id];
        }

        $elem['section'] = array('type' => 'hidden', 'value' => 'book');
        $elem['row_id'] = array('type' => 'hidden', 'value' => $row_id);

        $elem['date'] = array(
            'type'  => 'text',
            'title' => get_string('date', 'artefact.resume'),
            'defaultvalue' => $row->date,
        );
        $elem['rowtitle'] = array(
            'type'  => 'text',
            'title' => get_string('title', 'artefact.resume'),
            'defaultvalue' => $row->title,
        );
        $elem['contribution'] = array(
            'type'  => 'text',
            'title' => get_string('contribution', 'artefact.resume'),
            'defaultvalue' => $row->contribution,
        );
        $elem['description'] = array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 60,
            'title' => get_string('description', 'artefact.resume'),
            'defaultvalue' => $row->description,
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));

        return $elem;
    }

    public function edit_js() { }

    public function update_self($values) {
        if (isset($values['title'])) {
            return; // not for us
        }

        if (isset($values['row_id'])) {
            $order = $values['row_id'];
            $row = $this->rows[$order];
        }
        else {
            $order = count($this->rows);
            $row = new MultiResumeBookRow();
        }
        $row->date         = $values['date'];
        $row->title        = $values['rowtitle'];
        $row->contribution = $values['contribution'];
        $row->description  = $values['description'];

        $this->rows[$order] = $row;
    }

    public function to_html($lang, $rows=null) {
        if (empty($this->rows)) {
            return;
        }
        $smarty = smarty_core();
        $smarty->assign('date', get_string_from_language($lang, 'date', 'artefact.resume'));
        $smarty->assign('title', get_string_from_language($lang, 'title', 'artefact.resume'));

        $showrows = $this->rows;
        if (!is_null($rows)) {
            $showrows = array();
            foreach ($rows AS $r) {
                $showrows[] = $this->rows[$r];
            }
        }
        $smarty->assign('rows', $showrows);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/book.tpl');
    }

    public function __toString() { return serialize($this); }
}

class MultiResumeBookRow {

    public $date;
    public $title;
    public $contribution;
    public $description;

    public function rowtitle() {
        return $this->date . ' ' . $this->title;
    }

    public function __toString() { return serialize($this); }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

class MultiResumeMembership {
    public $rows;

    public function edit_form($id, $title, $order, $lang) {
        $elem = array();
        $elem['title'] = array(
            'type' => 'text',
            'title' => get_string('title', 'group'),
            'defaultvalue' => $title
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('edittitle', 'artefact.multiresume'));

        $smarty = smarty_core();
        $smarty->assign('rows', $this->rows);
        $smarty->assign('cv', param_integer('id'));
        $smarty->assign('id', $id);
        $smarty->assign('lang', $lang);
        $elem['rowsection'] = array(
            'type' => 'html',
            'value' => $smarty->fetch('artefact:multiresume:membershiprows.tpl')
        );

        return array(
            'name'        => 'membership_'. $id,
            'plugintype'  => 'artefact',
            'pluginname'  => 'multiresume',
            'jsform'      => false,
            'class'       => $id,
            'elements' => array(
                'id' => array('type' => 'hidden', 'value' => $id),
                'membershipfs' => array(
                    'type' => 'fieldset',
                    'collapsible'  => true,
                    'collapsed'    => param_integer('open', 0) != $id,
                    'legend' => $title,
                    'elements' => $elem,
                ),
            ),
        );
    }

    public function edit_js() { }

    public function edit_row_form($row_id, $lang) {
        $row = new MultiResumeMembershipRow();
        if (!is_null($row_id)) {
            $row = $this->rows[$row_id];
        }

        $elem['section'] = array('type' => 'hidden', 'value' => 'membership');
        $elem['row_id'] = array('type' => 'hidden', 'value' => $row_id);

        $elem['startdate'] = array(
            'type'  => 'text',
            'title' => get_string('startdate', 'artefact.resume'),
            'defaultvalue' => $row->startdate,
        );
        $elem['enddate'] = array(
            'type'  => 'text',
            'title' => get_string('enddate', 'artefact.resume'),
            'defaultvalue' => $row->enddate,
        );
        $elem['rowtitle'] = array(
            'type'  => 'text',
            'title' => get_string('title', 'artefact.resume'),
            'defaultvalue' => $row->title,
        );
        $elem['description'] = array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 60,
            'title' => get_string('description', 'artefact.resume'),
            'defaultvalue' => $row->description,
        );
        $elem['submit'] = array('type' => 'submit', 'value' => get_string('save'));

        return $elem;
    }

    public function update_self($values) {
        if (isset($values['title'])) {
            return; // not for us
        }

        if (isset($values['row_id'])) {
            $order = $values['row_id'];
            $row = $this->rows[$order];
        }
        else {
            $order = count($this->rows);
            $row = new MultiResumeMembershipRow();
        }
        $row->startdate   = $values['startdate'];
        $row->enddate     = $values['enddate'];
        $row->title        = $values['rowtitle'];
        $row->description  = $values['description'];

        $this->rows[$order] = $row;
    }

    public function to_html($lang, $rows=null) {
        if (empty($this->rows)) {
            return;
        }
        $smarty = smarty_core();
        $smarty->assign('startdate', get_string_from_language($lang, 'startdate', 'artefact.resume'));
        $smarty->assign('enddate', get_string_from_language($lang, 'enddate', 'artefact.resume'));
        $smarty->assign('title', get_string_from_language($lang, 'title', 'artefact.resume'));

        $showrows = $this->rows;
        if (!is_null($rows)) {
            $showrows = array();
            foreach ($rows AS $r) {
                $showrows[] = $this->rows[$r];
            }
        }
        $smarty->assign('rows', $showrows);
        $smarty->assign('suffix', mt_rand(99,9999));
        return $smarty->fetch('artefact:multiresume:fragments/membership.tpl');
    }

    public function __toString() { return serialize($this); }
}

class MultiResumeMembershipRow {

    public $startdate;
    public $enddate;
    public $title;
    public $description;

    public function rowtitle() {
        return $this->startdate . ' - ' . $this->enddate . ' ' . $this->title;
    }

    public function __toString() { return serialize($this); }
}

