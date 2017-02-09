<?php
define('INTERNAL', 1);
define('PUBLIC', 1);
require_once('../init.php');
safe_require('artefact', 'file');


if (param_alpha('type', '') === 'image') {
    $type = array('image');
    define('TITLE', get_string('image'));
}
else {
    $type = PluginArtefactFile::get_artefact_types();
    define('TITLE', get_string('file', 'artefact.file'));
}


$elms = ArtefactTypeFileBase::files_form(get_config('wwwroot') . 'local/filebrowser.php');
$elms['elements']['filebrowser']['filters'] = array('artefacttype' => $type);
$elms['elements']['filebrowser']['config']['upload']       = 0;
$elms['elements']['filebrowser']['config']['edit']         = 0;
$elms['elements']['filebrowser']['config']['createfolder'] = 0;
$elms['elements']['filebrowser']['config']['select']       = 0;
$elms['elements']['filebrowser']['tabs'] = array('type' => 'user', 'id' => $USER->get('id'));

$form = pieform($elms);

$tip = get_string('select');
$js = <<<EOF

addLoadEvent(mungeFileLinks);

function files_callback(form, data) {
    files_filebrowser.callback(form, data);
    mungeFileLinks();
}

function mungeFileLinks() {
    var as = document.getElementsByTagName('a');
    for (var i=0; i < as.length; ++i) {
        if (as[i].target === '_blank') {
            as[i].onclick = pickedFile;
            as[i].title = '$tip';
        }
    }
}

function pickedFile() {
    FileBrowserDialogue.mySubmit(this.href);
    return false;
}

var FileBrowserDialogue = {
    init : function () {
        // Here goes your code for setting your custom things onLoad.
    },
    mySubmit : function (URL) {
    
        var win = tinyMCEPopup.getWindowArg("window");
        /*< EKAMPUS -> dont ask onclick if you want to leave the page without saving*/
        window.onbeforeunload = null;
        /* EKAMPUS >*/
        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined") {
            // we are, so update image dimensions...
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();

            // ... and preview if necessary
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(URL);
        }
        tinyMCEPopup.close();
    }
}

tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);

EOF;

$extraconfig = array('sidebars' => false, 'stylesheets' => array('style/filebrowserpopup.css', 'theme/views.css'));
$smarty = smarty(array('js/tinymce/tiny_mce_popup.js'), array(), array(), $extraconfig);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('form', $form);
$smarty->display('form.tpl');

