<?php
namespace custom;

defined('INTERNAL') || die();

require_once('group.php');

class View {

    private $id;
    private $view;

    public function __construct($id) {
        $this->id = $id;
        $this->view = new \View($id);
    }

    function __get($field) {
        return $this->view->get($field);
    }

    function __call($name, $args) {
        return call_user_func_array(array($this->view, $name), $args);
    }

    
    public static function user_writeaccess($uid) {
        static $views;

        if (empty($views)) {
            $views = array();
        }

        if (!isset($views[$uid])) {
            $owned = get_column('view', 'id', 'owner', $uid);
            if (empty($owned)) {
                $owned = array();
            }
            // If user is group admin, all pages in that group can be edited.
            $groupadmin = get_column_sql(
                "SELECT v.id FROM {view} v
                INNER JOIN {group_member} gm ON v.group = gm.group
                WHERE gm.member = ? AND gm.role = 'admin' ", array($uid));
         
            if (empty($groupadmin)) {
                $groupadmin = array();
            }
            
            // Access can be granted for whole group or single user.
            $granted = get_column_sql(
                "SELECT w.view FROM {view_writeaccess} w
                LEFT JOIN {group_member} g ON (w.group = g.group AND w.role = g.role)
                WHERE (w.usr = ? OR g.member = ?) 
                AND (w.startdate IS NULL OR w.startdate < NOW())
                AND (w.stopdate IS NULL OR w.stopdate > NOW())", array($uid, $uid));

            if (empty($granted)) {
                $granted = array();
            }
            $views[$uid] = array_unique(array_merge($owned, $granted, $groupadmin));
        }
        return $views[$uid];
    }

    public function has_writeaccess($usr=null) {
        global $USER;
        if (is_null($usr)) {
            $usr = $USER;
        }
        return in_array($this->id, View::user_writeaccess($usr->get('id')));
    }

    /**
     * We have a bunch of views. Find out which ones current user has write access to.
     *
     */
    public static function mark_writeable($views) {
        global $USER;

        if (empty($views)) {
            return $views;
        }
        $writeaccess = View::user_writeaccess($USER->get('id'));
        foreach ($views AS &$v) {
            if (isset($v['id'])) {
                $v['readonly'] = !in_array($v['id'], $writeaccess);
            }
        }
        if (isset($views['collections']) && isset($views['collections']['views'])) {
            foreach ($views['collections']['views'] AS &$v) {
                if (isset($v['id'])) {
                    $v['readonly'] = !in_array($v['id'], $writeaccess);
                }
            }
        }
        if (isset($views['views'])) {
            foreach ($views['views'] AS &$v) {
                if (isset($v['id'])) {
                    $v['readonly'] = !in_array($v['id'], $writeaccess);
                }
            }
        }
        return $views;
    }

    public function get_writeaccesslist() {
        $data = get_records_sql_array(
            "SELECT *, '' AS accesstype, '' AS token, '' AS institution FROM {view_writeaccess}
            WHERE view = ? AND visible = 1", array($this->id)
        );
        if (empty($data)) {
            return array();
        }
        return \View::process_access_records($data, \get_string('strftimedatetimeshort'));
    }

    public function update_writeaccess($viewconfig) {

        $accesslist = $viewconfig['accesslist'];
        db_begin();
        
        delete_records_select('view_writeaccess', 'view = ? AND visible = 1', array($this->id));

        // View access
        $accessdata_added = array();
        if ($accesslist) {
            /*
             * There should be a cleaner way to do this
             * $accessdata_added ensures that the same access is not granted twice because the profile page
             * gets very grumpy if there are duplicate access rules
             *
             * Additional rules:
             * - Don't insert records with stopdate in the past
             * - Remove startdates that are in the past
             * - If view allows comments, access record comment permissions, don't apply, so reset them.
             * @todo: merge overlapping date ranges.
             */
            $time = time();
            foreach ($accesslist as $item) {

                if (!empty($item['stopdate']) && $item['stopdate'] < $time) {
                    continue;
                }
                if (!empty($item['startdate']) && $item['startdate'] < $time) {
                    unset($item['startdate']);
                }

                $accessrecord = (object)array(
                    'group' => null,
                    'role' => null,
                    'usr' => null,
                    'startdate' => $viewconfig['startdate'],
                    'stopdate' =>  $viewconfig['stopdate'],
                );

                switch ($item['type']) {
                case 'user':
                    $accessrecord->usr = $item['id'];
                    break;
                case 'groupmember':
                    $accessrecord->usr = $item['id'];
                    break;
                case 'group':
                    $accessrecord->group = $item['id'];
                    if (isset($item['role']) && strlen($item['role'])) {
                        // Don't insert a record for a role the group doesn't have
                        $roleinfo = group_get_role_info($item['id']);
                        if (!isset($roleinfo[$item['role']])) {
                            break;
                        }
                        $accessrecord->role = $item['role'];
                    }
                    break;
                }

                if (isset($item['startdate'])) {
                    $accessrecord->startdate = db_format_timestamp($item['startdate']);
                }
                if (isset($item['stopdate'])) {
                    $accessrecord->stopdate  = db_format_timestamp($item['stopdate']);
                }

                if (array_search($accessrecord, $accessdata_added) === false) {
                    $accessrecord->view = $this->id;
                    insert_record('view_writeaccess', $accessrecord);
                    unset($accessrecord->view);
                    $accessdata_added[] = $accessrecord;
                }
            }
        }

        handle_event('saveview', $this->id);

        db_commit();
        return $accessdata_added;
    }

}
