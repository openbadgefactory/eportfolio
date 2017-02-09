<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_comment_upgrade($oldversion=0) {

    $success = true;

    if ($oldversion < 2011011201) {
        $table = new XMLDBTable('artefact_comment_comment');
        $field = new XMLDBField('rating');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED);

        $success = $success && add_field($table, $field);
    }

    if ($oldversion < 2013072400) {
        $table = new XMLDBTable('artefact_comment_comment');
        $field = new XMLDBField('lastcontentupdate');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        $success = $success && add_field($table, $field);

        $success = $success && execute_sql(
            'update {artefact_comment_comment} acc
            set lastcontentupdate = (
                select a.mtime
                from {artefact} a
                where a.id = acc.artefact
            )'
        );
    }
    //< EKAMPUS Adding thumbup field for likes in a page
    if ($oldversion < 2014061200) {
        execute_sql("ALTER TABLE {artefact_comment_comment} ADD {thumbup} TINYINT NULL DEFAULT NULL");
    }
    
    // Drop thumb field and create a separate table for likes.
    if ($oldversion < 2014101000) {
        drop_field(new XMLDBTable('artefact_comment_comment'),
                new XMLDBField('thumbup'), false);
        $table = new XMLDBTable('artefact_comment_view_user_like');
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, '10', null, null);
        $table->addFieldInfo('liked', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL,
                null, null, null, '0');
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        $table->addKeyInfo('likepk', XMLDB_KEY_PRIMARY, array('view', 'usr'));
        
        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
    }
    
    // Make user field not mandatory in likes. We have to drop the whole table
    // because for some reason changing NOT NULL to NULL doesn't work.
    if ($oldversion < 2014101302) {
        drop_table(new XMLDBTable('artefact_comment_view_user_like'), false);
        
        $table = new XMLDBTable('artefact_comment_view_user_like');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true);
        $table->addFieldInfo('view', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, '10', null, null, null, null,
                null, null);
        $table->addFieldInfo('liked', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL,
                null, null, null, '0');
        $table->addKeyInfo('likepk', XMLDB_KEY_PRIMARY, array('id'));
        $table->addKeyInfo('viewfk', XMLDB_KEY_FOREIGN, array('view'), 'view', array('id'));
        $table->addKeyInfo('usrfk', XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        
        if (!create_table($table)) {
            throw new SQLException($table . " could not be created, check log for errors.");
        }
    }
    if($oldversion < 2014121601) {
        execute_sql("ALTER TABLE {artefact_comment_comment} ADD viewid BIGINT(10) NULL,
                    ADD FOREIGN KEY  {artecommcomm_vid_fk}(viewid) REFERENCES {view}(id) ON DELETE CASCADE");
    }
    
    // EKAMPUS >
    return $success;
}
