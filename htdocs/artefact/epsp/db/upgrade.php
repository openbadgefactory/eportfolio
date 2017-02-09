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

function xmldb_artefact_epsp_upgrade($oldversion = 0) {

    if ($oldversion < 2015011900) {
        execute_sql("
            ALTER TABLE {artefact_epsp_block_field}
                    ADD type VARCHAR(50) NOT NULL");
    }

    if ($oldversion < 2015012701) {
        execute_sql("
            ALTER TABLE {artefact_epsp_block_field}
                    ADD artefact BIGINT(10) NOT NULL"
        );

        execute_sql("
            UPDATE {artefact_epsp_block_field} f
               SET artefact = (
                    SELECT artefact
                      FROM {artefact_epsp_block}
                     WHERE id = f.block
                )"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_block_field}
         ADD CONSTRAINT FOREIGN KEY artefactfk (artefact)
             REFERENCES {artefact} (id) ON DELETE CASCADE"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_block_field}
       DROP FOREIGN KEY artefact_epsp_block_field_ibfk_1"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_block_field}
            DROP COLUMN block"
        );

        drop_table(new XMLDBTable('artefact_epsp_block'), false);
    }

    if ($oldversion < 2015012702) {
        rename_table(new XMLDBTable('artefact_epsp_block_field'),
                'artefact_epsp_field', false);
    }

    if ($oldversion < 2015012703) {
        // Access table for users.
        $table = new XMLDBTable('artefact_epsp_user');
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('user', XMLDB_TYPE_INTEGER, 10);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY,
                array('artefact', 'user'));

        if (!create_table($table, false)) {
            throw new SQLException('Couldn\'t create ePSP table for users.');
        }

        execute_sql("
            ALTER TABLE {artefact_epsp_user}
         ADD CONSTRAINT FOREIGN KEY artefactfk (artefact)
             REFERENCES {artefact} (id) ON DELETE CASCADE"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_user}
         ADD CONSTRAINT FOREIGN KEY usrfk (user)
             REFERENCES {usr} (id) ON DELETE CASCADE"
        );

        // Access table for groups.
        $gtable = new XMLDBTable('artefact_epsp_group');
        $gtable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10);
        $gtable->addFieldInfo('group', XMLDB_TYPE_INTEGER, 10);
        $gtable->addKeyInfo('primary', XMLDB_KEY_PRIMARY,
                array('artefact', 'group'));

        if (!create_table($gtable, false)) {
            throw new SQLException('Couldn\'t create ePSP table for groups.');
        }

        execute_sql("
            ALTER TABLE {artefact_epsp_group}
         ADD CONSTRAINT FOREIGN KEY artefactfk (artefact)
             REFERENCES {artefact} (id) ON DELETE CASCADE"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_group}
         ADD CONSTRAINT FOREIGN KEY groupfk (`group`)
             REFERENCES {group} (id) ON DELETE CASCADE"
        );

        // Access table for institutions.
        $itable = new XMLDBTable('artefact_epsp_institution');
        $itable->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10);
        $itable->addFieldInfo('institution', XMLDB_TYPE_CHAR, 255);
        $itable->addKeyInfo('primary', XMLDB_KEY_PRIMARY,
                array('artefact', 'institution'));

        if (!create_table($itable, false)) {
            throw new SQLException('Couldn\'t create ePSP table for institutions.');
        }

        execute_sql("
            ALTER TABLE {artefact_epsp_institution}
         ADD CONSTRAINT FOREIGN KEY artefactfk (artefact)
             REFERENCES {artefact} (id) ON DELETE CASCADE"
        );

        execute_sql("
            ALTER TABLE {artefact_epsp_institution}
         ADD CONSTRAINT FOREIGN KEY instfk (institution)
             REFERENCES {institution} (name) ON DELETE CASCADE"
        );
    }

    if ($oldversion < 2015020201) {
        // New view type.
        $obj = (object) array('type' => 'epsp');
        ensure_record_exists('view_type', $obj, $obj);
    }

    if ($oldversion < 2015021600) {
        insert_record('blocktype_category', (object) array(
            'name' => 'epsp',
            'sort' => 7
        ));
    }

    if ($oldversion < 2015022300) {
        $table = new XMLDBTable('artefact_epsp_field');
        $field = new XMLDBField('id');

        drop_field($table, $field, false);
    }

    if ($oldversion < 2015022301) {
        $table = new XMLDBTable('artefact_epsp_field');
        $key = new XMLDBKey('fieldpk');
        $key->setAttributes(XMLDB_KEY_PRIMARY, array('artefact'));

        add_key($table, $key, false);
    }

    if ($oldversion < 2015022303) {
        $table = new XMLDBTable('artefact_epsp_field');
        $field = new XMLDBField('title');

        drop_field($table, $field, false);
    }

    if ($oldversion < 2015042101) {
        $obj = (object) array(
            'name' => 'epsp',
            'sort' => 7
        );

        ensure_record_exists('blocktype_category', $obj, $obj);
    }

    // Once again, add the #!?#* category to database if it somehow doesn't
    // exist there yet.
    if ($oldversion < 2015042300) {
        $obj = (object) array(
            'name' => 'epsp',
            'sort' => 7
        );

        ensure_record_exists('blocktype_category', $obj, $obj);
    }

    return true;
}
