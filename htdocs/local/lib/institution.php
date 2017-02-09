<?php
namespace custom;

defined('INTERNAL') || die();


require_once('institution.php');
require_once('group.php');

/**
 * This acts as a proxy for the real Institution class.
 * Main purpose is to provide an interface to our_institution_config table.
 *
 * If a single institution is not defined in constructor, we operate on all current
 * user's institutions.
 *
 */
class Institution {

    private $name;
    private $_names;
    private $institution;
    private $config = array();
    private $dirty = false;
    private $_cache = array();

    function __construct($name=null) {
        if (is_array($name) && !empty($name)) {
            $this->_cache['names'] = $name;
            $names = $name;
        }
        else if (!is_null($name)) {
            $this->name = $name;
            $this->institution = new \Institution($name);
            $names = array($name);
        }
        else {
            $names = $this->names();
        }
        if (empty($names)) {
            return;
        }
        $names = join("','", $names);
        $rows = get_records_select_array('our_institution_config', "institution IN ('$names')", array());
        if (empty($rows)) {
            return;
        }
        foreach ($rows AS $row) {
            $obj = @unserialize($row->value);
            if ($obj === false) {
                $obj = $row->value;
            }
            if (!isset($this->config[$row->field])) {
                $this->config[$row->field] = array();
            }
            $this->config[$row->field][$row->institution] = $obj;
        }
    }

    function __get($field) {
        if (isset($this->config[$field])) {
            return is_null($this->name) ? $this->config[$field] : $this->config[$field][$this->name];
        }
        return is_null($this->name) ? null : $this->institution->$field; 
    }

    function __set($field, $value) {
        if (is_null($this->name)) {
            throw new \RuntimeException("Institution not defined");
        }
        $this->config[$field][$this->name] = $value;
        $this->dirty = true;
        return $value;
    }

    function __call($name, $args) {
        if (!is_null($this->name)) {
            return call_user_func_array(array($this->institution, $name), $args);
        }
        throw new \RuntimeException("Institution not defined");
    }

    function commit() {
        if (is_null($this->name)) {
            throw new \RuntimeException("Institution not defined");
        }
        if (!$this->dirty) {
            return;
        } 
        db_begin();
        foreach ($this->config AS $field => $value) {
            $obj = $value[$this->name];
            if (is_array($obj) || is_object($obj)) {
                $obj = serialize($obj);
            }
            execute_sql(
                "REPLACE INTO {our_institution_config} (institution, field, value) VALUES (?,?,?)",
                array($this->name, $field, $obj)
            );
        }
        db_commit();
        $this->dirty = false;
    }

    function staff() {
        if (is_null($this->name)) {
            throw new \RuntimeException("Institution not defined");
        }
        $rows = get_records_sql_array(
            "SELECT u.* FROM {usr} u
            INNER JOIN {usr_institution} ui ON u.id = ui.usr
            WHERE (ui.staff = 1 OR ui.admin = 1) AND ui.institution = ?
            ORDER BY u.lastname
            ", array($this->name)
        );
        if (empty($rows)) {
            return array(0 => '- none available -');
        }
        $staff = array(0 => '- none -');
        foreach ($rows AS $row) {
            $staff[$row->id] = display_name($row);
        }
        return $staff;
    }

    /**
     * List of current user's institution names
     *
     */
    static function mynames() {
        global $USER;
        $inst = $USER->get('institutions');
        if (empty($inst)) {
            return array('mahara');
        }
        return array_map(function ($i) { return $i->institution; }, $inst);
    }

    function names() {
        if (!isset($this->_cache['names'])) {
            $this->_cache['names'] = static::mynames(); 
        }
        return $this->_cache['names'];
    }

    /**
     * Returns a list of institutions where user has more rights.
     *
     */
    function user_elevated() {
        global $USER;
        $that = $this;
        $userid = $USER->get('id');
        return array_filter($this->names(), function ($i) use ($that, $userid) {
            if (isset($that->supervisor[$i]) && $userid === $that->supervisor[$i]) {
                return true;
            }
            if (!isset($that->elevated[$i])) {
                return false;
            }
            return in_array($userid, $that->elevated[$i]);
        });
    }

