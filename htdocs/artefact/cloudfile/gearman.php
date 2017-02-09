<?php
namespace cloudfile;

if (PHP_SAPI != 'cli') {
    exit(1);
}

define('INTERNAL', 1);

function our_error_handler($errno, $errmsg, $filename, $linenum, $vars) {
    $dt = date("Y-m-d H:i:s");
    $errortype = array (
        E_ERROR             => 'E_ERROR',         E_WARNING         => 'E_WARNING',         E_PARSE        => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',        E_CORE_ERROR      => 'E_CORE_ERROR',      E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING', E_USER_ERROR   => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',  E_USER_NOTICE     => 'E_USER_NOTICE',     E_STRICT       => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
    );
    if ($errno == E_USER_NOTICE) {
        $err = sprintf("[%s] %s\n", $dt, $errmsg);
    }
    else {
        $err = sprintf("[%s] %s %s at %s:%s\n", $dt, $errortype[$errno], $errmsg, $filename, $linenum);
    }
    file_put_contents("/var/log/mahara/gearman.log", $err, FILE_APPEND|LOCK_EX);
}


function info($msg) {
    trigger_error($msg, E_USER_NOTICE);
}


function artefact_children($artefact) {
    $sth = pdo_execute_sql(
        "SELECT a.*, af.size, af.oldextension, af.fileid, af.filetype FROM artefact a 
        LEFT JOIN artefact_file_files af ON a.id = af.artefact 
        WHERE a.id IN (SELECT id FROM artefact WHERE parent = ?)", $artefact->id
    );
    $children = array();
    while ($child = $sth->fetchObject()) {
        $child->basepath = $artefact->basepath;
        $children[] = $child;
    }
    return $children;
}

