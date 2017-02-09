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
 * @subpackage local
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

$viewid = param_integer('viewid', NULL);
$collectionid = param_integer('collectionid', NULL);
$groupid = param_integer('groupid', NULL);
$forme = param_boolean('forme', FALSE);

try {
    // $copyid can be either collection or view id.
    $copyid = copy_view($viewid, $collectionid, $groupid, $forme);
    json_reply(false, array('copyid' => $copyid));
} catch (Exception $ex) {
    json_reply(true, $ex->getMessage());
}

function copy_view($viewid, $collectionid = NULL, $groupid = NULL,
                   $forme = FALSE) {
    global $SESSION, $USER;

    $values = array();
    $values['new'] = 1;

    // <EKAMPUS
    // Comments have to be approved by default.
    $values['approvecomments'] = 1;
    // EKAMPUS>

    if ($forme) {
        $values['owner'] = $USER->get('id');
    }
    elseif ($groupid) {
        $values['owner'] = NULL;
        $values['group'] = $groupid;
    }
    else {
        $values['owner'] = $USER->get('id');
    }

    if ($collectionid) {
        require_once(get_config('libroot') . 'collection.php');
        $templateid = $collectionid;
        list($collection, $template, $copystatus) = Collection::create_from_template($values,
                        $templateid);
        if (isset($copystatus['quotaexceeded'])) {
            // FIXME: This is a JSON script, this doesn't work here.
            $SESSION->add_error_msg(get_string('collectioncopywouldexceedquota',
                            'collection'));
            redirect(get_config('wwwroot') . 'view/choosetemplate.php');
        }
        $SESSION->add_ok_msg(get_string('copiedpagesblocksandartefactsfromtemplate',
                        'collection', $copystatus['pages'],
                        $copystatus['blocks'], $copystatus['artefacts'],
                        $template->get('name'))
        );

        return ($collection->get('id'));
    }
    elseif ($viewid) {
        $templateid = $viewid;
        list($view, $template, $copystatus) = View::create_from_template($values,
                        $templateid);
        if (isset($copystatus['quotaexceeded'])) {
            $SESSION->add_error_msg(get_string('viewcopywouldexceedquota',
                            'view'));
            redirect(get_config('wwwroot') . 'view/choosetemplate.php');
        }
        $SESSION->add_ok_msg(get_string('copiedblocksandartefactsfromtemplate',
                        'view', $copystatus['blocks'], $copystatus['artefacts'],
                        $template->get('title'))
        );
        return ($view->get('id'));
    }
}