    /**
     * How many groups have been created in an institution
     *
     */
    function group_count($institution) {
        if (!isset($this->_cache['group_count'])) {
            $this->_cache['group_count'] = get_records_sql_menu(
                "SELECT institution, COUNT(id) AS count FROM `group`
                WHERE deleted = 0 GROUP BY institution", array()
            );
        }
        return (int)@$this->_cache['group_count'][$institution];
    }

    /**
     * Returns a list of institutions where user can create groups
     *
     */
    function institutions_groups_createable() {
        $that = $this;

        $standard_inst = array_filter($this->names(), function ($i) use ($that) {
            if (!isset($that->grouplimit[$i]) || empty($that->grouplimit[$i]) || $that->grouplimit[$i] == -1) {
                return true;
            }
            return $that->grouplimit[$i] > $that->group_count($i);
        });

        $elevated_inst = array_filter($this->user_elevated(), function ($i) use ($that) {
            if (!isset($that->elev_grouplimit[$i]) || empty($that->elev_grouplimit[$i]) || $that->elev_grouplimit[$i] == -1) {
                return true;
            }
            return $that->elev_grouplimit[$i] > $that->group_count($i);
        });

        return array_unique(array_merge($elevated_inst, $standard_inst));
    }

    function institution_menu($inst) {
        if (empty($inst)) {
            return array();
        }
        $inst = array_unique($inst);
        $placeholder = join(',', array_fill(0, count($inst), '?'));
        return get_records_sql_menu(
            "SELECT name, displayname FROM {institution}
            WHERE name IN ($placeholder)
            ORDER BY displayname", $inst
        );
    }

