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

require('querylib.php');

safe_require('search', 'internal');

@include_once(get_config('docroot') . 'artefact/europass/lib/locale.php');
@include_once(get_config('docroot') . 'artefact/europass/lib/europassxml.php');

/**
 * The Sphinx search plugin
 */
class PluginSearchSphinx extends PluginSearchInternal {

    /**
     * PostgreSQL needs custom functions
     *
     */
    public static function postinst($prevversion) {
        if ($prevversion == 0 && is_mysql()) {
            execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache LONGBLOB');
            execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache_mtime DATETIME');
        }
        if ($prevversion == 0 && is_postgres()) {
            execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache BYTEA');
            execute_sql('ALTER TABLE {view} ADD COLUMN sphinxcache_mtime DATETIME');

            if (!get_field_sql("SELECT 1 FROM pg_catalog.pg_language WHERE lanname = 'plpgsql'")) {
                execute_sql('CREATE LANGUAGE plpgsql');
            }

            execute_sql(
                'CREATE AGGREGATE array_accum (
                    sfunc = array_append,
                    basetype = anyelement,
                    stype = anyarray,
                    initcond = \'{}\'
                )'
            );

            execute_sql(
                'CREATE OR REPLACE FUNCTION _group_concat(text, text)
                RETURNS text AS $$
                SELECT CASE
                WHEN $2 IS NULL THEN $1
                WHEN $1 IS NULL THEN $2
                ELSE $1 operator(pg_catalog.||) \',\' operator(pg_catalog.||) $2
                END
                $$ IMMUTABLE LANGUAGE SQL;'
            );

            execute_sql(
                'CREATE AGGREGATE GROUP_CONCAT (
                    BASETYPE = text,
                    SFUNC = _group_concat,
                    STYPE = text
                )'
            );

