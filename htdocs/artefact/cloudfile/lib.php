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

defined('INTERNAL') || die();

safe_require('artefact', 'file');
require('socketlib.php');


function cloud_provider($artefact) {
    if (!$artefact) {
        return null;
    }
    $id = is_object($artefact) ? $artefact->get('id') : $artefact;
    $type = get_field('artefact_cloudfile', 'type', 'artefact', $id);
    if ($type) {
        return new CloudFile($type);
    }
    return null;
}

/**
 * Quickly check if we should use cloud file icons
 *
 */
function use_cloud_icon($id) {
    global $USER;
    static $list;

    if (is_null($list)) {
        $list = array();
        $res = get_column_sql(
            "SELECT a.id FROM {artefact} a
            INNER JOIN {artefact_cloudfile} cf ON a.id = cf.artefact
            WHERE a.owner = ?", array($USER->id)
        );
        if (!empty($res)) {
            foreach ($res AS $a) {
                $list[$a] = 1;
            }
        }
    }
    return isset($list[$id]);
}


class PluginArtefactCloudFile extends PluginArtefact {
    public static function get_artefact_types() {
        return array('cloudfile');
    }


    public static function get_block_types() {
        return array();
    }


    public static function get_plugin_name() {
        return 'cloudfile';
    }


    public static function menu_items() {
        return array();
    }


    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            execute_sql(
                "ALTER TABLE {artefact_cloudfile}
                ADD CONSTRAINT FOREIGN KEY cloudartefactfk (artefact)
                REFERENCES {artefact} (id) ON DELETE CASCADE"
            );
        }
    }


    public static function get_event_subscriptions() {
        return array(
            (object)array('plugin' => 'cloudfile', 'event' => 'suspenduser',    'callfunction' => 'delete_oauth'),
            (object)array('plugin' => 'cloudfile', 'event' => 'deleteuser',     'callfunction' => 'delete_oauth'),
            (object)array('plugin' => 'cloudfile', 'event' => 'expireuser',     'callfunction' => 'delete_oauth'),
            (object)array('plugin' => 'cloudfile', 'event' => 'deactivateuser', 'callfunction' => 'delete_oauth'),
        );
    }


    public static function delete_oauth($event, $user) {
        execute_sql("DELETE FROM {artefact_cloudfile_config} WHERE usr = ?", array($user['id']));
    }

    public static function can_be_disabled() {
        return true;
    }

    public static function get_cron() {
        $prune = (object)array(
            'callfunction' => 'prune_cloudcache',
            'hour' => '4',
            'minute' => '12',
        );
        return array($prune);
    }


    public static function prune_cloudcache() {
        $lock = fopen(get_config('dataroot') . '/temp/cloud.cache.lock', 'w');
        if (flock($lock, LOCK_EX)) {
            $dir = get_config('dataroot') . '/artefact/cloudfile';
            if (is_dir($dir)) {
                $dir = escapeshellarg($dir);
                exec("/usr/bin/find $dir -type f -atime +1 -delete");
            }
            flock($lock, LOCK_UN);
        }
        else {
            error_log("Failed to aquire cloudfile lock for pruning");
        }
        fclose($lock);
    }
}

// Dummy artefact type class. We don't use it, but one of these is expected.
class ArtefactTypeCloudfile extends ArtefactType {

    public static function get_icon($options=null) { }
    public static function is_singular()           { }
    public static function get_links($id)          { }
}

/* * * * * * * * */

class CloudFile {
    public $socket;
    private $is_authorized;


    public function __construct($type) {
        switch ($type) {
            case 'skydrive':
                $this->socket = new SkyDriveSocket();
                break;
            case 'googledrive':
                $this->socket = new GoogleDriveSocket();
                break;
            default:
                throw new SystemException("Unknown type $type");
        }
        $available = get_config('cloudfile');
        if (empty($available) || empty($available[$type])) {
            throw new ConfigException("$type not configured.");
        }
        $this->socket->config      = $available[$type];
        $this->socket->remote_addr = $_SERVER['REMOTE_ADDR'];
    }

