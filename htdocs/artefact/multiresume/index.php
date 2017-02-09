<?php
define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'multiresume');
define('SECTION_PAGE', 'index');

require dirname(dirname(dirname(__FILE__))) . '/init.php';
safe_require('artefact', 'multiresume');

define('TITLE', get_string('resumes','artefact.multiresume'));

$tags = ArtefactTypeMultiResume::get_tags();
$resumes = ArtefactTypeMultiResume::get_resumes();
$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/multiresume/js/multiresume'], function (multiresume) {
        multiresume.init();
    });
});

JS;

$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('resumes', $resumes);
$smarty->assign('tags', $tags);
$smarty->display('artefact:multiresume:resumes.tpl');