            execute_sql(
                'CREATE OR REPLACE FUNCTION crc32(word text)
                RETURNS bigint AS $$
                DECLARE tmp bigint;
                DECLARE i int;
                DECLARE j int;
                DECLARE word_array bytea;
                BEGIN
                    i = 0;
                    tmp = 4294967295;
                    word_array = decode(replace(word, E\'\\\\\', E\'\\\\\\\\\'), \'escape\');
                    LOOP
                        tmp = (tmp # get_byte(word_array, i))::bigint;
                        i = i + 1;
                        j = 0;
                        LOOP
                            tmp = ((tmp >> 1) # (3988292384 * (tmp & 1)))::bigint;
                            j = j + 1;
                            IF j >= 8 THEN
                                EXIT;
                            END IF;
                        END LOOP;
                        IF i >= char_length(word) THEN
                            EXIT;
                        END IF;
                    END LOOP;
                    return (tmp # 4294967295);
                END
                $$ IMMUTABLE STRICT LANGUAGE plpgsql'
            );
        }
    }

    public static function get_cron() {
        $delta = new StdClass;
        $delta->callfunction = 'reindex_delta';
        $delta->hour = '*';
        $delta->minute = '*/10';

        $all = new StdClass;
        $all->callfunction = 'reindex_all';
        $all->hour = '4';
        $all->minute = '2';

        return array($delta, $all);
    }

    /**
     * These events trigger immediate reindexing.
     *
     */
    public static function get_event_subscriptions() {
        return array(
            (object)array('plugin' => 'sphinx', 'event' => 'createuser',          'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'updateuser',          'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'suspenduser',         'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'unsuspenduser',       'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'deleteuser',          'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'undeleteuser',        'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'expireuser',          'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'unexpireuser',        'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'deactivateuser',      'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'activateuser',        'callfunction' => 'event_reindex_user'),
            (object)array('plugin' => 'sphinx', 'event' => 'userjoinsgroup',      'callfunction' => 'event_reindex_group'),
            (object)array('plugin' => 'sphinx', 'event' => 'creategroup',         'callfunction' => 'event_reindex_group'),
            (object)array('plugin' => 'sphinx', 'event' => 'saveview',            'callfunction' => 'event_cache_view'),
            (object)array('plugin' => 'sphinx', 'event' => 'blockinstancecommit', 'callfunction' => 'event_cache_view'),
        );
    }

    public static function event_cache_view($event, $arg) {

        $viewid = 0;
        if ($event === 'blockinstancecommit' && is_object($arg) && get_class($arg) === 'BlockInstance') {
            $viewid = (int)$arg->get('view');
        }
        else if (is_array($arg) && isset($arg['id'])) {
            $viewid = (int)$arg['id'];
        }
        else if (is_int($arg)) {
            $viewid = $arg;
        }

        if ($viewid) {
            $rec = (object) array(
                'id' => $viewid,
                'sphinxcache_mtime' => db_format_timestamp(time()),
                'sphinxcache' => self::view_textcontent($viewid)
            );
            update_record('view', $rec);
        }
    }

    public static function view_textcontent($viewid) {
        $content = '';

        $artefacts = array();

        $artid = get_column_sql(
            "SELECT artefact FROM {view_artefact} WHERE view = ?", array($viewid)
        );
        if (!empty($artid)) {
            $list = implode(',', $artid);
            $artefacts = get_records_sql_array(
                "SELECT id, title, description FROM {artefact}
                WHERE id IN ($list) OR parent IN ($list)", array());
        }

        $childtables = array(
            'artefact_tag',
            'artefact_resume_book',
            'artefact_resume_certification',
            'artefact_resume_educationhistory',
            'artefact_resume_employmenthistory',
            'artefact_resume_membership',
            'artefact_resume_personal_information'
        );
        if (is_callable('generate_europasscv_xml')) {
            $childtables[] = 'artefact_europass_mothertongue';
            $childtables[] = 'artefact_europass_otherlanguage';
        }

        // Artefacts and their children
        if (!empty($artefacts)) {
            foreach ($artefacts AS $a) {
                $content .= $a->title . ' ';
                $content .= $a->description . ' ';

                foreach ($childtables AS $table) {
                    $children = get_records_array($table, 'artefact', $a->id);
                    if (!empty($children)) {
                        foreach ($children AS $child) {
                            foreach ($child AS $k => $v) {
                                $content .= $v . ' ';
                            }
                        }
                    }
                }
            }
        }

        // Textbox block instances
        $textboxes = get_records_select_array(
            'block_instance', "view = ? AND blocktype = 'textbox'", array($viewid));
        if (!empty($textboxes)) {
            foreach ($textboxes AS $box) {
                $data = @unserialize($box->configdata);
                $content .= $box->title . ' ';
                $content .= @$data['text'] . ' ';
            }
        }

        // Tags
        $tags = (array)get_column('view_tag', 'tag', 'view', $viewid);
        $content .= join(' ', $tags) . ' ';

        // Europass (if available)
        $owner = get_field('view', 'owner', 'id', $viewid);
        if (!$owner || !is_callable('generate_europasscv_xml')) {
            return $content;
        }
        $europass = get_records_select_array(
            'block_instance', "view = ? AND blocktype IN ('europasscv','europasslp')", array($viewid));

        if (!empty($europass)) {
            foreach ($europass AS $euro) {
                $data = unserialize($euro->configdata);
                $xml = @generate_europasscv_xml($owner, false, @$data['locale']);
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXML($xml);
                $node = $dom->getElementsByTagName('learnerinfo')->item(0);
                if (!$node) {
                    continue;
                }
                if ($euro->blocktype == 'europasscv') {
                    $node->removeChild($node->getElementsByTagName('docinfo')->item(0));
                    $node->removeChild($node->getElementsByTagName('prefs')->item(0));
                    $node->removeChild($node->getElementsByTagName('languagelist')->item(0));
                    $content .= $node->C14N() . ' ';
                } else {
                    $content .= $node->getElementsByTagName('languagelist')->item(0)->C14N() . ' ';
                }
            }
        }

        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }


    public static function event_reindex_user($event, $user) {
        if (!get_field_sql("SELECT 1 FROM sphinx_delta WHERE type = 'user' AND id = ?", array($user['id']))) {
            try {
                execute_sql("INSERT INTO sphinx_delta (type,id) VALUES ('user', ?)", array($user['id']));
            }
            catch (SQLException $e) {
                //Propably duplicate after all. We don't care.
            }
        }
        self::reindex('mahara_users_delta --rotate');
    }

    public static function event_reindex_group() {
        self::reindex('mahara_groups --rotate');
    }

    public static function reindex_delta() {
        return self::reindex(
            'mahara_users_delta mahara_groups mahara_artefacts_delta mahara_views_delta mahara_forums_delta --rotate'
        );
    }

    public static function reindex_all() {

        @set_time_limit(300);

        // Pages which have not been indexed yet
        $missing = get_column_sql(
            "SELECT id FROM {view} WHERE type != 'dashboard' AND sphinxcache IS NULL");

        // Pages which have been updated today
        $recent = get_column_sql(
            "SELECT v.id FROM view v
            INNER JOIN view_artefact va ON v.id = va.view
            INNER JOIN artefact a ON va.artefact = a.id
            WHERE v.type != 'dashboard' AND (a.mtime > CURRENT_DATE)
            UNION
            SELECT v.id FROM view v
            INNER JOIN view_artefact va ON v.id = va.view
            INNER JOIN artefact a ON va.artefact = a.parent
            WHERE v.type != 'dashboard' AND (a.mtime > CURRENT_DATE)"
        );

        // Oldest pages, in case we missed something
        $old = get_column_sql(
            "SELECT id FROM {view}
            WHERE type != 'dashboard' AND sphinxcache IS NOT NULL
            ORDER BY sphinxcache_mtime LIMIT 500"
        );

        $views = array_unique(array_merge((array)$recent, (array)$old, (array)$missing));

        if (!empty($views)) {
            foreach ($views AS $v) {
                self::event_cache_view('reindex_all', (int)$v);
            }
        }
        return self::reindex('--all --rotate');
    }

    protected static function reindex($param) {
        if (get_config('searchplugin') !== 'sphinx') {
            return 0;
        }
        $indexer = get_config_plugin('search', 'sphinx', 'indexerbin');
        $conf    = get_config_plugin('search', 'sphinx', 'sphinxconf');

        $indexer  = $indexer ? preg_replace('/\/+$/', '', $indexer) : '/usr/local/bin';
        if (is_file($indexer)) {
            $indexer = pathinfo($indexer, PATHINFO_DIRNAME);
        }
        $indexer = escapeshellcmd($indexer . '/indexer');

        $conf    = $conf ? $conf : '/usr/local/etc/sphinx.conf';
        $param   = escapeshellcmd($param);

        if (!is_readable($conf)) {
            error_log("Sphinx error - Not readable: " . $conf);
            return 1;
        }
        if (!is_executable($indexer)) {
            error_log("Sphinx error - Not executable: " . $indexer);
            return 1;
        }
        $conf = '--config ' . escapeshellarg($conf);
        $nop = array();
        $ret = -1;
        exec("$indexer $conf $param", $nop, $ret);
        return $ret;
    }


    protected static function sort_results($res, $data, $to_array=true) {
        $sorted = array();
        foreach($res['matches'] AS $k => $v) {
            if (isset($data[$k])) {
                $sorted[] = $to_array ? (array) $data[$k] : $data[$k];
            }
        }
        return $sorted;
    }


    protected static function build_excerpts(&$res, $index, $query) {
        $sphinx = new SphinxQuery();
        $sphinx->connect($index)->format_query($query)->build_excerpts($res);
    }


    protected static function excluded_institutions() {
        global $USER;
        if ($USER->get('admin')) {
            return array(); // Admins are unfettered
        }
        $sql = "SELECT value FROM search_config WHERE plugin = 'sphinx' AND field = 'not_searchable'";
        if ($val = get_record_sql($sql, null)) {
            $uinst = $USER->get('institutions');
            $inst = array();
            foreach (split("\n", $val->value) AS $i) {
                foreach ($uinst AS $ui) {
                    if (crc32($ui->institution) === (int)$i) {
                        continue 2; 
                    }
                }
                $inst[] = (int)$i;
            }
            return $inst;
        }
        return array();
    }


    public static function search_user($query_string, $limit, $offset = 0, $data=array()) {
        if ($query_string === '' &&  self::filter_institution() === 0) {
            $data['orderby'] = 'preferredname';
            return parent::search_user($query_string, $limit, $offset, $data);
        }

        $sphinx = new SphinxQuery();
        $res = $sphinx->connect('mahara_users, mahara_users_delta')->user_query($query_string, $data)->run($limit, $offset);

        if (empty($res['matches'])) {
            return array('count' => '0', 'limit' => $limit, 'offset' => $offset, 'data' => array());
        }

        $sql = '
        SELECT id, username, firstname, lastname, preferredname, email, staff, urlid
        FROM {usr} u WHERE id IN (' . self::ids(array_keys($res['matches'])) . ')
        AND deleted = 0 AND active = 1'; 

        $data = get_records_sql_assoc($sql, null, null, null);
        return array(
            'count'   => $res['total_found'],
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => self::sort_results($res, $data),
        );
    }


    public static function admin_search_user($queries, $constraints, $offset, $limit, $sortfield, $sortdir) {

        $sphinx = new SphinxQuery();
        $res = $sphinx
            ->connect('mahara_users, mahara_users_delta')
            ->admin_user_query($queries, array('c' => $constraints, 'sortby' => $sortfield, 'sortdir' => $sortdir))
            ->run($limit, $offset);

        if (empty($res['matches'])) {
            return array('count' => 0, 'limit' => $limit, 'offset' => $offset, 'data' => null);
        }

        $data = get_records_sql_assoc('
            SELECT u.id, u.firstname, u.lastname, u.username, u.email, u.staff,
                u.active, NOT u.suspendedcusr IS NULL as suspended, u.profileicon
            FROM {usr} u WHERE u.id IN (' . self::ids(array_keys($res['matches'])) . ')
                AND u.id <> 0 AND u.deleted = 0
            ', array(), null, null);

        if ($data) {
            $inst = get_records_select_array('usr_institution', 
                'usr IN (' . self::ids(array_keys($data)) . ')',
                null, '', 'usr,institution');
            if ($inst) {
                foreach ($inst as $i) {
                    $data[$i->usr]->institutions[] = $i->institution;
                }
            }
            $inst = get_records_select_array('usr_institution_request', 
                'usr IN (' . self::ids(array_keys($data)) . ')',
                null, '', 'usr,institution,confirmedusr,confirmedinstitution');
            if ($inst) {
                foreach ($inst as $i) {
                    if ($i->confirmedusr) {
                        $data[$i->usr]->requested[] = $i->institution;
                    }
                    if ($i->confirmedinstitution) {
                        $data[$i->usr]->invitedby[] = $i->institution;
                    }
                }
            }
        }

        return array(
            'count'   => $res['total_found'],
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => self::sort_results($res, $data),
        );
    }


    public static function search_group($query_string, $limit, $offset=0, $type='member', $category=0) {
        if ($query_string === '' &&  self::filter_institution() === 0) {
            return parent::search_group($query_string, $limit, $offset, $type, $category);
        }

        $sphinx = new SphinxQuery();
        $res = $sphinx->connect('mahara_groups')->group_query($query_string, array($type, $category))->run($limit, $offset);

        if (empty($res['matches'])) {
            return array('count' => '0', 'limit' => $limit, 'offset' => $offset, 'data' => array());
        }
        $sql = '
            SELECT id, name, description, grouptype, jointype, ctime, mtime,
                   public, category, urlid
            FROM {group}
            WHERE deleted = 0 AND id IN (' . self::ids(array_keys($res['matches'])) . ')';
        $data = get_records_sql_assoc($sql, null, null, null);
        return array(
            'count'   => $res['total_found'],
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => array_slice(self::sort_results($res, $data, false), $offset, $limit, true),
        );
    }


    public static function group_search_user($group, $queries, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx = null) {
        $qs = array();
        while (!empty($queries)) {
            $first = array_shift($queries);
            $last = array_shift($queries);
            $qs[] = $first['string'];
        }
        while (!empty($constraints)) {
            $first = array_shift($constraints);
            $last = array_shift($constraints);
            $qs[] = '"' . $first['string'] . ' ' . $last['string'] . '"';
        }

        $qs = implode(' ', $qs);

        return parent::group_search_user($group, $qs, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx);
    }


    public static function self_search($querystring, $limit, $offset, $type = 'all') {
        global $USER;
        if (trim($querystring) == '') {
            return false;
        }

        $view_res     = array('total' => 0);
        $artefact_res = array('total' => 0);

        $sphinx = new SphinxQuery();

        if ($type === 'artefact' || $type === 'all') {
            $artefact_res = $sphinx->connect('mahara_artefacts, mahara_artefacts_delta')->self_artefact_query($querystring)->run($limit, $offset);
        }
        if ($type === 'view' || $type === 'all') {
            $view_res = $sphinx->connect('mahara_views, mahara_views_delta')->self_view_query($querystring)->run($limit, $offset);
        }

        if ($view_res['total'] == 0 && $artefact_res['total'] == 0) {
            return array('count' => 0, 'limit' => $limit, 'offset' => $offset, 'data' => null);
        }

        $has_artefact_matches = false;
        $artefact_total = isset($artefact_res['total_found']) ? $artefact_res['total_found'] : 0;

        if (isset($artefact_res['matches']) && is_array($artefact_res['matches'])) {
            $has_artefact_matches = true;
            $sql = "
                SELECT id, artefacttype, title, description, 'artefact' AS kind
                FROM {artefact}
                WHERE id IN (" . self::ids(array_keys($artefact_res['matches'])) . ")";
        }

        if (is_array($view_res['matches'])) {
            $viewsql = "
                SELECT id, NULL AS artefacttype, title, description, 'view' AS kind
                FROM {view}
                WHERE id IN (" . self::ids(array_keys($view_res['matches'])) . ")";

            if (!$has_artefact_matches || $artefact_res['total'] == 0) {
                $sql = $viewsql;
            }
            else if ($view_res['total'] > 0) {
               $sql .= ' UNION ' . $viewsql;
            }
        }

        if (empty($sql)) {
            return array('count' => 0, 'limit' => $limit, 'offset' => $offset, 'data' => null);
        }

        $sql .= " ORDER BY title";
        $data = get_records_sql_array($sql, null, null, null);
        $results = array(
            'data'   => $data,
            'offset' => $offset,
            'limit'  => $limit,
            'count'  => $view_res['total_found'] + $artefact_total
        );

        if ($results['data']) {
            foreach ($results['data'] as &$result) {
                $newresult = array();
                foreach ($result as $key => &$value) {
                    if ($key == 'id' || $key == 'artefacttype' || $key == 'title' || $key == 'description') {
                        $newresult[$key] = $value;
                    }
                }
                $newresult['type'] = $result->kind;
                $newresult['summary'] = $newresult['description'];
                $newresult['links'] = array();
                if ($newresult['artefacttype']) {
                    $artefactplugin =
                        get_field('artefact_installed_type', 'plugin', 'name', $newresult['artefacttype']);
                    if ($artefactplugin == 'internal') {
                        $newresult['summary'] = $newresult['title'];
                        $newresult['title'] = get_string($newresult['artefacttype'], 'artefact.' . $artefactplugin);
                    }
                }
                $result = $newresult;
            }
            self::self_search_make_links($results);
        }
        self::build_excerpts($results['data'], 'mahara_views', $querystring);
        return $results;
    }


    protected static function format_content_results($res, $data, $type, $qs, $limit, $offset) {
        $newdata = array();
        $x = 0;
        foreach ($data AS $d) {
            if ($type == 'submittedtogroup') {
                $newdata[$res[$d->kind]['matches'][$d->id]['attrs']['submittedtime'] . (++$x/100)] = (array)$d;
            }
            else {
                $newdata[$res[$d->kind]['matches'][$d->id]['weight'] . (++$x/100)] = (array)$d;
            }
        }
        krsort($newdata, SORT_NUMERIC);
        $results = array_slice($newdata, $offset, $limit);
        self::build_excerpts($results, 'mahara_views', $qs);
        return $results;
    }


    /**
     * This method can be used to search for pages, users, forum posts or groups 
     *
     * @param string  $querystring 
     * @param integer $limit 
     * @param integer $offset 
     * @param string  $type  What to search. Can be one of 'all', 'forum', 'groupview', 'portfolioview', 'submittedtogroup', 'user'
     * @param boolean $retry Internally used retry flag. Whether to try wider search when we find nothing in our own institutions
     * @return array  Looks like this:
     * 
     *     array(
     *         'count' => 3,
     *         'data' => array(
     *             array(
     *                 'id'      => 11,             // id of the resource found
     *                 'oid'     => 123,            // secondary id, only used in forum results as post id for now
     *                 'kind'    => 'forum',        // result type
     *                 'title'   => "title text",
     *                 'summary' => "summary text",
     *             ),
     *             array(
     *                 ...
     *         )
     *     )
     *
     */
    public static function content_search($querystring, $limit, $offset, $type, $retry=true) {
        global $USER;
        $querystring = trim($querystring);
        if ($type != 'submittedtogroup' && strlen($querystring) < 2) {
            return array('data' => null, 'count' => 0);
        }

        $sphinx = new SphinxQuery();

        $res = array('group' => 0, 'groupview' => 0, 'portfolioview' => 0, 'forum' => 0, 'user' => 0);
        if ($type == 'all' || $type == 'group') {
            $res['group'] = $sphinx->connect('mahara_groups')->content_query($querystring, $type)->run(1000, 0);
        }
        if ($type == 'all' || $type == 'portfolioview' || $type == 'submittedtogroup' || $type == 'groupview') {
            $res['view'] = $sphinx->connect('mahara_views, mahara_views_delta')->content_query($querystring, $type)->run(1000, 0);
        }
        if ($type == 'all' || $type == 'forum') {
            $res['forum'] = $sphinx->connect('mahara_forums, mahara_forums_delta')->content_query($querystring, $type)->run(1000, 0);
        }
        if ($type == 'all' || $type == 'user') {
            $res['user'] = $sphinx->connect('mahara_users, mahara_users_delta')->user_query($querystring, array())->run(1000, 0);
        }
        if ($type == 'username') {
            $res['user'] = $sphinx->connect('mahara_users, mahara_users_delta')->user_query($querystring, array('nameonly' => true))->run(1000, 0);
        }

        $sql = static::content_sql($res);
        $data = null;
        if (!empty($sql)) {
            $data = get_records_sql_array(join(' UNION ', $sql), null, null, null);
        }
        else if (param_integer('expand', 0) && $retry) {
            // If we find nothing in our own institutions, expand.
            $_GET['inst'] = 0; // a bit rude, I know
            $_GET['expand'] = 0;
            return self::content_search($querystring, $limit, $offset, $type, false);
        }
        if (!$data) {
            return array('data' => null, 'count' => 0);
        }

        $count = count($data);
        return array(
            'data' => self::format_content_results($res, $data, $type, $querystring, $limit, $offset),
            'count' => $count
        );
    }


    protected static function content_sql($res) {
        global $USER;
        $sql = array();
        $uid = (int)$USER->get('id');
        if (!empty($res['group']['matches'])) {
            $sql[] = "
                SELECT DISTINCT g.id, 0 AS oid, 'group' AS kind, g.name AS title, g.description AS summary, 0 AS profileicon, '' AS email 
                FROM {group} g
                JOIN {group_member} gm ON g.id = gm.group
                WHERE g.id IN (". self::ids(array_keys($res['group']['matches'])) .")";
        }
        if (!empty($res['view']['matches'])) {
            $summary = self::sqlconcat('v.description', "' '", 'v.sphinxcache');
            $sql[] =
                "SELECT DISTINCT v.id, v.owner AS oid, 'view' AS kind, v.title, $summary AS summary, 0 AS profileicon, '' AS email, v.submittedtime
                FROM {view} v
                LEFT JOIN {usr_friend} uf ON (uf.usr1 = v.owner OR uf.usr2 = v.owner)
                LEFT JOIN {view_access} va ON v.id = va.view
                LEFT JOIN {group_member} gm ON va.group = gm.group AND (va.role IS NULL OR va.role = gm.role)
                LEFT JOIN {usr_institution} ui ON va.institution = ui.institution
                WHERE
                (
                    va.accesstype IN ('public', 'loggedin')
                    OR (va.accesstype = 'friends' AND (uf.usr1 = ".$uid." OR uf.usr2 = ".$uid."))
                    OR v.owner = ".$uid."
                    OR va.usr = ".$uid."
                    OR gm.member =  ".$uid."
                    OR ui.usr =  ".$uid."
                )
                AND
                (
                    v.owner = ". $uid ." OR (
                        (va.startdate IS NULL OR va.startdate >= CURRENT_DATE)
                        AND (va.stopdate IS NULL OR va.stopdate <= CURRENT_DATE)
                    )
                )
                AND v.id IN (". self::ids(array_keys($res['view']['matches'])) .")";
        }
        if (!empty($res['forum']['matches'])) {
            $sql[] = 
                "SELECT DISTINCT p.id, t.id AS oid, 'forum' AS kind,
                    " . self::sqlconcat('ii.title', "'/'", 'p2.subject') . " AS title,
                    " . self::sqlconcat('ii.description', "' - '", 'p.body') . " AS summary,
                    0 AS profileicon, '' AS email 
                FROM {interaction_forum_topic} t 
                JOIN {interaction_forum_post} p ON t.id = p.topic
                JOIN {interaction_forum_post} p2 ON p2.topic = p.topic AND p2.parent IS NULL
                JOIN {interaction_instance} ii ON t.forum = ii.id
                JOIN {group} g ON ii.group = g.id
                JOIN {group_member} gm ON g.id = gm.group
                WHERE (g.public = 1 OR gm.member = ". $uid .")
                    AND p.id IN (". self::ids(array_keys($res['forum']['matches'])) . ")";
        }
        if (!empty($res['user']['matches'])) {
            $sql[] =
                "SELECT DISTINCT u.id, 0 AS oid, 'user' AS kind,
                    CASE COALESCE(u.preferredname, '')
                        WHEN '' THEN ". self::sqlconcat('u.firstname', "' '", 'u.lastname') ."
                        ELSE u.preferredname
                    END AS title, 
                    a.title AS summary, u.profileicon, u.email
                FROM {usr} u
                LEFT JOIN {artefact} a ON u.id = a.owner AND a.artefacttype = 'introduction'
                WHERE u.id IN (" . self::ids(array_keys($res['user']['matches'])) . ")
                    AND u.deleted = 0 AND u.active = 1";
        }
        return $sql;
    }


    public static function view_search($querystring, $limit, $offset) {
        if (trim($querystring) == '') {
            return null;
        }
        $sphinx = new SphinxQuery();
        return $sphinx->connect('mahara_views, mahara_views_delta')->content_query($querystring, 'view')->run(1000, 0);
    }


    protected static function filter_institution() {
        $default = defined('INSTITUTION_FILTER') ? 1 : 0;
        return param_integer('inst', $default);
    }


    public static function has_config() {
        return true;
    }


    public static function get_config_options() {
        $elements = array();

        $script = <<<SRC
            <script type="text/javascript">
                function sphinxAsync(link,action) {
                    link.disabled = true;
                    link.style.color = '#999999';
                    var span = link.nextSibling;
                    var progress = IMG({'src': get_themeurl('images/loading.gif')});
                    replaceChildNodes(span, progress);

                    sendjsonrequest(config.wwwroot + 'search/sphinx/sphinx.json.php', {'action': action}, 'POST',
                        function (data) { replaceChildNodes(span, ''); });
                }
            </script>
SRC;

        $elements['js'] = array(
            'type'  => 'html',
            'value' => $script
        );
        $elements['reindex'] = array(
            'type'  => 'html',
            'title' => get_string('rebuild', 'search.sphinx'), 
            'description'  =>  get_string('reindexnotice', 'search.sphinx'), 
            'value' => '<input type="button" class="button"
                            onclick="sphinxAsync(this, \'reindex\');" value="reindex"
                        ><span style="padding-left:20px"></span>'
        );
        $elements['sphinxconf'] = array(
            'type'         => 'text',
            'title'        => get_string('sphinxconf', 'search.sphinx'), 
            'description'  =>  get_string('defaultconf', 'search.sphinx'), 
            'defaultvalue' => get_config_plugin('search', 'sphinx', 'sphinxconf')
        );
        $elements['searchd_hostname'] = array(
            'type'         => 'text',
            'title'        => get_string('searchdhost', 'search.sphinx'), 
            'description'  =>  get_string('defaulthost', 'search.sphinx'), 
            'defaultvalue' => get_config_plugin('search', 'sphinx', 'searchd_hostname')
        );
        $elements['searchd_port'] = array(
            'type'         => 'text',
            'title'        => get_string('searchdport', 'search.sphinx'), 
            'description'  =>  get_string('defaultport', 'search.sphinx'), 
            'defaultvalue' => get_config_plugin('search', 'sphinx', 'searchd_port')
        );
        $elements['indexerbin'] = array(
            'type'         => 'text',
            'title'        => get_string('indexerbin', 'search.sphinx'), 
            'description'  =>  get_string('defaultindexerbin', 'search.sphinx'), 
            'defaultvalue' => get_config_plugin('search', 'sphinx', 'indexerbin'),
            'rules'        => array('regex' => '#^[\w/]*$#')
        );
        $elements['not_searchable_display'] = array(
            'type'         => 'textarea',
            'rows'         => 10,
            'cols'         => 60,
            'title'        => get_string('notsearchable', 'search.sphinx'), 
            'description'  => get_string('notsearchabledesc', 'search.sphinx'), 
            'defaultvalue' => get_config_plugin('search', 'sphinx', 'not_searchable_display'),
            'style'        => 'white-space:nowrap',
            'resizable'    => false,
        );
        return array(
            'elements' => $elements,
            'renderer' => 'table'
        );
    }


    public function save_config_options($values) {
        global $USER;
        if (!$USER->get('admin')) {
            throw new AccessDeniedException();
        }

        set_config_plugin('search', 'sphinx', 'sphinxconf', $values['sphinxconf']);
        set_config_plugin('search', 'sphinx', 'searchd_hostname', $values['searchd_hostname']);
        set_config_plugin('search', 'sphinx', 'searchd_port', $values['searchd_port']);
        set_config_plugin('search', 'sphinx', 'indexerbin', $values['indexerbin']);

        if (!empty($values['not_searchable_display'])) {
            $display = implode("','", explode("\\n", db_quote($values['not_searchable_display'])));
            $sql = "SELECT displayname, COALESCE(name, 'mahara') AS name FROM institution WHERE displayname IN ($display)";
            if ($names = get_records_sql_assoc($sql, null)) {
                $display2 = array();
                $names2 = array();
                foreach (split("\n", $values['not_searchable_display']) AS $d) {
                    if (isset($names[$d])) {
                        $display2[] = $names[$d]->displayname;
                        $names2[] = crc32($names[$d]->name);
                    }
                }
                set_config_plugin('search', 'sphinx', 'not_searchable', join("\n", $names2));
                set_config_plugin('search', 'sphinx', 'not_searchable_display', join("\n", $display2));
                return;
            }
        }
        set_config_plugin('search', 'sphinx', 'not_searchable', '');
        set_config_plugin('search', 'sphinx', 'not_searchable_display', '');
    }


    protected static function ids($matches) {
        if (empty($matches)) {
            return 'NULL';
        }
        foreach ($matches as &$id) {
            $id = (int)$id;
        }
        return join(',', $matches);
    }


    protected static function sqlconcat() {
        $args = func_get_args();
        if (is_postgres()) {
            return join(' || ', $args);
        }
        return "CONCAT(" . join(', ', $args) . ")";
    }
}
