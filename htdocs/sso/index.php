<?php

$authSources = array(
  'helsinki.mmg.fi' => 'default-sp',
  'ekampus.mmg.fi' => 'ekampus-sp',
);

// AuthSource from SimpleSAMLphp
$authSource = $authSources[$_SERVER['SERVER_NAME']];

// Should new users be created automatically?
$create_user_automatically = false;

ini_set('display_errors', 1);
define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

require_once('/var/www/simplesamlphp/lib/_autoload.php');
$as = new SimpleSAML_Auth_Simple($authSource);
$as->requireAuth();

$attributes = $as->getAttributes();
$email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0];
$firstname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0];
$lastname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0];

// do the normal user lookup
$sql = 'SELECT
                *,
                ' . db_format_tsfield('expiry') . ',
                ' . db_format_tsfield('lastlogin') . ',
                ' . db_format_tsfield('lastlastlogin') . ',
                ' . db_format_tsfield('lastaccess') . ',
                ' . db_format_tsfield('suspendedctime') . ',
                ' . db_format_tsfield('ctime') . '
            FROM
                {usr}
            WHERE
                LOWER(email) = ?';
$user = get_record_sql($sql, array(strtolower($email)));

$auth_instances = auth_get_auth_instances();
$auth_instance = $auth_instances[1];

if ($user === false) {
    if ($create_user_automatically) {
        db_begin();
        // Insert new user
        $user = new StdClass;
        $user->username = $email;
        $user->salt = auth_get_random_salt();
        $user->password = crypt('mahara', '$2a$' . get_config('bcrypt_cost') . '$' . substr(md5(get_config('passwordsaltmain') . $user->salt), 0, 22));
        $user->password = substr($user->password, 0, 7) . substr($user->password, 7 + 22);
        $user->authinstance = $auth_instance->id;
        $user->passwordchange = 0; // SSO user does not need to change password
        $user->admin = 0;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
        $user->id = insert_record('usr', $user, 'id', true);
        set_profile_field($user->id, 'email', $user->email);
        set_profile_field($user->id, 'firstname', $user->firstname);
        set_profile_field($user->id, 'lastname', $user->lastname);
        handle_event('createuser', $user);
        db_commit();

        $user = get_user($user->id);
    }
    else {
        $msg = sprintf('K&auml;ytt&auml;j&auml;&auml; ei l&ouml;ytynyt osoitteella %s.', $email);
        $_SESSION['messages'][] = array('type' => 'warning', 'msg' => $msg, 'placement' => 'messages');
        redirect('/?ssologin=false&reason=user-not-found');
    }
}

// Before we update anything in the DB, we should make sure the user is allowed to log in
ensure_user_account_is_active($user);

$USER->from_stdclass($user);
session_regenerate_id(true);
$USER->lastlastlogin      = $user->lastlogin;
$USER->lastlogin          = time();
$USER->lastaccess         = time();
$USER->sessionid          = session_id();
$USER->logout_time        = time() + get_config('session_timeout');
$USER->sesskey            = get_random_key();

redirect('/?ssologin=true');
