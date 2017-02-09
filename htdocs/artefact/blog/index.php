<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'blog');

if (!$USER->get_account_preference('multipleblogs')) {
    redirect(get_config('wwwroot') . 'artefact/blog/view/index.php');
}

define('TITLE', get_string('blogs','artefact.blog'));

if ($delete = param_integer('delete', 0)) {
    ArtefactTypeBlog::delete_form($delete);
}

$blogs = ArtefactTypeBlog::get_blogs();
$tags = ArtefactTypeBlog::get_blog_tags();
$count = count($blogs);
$wwwroot = get_config('wwwroot');
$js = <<<JS
requirejs.config({ baseUrl: '{$wwwroot}local/js' });
requirejs(['domReady!', 'config'], function () {
    require(['../../artefact/blog/js/blog'], function (blog) {
        blog.init({ total: $count });
    });
});

JS;

//< EKAMPUS
$smarty = smarty(array($wwwroot . 'local/js/lib/require.js'),  array(),
        array('artefact.blog' => array('deleteblog?')),
        array('sidebars' => false));
// EKAMPUS >
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('blogs', $blogs);
$smarty->assign('tags', $tags);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:blog:blogs.tpl');

function delete_blog_submit(Pieform $form, $values) {
    global $SESSION;
    $blog = new ArtefactTypeBlog($values['delete']);
    $blog->check_permission();
    if ($blog->get('locked')) {
        $SESSION->add_error_msg(get_string('submittedforassessment', 'view'));
    }
    else {
        $blog->delete();
        $SESSION->add_ok_msg(get_string('blogdeleted', 'artefact.blog'));
    }
    redirect('/artefact/blog/index.php');
}
