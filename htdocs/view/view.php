<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'view');

require(dirname(dirname(__FILE__)) . '/init.php');

require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
require_once('institution.php');
require_once('group.php');
safe_require('artefact', 'comment');

// access key for roaming teachers
$mnettoken = $SESSION->get('mnetuser') ? param_alphanum('mt', null) : null;

// access key for logged out users
$usertoken = (is_null($mnettoken) && get_config('allowpublicviews')) ? param_alphanum('t', null) : null;

if ($mnettoken) {
    if (!$viewid = get_view_from_token($mnettoken, false)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
}
else if ($usertoken) {
    if (!$viewid = get_view_from_token($usertoken, true)) {
//        throw new AccessDeniedException(get_string('accessdenied', 'error'));
        // <EKAMPUS
        // This view might be protected with a token passkey.
        if (get_column_sql("SELECT view FROM {view_access} WHERE token LIKE BINARY ?", array($usertoken . ':%'))) {
            redirect('/view/passkey.php?t=' . $usertoken);
            exit();
        }
        throw new NotFoundException('');
        // EKAMPUS>
    }
}
else if ($pageurl = param_alphanumext('page', null)) {
    if ($profile = param_alphanumext('profile', null)) {
        $view = new View(array('urlid' => $pageurl, 'ownerurlid' => $profile));
    }
    else if ($homepage = param_alphanumext('homepage', null)) {
        $view = new View(array('urlid' => $pageurl, 'groupurlid' => $homepage));
    }
    else {
        throw new ViewNotFoundException(get_string('viewnotfoundexceptiontitle', 'error'));
    }
    $viewid = $view->get('id');
}
else {
    $viewid = param_integer('id');
}

$new = param_boolean('new');
$showmore = param_boolean('showmore');
if (!$showmore) {
    $showmore = 0;
}

if (!isset($view)) {
    $view = new View($viewid);
}

if (!can_view_view($view)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

// Feedback list pagination requires limit/offset params
$limit       = param_integer('limit', 10);
$offset      = param_integer('offset', 0);
$showcomment = param_integer('showcomment', null);

// Create the "make feedback private form" now if it's been submitted
if (param_variable('make_public_submit', null)) {
    pieform(ArtefactTypeComment::make_public_form(param_integer('comment')));
}
else if (param_variable('delete_comment_submit_x', null)) {
    pieform(ArtefactTypeComment::delete_comment_form(param_integer('comment')));
}

$owner    = $view->get('owner');
$viewtype = $view->get('type');

if ($viewtype == 'profile' || $viewtype == 'dashboard' || $viewtype == 'grouphomepage') {
    redirect($view->get_url());
}

define('TITLE', $view->get('title'));

$collection = $view->get('collection');
$submittedgroup = (int)$view->get('submittedgroup');
if ($USER->is_logged_in() && $submittedgroup && group_user_can_assess_submitted_views($submittedgroup, $USER->get('id'))) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to release the view
    $submittedgroup = get_record('group', 'id', $submittedgroup);
    // If the view is part of a submitted collection, the whole
    // collection must be released at once.
    $releasecollection = !empty($collection) && $collection->get('submittedgroup') == $submittedgroup->id;
    if ($releasecollection) {
        if ($ctime = $collection->get('submittedtime')) {
            $text = get_string(
                'collectionsubmittedtogroupon', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name),
                format_date(strtotime($ctime))
            );
        }
        else {
            $text = get_string('collectionsubmittedtogroup', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
        }
    }
    else if ($view->get('submittedtime')) {
        $text = get_string('viewsubmittedtogroupon', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name), format_date(strtotime($view->get('submittedtime'))));
    }
    else {
        $text = get_string('viewsubmittedtogroup', 'view', group_homepage_url($submittedgroup), hsc($submittedgroup->name));
    }
    $releaseform = pieform(array(
        'name'     => 'releaseview',
        'method'   => 'post',
        'plugintype' => 'core',
        'pluginname' => 'view',
        'autofocus' => false,
        'elements' => array(
            'submittedview' => array(
                'type'  => 'html',
                'value' => $text,
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => $releasecollection ? get_string('releasecollection', 'group') : get_string('releaseview', 'group'),
            ),
        ),
    ));
}
else {
    $releaseform = '';
}

