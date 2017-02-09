<?php

define('INTERNAL', 1);
define('MENUITEM', 'content/multiresume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'multiresume');
define('SECTION_PAGE', 'edit_row');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'multiresume');

//define('TITLE', get_string('editresume','artefact.multiresume'));

$field = param_integer('id');
$rec = get_record_sql(
    "SELECT f.*, a.description AS language FROM {artefact_multiresume_field} f INNER JOIN {artefact} a ON f.artefact = a.id
    WHERE a.owner = ? AND f.id = ?", array($USER->id, $field));

$available = get_languages();
$lang = $rec->language;
if (!isset($available[$lang])) {
    $lang = 'en.utf8';
}

$obj = unserialize($rec->value);
$titlename = strtolower( substr(get_class($obj), 11));

$titlename = ($titlename == 'education' ? 'educationhistory' : $titlename );
$titlename = ($titlename == 'employment' ? 'employmenthistory' : $titlename );
define('TITLE', get_string($titlename,'artefact.resume'));
$elements = $obj->edit_row_form(param_integer('row', null), $lang);

$elements['returnto'] = array(
    'type' => 'hidden',
    'value' => param_integer('cv')
);

$form = pieform(array(
    'name' => 'edit_row',
    'plugintype' => 'artefact',
    'pluginname' => 'multiresume',
    'jsform' => false,
    'elements' => $elements
));

$smarty = smarty();
$smarty->assign('pagedescriptionhtml', '<h1>' . TITLE . '</h1>');
$smarty->assign('form', $form);
//$smarty->display('form.tpl');
$smarty->display('artefact:multiresume:edit_row.tpl');
function edit_row_submit(Pieform $form, $values) {
    global $rec;
    $obj = unserialize($rec->value);
    $obj->update_self($values);
    $rec->value = $obj;
    update_record('artefact_multiresume_field', $rec);

    $section = $values['section'];
    $returnto = $values['returnto'];
    //refresh parent window and hide the frame
    $javascript = <<<EOF
var replaceurl = 'edit.php?id=' + $returnto + '&open=' + $rec->id + '#' + '$section';
parent.document.location.reload();
parent.document.location.href = replaceurl;
parent.document.getElementById("frame").style.display = "none";
EOF;
    echo '<script>'.$javascript.'</script>';
exit;
  //  redirect('/artefact/multiresume/edit.php?id=' . $values['returnto'] . '&open=' . $rec->id . '#' . $values['section']);
}
