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
 * @subpackage artefact-studyjournal
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');

list($collections, $views) = View::get_views_and_collections($USER->id);

$categorizedviews = array();
$categorizedcollections = array();

if (is_array($views)) {
    foreach ($views as $view) {
        if (!isset($categorizedviews[$view['type']])) {
            $categorizedviews[$view['type']] = array(
                'views' => array(),
                'title' => get_string('title_' . $view['type'], 'artefact.studyjournal')
            );
        }

        $categorizedviews[$view['type']]['views'][] = $view;
    }
}

if (is_array($collections)) {
    foreach ($collections as $collection) {
        $type = empty($collection['type']) ? '' : $collection['type'];

        if (!isset($categorizedcollections[$type])) {
            $title = empty($type) ? get_string('Collections', 'collection') : get_string($type . 's', 'interaction.' . $type);
            $categorizedcollections[$type] = array(
                'collections' => array(),
                'title' => $title
            );
        }

        $categorizedcollections[$type]['collections'][] = $collection;
    }
}

$smarty = smarty_core();
$smarty->assign('views', $categorizedviews);
$smarty->assign('collections', $categorizedcollections);

json_reply(false, array('html' => $smarty->fetch('artefact:studyjournal:linktojournal.tpl')));