function artefact_obj($id, $basepath) {
    $obj = pdo_get_record(
        "SELECT a.*, af.size, af.oldextension, af.fileid, af.filetype FROM artefact a 
        LEFT JOIN artefact_file_files af ON a.id = af.artefact 
        WHERE a.id = ?", $id);
    $obj->basepath = $basepath;
    return $obj;
}


function add_folder($socket, $user, $artefact, $parent, $cleanup) {
    $children = artefact_children($artefact);

    $entry = run_create_folder($socket, $user, $artefact, $parent);
    if ($entry) {
        \pdo_execute_sql(
            "INSERT IGNORE INTO artefact_cloudfile (artefact, type, remote_id, src, checksum) VALUES (?,?,?,?,?)",
            $artefact->id, $socket->type, $entry['id'], $entry['src'], $entry['checksum']
        );
        if (!empty($children)) {
            foreach ($children AS $child) {
                if ($child->artefacttype == 'folder') {
                    if ( ! add_folder($socket, $user, $child, $entry['id'], $cleanup)) {
                        return false;
                    }
                }
                else {
                    if ( ! add_file($socket, $user, $child, $entry['id'], $cleanup)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    info("Failed to add artefact " . $artefact->id);
    return false;
}

function has_no_links($artefact) {
    $id = $artefact->id;
    $fileid = $artefact->fileid;

    if ($id != $fileid) {
        return false;
    }
    $link = \pdo_get_field(
        "SELECT COUNT(fileid) FROM artefact_file_files WHERE fileid = ?", $id);

    return $link == 1;
}

function get_path($a) {
    return $a->basepath . '/artefact/file/originals/' . ($a->fileid % 256) . '/' . $a->fileid;
}

function add_file($socket, $user, $artefact, $parent, $cleanup) {
    $path = get_path($artefact);
    if (!is_file($path)) {
        info("file not found: $path");
        return false;
    }
    $entry = run_upload($socket, $user, $path, $artefact);
    if ($entry) {
        if ($parent && !isset($entry['existed'])) {
            // move uploaded file to correct folder
            $socket->move($user, $entry['id'], $socket->root_id, $parent);
        }

        \pdo_execute_sql(
            "INSERT IGNORE INTO artefact_cloudfile (artefact, type, remote_id, src, checksum) VALUES (?,?,?,?,?)",
            $artefact->id, $socket->type, $entry['id'], $entry['src'], $entry['checksum']
        );
        if ($cleanup && has_no_links($artefact)) {
            $size = filesize($path);
            $lock = fopen($artefact->basepath . '/temp/cloud.download.lock', 'w');
            if ($lock && flock($lock, LOCK_EX)) {
                \pdo_execute_sql("UPDATE usr SET quotaused = quotaused - $size WHERE id = ?", $user);
                unlink($path);
            } else {
                info("Failed to get exclusive lock, keeping $path");
            }
            flock($lock, LOCK_UN); fclose($lock);
        }
        return true;
    }
    info("Failed to add artefact " . $artefact->id);
    return false;
}


function run_create_folder($socket, $user, $artefact, $parent, $retry=10) {
    try {
        $rec = \pdo_get_record(
            "SELECT remote_id AS id, src, checksum FROM artefact_cloudfile WHERE artefact = ?", $artefact->id);
        if ($rec) {
            return (array)$rec;
        }
        return $socket->create_folder($user, $artefact->title, $parent);
    }
    catch (\Exception $e) {
        info("Failed to create folder: " . $e->getMessage());
        if ($retry--) {
            sleep(mt_rand(15,60) - $retry);  
            info("Retrying...");
            return run_create_folder($socket, $user, $artefact, $parent, $retry);
        }
    }
    return false;
}


function run_upload($socket, $user, $path, $artefact, $retry=10) {
    try {
        $rec = \pdo_get_record(
            "SELECT cf.remote_id AS id, cf.src, cf.checksum,
            a.description, af.filetype, a.mtime, a.title AS filename, af.size
            FROM artefact_cloudfile cf
            INNER JOIN artefact a ON cf.artefact = a.id
            INNER JOIN artefact_file_files af ON a.id = af.artefact
            WHERE a.id = ?", $artefact->id
        );
        if ($rec) {
            $rec->existed = true;
            return (array)$rec;
        }
        return $socket->upload($user, $path, $artefact->filetype, $artefact->title, $artefact->description);
    }
    catch (\Exception $e) {
        info("Upload failed: " . $e->getMessage());
        if ($retry--) {
            sleep(mt_rand(15,60) - $retry);  
            info("Retrying...");
            return run_upload($socket, $user, $path, $artefact, $retry);
        }
    }
    return false;
}


function do_work($job) {

    $old_error_handler = set_error_handler("cloudfile\our_error_handler");

    $param = json_decode($job->workload());

    if ($param->action == 'ping') { // Just testing if we are still alive.
        return microtime(true);
    }

    if ($param->action != 'upload') {
        info("*fatal* Unknown action: " . $param->action);
        return false;
    }

    $socket = null;
    if ($param->cloud == 'googledrive') {
        $socket = new \GoogleDriveSocket();
    }
    if ($param->cloud == 'skydrive') {
        $socket = new \SkyDriveSocket();
    }

    if (!$socket) {
        info("*fatal* Failed to create socket for " . $param->cloud);
        return false;
    }

    require dirname(dirname(dirname(__FILE__))) . '/config.php'; // get $cfg variable
    $available = $cfg->cloudfile;
    if (empty($available) || empty($available[$param->cloud])) {
        info('*fatal* ' . $param->cloud . ' not configured');
        return false;
    }
    $socket->config = $available[$param->cloud];

    $socket->remote_addr = $param->remote_addr;

    info("uploading artefact " . $param->artefact);

    $artefact = artefact_obj($param->artefact, $cfg->dataroot);
    if (!isset($artefact->id)) {
        info("*fatal* artefact " . $param->artefact . ' not found');
        return false;
    }

    try {
        if ($artefact->artefacttype == 'folder') {
            add_folder($socket, $param->user, $artefact, $param->parent, $param->cleanup);
        }
        else {
            add_file($socket, $param->user, $artefact, $param->parent, $param->cleanup);
        }
    }
    catch (\Exception $e) {
        info($e->getMessage());
        info("*fatal* Failed to upload artefact " . $param->artefact . ' (giving up)');
        exit(1);
    }
    info('artefact ' . $param->artefact . " uploaded OK");

    return true;
}



/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


define('MAX_CHILDREN', 5);


$childcount = 0;

for (;;) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    }
    else if ($pid) {
        // we are the parent
        $childcount++;
        if ($childcount >= MAX_CHILDREN) {
            pcntl_wait($status);
            $childcount--;
        }
        sleep(2);
    }
    else {
        // we are the child
        require 'socketlib.php';

        $gmworker = new \GearmanWorker();
        $gmworker->addServer("127.0.0.1");
        $gmworker->addFunction("cloudfile", "cloudfile\do_work");
        $gmworker->work();
        $gmworker->unregisterAll();
        exit(0);
    }
}
