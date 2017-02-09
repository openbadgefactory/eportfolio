<?php
defined('INTERNAL') || die();

global $cfg;
require dirname(dirname(__FILE__)) . '/config.php';

function pdo_croak($msg) {
    trigger_error("Fatal DB error: " . $msg, E_USER_ERROR);
    exit(1);
} 

function pdo_connect() {
    static $dbconn;

    if (is_null($dbconn)) {
        global $cfg;
        try {
            $dbconn = new PDO(
                "mysql:host=$cfg->dbhost;dbname=$cfg->dbname", $cfg->dbuser, $cfg->dbpass, array(PDO::ATTR_PERSISTENT => false)
            );
            $dbconn->exec('SET NAMES UTF8');
        } catch (PDOException $e) {
            pdo_croak("connection failed - " . $e->getMessage());
        }
    }
    return $dbconn;
}

function pdo_execute_sql() {
    $args = func_get_args();
    $query = array_shift($args);
    $dbconn = pdo_connect();
    $sth = $dbconn->prepare($query) or pdo_croak('pdo_execute_sql, prepare failed');
    $sth->execute($args)            or pdo_croak('pdo_execute_sql, execute failed');
    return $sth;
}

function pdo_get_field() {
    $args = func_get_args();
    $query = array_shift($args);
    $dbconn = pdo_connect();
    $sth = $dbconn->prepare($query) or pdo_croak('pdo_get_field, prepare failed');
    $sth->execute($args)            or pdo_croak('pdo_get_field, execute failed');
    return $sth->fetchColumn(0);
}

function pdo_get_record() {
    $args = func_get_args();
    $query = array_shift($args);
    $dbconn = pdo_connect();
    $sth = $dbconn->prepare($query) or pdo_croak('pdo_get_record, prepare failed');
    $sth->execute($args)            or pdo_croak('pdo_get_record, execute failed');
    return $sth->fetchObject();
}
