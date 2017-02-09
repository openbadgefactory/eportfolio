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
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('ADMIN', 1);
define('CLI', 1);

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('cli.php');
require_once('institution.php');
require_once('user.php');

$cli = get_cli();
$options = array();

$options['from'] = new stdClass();
$options['from']->examplevalue = '\'myinstitution\'';
$options['from']->shortoptions = array('f');
$options['from']->description = 'Add users from this institution. If not set, all '
        . 'users will be added. Separate multiple institutions with commas.';
$options['from']->required = false;
$options['from']->defaultvalue = 'all';

$options['to'] = new stdClass();
$options['to']->examplevalue = '\'anotherinstitution\'';
$options['to']->shortoptions = array('t');
$options['to']->description = 'Add users to this institution.';
$options['to']->required = true;

$options['copyroles'] = new stdClass();
$options['copyroles']->examplevalue = '0';
$options['copyroles']->shortoptions = array('c');
$options['copyroles']->description = 'If set, the admin and staff users will have '
        . 'the same roles in the target institution also.';
$options['copyroles']->required = false;
$options['copyroles']->defaultvalue = 1;

$settings = new stdClass();
$settings->options = $options;
$settings->info = 'Adds existing users to selected institution.';

$cli->setup($settings);

try {
    $from = $cli->get_cli_param('from');
    $to = $cli->get_cli_param('to');
    $copyroles = $cli->get_cli_param('copyroles');
    
    try {
        $USER->find_by_id(1); // To bypass admin checks.
        
        $targetinstitution = new Institution($to);
        $userids = array();
        
        // Get all users.
        if ($from === 'all') {
            $userids = get_column_sql("
                SELECT id
                  FROM {usr}
                 WHERE id > 0 AND deleted = ? AND id NOT IN (
                    SELECT usr
                      FROM {usr_institution}
                     WHERE institution = ?)", array(0, $to));
        }
        
        // Get users from selected institutions.
        else {
            $instlist = implode(',', array_map('db_quote', explode(',', $from)));
            $userids = get_column_sql("
                SELECT ui.usr
                  FROM {usr_institution} ui
             LEFT JOIN {usr} u ON ui.usr = u.id
                 WHERE institution IN ($instlist) AND u.deleted = ? AND ui.usr NOT IN (
                    SELECT usr
                      FROM {usr_institution}
                     WHERE institution = ?)", array(0, $to));
        }
        
        // Just ensure we're handling a unique array.
        $userids = is_array($userids) ? array_unique($userids) : array();
        
        log_info('---------- Number of users to be added: ' . count($userids));
       
        // Add users to institution.
        $targetinstitution->add_members($userids);
        
        // Set admin and staff roles to target institution.
        if ($copyroles) {
            $instwhere = "";
            
            if ($from !== 'all') {
                $instlist = implode(',', array_map('db_quote', explode(',', $from)));
                $instwhere = " AND institution IN ($instlist)";
            }
            
            // Update admin roles.
            execute_sql("
                UPDATE {usr_institution}
                   SET admin = 1
                 WHERE institution = ? AND usr IN (
                    SELECT usr FROM (
                        SELECT usr
                          FROM {usr_institution}
                         WHERE admin = 1
                         $instwhere) AS tmp)", array($to));
            
            // Update staff roles.
            execute_sql("
                UPDATE {usr_institution}
                   SET staff = 1
                 WHERE institution = ? AND usr IN (
                    SELECT usr FROM (
                        SELECT usr
                          FROM {usr_institution}
                         WHERE staff = 1
                         $instwhere) AS tmp)", array($to));
        }
    }
    catch (ParamOutOfRangeException $e) {
        throw new Exception('Target institution "' . $to . '" not found.');
    }
} catch (Exception $ex) {
    $USER->logout();
    cli::cli_exit($ex->getMessage(), true);
}

$USER->logout();
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);