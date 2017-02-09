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
 * @subpackage artefact-multiresume
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012- Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'multiresume');
define('SECTION_PAGE', 'new');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'multiresume');

$id = param_integer('id', 0);

$title = '';
$language = null;
$tags = array();
if ($id) {
    $artefact = get_record('artefact', 'id', $id, 'owner', $USER->id);
    if (!$artefact) {
        throw new NotFoundException('');
    }
    $title    = $artefact->title;
    $language = $artefact->description;
    $tags = ArtefactType::artefact_get_tags($id);
    define('TITLE', get_string('resumesettings','artefact.multiresume'));
}
else {
    $language = current_language(); // Set current language as the default value.
    define('TITLE', get_string('newresume','artefact.multiresume'));
}

$elem = array();


$elem['title'] = array(
    'type'        => 'text',
    'title'       => get_string('resumetitle', 'artefact.multiresume'),
    'description' => get_string('resumetitledesc', 'artefact.multiresume'),
    'defaultvalue' => $title,
    'rules' => array(
        'required'    => true
    ),
);

$langs_available = get_languages();
$langs_available['other'] = get_string('other', 'artefact.multiresume');

$elem['language'] = array(
    'type' => 'select',
    'title' => get_string('languageselect', 'artefact.multiresume'),
    'description' => get_string('languageselectdesc', 'artefact.multiresume'),
    'options' => $langs_available,
    'defaultvalue' => is_null($language) ? 'en.utf8' : isset($langs_available[$language]) ? $language : 'other',
);

$elem['otherlanguage'] = array(
    'type' => 'text',
    'defaultvalue' => $language,
    'rules' => array('required' => true)
);

if (!$id) {
    $copyable = get_records_sql_menu(
        "SELECT id, title FROM {artefact}
        WHERE artefacttype = 'multiresume' AND owner = ? ORDER BY title",
        array($USER->id));

    if ( ! empty($copyable)) {
        $elem['copyresume'] = array(
            'type' => 'checkbox',
            'title' => get_string('copyresume', 'artefact.multiresume'),
            'description' => get_string('copyresumedesc', 'artefact.multiresume'),
        );
        $elem['copyable'] = array(
            'type' => 'select',
            'options' => $copyable,
            'collapseifoneoption' => false
        );
    }
}

$elem['tags'] = array(
    'type' => 'tags',
    'title' => get_string('tags'),
    'description' => get_string('tagsdescprofile'),
    'defaultvalue' => $tags
);

$elem['submit'] = array(
    'type'  => 'submitcancel',
    'value' => array(
        $id ? get_string('save') : get_string('createresume', 'artefact.multiresume'),
        get_string('cancel')
    )
);

$form = pieform(array(
    'name' => 'resumesettings',
    'method' => 'post',
    'action' => '',
    'plugintype' => 'artefact',
    'pluginname' => 'multiresume',
    'elements' => array(
               'id' => array( 'type' => 'hidden',   'value' => $id ),
        'container' => array( 'type' => 'fieldset', 'elements' => $elem )
    )
));

$js = <<<'JS'

$j(document).ready(function () {

    if ( ! $j('#resumesettings_copyresume').prop('checked')) {
        $j('#resumesettings_copyable').hide();
    }

    if ($j('#resumesettings_language').val() != 'other') {
        $j('#resumesettings_otherlanguage').hide();
        $j('#resumesettings_otherlanguage').val('-');
    }

    $j('#resumesettings_language').change(function () {
        if (this.value == 'other') {
            $j('#resumesettings_otherlanguage').val('');
            $j('#resumesettings_otherlanguage').show();
        }
        else {
            $j('#resumesettings_otherlanguage').hide();
            $j('#resumesettings_otherlanguage').val('-');
        }
    });

    $j('#resumesettings_copyresume').click(function () {
        $j('#resumesettings_copyable').toggle( $j('#resumesettings_copyresume').prop('checked') );
    });
});

JS;

$smarty = smarty(array('jquery'));
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('form.tpl');

function resumesettings_submit(Pieform $form, $values) {
    global $USER;
    
    if (!empty($values['id'])) {
        $id = $values['id'];
        $lang = $values['language'] != 'other' ? $values['language'] : $values['otherlanguage'];
        $resume = new ArtefactTypeMultiResume($values['id']);
        $resume->set('title', $values['title']);
        $resume->set('description', $lang);
        $resume->set('tags', $values['tags']);
        $resume->commit();
    }
    else {
        $id = ArtefactTypeMultiResume::new_resume($USER, $values);
    }
    redirect('/artefact/multiresume/edit.php?id=' . $id);
}

function resumesettings_cancel_submit() {
    redirect('/artefact/multiresume/');
}
