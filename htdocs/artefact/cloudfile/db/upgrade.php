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
 * @subpackage artefact-cloudfile
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
defined('INTERNAL') || die();

function xmldb_artefact_cloudfile_upgrade($oldversion=0) {
    $success = true;

    // Google deprecated the Document API. Let's get rid of old Drive file
    // references.
    if ($oldversion < 2015042900) {
        // Get old Google Drive mountpoints and delete those later.
        $mountpoints = get_column_sql("
            SELECT artefact
              FROM {artefact_cloudfile}
             WHERE type = ? AND remote_id IN (?, ?)", array('googledrive', 'folder:root', 'root'));

        db_begin();

        // Delete Google Drive references.
        delete_records('artefact_cloudfile', 'type', 'googledrive');

        // Delete old mountpoints.
        if (is_array($mountpoints) && count($mountpoints) > 0) {
            foreach ($mountpoints as $folderid) {
                $folder = new ArtefactTypeFolder($folderid);
                $folder->delete();
            }
        }

        // Finally unlink all accounts from Google Drive
        delete_records('artefact_cloudfile_config', 'type', 'googledrive');

        db_commit();
    }

    if ($oldversion < 2015052800) {
        // Fix missing fileid's for cloud files.
        execute_sql("
            UPDATE {artefact_file_files}
               SET fileid = artefact
             WHERE fileid IS NULL
                AND artefact IN (SELECT artefact FROM {artefact_cloudfile})");
    }

    return $success;
}