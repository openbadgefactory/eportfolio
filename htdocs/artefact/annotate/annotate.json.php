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
 * @subpackage artefact-annotate
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('activity.php');

safe_require('artefact', 'comment');
safe_require('artefact', 'annotate');

$action = param_alphanumext('action');

$artefact = param_integer('artefact');
$view = param_integer('view');

if ( ! artefact_in_view($artefact, $view)) {
    throw new AccessDeniedException("");
}
if ( ! can_view_view($view)) {
    throw new AccessDeniedException("");
}

if (!get_field('artefact', 'allowcomments', 'id', $artefact)) {
    throw new ParameterException("Comments are disabled for this artefact.");
}

if ($action == 'show') {
    $id = param_integer('annotation');
    $private = get_field_sql(
        "SELECT c.private FROM {artefact_comment_comment} c
        INNER JOIN {artefact} a ON c.artefact = a.parent
        WHERE a.id = ?", array($id)
    );
    $data = array();
    $allowed = true;

    if ($private) {
        $uid = $USER->get('id');
        $allowed = get_field_sql(
            "SELECT 1 FROM {artefact} WHERE id = ? AND (author = ? OR owner = ?)",
                array($id, $uid, $uid)
        );
    }
    if ($allowed) {
        $data['annotation'] = get_field('artefact', 'description', 'id', $id);
        $usr = get_record_sql(
            "SELECT u.* FROM {usr} u INNER JOIN {artefact} a ON a.author = u.id
            WHERE a.id = ?", array($id)
        );
        $data['author'] = (object)array(
            'name' => display_name($usr),
            'image' => profile_icon_url($usr, 20, 20),
            'profile' => get_config('wwwroot') . 'user/view.php?id=' . $usr->id,
        );
    }
}

else if ($action == 'create') {
    if (!is_logged_in()) {
        throw new AccessDeniedException();
    }

    $owner = get_field('artefact', 'owner', 'id', $artefact);
    $desc = param_variable('data');
    $data = array(
        'artefacttype' => 'annotate',
        'owner' => $owner,
        'author' => $USER->get('id'),
        'title' => '',
        'description' => $desc,
    );

    db_begin();

    $ann = new ArtefactTypeAnnotate(null, $data);
    $ann->commit();

    $lang = get_field('usr_account_preference', 'value', 'field', 'lang', 'usr', $owner);
    if (!$lang || $lang == 'default') {
        $lang = 'en.utf8';
    }
    // Add comment also
    $comment = get_string_from_language($lang, 'createdannotation', 'artefact.annotate', '#annotation' . $ann->get('id'));

    $data = (object) array(
        'title'       => get_string_from_language($lang, 'Comment', 'artefact.comment'),
        'description' => $comment,
        'onartefact'  => $artefact,
        'owner'       => $data['owner'],
        'author'      => $USER->get('id'),
        'private'     => 1,
    );

    $comment = new ArtefactTypeComment(0, $data);

    $comment->commit();

    $ann->set('parent', $comment->get('id'));
    $ann->commit();

    $data = (object) array(
        'commentid' => $comment->get('id'),
        'viewid'    => $view
    );
    activity_occurred('feedback', $data, 'artefact', 'comment');

    db_commit();

    $data = 'ok';
    $SESSION->add_ok_msg(get_string("annotationcreated", 'artefact.annotate'));
}

else if ($action == 'delete') {
    $id = param_integer('annotation');
    $ann = new ArtefactTypeAnnotate($id);
    $uid = $USER->get('id');
    if ($ann->get('owner') == $uid || $ann->get('author') == $uid) {
        $ann->delete();
        $data = 'ok';
    }
    else {
        throw new AccessDeniedException("You are not allowed to delete this annotation.");
    }
}

json_reply(false, $data);