function releaseview_submit() {
    global $USER, $SESSION, $view, $collection, $submittedgroup, $releasecollection;
    if ($releasecollection) {
        $collection->release($USER);
        $SESSION->add_ok_msg(get_string('collectionreleasedsuccess', 'group'));
    }
    else {
        $view->release($USER);
        $SESSION->add_ok_msg(get_string('viewreleasedsuccess', 'group'));
    }
    if ($submittedgroup) {
        // The tutor might not have access to the view any more; send
        // them back to the group page.
        redirect(group_homepage_url($submittedgroup));
    }
    redirect($view->get_url());
}

$javascript = array('paginator', 'viewmenu', 'expandable');
$blocktype_js = $view->get_all_blocktype_javascript();
$javascript = array_merge($javascript, $blocktype_js['jsfiles']);
$inlinejs = "addLoadEvent( function() {\n" . join("\n", $blocktype_js['initjs']) . "\n});";

$extrastylesheets = array('style/views.css');

// If the view has comments turned off, tutors can still leave
// comments if the view is submitted to their group.
if (!empty($releaseform) || ($commenttype = $view->user_comments_allowed($USER))) {
    $defaultprivate = !empty($releaseform);
    $moderate = isset($commenttype) && $commenttype === 'private';
    //<EKAMPUS
    $thumbs = thumb_count($view->get('id'));

    $addfeedbackform = pieform(ArtefactTypeComment::add_comment_form($defaultprivate, $moderate));
    // EKAMPUS>
    $extrastylesheets[] = 'style/jquery.rating.css';
    $javascript[] = 'jquery.rating';
}
if ($USER->is_logged_in()) {
    $objectionform = pieform(objection_form());
    if ($notrudeform = $view->notrude_form()) {
        $notrudeform = pieform($notrudeform);
    }
}

$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

$feedback = ArtefactTypeComment::get_comments($limit, $offset, $showcomment, $view);

// Set up theme
$viewtheme = $view->get('theme');
if ($viewtheme && $THEME->basename != $viewtheme) {
    $THEME = new Theme($viewtheme);
}
$headers = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css?v=' . get_config('release'). '">');
$headers = array_merge($headers, $view->get_all_blocktype_css());
// Set up skin, if the page has one
$viewskin = $view->get('skin');
$issiteview = $view->get('institution') == 'mahara';
if ($viewskin && get_config('skins') && can_use_skins($owner, false, $issiteview) && (!isset($THEME->skins) || $THEME->skins !== false)) {
    $skin = array('skinid' => $viewskin, 'viewid' => $view->get('id'));
    $skindata = unserialize(get_field('skin', 'viewskin', 'id', $viewskin));
}
else {
    $skin = false;
}

if (!$view->is_public()) {
    $headers[] = '<meta name="robots" content="noindex">';  // Tell search engines not to index non-public views
}

// include slimbox2 js and css files, if it is enabled...
if (get_config_plugin('blocktype', 'gallery', 'useslimbox2')) {
    $langdir = (get_string('thisdirection', 'langconfig') == 'rtl' ? '-rtl' : '');
    $headers = array_merge($headers, array(
        '<script type="text/javascript" src="' . get_config('wwwroot') . 'lib/slimbox2/js/slimbox2.js?v=' . get_config('release'). '"></script>',
        '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'lib/slimbox2/css/slimbox2' . $langdir . '.css?v=' . get_config('release'). '">'
    ));
}

// <EKAMPUS
$wwwroot = get_config('wwwroot');

if ($view->get('type') === 'studyjournal') {
    $headers[] = '<link rel="stylesheet" type="text/css" href="' . $wwwroot .
            'artefact/studyjournal/theme/raw/static/style/style.css">';
}

if ($view->is_public()) {
    $headers[] = '<script type="text/javascript" src="' . $wwwroot .
            'js/jquery/jquery.sharrre.js?v=' . get_config('release') . '"></script>';
}
// EKAMPUS>

