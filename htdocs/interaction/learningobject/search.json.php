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
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('collection.php');

safe_require('interaction', 'learningobject');
safe_require('interaction', 'pages');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$sortby = param_variable('sortby', 'modified');
$sharedparam = param_variable('shared', null);
$ownerquery = param_variable('ownerquery', null);

$owned = array();
$share = array();
$sorting = array();

$sortdesc = $sortby === 'modified';
$shared = is_array($sharedparam) ? $sharedparam : (!empty($sharedparam) ? explode(',', $sharedparam) : array());
$getpublic = in_array('public', $shared);
$getpublished = in_array('published', $shared);
$getown = in_array('own', $shared);
$getall = in_array('all', $shared);
$getprivate = ($getown || $getall) && !$getpublic && !$getpublished;
$types = array('learningobject');

if ($getpublic || !$getpublished) {
    $share[] = 'public';
}

if ($getpublished || !$getpublic) {
    $share = array_merge($share,
            array('user', 'friend', 'group', 'institution',
        'loggedin', 'token'));
}

if ($getprivate) {
    $share[] = 'private';
}

if ($getown || $getall) {
    $share[] = 'own';
}

// Sorting settings.
if ($sortby === 'modified') {
    $sorting[] = array('column' => 'lastchanged', 'desc' => $sortdesc);
}
else {
    $sorting[] = array('column' => 'name', 'desc' => $sortdesc, 'tablealias' => 'c');
}

if ($getown) {
    $owned['owner'] = $USER->id;
}
else {
    $owned['multiple'] = true;
}

$ownedby = (empty($owned)) ? null : (object) $owned;
$results = Collection::collection_search($query, $ownedby, $limit, $offset,
                $sorting, $share, null, $ownerquery, $types);
$pages = $results->data;

PluginInteractionPages::get_sharedview_accessrecord($pages, 'view_id');

$ret = array('total' => $results->count, 'html' => '');

if ($pages) {
    $pubdesc = get_string('thepublicityofthispageis',
            'interaction.learningobject');
    $sm = smarty_core();
    $wwwroot = get_config('wwwroot');
    $is_teacher = is_teacher();

    // Get the assignation status of the learning objects.
    InteractionLearningobjectInstance::add_assignation_status($USER, $pages);

    foreach ($pages as &$page) {
        $page['data'] = array('type' => 'learningobject');
        $page['menuitems'] = array();

        // Own page
        if (isset($page['is_editable']) && $page['is_editable']) {
            $page['menuitems'] = array(
                array(
                    'url' => $wwwroot . 'interaction/learningobject/assign.php?id=' . $page['id'],
                    'title' => get_string('assign', 'interaction.learningobject')
                ),
                array(
                    'url' => $wwwroot . 'collection/edit.php?id=' . $page['id'],
                    'title' => get_string('edittitleanddesc', 'collection')
                ),
                array(
                    'url' => $wwwroot . 'collection/views.php?id=' . $page['id'],
                    'title' => get_string('manageviews', 'collection')
                )
            );

            if (!empty($page['first_view_id'])) {
                // Access settings
                $page['menuitems'][] = array(
                    'url' => $wwwroot . 'view/access.php?id=' . $page['first_view_id'] .
                        '&hidetabs=1&backto=interaction/learningobject/index.php',
                    'classes' => 'editaccess',
                    'title' => get_string('editaccess', 'view')
                );
                // Copy learning object.
                $page['menuitems'][] = array(
                    'title' => get_string('copycollection', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copypage'
                );
                // Copy learning object to skills folder.
                $page['menuitems'][] = array(
                    'title' => get_string('copytoskillsfolder', 'interaction.learningobject'),
                    'url' => '#',
                    'classes' => 'copytoskillsfolder'
                );
            }

            // Removable
            if (isset($page['is_removable']) && $page['is_removable']) {
                $page['menuitems'][] = array(
                    'url' => $wwwroot . 'collection/delete.php?id=' . $page['id'],
                    'title' => get_string('delete')
                );
            }
        }

        // Creation of another user, but copyable.
        else if (isset($page['view_template']) && $page['view_template'] == 1) {
            // Copy learning object (only teachers).
            if ($is_teacher) {
                $page['menuitems'][] = array(
                    'url' => '#',
                    'title' => get_string('copycollection', 'interaction.learningobject'),
                    'classes' => 'copypage'
                );
            }

            // Copy learning object to skills folder.
            $page['menuitems'][] = array(
                'title' => get_string('copytoskillsfolder', 'interaction.learningobject'),
                'url' => '#',
                'classes' => 'copytoskillsfolder'
            );
        }

        $shared = explode(' ', $page['shared_to']);
        $author = isset($page['user']) ? full_name($page['user']) : '';

        $sm->assign('id', $page['id']);
        $sm->assign('author', $author);

        // Was dis?
        $sm->assign('author_id', $page['owner'] ? $page['owner'] : null);

        $view = !empty($page['first_view_id']) ? $page['first_view_id'] : null;
        $url = !empty($page['first_view_id'])
            ? $wwwroot . 'view/view.php?id=' . $page['first_view_id']
            : $wwwroot . 'collection/views.php?id=' . $page['id'];

        $sm->assign('view', $view);
        $sm->assign('uniqueid', $page['type'] . '-' . $page['id']);
        $sm->assign('url', $url);
        $sm->assign('title', $page['name']);
        $sm->assign('publicity', $page['shared_to']);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue',
                get_string($page['is_assigned'] ? 'assignedtoyou' : $shared[0], 'interaction.learningobject'));
        $sm->assign('menuitems', $page['menuitems']);
        $sm->assign('extraclasses', 'learningobject-item' . ($page['is_assigned'] ? ' assigned' : ''));
        $sm->assign('mtime', $page['mtime']);
        $sm->assign('extradata', $page['data']);
        $sm->assign('type', 'learningobject');

        // Make box bottoms unclickable for others' views or if there are no
        // views in this learning object.
        $sm->assign('cannoteditaccess', is_null($view) || !in_array('own', $shared));

        if (!empty($page['rtime'])) {
            $formatteddate = format_date($page['rtime'], 'strftimedateshort');
            $sm->assign('headdata', '<span class="return_date" title="' .
                    get_string('returndate', 'interaction.learningobject', $formatteddate) .
                    '">' . $formatteddate . '</span>');
        }
        else {
            $sm->assign('headdata', null);
        }

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

json_reply(false, $ret);