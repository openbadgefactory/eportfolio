<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('skin.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');

$id = param_integer('id');
$new = param_boolean('new');
// <EKAMPUS
$backto = param_variable('backto', '');
$from = param_alpha('from', null);
// EKAMPUS>
$view = new View($id);
$issiteview = $view->get('institution') == 'mahara';

if (!can_use_skins(null, false, $issiteview)) {
    throw new FeatureNotEnabledException();
}
/*<EKAMPUS */
if ($view->get('type') == 'profile') {
    $profile = true;
    $title = get_string('usersprofile', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title);
}
else if ($view->get('type') == 'dashboard') {
    $dashboard = true;
    $title = get_string('usersdashboard', 'mahara', display_name($view->get('owner'), null, true));
    define('TITLE', $title);
}
else if ($view->get('type') == 'grouphomepage') {
    $title = get_string('grouphomepage', 'view');
    $groupurl = group_homepage_url(get_record('group', 'id', $view->get('group')), false);
    define('TITLE', $title);
}
else if ($new) {
    define('TITLE', get_string('chooseviewskin', 'skin'));
}
else {
    define('TITLE', $view->get('title'));
}
/* EKAMPUS >*/
$view->set_edit_nav();
$view->set_user_theme();
// Is page skin already saved/set for current page?
$skin = param_integer('skin', null);
$saved = false;
if (!isset($skin)) {
    $skin = $view->get('skin');
    $saved = true;
}
if (!$skin || !($currentskin = get_record('skin', 'id', $skin))) {
    $currentskin = new stdClass();
    $currentskin->id = 0;
    $currentskin->title = get_string('skinnotselected', 'skin');
}
$incompatible = (isset($THEME->skins) && $THEME->skins === false && $currentskin->id != 0);
if ($incompatible) {
    $incompatible = ($view->get('theme')) ? 'notcompatiblewithpagetheme' : 'notcompatiblewiththeme';
    $incompatible = get_string($incompatible, 'skin', $THEME->displayname);
}
$metadata = array();
if (!empty($currentskin->id)) {
    $owner = new User();
    $owner->find_by_id($currentskin->owner);
    //<EKAMPUS
    $currentskin->editable = $currentskin->owner == $USER->get('id') || ($currentskin->type == 'site' && $USER->get('admin'));
     // EKAMPUS>
    $currentskin->metadata = array(
        'displayname' => '<a href="' . get_config('wwwroot') . 'user/view.php?id=' . $currentskin->owner . '">' . display_name($owner) . '</a>',
        'description' => nl2br($currentskin->description),
        'ctime' => format_date(strtotime($currentskin->ctime)),
        'mtime' => format_date(strtotime($currentskin->mtime)),
    );
}

$userskins   = Skin::get_user_skins();
$favorskins  = Skin::get_favorite_skins();
$siteskins   = Skin::get_site_skins();

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}