$can_edit = $USER->can_edit_view($view) && !$submittedgroup && !$view->is_submitted();

$viewgroupform = false;
// <EKAMPUS
// Disable view submitting
//if ($owner && $owner == $USER->get('id')) {
//    if ($tutorgroupdata = group_get_user_course_groups()) {
//        if (!$view->is_submitted()) {
//            $viewgroupform = view_group_submission_form($view, $tutorgroupdata, 'view');
//        }
//    }
//}

//Adding tablerenderer and bootstrap for return view button
$javascript[] = 'tablerenderer';
$javascript[] = get_config('wwwroot') . 'local/js/lib/bootstrap.min.js';
$javascript[] = get_config('wwwroot') . 'local/js/lib/require.js';
$headers = array_merge($headers, array('<link rel="stylesheet" href="' . get_config('wwwroot') . 'local/css/bootstrap.css" />'));
$extraconfig = array(
        'stylesheets' => $extrastylesheets,
        'sidebars' => false,
        'skin' => $skin
    );

if (is_teacher()) {
    $extraconfig['tinymceplugins'] = array(
        array(
            'name' => 'speedometer',
            'button' => array(
                'row' => 1,
                'after' => 'emotions'
            )
        ),
        array(
            'name' => 'trafficlight',
            'button' => array(
                'row' => 1,
                'after' => 'emotions'
            )
        )
    );
}
// EKAMPUS>
$smarty = smarty(
    $javascript,
    $headers,
    // <EKAMPUS
    array('artefact.comment' => array('likes', 'reallydeletethiscomment', 'messageemptynofiles')),
    $extraconfig
    // EKAMPUS>
);

// <EKAMPUS
$jsshare = '';

if ($view->is_public()) {
    $fbappid = get_config('facebook_appid');
    $enablefb = json_encode(!empty($fbappid));
    $buttons = $enablefb ? json_encode(array('facebook' => array('appId' => $fbappid))) : array();

    $jsshare .= <<<JS
\$j('#share-view').sharrre({
    share: {
        facebook: $enablefb,
        twitter: true,
        linkedin: true
    },
    buttons: $buttons,
    enableCounter: false
});
JS;
}
// EKAMPUS>

$javascript = <<<EOF
var viewid = {$viewid};
var showmore = {$showmore};
addLoadEvent(function () {
    paginator = {$feedback->pagination_js}
    // <EKAMPUS
    {$jsshare}
    // EKAMPUS>
});
EOF;

// collection top navigation
if ($collection) {
    $shownav = $collection->get('navigation');
    if ($shownav) {
        if ($views = $collection->get('views')) {
            if (count($views['views']) > 1) {
                $smarty->assign_by_ref('collection', array_chunk($views['views'], 5));
            }
        }
    }
    // <EKAMPUS
    $smarty->assign('colldescription', $collection->get('description'));

    // If user owns the view or the view (-> collection) is copyable and we're
    // dealing with a learning object here, add a button that allows the user to
    // copy the thing to own skills folder.
    $is_owner = $owner && $owner === $USER->get('id');

    if ($USER->is_logged_in() && $collection->get('type') === 'learningobject' && ($is_owner || $view->get('template'))) {
        $smarty->assign('copyabletoskillsfolder', true);
    }

    $smarty->assign('collectionid', $collection->get('id'));

    // Is the collection assignable? (= Is a learning object and the user is the
    // owner.
    if ($collection->get('type') === 'learningobject' && $is_owner) {
        $smarty->assign('assignable', true);
    }
    // EKAMPUS>
}

$smarty->assign('INLINEJAVASCRIPT', $javascript . $inlinejs);
$smarty->assign('new', $new);
$smarty->assign('viewid', $viewid);
$smarty->assign('viewtype', $viewtype);
$smarty->assign('feedback', $feedback);
$smarty->assign('owner', $owner);
$smarty->assign('tags', $view->get('tags'));
$smarty->assign('author', $view->display_author());

$smarty->assign('PAGEAUTHOR', $view->formatted_owner());

$titletext = ($collection && $shownav) ? hsc($collection->get('name')) : $view->display_title(true, false, false);

