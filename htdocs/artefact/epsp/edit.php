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
 * @subpackage artefact-epsp
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'epsp');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'epsp');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'epsp');

$id = param_integer('id', 0);

define('TITLE', get_string('edit', 'artefact.epsp'));

$title = '';
$tags = '';
$description = '';

// Only own epsp's can be edited.
if (!empty($id)) {
    $epsp = new ArtefactTypeEpsp($id);

    if ($epsp->get('owner') !== $USER->get('id')) {
        throw new AccessDeniedException();
    }

    $title = $epsp->get('title');
    $tags = $epsp->get('tags');
    $description = $epsp->get('description');
}

$form = pieform(array(
    'name' => 'epsp',
    'method' => 'post',
    'checkdirtychange' => false,
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $id
        ),
        'title' => array(
            'type' => 'text',
            'title' => get_string('title', 'artefact.epsp'),
            'rules' => array('required' => true),
            'defaultvalue' => $title
        ),
        'description' => array(
            'type' => 'textarea',
            'rows' => 10,
            'cols' => 70,
            'title' => get_string('description', 'artefact.epsp'),
            'defaultvalue' => $description,
            'rules' => array(
                'maxlength' => 65536,
                'required' => false
            )
        ),
        'tags' => array(
            'type' => 'tags',
            'title' => get_string('tags'),
            'description' => get_string('tagsdescprofile'),
            'defaultvalue' => $tags
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(
                get_string('saveandcontinue'),
                get_string('cancel')
            )
        )
    ))
);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('artefact:epsp:edit.tpl');

function epsp_submit(Pieform $form, $values) {
    global $USER;

    $epsp = empty($values['id']) ? new ArtefactTypeEpsp() : new ArtefactTypeEpsp($values['id']);

    // New epsp, set the current user as the owner.
    if (empty($values['id'])) {
        $epsp->set('owner', $USER->get('id'));
    }

    $epsp->set('title', $values['title']);
    $epsp->set('description', $values['description']);
    $epsp->set('tags', $values['tags']);
    $epsp->commit();

    redirect('/artefact/epsp/fields.php?id=' . $epsp->get('id'));
}

function epsp_cancel_submit() {
    $redirecturl = '/artefact/epsp/' . (is_teacher() ? '' : 'own.php');

    redirect($redirecturl);
}