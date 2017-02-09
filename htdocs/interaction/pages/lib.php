<?php
defined('INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/lib.php');

class PluginInteractionPages extends PluginInteraction {

    public static function group_menu_items($group) {
        $menu = array();
//        $menu['views'] = array(
//            'path' => 'groups/views',
//            'url' => 'interaction/pages/grouppages.php?group=' . $group->id,
//            'title' => get_string('Views', 'view'),
//            'weight' => 10
//        );

        return $menu;
    }

    public static function instance_config_form($group, $instance = null) {

    }

    public static function instance_config_save($instance, $values) {

    }

    public static function menu_items() {
//        $menu = array(
//            'myportfolio/views' => array(
//                'path' => 'myportfolio/views',
//                'url' => 'interaction/pages/index.php',
//                'title' => get_string('Views', 'view'),
//                'weight' => 10));
//
//        return $menu;

        return array();
    }

    public static function get_view_tags($user = null, $groupid = null) {
        $where = !empty($user) ? 'owner = ?' : '`group` = ?';
        $tagrecords = get_records_sql_array("
            SELECT DISTINCT(tag) AS tag
              FROM view_tag
             WHERE view IN (
                SELECT id
                  FROM view
                 WHERE $where
             )
          ORDER BY tag COLLATE utf8_swedish_ci ASC", array(!empty($user) ? $user->id : $groupid));
        $tags = array();

        if (is_array($tagrecords)) {
            foreach ($tagrecords as $record) {
                $tags[] = $record->tag;
            }
        }

        return $tags;
    }

    public static function get_collection_tags($user = null, $groupid = null) {
        $where = !empty($user) ? 'owner = ?' : '`group` = ?';
        $where .= ' AND type IS NULL'; // EKAMPUS - Skip learning objects.
        $tagrecords = get_records_sql_array("
            SELECT DISTINCT(tag) AS tag
              FROM collection_tag
             WHERE collection IN (
                SELECT id
                  FROM collection
                 WHERE $where
             )
         ORDER BY tag COLLATE utf8_swedish_ci ASC", array(!empty($user) ? $user->id : $groupid));
        $tags = array();

        if (is_array($tagrecords)) {
            foreach ($tagrecords as $record) {
                $tags[] = $record->tag;
            }
        }

        return $tags;
    }

    public static function add_access_info(&$pages, $accesslists, $editlocked = false, $collections = false) {
        if (empty($pages)) {
            return;
        }

        // Sort initially by modification time.
        if (!$collections){
            usort($pages,
                function ($page1, $page2) {
            return $page1['mtime'] < $page2['mtime'];
            });
        }

        // Jsonify tags & add access lists.
        foreach ($pages as &$item) {
            $item['jsontags'] = json_encode(isset($item['tags']) ? $item['tags']
                                : array(), JSON_HEX_QUOT);
            $item['shared_to'] = 'private';

            if (!$collections){
                //$editlocked - checks group role privilages, only admin can remove pages
                $removable = View::can_remove_viewtype($item['type']);
                $item['is_editable'] = (int) (!isset($item['submittedto']) && (empty($item['locked'])));
                $item['is_removable'] = (int) (!isset($item['submittedto']) && $removable
                        && (empty($item['locked']) && !$editlocked));


                // Try to find the correct view from views-array.
                if (isset($accesslists['views'][$item['id']])) {
                    $accessitem = $accesslists['views'][$item['id']];

                    // TODO: Check access attributes (dates etc.).
                    if (isset($accessitem['accessgroups'])) {
                        self::add_access_type($item, $accessitem['accessgroups']);
                    }
                }

                // Try to find the view from collections.
                else if (isset($accesslists['collections'])) {
                    foreach ($accesslists['collections'] as $collectionid =>
                                $collection) {
                        if (isset($collection['views'][$item['id']])) {
                            if (isset($collection['accessgroups'])) {
                                self::add_access_type($item, $collection['accessgroups']);
                            }
                        }
                    }
                }

            }
            else {
                $item['is_editable'] = ($editlocked) ? 0 : 1;
                $item['is_removable'] = ($editlocked) ? 0 : 1;

                if (isset($accesslists['collections'])) {
                    foreach ($accesslists['collections'] as $collectionid => $collection) {
                        if ($collectionid == $item['id']) {
                            if (isset($collection['accessgroups'])) {
                                self::add_access_type($item, $collection['accessgroups']);
                            }
                            else {
                                $item['shared_to'] = 'private';
                            }
                        }
                    }
                }
            }
        }

    }

    private static function add_access_type(&$item, $accessgroups) {
        $accesstypes = array();
        $item['access'] = array();

        foreach ($accessgroups as $group) {
            $current_timestamp = time();
            //check time limits
            if ((!$group['startdate'] || strtotime($group['startdate']) < $current_timestamp) && (!$group['stopdate'] || strtotime($group['stopdate']) > $current_timestamp)){
                $accesstypes[] = $group['accesstype'];
            }
            $item['access'][] = array(
                'type' => $group['accesstype'],
                'id' => isset($group['id']) ? $group['id'] : null,
                'name' => $group['name']
            );
        }

        if (in_array('public', $accesstypes)) {
            $item['shared_to'] = 'public';
        }
        else if (count(array_intersect(array('loggedin', 'group', 'friends', 'institution',
                    'user', 'usr'), $accesstypes)) > 0) {
            $item['shared_to'] = 'published';
        }
        else if (in_array('token', $accesstypes)) {
            $item['shared_to'] = 'token';
        }
        else {
            $item['shared_to'] = 'private';
        }
    }

    private static function init_pages_smarty($pages, $tags, $title,$groupid = null, $collection = false, $total = 0) {
        //$fulltextsearch = (int) (get_config('searchplugin') === 'sphinx');
        $fulltextsearch = 1;
        $groupid = is_null($groupid) ? -1 : (int) $groupid;
        $wwwroot = get_config('wwwroot');
        $js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../interaction/pages/js/view'], function (v) {
        v.init({ fulltextsearch: $fulltextsearch, groupid: $groupid, total: $total });
    });
});
JS;

        $smarty = smarty(array($wwwroot . 'local/js/lib/require.js'), array(),
                array('view' => array('editaccess', 'deletethisview', 'editcontentandlayout')),
                array());
        $smarty->assign('pages', $pages);
        $smarty->assign('PAGEHEADING', $title);
        $smarty->assign('tags', $tags);
        $smarty->assign('INLINEJAVASCRIPT', $js);
        $smarty->assign('fulltextsearch', $fulltextsearch);

        return $smarty;
    }

    /**
     *
     * @param type $pages
     * @param type $tags
     * @param type $title
     * @param type $groupid
     */

    public static function show_pages($pages, $tags, $title, $groupid = null, $can_edit = true) {
        //dont show tags for not editable pages
        if (!$can_edit) {
            $tags = false;
        }
        $smarty = self::init_pages_smarty($pages, $tags, $title, $groupid);
        if ($can_edit) {
            $elements = create_view_form($groupid);
            $elements['elements']['submit_'] = $elements['elements']['submit'];
            $elements['elements']['submithack'] = array(
                'type' => 'markup',
                'value' => '<span id="createview-submithack" class="createitem-link">' .
                get_string('addnewpage', 'interaction.pages') . '</span>');
            unset($elements['elements']['submit']);
            $createviewform = pieform($elements);

            $smarty->assign('createviewform', $createviewform);

        }
        $smarty->display('interaction:pages:index.tpl');

    }

    public static function show_galleria_pages(&$pages, $tags, $title, $searchform, $types, $total=null) {
        $groupid = NULL;
        $smarty = self::init_pages_smarty($pages, $tags, $title, $groupid, false, $total);
        $smarty->assign('gallery', true);
        $smarty->assign('types', $types);
        $smarty->assign('searchform', $searchform);
        $smarty->display('interaction:pages:sharedviews.tpl');
    }

    /**
     *
     * @param type $viewdata An array of view-arrays
     * @param type $idcolumn The name of the column the view id is located. If
     *      we're handling collections here, the default id-column
     *      is the collection id, not the view id.
     */
    public static function get_sharedview_accessrecord(&$viewdata, $idcolumn = 'id'){
        global $USER;

        $accessrecords = array();

        foreach ($viewdata as &$view) {
            $view = (array)$view;
            // If we're handling collection data, we may not have a view to
            // check the access records (if the collection doesn't have any
            // views).
            $accessrecords = isset($view[$idcolumn])
                    ? View::get_access_records($view[$idcolumn])
                    : array();
            $accesstypes = array();

            foreach ($accessrecords as &$arecord) {
                $arecord = (array) $arecord;

                $current_timestamp = time();
                //check time limits
                if ((!$arecord['startdate'] || strtotime($arecord['startdate']) < $current_timestamp) && (!$arecord['stopdate'] || strtotime($arecord['stopdate']) > $current_timestamp)){
                    foreach($arecord as $key => $arec){
                        if ($arec != null && !in_array($key, array('allowcomments', 'approvecomments'))){
                                $accesstypes[] = $key == 'accesstype' ? $arec : $key;
                        }
                    }
                }
            }

            if (in_array('public', $accesstypes)) {
                $view['shared_to'] = 'public';
            }
            else if (count(array_intersect(array('loggedin', 'group', 'friends', 'institution',
                        'usr'), $accesstypes)) > 0) {
                $view['shared_to'] = 'published';
            }
            else if (in_array('token', $accesstypes)) {
                $view['shared_to'] = ($USER->get('id') == $view['owner']) ? 'token' : 'published';
            }
            else {
                $view['shared_to'] = 'private';
            }

            if ($USER->get('id') == $view['owner']){
                $view['shared_to'] .= ' own';
                /*if (in_array('token', $accesstypes)){
                    $view['shared_to'] .= ' token';
                }*/
                $view['is_editable'] = true;
                $view['is_removable'] = View::can_remove_viewtype($view['type']);
            }
            elseif (isset($view['group']) AND $view['group']){
                $role = group_user_access($view['group']);
                $groupadmin = ($role == 'admin');
                $can_edit = $role && group_role_can_edit_views($view['group'], $role);
                $view['is_editable'] = $can_edit;
                $view['is_removable'] = (int) $can_edit && View::can_remove_viewtype($view['type']) && $groupadmin;
            }
        }

    }
    public static function get_tags_from_pages($pages) {
        $viewids = array();

        if (is_array($pages)) {
            foreach ($pages as $page) {
                $viewids[] = (int) $page['id'];
            }
        }

        if (count($viewids) === 0) {
            return array();
        }

        $tagrecords = get_records_sql_array(
                'SELECT DISTINCT(tag) AS tag '
                . 'FROM view_tag WHERE view IN (' . implode(',', $viewids) . ')',
                array());
        $tags = array();

        if (is_array($tagrecords)) {
            foreach ($tagrecords as $record) {
                $tags[] = $record->tag;
            }
        }

        return $tags;
    }

    public static function update_group_tags($groupid, $userid, array $tags) {
        db_begin();

        // First, delete old tags...
        delete_records_sql(
                "DELETE FROM {our_resourcetree} "
                . "WHERE type = ? AND usr = ? AND resource = ?",
                array('group', $userid, $groupid));

        // ... then insert the current ones.
        foreach ($tags as $tag) {
            $folderid = ensure_record_exists('our_resourcetree_folder',
                    (object) array('type' => 'group', 'usr' => $userid, 'title' => $tag),
                    (object) array('type' => 'group', 'usr' => $userid, 'title' => $tag),
                    'id', true);

            insert_record('our_resourcetree',
                    (object) array(
                        'type' => 'group',
                        'usr' => $userid,
                        'folder' => $folderid,
                        'resource' => $groupid
            ));
        }

        // TODO: Remove empty resourcetree folders.

        db_commit();

        // Return current tags.
        return self::get_group_tags($userid);
    }

    public static function get_group_tags($userid) {
        // Resourcetree uses group-lib, which uses Pieforms but doesn't include
        // the lib.
        require_once('pieforms/pieform.php');
        require_once(dirname(dirname(dirname(__FILE__))) . '/local/lib/resourcetree.php');

        $tree = custom\resourcetree_data('group', $userid);
        $tags = array();

        if (is_array($tree['tree'])) {
            foreach ($tree['tree'] as $obj) {
                $tags[] = $tree['folder'][$obj->folder]->title;
            }
        }

        return array_values(array_unique($tags));
    }

}

class InteractionPagesInstance extends InteractionInstance {

    public function interaction_remove_user($userid) {

    }

    public static function get_plugin() {
        return 'pages';
    }

}