    public function in_cloud($artefact_id) {
        return isset($artefact_id) && record_exists('artefact_cloudfile', 'artefact', $artefact_id);
    }

    public function is_authorized() {
        if (!is_null($this->is_authorized)) {
            return $this->is_authorized;
        }
        global $USER;
        $this->is_authorized = false;
        try {
            $this->is_authorized = (boolean) $this->socket->get_access_token($USER->get('id'));
        }
        catch (CloudFileSocketException $e) { }
        return $this->is_authorized;
    }

    public function sync_artefacts($user=null, $ttl=100) {
        global $USER;
        if (is_null($user)) {
            $user = $USER->get('id');
        }

        // We must not sync same user's artefacts simultaneously.
        $lock = fopen(get_config('dataroot') . '/temp/cloud.sync.lock.' . ($user % 99), 'w');
        if ( ! flock($lock, LOCK_EX)) {
            error_log("Failed to aquire exclusive cloudfile sync lock for user " . $user);
            return;
        }

        // Prevent sync spamming
        $apc_key = 'synced_' . $this->socket->type . '_' . $user;
        if (get_config('enable_apc')) {
            $prev = (int) apc_fetch($apc_key);
            if ($prev > time() - $ttl) {
                flock($lock, LOCK_UN);
                fclose($lock);
                return;
            }
        }

        $existing = get_records_sql_assoc(
            "SELECT cf.remote_id, a.id, a.artefacttype, a.title, a.description, af.size, af.oldextension, af.filetype
            FROM {artefact_cloudfile} cf
            INNER JOIN {artefact} a ON cf.artefact = a.id
            LEFT JOIN {artefact_file_files} af ON a.id = af.artefact
            WHERE a.owner = ? AND cf.type = ? AND cf.remote_id != ?",
            array($user, $this->socket->type, $this->socket->root_id)
        );

        $entries = $this->socket->remote_filelist($user);

        db_begin();

        // First, sync folders
        $folders = array();
        foreach ($entries AS $id => $entry) {
            if ( ! $entry['isfolder']) {
                continue;
            }
            if (isset($existing[$id])) {
                $folder = new ArtefactTypeFolder($existing[$id]->id);
            }
            else {
                $folder = new ArtefactTypeFolder();
            }
            $folder->set('title', $entry['title']);
            $folder->set('description', $entry['description']);
            $folder->set('owner', $user);
            $folder->set('author', $user);
            $folder->commit();

            if (!isset($existing[$id])) {
                insert_record('artefact_cloudfile', (object)array(
                    'artefact' => $folder->get('id'),
                    'type' => $this->socket->type,
                    'remote_id' => $id,
                ));
            }
            $folders[$id] = $folder;
        }

        // Sync file artefacts
        $files = array();
        foreach ($entries AS $id => $entry) {
            if ($entry['isfolder']) {
                continue;
            }
            if (isset($existing[$id])) {
                $file = artefact_instance_from_id($existing[$id]->id);
            }
            else if ($entry['isimage']) {
                $file = new ArtefactTypeImage();
            }
            else {
                $file = new ArtefactTypeFile();
            }
            $extension = preg_replace('/.+\./', '', $entry['filename']);
            $file->set('title', $entry['title']);
            $file->set('description', $entry['description']);
            $file->set('owner', $user);
            $file->set('author', $user);
            $file->set('size', $entry['size']);
            $file->set('filetype', $entry['filetype']);
            $file->set('oldextension', $extension);

            $file->commit();

            // Set the fileid field so it doesn't get nullified in the following
            // commit.
            $file->set('fileid', $file->get('id'));

            if (!isset($existing[$id])) {
                insert_record('artefact_cloudfile', (object)array(
                    'artefact' => $file->get('id'),
                    'type' => $this->socket->type,
                    'remote_id' => $id,
                    'src' => $entry['src'],
                    'checksum' => $entry['checksum'],
                ));
            }
            $files[$id] = $file;
        }

        // Set parents
        $root = $this->socket->mountpoint($user);
        foreach ($folders AS $remote_id => $folder) {
            if ($entries[$remote_id]['parent'] == $this->socket->root_id) {
                $folder->set('parent', $root->get('id'));
            }
            else {
                $parent = @$folders[$entries[$remote_id]['parent']];
                if ($parent) {
                    $folder->set('parent', $parent->get('id'));
                }
                else {
                    $folder->set('parent', $root->get('id'));
                }
            }
            $folder->commit();
        }
        foreach ($files AS $remote_id => $file) {
            if ($entries[$remote_id]['parent'] == $this->socket->root_id) {
                $file->set('parent', $root->get('id'));
            }
            else {
                $parent = @$folders[$entries[$remote_id]['parent']];
                if ($parent) {
                    $file->set('parent', $parent->get('id'));
                }
                else {
                    $file->set('parent', $root->get('id'));
                }
            }
            $file->commit();
        }

        // Remove missing artefacts
        if (!empty($existing)) {
            foreach ($existing AS $ex) {
                if (!isset($entries[$ex->remote_id])) {
                    $artefact = artefact_instance_from_id($ex->id);
                    $artefact->delete();
                }
            }
        }

        db_commit();

        if (get_config('enable_apc')) {
            apc_store($apc_key, time(), 7200);
        }
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    protected function fetch_file($artefact, $saveas, $stream=true, $ttl=30000) {
        $lock = fopen(get_config('dataroot') . '/temp/cloud.cache.lock', 'w');
        if ( ! flock($lock, LOCK_SH)) {
            error_log("Failed to aquire shared cloudfile lock.");
            // Continue anyway
        }
        if (!$artefact->dirty && is_readable($saveas) && filesize($saveas) && filemtime($saveas) + $ttl > time()) {
            log_info("serving cloudfile {$artefact->id} from cache");
            if ($stream) {
                serve_file($saveas, $artefact->filename, $artefact->filetype);
            }
        }
        else {
            log_info("fetching cloudfile {$artefact->id} from remote server");

            $this->socket->refresh_artefact($artefact);

            if ($stream) // Set headers
            {
                header('Accept-Ranges: none'); // Do not allow byteserving when caching disabled
                header('Cache-Control: max-age=10');
                header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
                header('Pragma: ');

                $forcedl = isset($artefact->options['forcedownload']) || !preg_match('/image|text/', $artefact->filetype);

                if ($forcedl) {
                    header('Content-Disposition: attachment; filename="' . $artefact->filename . '"');
                }
                if ($artefact->filetype == 'text/plain') {
                    // Add encoding
                    header('Content-Type: text/plain; charset=utf-8');
                }
                else {
                    if (isset($artefact->options['overridecontenttype'])) {
                        header('Content-Type: ' . $artefact->options['overridecontenttype']);
                    }
                    else {
                        header('Content-Type: ' . $artefact->filetype);
                    }
                }
                if (ini_get('zlib.output_compression')) {
                    ini_set('zlib.output_compression', 'Off');
                }
                if ($artefact->size) {
                    header('Content-Length: ' . $artefact->size);
                }
            }

            global $CLOUDHANDLE;
            $tmp = tempnam(get_config('dataroot') . '/temp', 'cloudfile');
            $CLOUDHANDLE = fopen($tmp, 'w');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $artefact->src);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->socket->request_header($artefact));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, $stream ? 'cloudfile_chunked_stream' : 'cloudfile_chunked');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla 4');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');

            curl_exec($ch);

            fclose($CLOUDHANDLE);

            $info = curl_getinfo($ch);

            if (curl_errno($ch) || $info['http_code'] != 200) {
                log_info("Fatal: failed to fetch cloudfile {$artefact->id} from remote server");
                log_info("URL: " . $artefact->src);
                log_info("HTTP code: " . $info['http_code']);
                log_info("Curl errno: " . curl_errno($ch));
                log_info("Curl error: " . curl_error($ch));
                curl_close($ch);
                unlink($tmp);
                return false;
            }
            curl_close($ch);

            rename($tmp, $saveas);
        }

        flock($lock, LOCK_UN);
        fclose($lock);

        return $saveas;
    }

    protected function get_artefact($artefact_id) {
        $artefact = get_record_sql(
            "SELECT a.*, a.title AS filename, af.filetype, af.size, af.oldextension, cf.remote_id, cf.src, cf.checksum, cf.dirty FROM {artefact} a
            INNER  JOIN {artefact_cloudfile} cf ON a.id = cf.artefact
            LEFT JOIN {artefact_file_files} af ON a.id = af.artefact
            WHERE a.id = ?", array($artefact_id)
        );
        if (!$artefact) {
            throw new NotFoundException('');
        }
        $extn = $artefact->oldextension;
        if ($extn) {
            $name = $artefact->filename;
            if (substr($name, -1 - strlen($extn)) != '.' . $extn) {
                $artefact->filename = $name . (substr($name, -1) == '.' ? '' : '.') . $extn;
            }
        }
        return $artefact;
    }

    public function get_path($artefact_id) {
        $artefact = $this->get_artefact($artefact_id);
        $saveas = $this->cache_path($artefact->checksum);
        $this->fetch_file($artefact, $saveas, false, 600);
        return $saveas;
    }

    public function serve($artefact_id, $size, $options) {
        $artefact = $this->get_artefact($artefact_id);
        $artefact->options = $options;
        $this->socket->augment($artefact);

        $is_image = preg_match('/image/', $artefact->filetype);

        if ($is_image && $size) {
            return $this->serve_image($artefact, $size);
        }

//        $cache = $this->cache_path($artefact->checksum, $artefact->document_type);
        $cache = $this->cache_path($artefact->checksum);

        $this->fetch_file($artefact, $cache, $artefact->stream, $artefact->ttl);

        if (!$artefact->stream) {
            serve_file($cache, $artefact->filename, $artefact->filetype, $options);
        }

        return true;
    }

    public function serve_image($artefact, $s) {
        if (isset($s['maxw']) && isset($s['maxh']) && $s['maxw'] == $s['maxh']) {
            // Serve generic icon
            header('Content-Type: image/png');
            $file = get_config('docroot') . 'artefact/cloudfile/theme/raw/static/image-x-generic';
            $end = "-{$s['maxw']}x{$s['maxh']}.png";
            if (is_file($file . $end)) {
                echo file_get_contents($file . $end);
            }
            else {
                echo file_get_contents($file . '.png');
            }
            return true;
        }
        // We can't use cache_path because get_dataroot_image_path() tries to find the image by id.
        $id = $artefact->id;
        $base = '/artefact/cloudfile/' . $this->socket->type . '/' . substr($artefact->checksum, 0, 3);
        $path = get_config('dataroot') . $base . '/originals/' . ($id % 256);
        check_dir_exists($path);
        $saveas = $path . "/$id";
        $this->fetch_file($artefact, $saveas, false, 36000);
        serve_file(get_dataroot_image_path($base, $id, $s), $artefact->filename, $artefact->filetype);
    }

    public function cache_path($checksum, $type='file') {
        $path = get_config('dataroot') . 'artefact/cloudfile/' . $this->socket->type . '/' . substr($checksum, 0, 3);
        check_dir_exists($path);
        return $path . '/' . substr($checksum, 3) . ".$type";
    }

    public function unlink($mountpoint=null) {
        global $USER;

        db_begin();

        if (is_null($mountpoint)) {
            $mountpoint = $this->socket->mountpoint($USER->get('id'));
        }
        if ($mountpoint->get('owner') == $USER->get('id')) {
            $mountpoint->delete();
        }

        $this->socket->revoke($USER->get('id'));

        db_commit();
    }

    public function upload($id) {
        global $USER;
        $artefact = artefact_instance_from_id($id);
        if (!@$this->socket->config['upload_enabled']) {
            $artefact->delete();
            throw new UploadException('Cloud file upload is disabled by administrator.');
        }
        if ($USER->get('id') != $artefact->get('owner')) {
            throw new UploadException('Owner mismatch');
        }
        $parent = get_field('artefact_cloudfile', 'remote_id', 'artefact', $artefact->get('parent'));
        $this->deferred_upload($artefact->get('owner'), $id, $parent, true);
    }

    public function create_folder($folder) {
        $parent = get_field('artefact_cloudfile', 'remote_id', 'artefact', $folder->get('parent'));
        $entry = $this->socket->create_folder($folder->get('owner'), $folder->get('title'), $parent);

        insert_record('artefact_cloudfile', (object)array(
            'artefact'  => $folder->get('id'),
            'type'      => $this->socket->type,
            'remote_id' => $entry['id'],
            'src'       => $entry['src'],
            'checksum'  => $entry['checksum'],
        ));
        return true;
    }

    public function update($artefact) {
        $remote = $this->get_artefact($artefact->get('id'));
        $this->socket->update($remote);
        return true;
    }

    public function move($artefact, $newparentid) {
        $c1 = $this->in_cloud($artefact->get('id'));
        $c2 = $this->in_cloud($newparentid);
        $ok = false;

        // Case 1: artefact in cloud, target in cloud
        if ($c1 && $c2) {
            $id   = get_field('artefact_cloudfile', 'remote_id', 'artefact', $artefact->get('id'));
            $from = get_field('artefact_cloudfile', 'remote_id', 'artefact', $artefact->get('parent'));
            $to   = get_field('artefact_cloudfile', 'remote_id', 'artefact', $newparentid);

            try {
                $ok = $this->socket->move($artefact->get('owner'), $id, $from, $to);
            }
            catch (CloudFileSocketException $e) {
                log_info($e->getMessage());
                return false;
            }
        }
        // Case 2: artefact in cloud, target local
        else if ($c1 && !$c2) {
            return false; // not implemented
        }
        // Case 3: artefact local, target in cloud
        else if (!$c1 && $c2) {
            if (!@$this->socket->config['upload_enabled']) {
                return false;
            }
            $ok = $this->deferred_upload($artefact->get('owner'), $artefact->get('id'), null, true);
        }
        if ($ok) {
            return $artefact->move($newparentid);
        }
        return false;
    }

    public function delete($artefact) {
        $remote_id = get_field('artefact_cloudfile', 'remote_id', 'artefact', $artefact->get('id'));
        if ($remote_id && $this->socket->delete($artefact->get('owner'), $remote_id)) {
            $artefact->delete();
            return true;
        }
    }

    protected function deferred_upload($user, $artefact, $parent, $cleanup=true) {
        $client = new GearmanClient();
        $client->addServer("127.0.0.1");

        $param = (object) array(
            'action'      => 'upload',
            'cloud'       => $this->socket->type,
            'user'        => $user,
            'artefact'    => $artefact,
            'parent'      => $parent,
            'cleanup'     => $cleanup,
            'remote_addr' => $_SERVER['REMOTE_ADDR']
        );
        $client->doBackground("cloudfile", json_encode($param));
        $code = $client->returnCode();
        if ($code != GEARMAN_SUCCESS) {
            log_info("Bad Gearman return code " . $code);
            return false;
        }
        return true;
    }
}


function cloudfile_chunked_stream($ch, $string) {
    global $CLOUDHANDLE;
    echo $string;
    flush();
    return fwrite($CLOUDHANDLE, $string);
}


function cloudfile_chunked($ch, $string) {
    global $CLOUDHANDLE;
    return fwrite($CLOUDHANDLE, $string);
}

