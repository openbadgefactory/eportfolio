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
 * @subpackage core
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

define('TITLE', get_string('tokenpasskey', 'view'));


if (!get_config('allowpublicviews')) {
    throw new NotFoundException('');
}

$usertoken = param_alphanum('t');

$form = pieform(array(
    'name' => 'passkey',
    'autofocus' => false,
    'elements' => array(
        'key' => array(
            'type' => 'password',
            'title' => get_string('password'),
            'rules' => array('required' => true),
        ),
        'token' => array(
            'type' => 'hidden',
            'value' => $usertoken,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('submit')
        )
    )
));

$smarty = smarty(array(), array('<meta name="robots" content="noindex">'), array(), array('pagehelp' => false, 'sidebars' => false));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
// <EKAMPUS
$smarty->assign('HIDE_LOGINBLOCK', true);
// EKAMPUS>
$smarty->display('form.tpl');

function passkey_validate(Pieform $form, $values) {
    $token = $values['token'] . ':' . $values['key'];
    if ($viewid = get_view_from_token($token, true)) {
        define('TOKENPASSKEYVIEW', $viewid);
    }
    else {
        $form->set_error('key', get_string('wrongtokenpasskey', 'view'));
    }
}

function passkey_submit(Pieform $form, $values) {
    if (defined('TOKENPASSKEYVIEW')) {
        redirect('/view/view.php?id=' . TOKENPASSKEYVIEW);
    }
    throw new NotFoundException('');
}