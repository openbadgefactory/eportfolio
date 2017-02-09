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

function xmldb_artefact_studyjournal_upgrade($oldversion = 0) {
    if ($oldversion < 2014060401) {
        drop_table(new XMLDBTable('artefact_study_journal_entry_value'));
        drop_table(new XMLDBTable('artefact_study_journal_field'));

        install_from_xmldb_file(get_config('docroot') .
                'artefact/studyjournal/db/install.xml'
        );
    }

    if ($oldversion < 2014061000) {
        if (is_mysql()) {
            execute_sql('ALTER TABLE {artefact_study_journal_field} MODIFY title TEXT NOT NULL');
        }
        else {
            execute_sql('ALTER TABLE {artefact_study_journal_field} ALTER COLUMN title TEXT NOT NULL');
        }
    }

    if ($oldversion < 2014061100) {
        drop_table(new XMLDBTable('artefact_study_journal_entry_value'));
        drop_table(new XMLDBTable('artefact_study_journal_entry'));
        drop_table(new XMLDBTable('artefact_study_journal_field'));

        install_from_xmldb_file(get_config('docroot') .
                'artefact/studyjournal/db/install.xml'
        );
    }
    if ($oldversion < 2014061600) {
        execute_sql("INSERT INTO {view_type} (type) VALUES ('studyjournal')");
        execute_sql("INSERT INTO {blocktype_installed_viewtype} (blocktype, viewtype)
                    VALUES ('studyjournal', 'studyjournal')");
    }

    if ($oldversion < 2014061800) {
        $vtable = new XMLDBTable('artefact_study_journal_entry_view');
        $vtable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $vtable->addFieldInfo('view', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $vtable->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $vtable->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $vtable->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('artefact', 'view'));

        if (!create_table($vtable)) {
            throw new SQLException($vtable . " could not be created, check log for errors.");
        }

        $ctable = new XMLDBTable('artefact_study_journal_entry_collection');
        $ctable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $ctable->addFieldInfo('collection', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $ctable->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $ctable->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN, array('collection'), 'collection', array('id'));
        $ctable->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('artefact', 'collection'));

        if (!create_table($ctable)) {
            throw new SQLException($ctable . " could not be created, check log for errors.");
        }
    }

    if ($oldversion < 2014080100) {
        insert_record('blocktype_category', (object) array(
            'name' => 'studyjournal',
            'sort' => 6
        ));
    }

    if ($oldversion < 2014082000) {
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
        insert_record('artefact_study_journal_field',
                (object) array(
                    'artefact' => $id,
                    'title' => '',
                    'weight' => 0,
                    'type' => 'text',
                ));
    }

    if ($oldversion < 2014101500) {
        $gtable = new XMLDBTable('artefact_study_journal_group');
        $gtable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $gtable->addFieldInfo('group', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $gtable->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $gtable->addKeyInfo('groupfk', XMLDB_KEY_FOREIGN, array('group'), 'group', array('id'));
        $gtable->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('artefact', 'group'));

        create_table($gtable, false);

        $itable = new XMLDBTable('artefact_study_journal_institution');
        $itable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $itable->addFieldInfo('institution', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $itable->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $itable->addKeyInfo('instfk', XMLDB_KEY_FOREIGN, array('institution'), 'institution', array('name'));
        $itable->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('artefact', 'institution'));

        create_table($itable, false);
    }

    if ($oldversion < 2014102800) {
        // Institution and locked columns are no longer used. Reset the values
        // to avoid any problems in future.
        execute_sql("
            UPDATE {artefact}
               SET institution = NULL, locked = 0
             WHERE artefacttype = 'studyjournaltemplate'");
    }

    return true;
}
