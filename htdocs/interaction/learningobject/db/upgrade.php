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

function xmldb_interaction_learningobject_upgrade($oldversion = 0) {
    if ($oldversion < 2014112700) {
        $table = new XMLDBTable('interaction_learningobject_collection_parent');
        $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addFieldInfo('parent', XMLDB_TYPE_INTEGER, 10, null, null);
        $table->addKeyInfo('collectionfk', XMLDB_KEY_FOREIGN,
                array('collection'), 'collection', array('id'));
        $table->addKeyInfo('parentfk', XMLDB_KEY_FOREIGN, array('parent'),
                'collection', array('id'));
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY,
                array('collection', 'parent'));

        create_table($table, false);
    }

    if ($oldversion < 2014112702) {
        $table = new XMLDBTable('interaction_learningobject_collection_parent');
        $key1 = new XMLDBKey('collectionfk');
        $key1->setAttributes(XMLDB_KEY_FOREIGN, array('collection'),
                'collection', array('id'));
        $key2 = new XMLDBKey('parentfk');
        $key2->setAttributes(XMLDB_KEY_FOREIGN, array('parent'), 'collection',
                array('id'));

        if (!drop_key($table, $key1, false)) {
            throw new SQLException('Could not drop foreign key. See logs for errors.');
        }

        if (!drop_key($table, $key2, false)) {
            throw new SQLException('Could not drop foreign key. See logs for errors');
        }

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
    }

    if ($oldversion < 2014120400) {
        create_assignee_table('user', 'usr');
        create_assignee_table('group', 'group');
        create_assignee_table('institution', 'institution', 'name',
                XMLDB_TYPE_CHAR, 255);

        $table = new XMLDBTable('interaction_learningobject_assignment_instructor');
        $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('user', XMLDB_TYPE_INTEGER, 10);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('collection', 'user'));

        if (!create_table($table)) {
            throw new SQLException('Couldn\'t create table for assignment instructors.');
        }

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
    }

    if ($oldversion < 2014120800) {
        //Create returned_view table
        $returnedview = new XMLDBTable('interaction_learningobject_returned_view');
        $returnedview->addFieldInfo('viewid', XMLDB_TYPE_INTEGER, 10);
        $returnedview->addFieldInfo('first_return_date', XMLDB_TYPE_DATETIME);
        $returnedview->addFieldInfo('prev_return_date', XMLDB_TYPE_DATETIME);
        $returnedview->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('viewid'));

        if (!create_table($returnedview, false)) {
            throw new SQLException('Couldn\'t create table for learningobjects: interaction_learningobject_returned_view');
        }

        execute_sql(
            "ALTER TABLE {interaction_learningobject_returned_view}
            ADD CONSTRAINT FOREIGN KEY viewfk (viewid)
            REFERENCES {view} (id) ON DELETE CASCADE"
        );
        //Create returned_collection table
        $returnedcollection = new XMLDBTable('interaction_learningobject_returned_collection');
        $returnedcollection->addFieldInfo('collectionid', XMLDB_TYPE_INTEGER, 10);
        $returnedcollection->addFieldInfo('first_return_date', XMLDB_TYPE_DATETIME);
        $returnedcollection->addFieldInfo('prev_return_date', XMLDB_TYPE_DATETIME);
        $returnedcollection->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('collectionid'));

        if (!create_table($returnedcollection, false)) {
            throw new SQLException('Couldn\'t create table for learningobjects: interaction_learningobject_returned_collection');
        }
        execute_sql(
            "ALTER TABLE {interaction_learningobject_returned_collection}
            ADD CONSTRAINT FOREIGN KEY collectionfk (collectionid)
            REFERENCES {collection} (id) ON DELETE CASCADE"
        );
        //Create returned_view_instructor table
        $table1 = new XMLDBTable('interaction_learningobject_returned_view_instructor');
        $table1->addFieldInfo('view', XMLDB_TYPE_INTEGER, 10);
        $table1->addFieldInfo('user', XMLDB_TYPE_INTEGER, 10);
        $table1->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('view', 'user'));

        if (!create_table($table1)) {
            throw new SQLException('Couldn\'t create table interaction_learningobject_returned_view_instructor.');
        }

        // Add cascading rules to returned_view instructors.
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
        //Create returned_collection_instructor table
        $table2 = new XMLDBTable('interaction_learningobject_returned_collection_instructor');
        $table2->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10);
        $table2->addFieldInfo('user', XMLDB_TYPE_INTEGER, 10);
        $table2->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('collection', 'user'));

        if (!create_table($table2)) {
            throw new SQLException('Couldn\'t create table for interaction_learningobject_returned_collection_instructor.');
        }

        // Add cascading rules to returned_collection instructors.
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

    return true;
}

function create_assignee_table($type, $reftable, $refcolumn = 'id', $coltype = XMLDB_TYPE_INTEGER, $precision = 10) {
    $table = new XMLDBTable('interaction_learningobject_assigned_' . $type);
    $table->addFieldInfo('collection', XMLDB_TYPE_INTEGER, 10);
    $table->addFieldInfo($type, $coltype, $precision);
    $table->addFieldInfo('assignment_date', XMLDB_TYPE_DATETIME);
    $table->addFieldInfo('is_assigned', XMLDB_TYPE_INTEGER, 1, null, null, null, null, null, 0);
    $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('collection', $type));

    if (!create_table($table, false)) {
        throw new SQLException('Couldn\'t create table for assignees (' . $type . ')');
    }

    execute_sql("
            ALTER TABLE {interaction_learningobject_assigned_$type}
         ADD CONSTRAINT FOREIGN KEY collectionfk (collection)
             REFERENCES {collection} (id) ON DELETE CASCADE"
    );

    $fkname = $type . "fk";

    execute_sql("
            ALTER TABLE {interaction_learningobject_assigned_$type}
         ADD CONSTRAINT FOREIGN KEY $fkname (`$type`)
             REFERENCES {" . $reftable . "} ($refcolumn) ON DELETE CASCADE"
    );
}