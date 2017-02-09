<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage search-sphinx
 * @author     Discendum Oy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010 Discendum Oy http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_search_sphinx_upgrade($oldversion=0) {

    $INNODB = is_mysql() ? ' TYPE=innodb' : '';

    // There was no database prior to this version.
    if ($oldversion < 2010061600) {
        install_from_xmldb_file(get_config('docroot') . 'search/sphinx/db/install.xml');
    }

    if ($oldversion < 2011112303) {
        $blob = is_mysql() ? 'LONGBLOB' : 'BYTEA';
        execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache ' . $blob);

        execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache_mtime DATETIME');
    }

    return true;
}

