<?php

ini_set('display_errors', 1);
define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

require_once ('/var/www/simplesamlphp/lib/_autoload.php');
$as = new SimpleSAML_Auth_Simple('default-sp');
$as->requireAuth();

$attributes = $as->getAttributes();
$email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0];
$firstname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname'][0];
$lastname = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'][0];

echo $firstname .' '. $lastname .' ('. $email .')';
