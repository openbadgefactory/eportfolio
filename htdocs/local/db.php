<?php

if (@$_GET['RUN']) {
    define('INTERNAL', 1);
    define('ADMIN', 1);

    require_once('../init.php');
    require_once('ddl.php');

    local_db_modify();

    $SESSION->add_ok_msg("DB schema synced successfully.");

    redirect('/index.php');
}

function local_db_modify() {

    $tables = get_column_sql("SHOW TABLES");

    if (!in_array('view_writeaccess', $tables)) {
        log_info('CREATE TABLE view_writeaccess');

        execute_sql("
            CREATE TABLE `view_writeaccess` (
                `view` bigint(10) NOT NULL,
                `startdate` datetime DEFAULT NULL,
                `stopdate` datetime DEFAULT NULL,
                `group` bigint(10) DEFAULT NULL,
                `role` varchar(255) DEFAULT NULL,
                `usr` bigint(10) DEFAULT NULL,
                `visible` tinyint(1) NOT NULL DEFAULT '1',
                KEY `wviewacce_vie_ix` (`view`),
                KEY `wviewacce_gro_ix` (`group`),
                KEY `wviewacce_usr_ix` (`usr`),
                CONSTRAINT `wviewacce_gro_fk` FOREIGN KEY (`group`) REFERENCES `group` (`id`) ON DELETE CASCADE,
                CONSTRAINT `wviewacce_usr_fk` FOREIGN KEY (`usr`) REFERENCES `usr` (`id`) ON DELETE CASCADE,
                CONSTRAINT `wviewacce_vie_fk` FOREIGN KEY (`view`) REFERENCES `view` (`id`) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");

        // Insert group members as editors for existing group views.
        $rows = get_records_sql_array(
            "SELECT id AS view, `group` FROM {view} WHERE `group` IS NOT NULL", array()
        );
        if (!empty($rows)) {
            db_begin();
            foreach ($rows AS $r) {
                $r->visible = 1;
                $r->role = 'member';
                insert_record('view_writeaccess', $r);
            }
            db_commit();
        }
    }


    if (!in_array('our_resourcetree_folder', $tables)) {
        log_info('CREATE TABLE our_resourcetree_folder');

        execute_sql("
            CREATE TABLE `our_resourcetree_folder` (
                `id` bigint(10) AUTO_INCREMENT PRIMARY KEY,
                `type` varchar(255) NOT NULL,
                `title` varchar(255) NOT NULL,
                `usr` bigint(10) DEFAULT NULL,
                `group` bigint(10) DEFAULT NULL,
                `parent` bigint(10) DEFAULT NULL,
                KEY `restreef_type_ix` (`type`),
                KEY `restreef_gro_ix` (`group`),
                KEY `restreef_usr_ix` (`usr`),
                KEY `restreef_parent_ix` (`parent`),
                CONSTRAINT `restreef_gro_fk` FOREIGN KEY (`group`) REFERENCES `group` (`id`) ON DELETE CASCADE,
                CONSTRAINT `restreef_usr_fk` FOREIGN KEY (`usr`) REFERENCES `usr` (`id`) ON DELETE CASCADE,
                CONSTRAINT `restreef_parent_fk` FOREIGN KEY (`parent`) REFERENCES `our_resourcetree_folder` (`id`) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    if (!in_array('our_resourcetree', $tables)) {
        log_info('CREATE TABLE our_resourcetree');

        execute_sql("
            CREATE TABLE `our_resourcetree` (
                `type` varchar(255) NOT NULL,
                `usr` bigint(10) DEFAULT NULL,
                `group` bigint(10) DEFAULT NULL,
                `folder` bigint(10) DEFAULT NULL,
                `resource` bigint(10) NOT NULL,
                KEY `restree_type_ix` (`type`),
                KEY `restree_gro_ix` (`group`),
                KEY `restree_usr_ix` (`usr`),
                KEY `restree_folder_ix` (`folder`),
                CONSTRAINT `restree_gro_fk` FOREIGN KEY (`group`) REFERENCES `group` (`id`) ON DELETE CASCADE,
                CONSTRAINT `restree_usr_fk` FOREIGN KEY (`usr`) REFERENCES `usr` (`id`) ON DELETE CASCADE,
                CONSTRAINT `restree_folder_fk` FOREIGN KEY (`folder`) REFERENCES `our_resourcetree_folder` (`id`) ON DELETE CASCADE
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    if (!in_array('group_tag', $tables)){
        log_info('CREATE TABLE group_tag');

        execute_sql("
                CREATE TABLE `group_tag` (
                `group` bigint(10) NOT NULL,
                `usr` bigint(10) NOT NULL,
                `tag` varchar(128) NOT NULL,
                PRIMARY KEY (`group`, `usr`, `tag`),
                KEY `grouptag_gro_ix` (`group`),
                KEY `grouptag_usr_ix` (`usr`),
                CONSTRAINT `grouptag_gro_fk` FOREIGN KEY (`group`) REFERENCES `group` (`id`) ON DELETE CASCADE,
                CONSTRAINT `grouptag_usr_fk` FOREIGN KEY (`usr`) REFERENCES `usr` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    $table = new XMLDBTable('view');
    $field = new XMLDBField('title');
    $field->setAttributes(XMLDB_TYPE_CHAR, '255', null, true);

    change_field_type($table, $field);
//    change_field_type($table, $field);
}

