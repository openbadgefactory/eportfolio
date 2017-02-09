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
 * @subpackage artefact-cloudfile
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2012 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2012 Catalyst IT Ltd http://catalyst.net.nz
 *
 */


define('INTERNAL', 1);
define('MENUITEM', 'settings/cloudfiles');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'cloudfile');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('Cloudfiles', 'artefact.cloudfile'));
safe_require('artefact', 'cloudfile');

$available = get_config('cloudfile');
if (empty($available)) {
    throw new ConfigException("No cloud services configured.");
}

$services = array();

if (isset($available['googledrive'])) {
    $goog = new CloudFile('googledrive');

    $unlink = pieform(array(
        'name'     => 'unlink_googledrive',
        'method'   => 'post',
        'renderer' => 'div',
        'successcallback' => 'unlink_cloud_submit',
        'elements' => array(
            'type' => array('type' => 'hidden', 'value' => 'googledrive'),
            'desc' => array(
                'type' => 'html',
                'value' => '<p>' . get_string('unlinkgoogledrivedesc', 'artefact.cloudfile') . '</p>'
            ),
            'submit' => array('type' => 'submit', 'value' => get_string('unlinkgoogledrive', 'artefact.cloudfile'))
        ),
    ));

    $resync = pieform(array(
        'name'     => 'resync_googledrive',
        'method'   => 'post',
        'renderer' => 'div',
        'successcallback' => 'resync_cloud_submit',
        'elements' => array(
            'type' => array('type' => 'hidden', 'value' => 'googledrive'),
            'desc' => array(
                'type' => 'html',
                'value' => '<p>' . get_string('resyncdesc', 'artefact.cloudfile') . '</p>'
            ),
            'submit' => array('type' => 'submit', 'value' => get_string('resync', 'artefact.cloudfile'))
        ),
    ));

    $url  = 'https://accounts.google.com/o/oauth2/auth?response_type=code&approval_prompt=force&access_type=offline';
    $url .= '&scope='        . $available['googledrive']['scope'];
    $url .= '&redirect_uri=' . urlencode($available['googledrive']['redirect_uri']);
    $url .= '&client_id='    . $available['googledrive']['client_id'];

    $link  = '<p>' . get_string('linkgoogledrivedesc', 'artefact.cloudfile') . '</p>';
    $link .= '<div><a class="btn" href="' . $url . '">';
    $link .= get_string('linkgoogledrive', 'artefact.cloudfile') . '</a></div>';

    $services['googledrive'] = array(
        'title' => 'Google Drive',
        'authorized' => $goog->is_authorized(),
        'linkform' => $link,
        'unlinkform' => $unlink,
        'resyncform' => $resync,
    );
}

if (isset($available['skydrive'])) {
    $skydrive = new CloudFile('skydrive');

    $unlink = pieform(array(
        'name'     => 'unlink_skydrive',
        'method'   => 'post',
        'renderer' => 'div',
        'successcallback' => 'unlink_cloud_submit',
        'elements' => array(
            'type' => array('type' => 'hidden', 'value' => 'skydrive'),
            'desc' => array(
                'type' => 'html',
                'value' => '<p>' . get_string('unlinkskydrivedesc', 'artefact.cloudfile') . '</p>'
            ),
            'submit' => array('type' => 'submit', 'value' => get_string('unlinkskydrive', 'artefact.cloudfile'))
        ),
    ));

    $resync = pieform(array(
        'name'     => 'resync_skydrive',
        'method'   => 'post',
        'renderer' => 'div',
        'successcallback' => 'resync_cloud_submit',
        'elements' => array(
            'type' => array('type' => 'hidden', 'value' => 'skydrive'),
            'desc' => array(
                'type' => 'html',
                'value' => '<p>' . get_string('resyncdesc', 'artefact.cloudfile') . '</p>'
            ),
            'submit' => array('type' => 'submit', 'value' => get_string('resync', 'artefact.cloudfile'))
        ),
    ));

    $url  = 'https://login.live.com/oauth20_authorize.srf?response_type=code';

    $url .= '&scope='        . $available['skydrive']['scope'];
    $url .= '&redirect_uri=' . urlencode($available['skydrive']['redirect_uri']);
    $url .= '&client_id='    . $available['skydrive']['client_id'];

    $link  = '<p>' . get_string('linkskydrivedesc', 'artefact.cloudfile') . '</p>';
    $link .= '<div><a class="btn" href="' . $url . '">';
    $link .= get_string('linkskydrive', 'artefact.cloudfile') . '</a></div>';

    $services['skydrive'] = array(
        'title' => 'Microsoft OneDrive',
        'authorized' => $skydrive->is_authorized(),
        'linkform' => $link,
        'unlinkform' => $unlink,
        'resyncform' => $resync,
    );
}


$smarty = smarty();

$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('services', $services);
$smarty->display('artefact:cloudfile:index.tpl');


function unlink_cloud_submit(Pieform $form, $values) {
    $cf = new CloudFile($values['type']);
    $cf->unlink();
    redirect('/artefact/cloudfile/index.php');
}

function resync_cloud_submit(Pieform $form, $values) {
    global $USER;
    execute_sql(
        "UPDATE {artefact_cloudfile} SET dirty = 1
        WHERE type = ? AND artefact IN (SELECT id FROM {artefact} WHERE owner = ?)",
        array($values['type'], $USER->get('id'))
    );
    $cf = new CloudFile($values['type']);
    $cf->sync_artefacts($USER->get('id'), 0);
    redirect('/artefact/cloudfile/index.php');
}

