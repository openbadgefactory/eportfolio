<?php
/**
 *
 * @package    mahara
 * @subpackage auth-ldap
 * @author     Patrick Pollet <pp@patrickpollet.net>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 INSA de Lyon France
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    Moodle - Modular Object-Oriented Dynamic Learning Environment
 *             http://moodle.com
 *
 *    Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details:
 *
 *             http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Specialized version of auth/ldap/cli/sync_groups_attribute.php script.
 *
 * This script loops through all LDAP-configured institutions and updates groups and roles
 * based on eKampus specific attributes.
 *
 */


define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . 'auth/ldap/lib.php');

// must be done before any output
$USER->reanimate(1, 1);

require_once(get_config('libroot') . 'institution.php');
require_once(get_config('libroot') . 'group.php');
require_once(get_config('libroot') . 'searchlib.php');
require_once(dirname(dirname(__FILE__))) . '/lib.php';

$cli = get_cli();

$options = array();

$options['attribute'] = new stdClass();
$options['attribute']->examplevalue = '\'eduhelcoursecode,eduhelclasscode\'';
$options['attribute']->shortoptions = array('a');
$options['attribute']->description = get_string('attributename', 'auth.ldap');
$options['attribute']->required = false;
$options['attribute']->defaultvalue = 'eduhelcoursecode,eduhelclasscode';

$options['exclude'] = new stdClass();
$options['exclude']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['exclude']->shortoptions = array('x');
$options['exclude']->description = get_string('excludelist', 'auth.ldap');
$options['exclude']->required = false;
$options['exclude']->defaultvalue = -1;

$options['include'] = new stdClass();
$options['include']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['include']->shortoptions = array('o');
$options['include']->description = get_string('includelist', 'auth.ldap');
$options['include']->required = false;
$options['include']->defaultvalue = -1;

$options['contexts'] = new stdClass();
$options['contexts']->examplevalue = '\'ou=students,ou=pc,dc=insa-lyon,dc=fr[;anothercontext]\'';
$options['contexts']->shortoptions = array('c');
$options['contexts']->description = get_string('searchcontexts', 'auth.ldap');
$options['contexts']->required = false;
$options['contexts']->defaultvalue = -1;

$options['searchsub'] = new stdClass();
$options['searchsub']->examplevalue = '0';
$options['searchsub']->shortoptions = array('s');
$options['searchsub']->description = get_string('searchsubcontextscliparam', 'auth.ldap');
$options['searchsub']->required = false;
$options['searchsub']->defaultvalue = -1;

$options['grouptype'] = new stdClass();
$options['grouptype']->examplevalue = 'system|course|standard';
$options['grouptype']->shortoptions = array('t');
$options['grouptype']->description = get_string('grouptype', 'auth.ldap');
$options['grouptype']->required = false;
$options['grouptype']->defaultvalue = 'system';

$options['dryrun'] = new stdClass();
$options['dryrun']->description = get_string('dryrun', 'auth.ldap');
$options['dryrun']->required = false;


$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_info_sync_groups_attribute', 'auth.ldap');

$cli->setup($settings);


$attributenames = array_map( function ($s) {return trim($s);}, explode(',', $cli->get_cli_param('attribute')) );

$opt = _get_opt($cli);

foreach (_get_institutions() AS $inst) {
    if ($inst == 'stadinecampus') {
        continue;
    }
    $opt->institutionname = $inst;
    foreach ($attributenames AS $attrib) {
        $opt->attributename = $attrib;
        try {
            _do_sync($opt);
        }
        catch (Exception $e) {
            log_info($e->getMessage());
        }
    }
}

$USER->logout(); // important
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);

///

function _get_institutions() {
    $inst = get_column('auth_instance', 'institution', 'authname', 'ldap');
    if ( ! $inst) {
        $inst = array();
    }
    return $inst;
}

///

function _get_opt($cli) {
    $opt = new stdclass();

    $opt->excludelist = $cli->get_cli_param('exclude');
    if ($opt->excludelist == -1) {
        $opt->excludelist = null;
    }

    $opt->includelist = $cli->get_cli_param('include');
    if ($opt->includelist == -1) {
        $opt->includelist = null;
    }

    $opt->onlycontexts = $cli->get_cli_param('contexts');
    if ($opt->onlycontexts == -1) {
        $opt->onlycontexts = null;
    }

    $opt->searchsub = $cli->get_cli_param('searchsub');
    if ($opt->searchsub == -1) {
        $opt->searchsub = null;
    }

    $opt->grouptype = $cli->get_cli_param('grouptype');

    $opt->dryrun = $cli->get_cli_param('dryrun');

    return $opt;
}

///

function _do_sync($opt) {
    auth_ldap_sync_groups(
        $opt->institutionname,
        false, // syncbyclass
        $opt->excludelist,
        $opt->includelist,
        $opt->onlycontexts,
        $opt->searchsub,
        $opt->grouptype,
        true, // docreate
        null, // nestedgroups
        null, // groupclass
        null, // groupattribute
        true, // syncbyattribute
        $opt->attributename,
        null, // attrgroupnames
        $opt->dryrun
    );
}
