<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// NOTE: this JSON script is used by the 'viewacl' element. It could probably
// be moved elsewhere without harm if necessary (e.g. if the 'viewacl' element
// was used in more places
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');

$type   = param_variable('type');
$query  = param_variable('query', '');
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
// <EKAMPUS
$onlysystemgroups = (param_alpha('grouptype', 'all') === 'system');
$groupid = (int) param_variable('group', 0);
$includeuser = (bool) param_variable('includeuser', 0);
// EKAMPUS>

switch ($type) {
    // <EKAMPUS
    case 'institution':
        require_once('institution.php');

        $query = trim($query);

        // TODO: after testing change from AND to OR
        if (empty($query) && !is_teacher()) {
            $data = get_empty_resultset($limit, $offset);
        }

        $institutions = Institution::count_members(null, false, $query, $limit,
                $offset, $count);
        $data = array('count' => $count, 'error' => false, 'limit' => $limit,
            'offset' => $offset, 'data' => array_values($institutions));

        break;
    // EKAMPUS>
    case 'user':
    // <EKAMPUS
    case 'teacher':
        $query = trim($query);
        if (empty($query) && !is_teacher()) {
            $data = get_empty_resultset($limit, $offset);
        }
        else {
            if (!$includeuser) {
                $searchparams = array('exclude' => $USER->get('id'));
            }

            if ($groupid > 0) {
                $searchparams['group'] = $groupid;
            }

            if ($type === 'teacher') {
                $searchparams['teachers'] = true;
            }

            $data = search_user($query, $limit, $offset, $searchparams);
        }
        // EKAMPUS>

        break;
    case 'group':
        require_once('group.php');
        // <EKAMPUS
        $data = search_group($query, $limit, $offset, '', '', true, true);
        // EKAMPUS>
        $roles = get_records_array('grouptype_roles');
        $data['roles'] = array();
        foreach ($roles as $r) {
            $data['roles'][$r->grouptype][] = array('name' => $r->role, 'display' => get_string($r->role, 'grouptype.'.$r->grouptype));
        }
        foreach ($data['data'] as &$r) {
            $r->url = group_homepage_url($r);
        }
        break;
    // <KYVYT
    case 'token':
        $view = param_integer('v');
        $token = get_random_key(20);
        while (record_exists('view_access', 'token', $token)) {
            $token = get_random_key(20);
        }
        $rec = array(
            'view' => $view,
            'token' => $token . ':',
            'allowcomments' => 0,
            'approvecomments' => 0,
            'visible' => 1
        );
        insert_record('view_access', (object) $rec);
        $data = array('type' => 'token', 'name' => get_string('token', 'view'), 'id' => $token . ':');
        break;
    // KYVYT>
}

$data['error'] = false;
$data['message'] = '';
json_reply(false, $data);

// <EKAMPUS
function new_token() {
    $token = get_random_key(20);
    if (record_exists('view_access', 'token', $token)) {
        return new_token();
    }
    return $token;
}

function get_empty_resultset($limit, $offset) {
    return array('count' => 0, 'limit' => $limit, 'offset' => $offset,
        'data' => array());
}

// EKAMPUS>
