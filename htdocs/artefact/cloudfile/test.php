<?php

exit(0); //XXX

define('INTERNAL', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'cloudfile');

$conf = get_config('cloudfile');
$sky = new SkyDriveSocket();
$sky->config = $conf['skydrive'];
$files = $sky->remote_filelist($USER->id);
header('Content-type: text/plain; charset=utf-8');
print_r($files);
