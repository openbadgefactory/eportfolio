<?php
defined('INTERNAL') || die();

require 'sphinxapi.php';

class SphinxQuery {

    public $client;
    public $index;
    public $query;

    public function connect($index) {
        if ($this->client) {
            $this->client->Close();
        }
        $this->client = new SphinxClient();
        $host = get_config_plugin('search', 'sphinx', 'searchd_hostname') ?: 'localhost';
        $port = get_config_plugin('search', 'sphinx', 'searchd_port')     ?: 9312;
        if ($host && $port) {
            $this->client->SetServer($host, (int)$port);
        }
        $this->client->SetRankingMode(SPH_RANK_NONE);
        $this->client->SetMatchMode(SPH_MATCH_EXTENDED2);

        $this->index = $index;

        return $this;
    }


    public function filter_institution() {
        $default = defined('INSTITUTION_FILTER') ? 1 : 0;
        return param_integer('inst', $default);
    }


    public function excluded_institutions() {
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


    public function format_query($query, $relaxed=true) {
        $query = trim(mb_strtolower($query, 'UTF-8'));
        if (empty($query)) {
            return '';
        }
        if (preg_match('/".*"/', $query)) {
            return $query;
        }
        $parts = preg_split('/\s+/', $query);
        $query = array(); 
        if ($relaxed) {
            $query[] = '@@relaxed'; 
        }
        $empty = true;
        foreach ($parts as $part) {
            if (strlen($part) > 1) { 
                $empty = false;
                if (preg_match('/\+|\*|\(|\)|=|\||@|\/|\[|\]|<<|\^|\$|^\-|!/', $part)) {
                    // pass special syntax unchanged, otherwise search also with partial words 
                    $query[] = $part;
                }
                else { 
                    $part = str_replace('-', '\-', $part); //We want hyphens, not negation.
                    $query[] = "($part | *$part*)";
                }
            }
        }
        return $empty ? '' : join(' ', $query);
    }


    public function run($limit, $offset) {
        $this->client->SetLimits($offset, $limit);

        $res = $this->client->Query($this->query, $this->index);

        $err  = $this->client->GetlastError();
        $warn = $this->client->GetlastWarning();
        if ($err) {
            error_log($err);
        }
        if ($warn) {
            error_log($warn);
        }
        $res['matches'] = isset($res['matches']) ? $res['matches'] : array();

        if (isset($res['total_found']) && $res['total_found'] > 1000) {
            $res['total_found'] = 1000;
        }
        return $res;
    }


    public function build_excerpts(&$res) {
        $docs = array();
        foreach ($res AS $r) {
            if (isset($r['summary'])) {
                $docs[] = strip_tags(clean_html($r['summary']));
            }
        }
        $excerpts = $this->client->BuildExcerpts($docs, $this->index, $this->query);
        foreach ($res AS &$r) {
            if (isset($r['summary'])) {
                $r['summary'] = array_shift($excerpts);
            }
        }
    }


    public function user_query($query, $data) {
        global $USER;

        $query = $this->format_query($query, false);

        if (isset($data['nameonly']) || preg_match('/access\.json/', $_SERVER['PHP_SELF'])) {
            $query = "( @(firstname,lastname,preferredname) ( $query ) )";
        }
        else if (!$USER->get('admin') && !$USER->get('staff') && !empty($query)) {
            // Admins and staff can search all fields.
            // Others have limitations based on site settings.
            $query = "( $query )";
            $common = "introduction,instname,profile";
            if (get_config('searchusernames')) {
                $common .= ",username";
            }
            if (get_config('userscanhiderealnames')) {
                $query = "( (@preferredname $query) | (@($common) $query) ) | ( (@preferredname =__NOPREFERREDNAME__) & ((@(firstname,lastname) $query) | (@($common) $query)) )";
            }
            else {
                $query = "( (@(firstname,lastname,preferredname) $query) | (@($common) $query) )";
            }
        }

        $this->query = $query;

        $this->client->SetSortMode(SPH_SORT_ATTR_ASC, 'lastname_sort');

        if ($this->filter_institution()) {
            $insts = $USER->get('institutions');
            if (empty($insts)) {
                $insts = array(array('institution' => 'mahara'));
            }
            $inst = array();
            foreach ($insts as $i) {
                $i = (object) $i;
                $inst[] = crc32($i->institution);
            }
            if (!empty($inst)) {
                $this->client->SetFilter('institution', $inst);
            }
        }
        $this->client->SetFilter('myid', array($USER->id), true);
        $exclude = $this->excluded_institutions();
        if (!empty($exclude)) {
            $this->client->SetFilter('institution', $exclude, true);
        }

        if (!empty($data['friends'])) {
            $this->client->SetFilter('friends', array($USER->id));
        }

        return $this;
    }


    public function admin_user_query($queries, $data) {

        $pcs = array('contains' => array(), 'starts'=> array(), 'equals' => array(), 'in' => array());
        foreach ($queries AS $q) {
            $pcs[ $q['type'] ][ $this->client->EscapeString($q['string']) ] = 1;
        }
        $pcs['contains'] = !empty($pcs['contains']) ? join(' ', array_keys($pcs['contains']))         : '';
        $pcs['starts']   = !empty($pcs['starts'])   ? join('* ', array_keys($pcs['starts']))          : '';
        $pcs['equals']   = !empty($pcs['equals'])   ? '=' . join(' =', array_keys($pcs['equals']))    : '';
        $pcs['in']       = !empty($pcs['in'])       ? '(' . join(' | ', array_keys($pcs['in'])) . ')' : '';

        $this->query = trim(join(' ', $pcs));

        $constraints = $data['c'];
        $inst_filter = array();
        $usr_filter = array();
        $filter_by_duplicate = false;
        foreach ($constraints AS $c) {
            if (strpos($c['field'], 'institution') !== false) {
                if (is_array($c['string'])) {
                    foreach ($c['string'] AS $i) {
                        $inst_filter[] = crc32($i);
                    }
                }
                else {
                    $inst_filter[] = crc32($c['string']);
                }
            }
            else if ($c['field'] == 'duplicateemail') {
                $filter_by_duplicate = true;
                if (is_array($c['string'])) {
                    $uids = get_column_sql(
                            'SELECT owner '
                            . 'FROM {artefact} '
                            . 'WHERE id IN (' . join(',', array_map('db_quote', $c['string'])) . ')');

                    foreach ($uids as $uid) {
                        $usr_filter[] = $uid;
                    }
                }
                // PENDING: Can it be something other than an array?
                else {

                }
            }
            else if ($c['type'] === 'starts') {
                $this->query .= ' @' . $c['field'] . ' ' . $c['string'] . '*';
            }
            else {
                $this->query .= ' @' . $c['field'] . ' ' . $c['string'];
            }
        }
        if (!empty($inst_filter)) {
            $this->client->SetFilter('institution', $inst_filter);
        }
        if ($filter_by_duplicate) {
            $this->client->SetFilter('myid', $usr_filter);
        }
        $mode = array('ASC' => SPH_SORT_ATTR_ASC, 'DESC' => SPH_SORT_ATTR_DESC);
        $this->client->SetSortMode($mode[strtoupper($data['sortdir'])], $data['sortby'] . '_sort');

        return $this;
    }


    public function group_query($query, $args) {
        global $USER;

        list($type, $category) = $args;

        if ($this->filter_institution()) {
            // filter by user's own institution(s)
            $inst = array();
            foreach ($USER->get('institutions') as $i) {
                $inst[] = crc32($i->institution);
            }
            if (!empty($inst)) {
                $this->client->SetFilter('institution', $inst);
            }
        }

        $this->query = $this->format_query($query);

        if ($type == 'member') {
            $this->client->SetFilter('members', array($USER->id));
        }
        else if ($type == 'notmember') {
            $this->client->SetFilter('members', array($USER->id), true);
        }
        $this->client->SetSortMode(SPH_SORT_ATTR_ASC, 'name_sort');
        $exclude = $this->excluded_institutions();
        if (!empty($exclude)) { 
            $this->client->SetFilter('institution', $exclude, true);
        }
        if ($category != 0) {
            if ($category == -1) {
                $category = 0; // 'No category' is marked as zero in Sphinx index
            }
            $this->client->SetFilter('category', array($category));
        }

        return $this;
    }


    public function self_artefact_query($query) {
        global $USER;
        $this->client->SetFilter('owner', array($USER->get('id')));
        $this->client->SetSortMode(SPH_SORT_RELEVANCE);
        $this->client->SetRankingMode(SPH_RANK_PROXIMITY_BM25);

        $this->query = $this->format_query($query);

        return $this;
    }


    public function self_view_query($query) {
        global $USER;
        $this->client->SetFilter('owner', array($USER->get('id')));
        $this->client->SetSortMode(SPH_SORT_RELEVANCE);
        $this->client->SetRankingMode(SPH_RANK_PROXIMITY_BM25);

        $this->query = $this->format_query($query);

        return $this;
    }

    public function content_query($query, $type) {
        global $USER;

        if ($this->filter_institution()) {
            // filter by user's own institution(s)
            $insts = $USER->get('institutions');
            if (empty($insts)) {
                $insts = array(array('institution' => 'mahara'));
            }
            $inst = array();
            foreach ($insts as $i) {
                $i = (object) $i;
                $inst[] = crc32($i->institution);
            }
            if (!empty($inst)) {
                $this->client->SetFilter('institution', $inst);
            }
        }

        if ($type == 'groupview') {
            $this->client->SetFilter('groupview', array(1));
        }
        if ($type == 'portfolioview') {
            $this->client->SetFilter('groupview', array(1), true);
        }
        if ($type == 'submittedtogroup') {
            $group = param_integer('group', null);
            if (!$group) {
                // submitted to any group
                $this->client->SetFilter('submittedgroup', array(0), true);
            }
            else {
                // specific group
                $this->client->SetFilter('submittedgroup', array($group));
            }
            $this->client->SetSortMode(SPH_SORT_ATTR_DESC, 'submittedtime');
        }
        else {
            $this->client->SetSortMode(SPH_SORT_RELEVANCE);
            $this->client->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
        }

        $this->query = $this->format_query($query);

        $exclude = $this->excluded_institutions();
        if (!empty($exclude)) {
            $this->client->SetFilter('institution', $exclude, true);
        }

        return $this;
    }

}
