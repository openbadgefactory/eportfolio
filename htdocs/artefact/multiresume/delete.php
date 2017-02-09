<?php

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/resume');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'multiresume');

$id = param_integer('id');

$artefact = new ArtefactTypeMultiresume($id);
if ($artefact->get('owner') != $USER->id) {
    throw new AccessDeniedException('');
}

define('TITLE', get_string('deleteresume', 'artefact.multiresume') . ': ' . $artefact->get('title'));

$form = pieform(array(
    'name' => 'deleteresume',
    'autofocus' => false,
    'method' => 'post',
    'renderer' => 'div',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto' => get_config('wwwroot') . 'artefact/multiresume/',
        )
    ),
));

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->display('artefact:multiresume:delete.tpl');

function deleteresume_submit(Pieform $form, $values) {
    global $SESSION, $artefact;
    $artefact->delete();
    $SESSION->add_ok_msg(get_string('resumedeleted', 'artefact.multiresume'));
    redirect('/artefact/multiresume/');
}
