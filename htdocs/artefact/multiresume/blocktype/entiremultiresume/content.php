<?php
define('INTERNAL', 1);
define('PUBLIC', 1);

require dirname(__FILE__) . '/../../../../init.php';
require dirname(__FILE__) . '/../../../../blocktype/lib.php';
safe_require('artefact', 'multiresume');
require dirname(__FILE__) . '/lib.php';

$id   = param_integer('id');
$lang = param_variable('lang');

$instance = new BlockInstance($id);
$instance->forcelang = $lang;

if (!can_view_view($instance->get('view'))) {
    log_info("Access denied: user: " . $USER->id . " view: " . $instance->get('view'));
    echo 0;
    exit();
}

header('Content-Type: application/json');

echo json_encode((object)array(
    'title'   => PluginBlocktypeEntireMultiresume::get_instance_title($instance),
    'content' => PluginBlocktypeEntireMultiresume::render_instance($instance)
));
