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
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
safe_require('artefact', 'studyjournal');
safe_require('interaction', 'pages');

if (!is_teacher()) {
    throw new AccessDeniedException();
}

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$sortby = param_variable('sortby', 'modified') === 'modified' ? 'lastchanged' : 'title';
$sortdir = $sortby === 'lastchanged' ? 'desc' : 'asc';
$publicity = param_variable('shared', 'all');
$name = param_variable('name', '');

// Access settings
$access = array();
$getpublic = $publicity === 'all' || $publicity === 'public';
$getpublished = $publicity === 'all' || $publicity === 'published';

if ($getpublic) {
    $access[] = 'public';
}
if ($getpublished) {
    $access = array_merge($access, array('user', 'friend', 'group', 'institution', 'loggedin', 'token'));
}

$results = PluginArtefactStudyJournal::get_shared_journals($query, $limit, $offset,
        $sortby, $sortdir, $access, $name);
$ret = array('total' => $results->count, 'html' => '');

if (is_array($results->data)) {
    PluginInteractionPages::get_sharedview_accessrecord($results->data);

    $wwwroot = get_config('wwwroot');
    $pubdesc = get_string('thepublicityofthispageis', 'interaction.pages');
    $sm = smarty_core();

    foreach ($results->data as &$page) {
        $page['menuitems'] = array();

        if (isset($page['is_editable']) && $page['is_editable']) {
            $page['menuitems'][] = array(
                'url' => $wwwroot . 'view/blocks.php?id=' . $page['id'],
                'title' => get_string('editcontentandlayout', 'view')
            );

            $page['menuitems'][] = array(
                'url' => $wwwroot . 'view/access.php?id=' . $page['id'] . '&backto=interaction/pages/sharedviews.php',
                'title' => get_string('editaccess', 'view'),
                'classes' => 'editaccess'
            );
        }

        if (isset($page['is_removable']) && $page['is_removable']) {
            $page['menuitems'][] = array(
                'url' => $wwwroot . 'view/delete.php?id=' . $page['id'],
                'title' => get_string('deletethisview', 'view')
            );
        }

        $shared = explode(' ', $page['shared_to']);
        $author = isset($page['user']) ? full_name($page['user']) : '';

        $sm->assign('id', $page['id']);
        $sm->assign('author', $author);

        if (!empty($page['owner'])) {
            $sm->assign('author_id', $page['owner']);
        }

        $sm->assign('uniqueid', $page['type'] . '-' . $page['id']);
        $sm->assign('url', $page['fullurl']);
        $sm->assign('title', $page['displaytitle']);
        $sm->assign('publicity', $page['shared_to']);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue', get_string($shared[0], 'interaction.pages'));
        $sm->assign('menuitems', $page['menuitems']);
        $sm->assign('extraclasses', $page['type'] . '-item');
        $sm->assign('mtime', $page['mtime']);
        $sm->assign('description', strip_tags($page['description']));
        $sm->assign('cannoteditaccess', true);

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

json_reply(false, $ret);
