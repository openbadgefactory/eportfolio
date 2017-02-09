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
 * @subpackage artefact.epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 20);
$sortby = param_variable('sortby', 'modified');
$institution = is_teacher() ? param_variable('institution', null) : null;
$authorname = param_variable('ownerquery', '');

// Students do not have templates.
$publicity = is_teacher() ? param_variable('shared', 'all') : 'others';

$templates = ArtefactTypeEpsp::get_templates($query, $offset, $limit, $sortby,
        $publicity, $institution, $authorname);
$ret = array('total' => $templates['count'], 'html' => '');

if (count($templates['items']) > 0) {
    $sm = smarty_core();
    $pubdesc = get_string('publicityofthistemplateis', 'artefact.epsp');
    $wwwroot = get_config('wwwroot');

    foreach ($templates['items'] as $template) {
        $sm->assign('author', $template->author);
        $sm->assign('id', $template->id);
        $sm->assign('extraclasses', 'epsp-item epsp-template');
        $sm->assign('type', 'epsp');
        $sm->assign('author_id', $template->owner);
        $sm->assign('title', $template->title);
        $sm->assign('url', $wwwroot . 'artefact/epsp/fields.php?id=' . $template->id);
        $sm->assign('publicitydescription', $pubdesc);
        $sm->assign('publicityvalue', get_string($template->publicity, 'artefact.epsp'));
        $sm->assign('menuitems', $template->menuitems);
        $sm->assign('tags', $template->jsontags);
        $sm->assign('mtime', $template->mtime);
        $sm->assign('description', $template->description);
        $sm->assign('publicity', $template->publicity);
        $sm->assign('cannoteditaccess', ($template->owner != $USER->get('id')));

        $ret['html'] .= $sm->fetch('gridder/item.tpl');
    }
}

json_reply(false, $ret);