$skinform = pieform(array(
    'name' => 'viewskin',
    'elements' => array(
        'skin'  => array(
            'type' => 'hidden',
            'value' => $currentskin->id,
        ),
        'view' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
));
//< EKAMPUS
$editsiteskin = get_string('editsiteskin?', 'skin');
$return = get_string('return', 'skin');
// EKAMPUS >
// SEE: http://valums.com/scroll-menu-jquery/
$js = <<<EOF
jQuery(function(_$){
    // Get our elements for faster access and set overlay width
        /*
    var usrdiv = _$('div.userskins'),
        usrul = _$('ul.userskins'),
        favdiv = _$('div.favorskins'),
        favul = _$('ul.favorskins'),
        sitediv = _$('div.siteskins'),
        siteul = _$('ul.siteskins'),
        ulPadding = 10;

    // Get menu width
    var usrdivWidth = usrdiv.width();
    var favdivWidth = favdiv.width();
    var sitedivWidth = sitediv.width();

    // Remove scrollbars
    //< EKAMPUS - dont remove if it is mobile device
    if (!window.config.handheld_device) {
    // EKAMPUS >
        usrdiv.css({overflow: 'hidden'});
        favdiv.css({overflow: 'hidden'});
        sitediv.css({overflow: 'hidden'});
    }

    // Find last image container
    var usrlastLi = usrul.find('li:last-child');
    var favlastLi = favul.find('li:last-child');
    var sitelastLi = siteul.find('li:last-child');

    // When user move mouse over menu
    usrdiv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        // <EKAMPUS - Fix JS-errors if there's no items in menu.
        if (!usrlastLi[0]) {
            return;
        }
        // EKAMPUS>
        var usrulWidth = usrlastLi[0].offsetLeft + usrlastLi.outerWidth() + ulPadding;
        var left = (e.pageX - usrdiv.offset().left) * (usrulWidth-usrdivWidth) / usrdivWidth;
        usrdiv.scrollLeft(left);
    });

    // When user move mouse over menu
    favdiv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        // <EKAMPUS - Fix JS-errors if there's no items in menu.
        if (!favlastLi[0]) {
            return;
        }
        // EKAMPUS>
        var favulWidth = favlastLi[0].offsetLeft + favlastLi.outerWidth() + ulPadding;
        var left = (e.pageX - favdiv.offset().left) * (favulWidth-favdivWidth) / favdivWidth;
        favdiv.scrollLeft(left);
    });

    // When user move mouse over menu
    sitediv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        // <EKAMPUS - Fix JS-errors if there's no items in menu.
        if (!sitelastLi[0]) {
            return;
        }
        // EKAMPUS>
        var siteulWidth = sitelastLi[0].offsetLeft + sitelastLi.outerWidth() + ulPadding;
        var left = (e.pageX - sitediv.offset().left) * (siteulWidth-sitedivWidth) / sitedivWidth;
        sitediv.scrollLeft(left);
    });
        */
   //<EKAMPUS
    var add_edit_skin_modal = function() {
        var load_modal = function(evt){
            var editurl = _$(evt.target).attr('href');
            var confirmed = true;
            if (editurl.toLowerCase().indexOf("site=1") >= 0){
                confirmed = confirm('$editsiteskin');
            }
            if (confirmed){
                _$('#skineditframe').attr('src', editurl).on('load', function(){
                    _$('#skineditframe').contents().find('html').addClass('iniframe');
                    var closebutton = '<button type="button" class="close" id="closebutton" data-dismiss="modal" aria-hidden="true">$return</button>';
                    if (_$('#skineditframe').contents().find('#mainmiddle #closebutton').length < 1){
                        _$('#skineditframe').contents().find('#mainmiddle').prepend(closebutton);
                    }
                    _$('#skineditframe').contents().find('button#closebutton').on('click', function () {
                        window.location.reload(true);
                    });
                });
                _$('#edit-skin-modal').modal('show');
            }
        }
        _$('#skinedit').on('click', function(evt) {
            evt.preventDefault();
            load_modal(evt);
        });
        _$('#manageskins').on('click', function(evt) {
            evt.preventDefault();
            load_modal(evt);
        });
    };
    add_edit_skin_modal();
});
EOF;

$css = array(
    '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/raw/static/style/skin.css">',
    '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'local/css/bootstrap.css"></link>',/*<EKAmPUS>*/
);

$displaylink = $view->get_url();
if ($new) {
    $displaylink .= (strpos($displaylink, '?') === false ? '?' : '&') . 'new=1';
}

// <EKAMPUS
$smarty = smarty(array(get_config('wwwroot') . 'local/js/lib/bootstrap.min.js',
        'tablerenderer'), $css, array(), array('sidebars' => false)); // EKAMPUS
// EKAMPUS>
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('saved', $saved);
$smarty->assign('incompatible', $incompatible);
$smarty->assign('currentskin', $currentskin->id);
$smarty->assign('cskin', $currentskin); // EKAMPUS
$smarty->assign('currenttitle', $currentskin->title);
$smarty->assign('currentmetadata', (!empty($currentskin->metadata)) ? $currentskin->metadata : null);
$smarty->assign('userskins', $userskins);
$smarty->assign('favorskins', $favorskins);
$smarty->assign('siteskins', $siteskins);
$smarty->assign('form', $skinform);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
// <EKAMPUS
$smarty->assign('owner', $view->get('owner'));
add_learningobject_vars($view->get('collection'), $smarty, $view->get('id'));
$smarty->assign('viewtitle', TITLE);
// EKAMPUS>
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $displaylink);
$smarty->assign('new', $new);
// <EKAMPUS
$smarty->assign('from', $from);
$smarty->assign('backto', $backto);
if (get_config('viewmicroheaders') || !empty($from)) {
// EKAMPUS
    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false, false));
}
$smarty->assign('issiteview', $issiteview);
$smarty->display('view/skin.tpl');

function viewskin_validate(Pieform $form, $values) {
    $skinid = $values['skin'];
    if ($skinid) {
        $skin = new Skin($values['skin']);
        if (!$skin->can_use()) {
            throw new AcessDeniedException();
        }
    }
}

function viewskin_submit(Pieform $form, $values) {
    global $SESSION, $from;

    $view = new View($values['view']);
    $new = $values['new'];
    $view->set('skin', $values['skin']);
    $view->commit();
    handle_event('saveview', $view->get('id'));
    $SESSION->add_ok_msg(get_string('viewskinchanged', 'skin'));
    // <EKAMPUS
    if (!empty($from)) {
        redirect('/view/access.php?id=' . $view->get('id') . ($new ? '&new=1' : '') . '&from=' . $from);
    }
    else {
        if ($view->get('type') == 'profile'){
            redirect('/user/view.php?id='. $view->get('owner') . '&showtabs=true' );
        }
        elseif ($view->get('type') == 'grouphomepage'){
            redirect('/group/view.php?id='. $view->get('group') . '&showtabs=true' );
        }
        else {
            redirect('/view/view.php?id=' . $view->get('id') . ($new ? '&new=1' : '') . '&showtabs=true');
        }
    }
    // EKAMPUS>
}
