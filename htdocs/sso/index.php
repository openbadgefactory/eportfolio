<?php

ini_set('display_errors', 1);
define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

if (isset($_GET['test']) && $_GET['test'] == 'foobar') {
    $email = 'jukka.puupaa@joku.com';
    $firstname = 'Jukka';
    $lastname = 'Puupää';
}
else {
    require_once('/var/www/simplesamlphp/lib/_autoload.php');
    $as = new SimpleSAML_Auth_Simple('default-sp');
    $as->requireAuth();

    $attributes = $as->getAttributes();
    $email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0];
    $firstname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0];
    $lastname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0];
}

@session_write_close();

// now - let's continue with the session handling that would normally be done
// by Maharas init.php
// the main thin is that it sets the session cookie name back to what it should be
// session_name(get_config('cookieprefix') . 'mahara');
// and starts the session again

// ***********************************************************************
// copied from original init.php
// ***********************************************************************
// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require_once(dirname(dirname(__FILE__)) . '/auth/lib.php');
$SESSION = Session::singleton();
$USER    = new LiveUser();
$THEME   = new Theme($USER);
// ***********************************************************************
// END of copied stuff from original init.php
// ***********************************************************************
// restart the session for Mahara
@session_start();

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

//print_r($user);
$auth_instances = auth_get_auth_instances();
$auth_instance = $auth_instances[1];

if ($user == false) {
    db_begin();
    // Insert new user
    $user = new StdClass;
    $user->username = $email;
    $user->salt = auth_get_random_salt();
    $user->password = crypt('mahara', '$2a$' . get_config('bcrypt_cost') . '$' . substr(md5(get_config('passwordsaltmain') . $user->salt), 0, 22));
    $user->password = substr($user->password, 0, 7) . substr($user->password, 7+22);
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
//    activity_add_admin_defaults(array($user->id));
    db_commit();

    $user = get_user($user->id);
    print_r($user);
}

//$success = $USER->login($user->username, $user->password);
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
