<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_multiresume_upgrade($oldversion=0) {

    if ($oldversion < 2014080600) {
        execute_sql("INSERT INTO {view_type} (type) VALUES ('multiresume')");
        execute_sql("INSERT INTO {blocktype_installed_viewtype} (blocktype, viewtype)
                    VALUES ('entiremultiresume', 'multiresume')");
    }
    if ($oldversion < 2014081400) {
        execute_sql("INSERT INTO {blocktype_installed_viewtype} (blocktype, viewtype)
                    VALUES ('openbadgedisplayer', 'multiresume')");
    }

    return true;
}
