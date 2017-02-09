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

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$action = param_variable('action', 'status');
safe_require('search', 'sphinx');

$data = array();
$data['error'] = false;
$data['message'] = false;

if (!$USER->get('admin')) throw new AccessDeniedException();

switch ($action) {
    case 'reindex':
        $klass = generate_class_name('search', 'sphinx');
        $ret = call_static_method($klass, 'reindex_all');
        if ($ret !== 0) {
            $data['error'] = true;
            $data['message'] = get_string('reindexerr', 'search.sphinx');
        } else {
            $data['message'] = get_string('reindexok', 'search.sphinx');
        }
        break;
    default:
        break;
}
json_headers();
echo json_encode($data);