    function has_feature($name) {
        global $USER;
        if ($USER->get('admin')) {
            return true; // admins have them all
        }
        if ($name == 'wiki') {
            return true; // free for all
        }
        if (isset($this->config[$name])) { 
            foreach ($this->names() AS $i) {
                if (@$this->config[$name][$i]) {
                    if ($this->config[$name][$i] == -1) return true;
                    $curr = 0;
                    if ($name == 'wiki') {
                        $curr = get_field_sql(
                            "SELECT COUNT(g.id) FROM {group} g
                            INNER JOIN {our_group_config} c ON g.id = c.group
                            WHERE c.field = 'wiki' AND c.value = 1 AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    if ($name == 'returnbox') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            WHERE ii.plugin = 'returnbox' AND ii.deleted = 0
                                AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    else if ($name == 'private_forum') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            INNER JOIN {interaction_forum_instance_config} c ON ii.id = c.forum
                            WHERE c.field = 'isprivate' AND c.value = 1 AND ii.deleted = 0
                                AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    else if ($name == 'chat') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            WHERE ii.plugin = 'chat' AND ii.deleted = 0 AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    if ((int)$curr < (int)$this->config[$name][$i]) return true;
                }
            }
        }
        $name = 'elev_' . $name;
        if (isset($this->config[$name])) { 
            foreach ($this->user_elevated() AS $i) {
                if (isset($this->config[$name][$i]) && $this->config[$name][$i]) {
                    if ($this->config[$name][$i] == -1) return true;
                    $curr = 0;
                    if ($name == 'elev_wiki') {
                        $curr = get_field_sql(
                            "SELECT COUNT(g.id) FROM {group} g
                            INNER JOIN {our_group_config} c ON g.id = c.group
                            WHERE c.field = 'wiki' AND c.value = 1 AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    if ($name == 'elev_returnbox') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            WHERE ii.plugin = 'returnbox' AND ii.deleted = 0
                                AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    else if ($name == 'elev_private_forum') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            INNER JOIN {interaction_forum_instance_config} c ON ii.id = c.forum
                            WHERE c.field = 'isprivate' AND c.value = 1 AND ii.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    else if ($name == 'elev_chat') {
                        $curr = get_field_sql(
                            "SELECT COUNT(ii.id) FROM {interaction_instance} ii 
                            INNER JOIN {group} g ON ii.group = g.id 
                            WHERE ii.plugin = 'chat' AND ii.deleted = 0 AND g.deleted = 0 AND g.institution = ?", array($i)
                        );
                    }
                    if ((int)$curr < (int)$this->config[$name][$i]) return true;
                }
            }
        }
        return false;
    }


    function elevated_request_institutions() {
        global $USER;
        $that = $this;
        return array_filter($this->names(), function ($i) use ($that, $USER) {
            if (!isset($that->supervisor[$i]) || $that->supervisor[$i] == 0 || $USER->get('id') === $that->supervisor[$i]) {
                return false;
            }
            return !isset($that->elevated[$i]) || !in_array($USER->get('id'), $that->elevated[$i]);
        });
    }

    /**
     * 
     *
     */
    function supervisor_for($uid) {
        global $USER;
        $inst = get_column('usr_institution', 'institution', 'usr', $uid); 
        if (empty($inst)) {
            $inst = array('mahara');
        }
        $that = $this;
        return array_filter($inst, function ($i) use ($that, $USER) {
            return $USER->get('id') === @$that->supervisor[$i];
        });
    }

    /**
     * Adds/removes members to/from the helpdesk group.
     *
     */
    function helpdesk_membership_check() {
        if (!$this->name || !$this->helpdesk_group) {
            return;
        }
        $members = \get_column_sql(
            "SELECT gm.member FROM {group_member} gm
            INNER JOIN {usr_institution} ui ON gm.member = ui.usr
            WHERE gm.group = ? AND ui.institution = ? AND gm.role = 'member'
            ", array($this->helpdesk_group, $this->name)
        );
        if (empty($members)) $members = array();
        $users = array();
        if ($this->helpdesk_all) {
            $users = \get_column('usr_institution', 'usr', 'institution', $this->name);
        }
        else if ($this->helpdesk_elev) {
            $users = $this->elevated;
        }
        if (!is_array($users)) $users = array();
        if (!is_array($this->helpdesk_members_selected)) $this->helpdesk_members_selected = array();
        $users = array_unique(array_merge($users, $this->helpdesk_members_selected));

        $admins = get_column('group_member', 'member', 'role', 'admin', 'group', $this->helpdesk_group);
        if (!is_array($admins)) $admins = array();
        $users = array_diff($users, $admins);

        foreach (array_diff($members, $users) AS $rem) {
            \group_remove_user($this->helpdesk_group, $rem, true);
        }
        foreach (array_diff($users, $members) AS $add) {
            $this->group_add($this->helpdesk_group, $add);
        }
    }

    /**
     * Add user to any helpdesk groups available
     *
     */
    function helpdesk_add($uid) {
        $groups = @$this->config['helpdesk_group'];
        if (empty($groups)) {
            return;
        }
        foreach ($groups AS $i => $gid) {
            if (record_exists('group_member', 'group', $gid, 'member', $uid)) {
                continue;
            }
            if ($this->helpdesk_all[$i]
                || ($this->helpdesk_elev[$i] && @in_array($uid, $this->elevated[$i]))
                || @in_array($uid, $this->helpdesk_members_selected[$i])
            ) {
                $this->group_add($gid, $uid);
            }
        }
    }

    function helpdesk_remove($inst, $uid) {
        if (isset($this->helpdesk_group[$inst])) {
            \group_remove_user($this->helpdesk_group[$inst], $uid, true);
        }
    }

    protected function group_add($group, $uid) {
        try {
            \group_add_user($group, $uid, 'member');
        }
        catch (\SQLException $e) {
            $msg = $e->getMessage();
            if (preg_match('/Duplicate/', $msg)) {
                error_log("SKIP: User $uid is already a member in helpdesk group " . $group);
            } else {
                throw $e;
            }
        }
    }

    public function extraquota_allotted() {
        return (int) get_field_sql(
            "SELECT SUM(c.value) FROM {our_group_config} c
            INNER JOIN {group} g ON c.group = g.id
            WHERE g.institution = ? AND g.deleted = 0 AND c.field = 'extraquota'", array($this->name));
    }

    public function extraquota_available() {
        $total = $this->extraquota * 1024; // stored in gigabytes
        return $total - $this->extraquota_allotted();
    }
}