if (get_config('viewmicroheaders')) {
    $smarty->assign('microheaders', true);

    $smarty->assign('microheadertitle', $titletext);

    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    // Support for normal, light, or dark small Mahara logo - to use with skins
    if ($skin) {
        if ($skindata['header_logo_image'] == 'light') {
            $smarty->assign('maharalogofilename', 'images/site-logo-small-light.png');
        }
        else if ($skindata['header_logo_image'] == 'dark') {
            $smarty->assign('maharalogofilename', 'images/site-logo-small-dark.png');
        }
    }

    if ($can_edit) {
        if ($new) {
            $microheaderlinks = array(
                array(
                    'name' => get_string('back'),
                    'url' => get_config('wwwroot') . 'view/blocks.php?id=' . $viewid . '&new=1',
                    'type' => 'reply',
                ),
            );
        }
        else {
            $microheaderlinks = array(
                array(
                    'name' => get_string('editthisview', 'view'),
                    'image' => $THEME->get_url('images/btn_edit.png'),
                    'url' => get_config('wwwroot') . 'view/blocks.php?id=' . $viewid,
                ),
            );
        }
        $smarty->assign('microheaderlinks', $microheaderlinks);
    }

}
else if ($can_edit) {
    $smarty->assign('visitstring', $view->visit_message());
    $smarty->assign('editurl', get_config('wwwroot') . 'view/blocks.php?id=' . $viewid . ($new ? '&new=1' : ''));

    // <EKAMPUS
    if ($collection) {
        $createviewform = pieform(create_view_form($view->get('group'), $view->get('institution'),
                null, null, $collection->get('id')));
        $smarty->assign('createviewform', $createviewform);
    }
}

$title = hsc(TITLE);

if (!get_config('viewmicroheaders')) {
    $smarty->assign('maintitle', $titletext);
    if ($skin) {
        if ($skindata['header_logo_image'] == 'light' || $skindata['header_logo_image'] == 'dark') {
            // override the default $smarty->assign('sitelogo') that happens
            // in the initial call to smarty()
            $smarty->assign('sitelogo', $THEME->header_logo($skindata['header_logo_image']));
        }
    }
}

// Provide a link for roaming teachers to return
if ($mnetviewlist = $SESSION->get('mnetviewaccess')) {
    if (isset($mnetviewlist[$view->get('id')])) {
        $returnurl = $SESSION->get('mnetuserfrom');
        require_once(get_config('docroot') . 'api/xmlrpc/lib.php');
        if ($peer = get_peer_from_instanceid($SESSION->get('authinstance'))) {
            $smarty->assign('mnethost', array(
                'name'      => $peer->name,
                'url'       => $returnurl ? $returnurl : $peer->wwwroot,
            ));
        }
    }
}

$smarty->assign('viewdescription', $view->get('description'));
$smarty->assign('viewcontent', $view->build_rows());
$smarty->assign('releaseform', $releaseform);
if (isset($addfeedbackform)) {
    $smarty->assign('enablecomments', 1);
    $smarty->assign('addfeedbackform', $addfeedbackform);
}
//< EKAMPUS
if (isset($thumbs)) {
    $smarty->assign('has_liked', user_has_liked($view));
    $smarty->assign('thumbs', $thumbs);
}
$smarty->assign('is_public', $view->is_public());
$showtabs = param_boolean('showtabs', false);
$backto = param_variable('backto', '');
$smarty->assign('backto', $backto);
$smarty->assign('showtabs', $showtabs);
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $view->get_url());

if ($can_edit) {
    add_learningobject_vars($collection, $smarty, $viewid);
}

// EKAMPUS >
if (isset($objectionform)) {
    $smarty->assign('objectionform', $objectionform);
    $smarty->assign('notrudeform', $notrudeform);
}
$smarty->assign('viewbeingwatched', $viewbeingwatched);

if ($viewgroupform) {
    $smarty->assign('view_group_submission_form', $viewgroupform);
}

$smarty->display('view/view.tpl');

mahara_log('views', "$viewid"); // Log view visits
