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
 * @subpackage interaction-learningobject
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('collection.php');

$id = param_integer('id');

try {
    $copyid = create_from_collection($id);
    json_reply(false, array('copyid', $copyid));

} catch (Exception $ex) {
    json_reply(true, $ex->getMessage());
}

function create_from_collection($id) {
    global $USER, $SESSION;

    $values = array();
    $values['new'] = 1;
    $values['owner'] = $USER->get('id');
    $values['type'] = 'learningobject';

    list($collection, $template, $copystatus) = Collection::create_from_template($values,
            $id, null, true, true);

    if (isset($copystatus['quotaexceeded'])) {
        throw new Exception(get_string('collectioncopywouldexceedquota', 'collection'));
    }

    // Set the new learning object private by default.
    $viewids = $collection->get_viewids();
    $config = array(
        'allowcomments' => 1,
        'approvecomments' => 1,
        'template' => 1,
        'accesslist' => array(),
        'startdate' => null,
        'stopdate' => null,
        'retainview' => false
    );

    View::update_view_access($config, $viewids);

    $SESSION->add_ok_msg(get_string('copiedpagesblocksandartefactsfromtemplate',
            'collection', $copystatus['pages'], $copystatus['blocks'],
            $copystatus['artefacts'], $template->get('name')));

    return $collection->get('id');
}