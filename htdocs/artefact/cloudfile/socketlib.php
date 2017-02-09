<?php
defined('INTERNAL') || die();

require dirname(dirname(dirname(__FILE__))) . '/local/pdo.php';
require dirname(dirname(dirname(__FILE__))) . '/local/mime_types.php';


function header_val($response, $key) {
    if (strpos($key, ':') === false) {
        $key .= ':';
    }
    $ret = null;
    $m = array();
    if (preg_match("{^$key (.+)$}m", $response, $m)) {
        $ret = trim($m[1]);
    }
    return $ret;
}


function first($obj) {
    $list = (array)$obj;
    if (empty($list)) {
        return null;
    }
    return $list[0];
}


function escape_xml($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


function cloudfile_http_request($config) {
    // Copied from mahara_http_request()

    $ch = curl_init();
    // standard curl_setopt stuff; configs passed to the function can override these
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla 4');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

    curl_setopt_array($ch, $config);

    $result = new StdClass();
    $result->data = curl_exec($ch);
    $result->info = curl_getinfo($ch);
    $result->error = curl_error($ch);
    $result->errno = curl_errno($ch);

    if ($result->errno) {
        trigger_error('Curl error: ' . $result->errno . ': ' . $result->error);
    }
    curl_close($ch);

    return $result;
}

/* * * * * * * * * * * * * * * */

abstract class CloudFileSocket {
    protected function get($url, $headers=array()) {
        return cloudfile_http_request(array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPGET => 1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
        ));
    }

    protected function post($url, $param=array()) {
        $post = array();
        foreach ($param AS $key => $val) {
            $post[] = $key . '=' . urlencode($val);
        }
        return cloudfile_http_request(array(
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_POSTFIELDS => implode('&', $post),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
        ));
    }

    protected function request($method, $url, $headers=null, $body=null) {
        $param = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => 1,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem',
            CURLOPT_VERBOSE => 0,
        );
        if ($headers) {
            $param[CURLOPT_HTTPHEADER] = $headers;
        }
        if ($body) {
            $param[CURLOPT_POSTFIELDS] = $body;
        }
        return cloudfile_http_request($param);
    }

    public function exchange_authorization_code($user, $code) {
        $param = array(
            'code'          => $code,
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri'  => $this->config['redirect_uri'],
            'grant_type'    => 'authorization_code',
        );

        $res = $this->post($this->oauth_exchange_url(), $param);
        $parsed = json_decode($res->data);

        if (isset($parsed->access_token) && isset($parsed->refresh_token)) {
            $dbh = pdo_connect();
            $dbh->beginTransaction();
            $oauth = new stdClass();
            $oauth->access_token  = $parsed->access_token;
            $oauth->refresh_token = $parsed->refresh_token;
            $oauth->expires_in    = $parsed->expires_in + time() - 10;

            pdo_execute_sql("LOCK TABLES artefact_cloudfile_config WRITE");
            pdo_execute_sql(
                "REPLACE INTO artefact_cloudfile_config (usr, type, value)  VALUES (?,?,?)",
                $user, $this->type, serialize($oauth)
            );
            pdo_execute_sql("UNLOCK TABLES");
            $this->mountpoint($user); //ensure that mountpoint exists
            $dbh->commit();
        }
        else {
            failed("Authorization code exchange failed. Please try again.", "Unexpected response: {$res->data}");
        }
    }

    public function mountpoint($user) {
        $id = pdo_get_field(
            "SELECT a.id FROM artefact a
            INNER JOIN artefact_cloudfile cf ON a.id = cf.artefact
            WHERE a.owner = ? AND a.parent IS NULL AND a.artefacttype = 'folder'
            AND cf.type = ? AND cf.remote_id = ?", $user, $this->type, $this->root_id
        );
        if ($id) {
            return new ArtefactTypeFolder($id);
        }

        $folder = new ArtefactTypeFolder();
        $folder->set('title', $this->name);
        $folder->set('description', get_string('rootfolderdesc', 'artefact.cloudfile'));
        $folder->set('owner', $user);
        $folder->set('author', $user);
        $folder->set('tags', array());
        $folder->commit();
        insert_record('artefact_cloudfile', (object)array(
            'artefact'  => $folder->get('id'),
            'type'      => $this->type,
            'remote_id' => $this->root_id,
        ));

        return $folder;
    }

    public function get_access_token($user) {
        $oauth = pdo_get_field("SELECT value FROM artefact_cloudfile_config WHERE type = ? AND usr = ?", $this->type, $user);
        if (!$oauth) {
            return null;
        }
        $oauth = unserialize($oauth);
        $expires = $oauth->expires_in;
        $token = $oauth->access_token;
        if (empty($token) || ($expires > 0 && $expires < time())) {
            $dbh = pdo_connect();
            $dbh->beginTransaction();
            pdo_execute_sql("LOCK TABLES artefact_cloudfile_config WRITE");
            $token = $this->refresh_oauth($user);
            pdo_execute_sql("UNLOCK TABLES");
            $dbh->commit();
        }
        return $token;
    }

    protected function refresh_oauth($user) {
        $oauth = pdo_get_field("SELECT value FROM artefact_cloudfile_config WHERE type = ? AND usr = ?", $this->type, $user);
        if ( ! $oauth) {
            // Other process has revoked token while we were waiting for write lock.
            return null;
        }
        $oauth = unserialize($oauth);
        if ($oauth->expires_in > time() + 60) {
            // Other process has updated token while we were waiting for write lock.
            return $oauth->access_token;
        }

        $params = array(
                'refresh_token' => $oauth->refresh_token,
                'client_id'     => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type'    => 'refresh_token',
            );

        $res = $this->post($this->oauth_exchange_url(), $params);
        if ($res->info['http_code'] != 200) {
            failed("Fatal: failed to fetch new access token", $res);
        }
        $res = json_decode($res->data);

        $oauth->access_token = $res->access_token;
        $oauth->expires_in = $res->expires_in + time() - 10;
        pdo_execute_sql(
            "UPDATE artefact_cloudfile_config SET value = ? WHERE type = ? AND usr = ?",
            serialize($oauth), $this->type, $user
        );
        return $oauth->access_token;
    }

    public function revoke($user) {
        $dbh = pdo_connect();
        $dbh->beginTransaction();
        pdo_execute_sql("LOCK TABLES artefact_cloudfile_config WRITE");

        pdo_execute_sql("DELETE FROM artefact_cloudfile_config WHERE usr = ? AND type = ?", $user, $this->type);

        pdo_execute_sql("UNLOCK TABLES");
        $dbh->commit();
    }

    // url used in oauth2 token exchange
    protected abstract function oauth_exchange_url();

    // array of headers used in REST api calls
    public abstract function request_header($artefact);

    // all files and folders in cloud
    public abstract function remote_filelist($user);

    // add cloud-specific attributes to artefact object
    public abstract function augment(&$artefact);

    // update file meta data
    public abstract function refresh_artefact(&$artefact);

    // file and folder ops

    public abstract function upload($user, $path, $filetype, $title, $description);

    public abstract function update($artefact);

    public abstract function delete($user, $id);

    public abstract function move($user, $id, $from, $to);

    public abstract function create_folder($user, $title, $parent);
}

/* * * * * * * * * * * * * * * * * * * * * * * */

class GoogleDriveSocket extends CloudFileSocket {
    public $type    = 'googledrive';    // identifier in db
    public $name    = 'Google Drive';  // display name of cloud service
    public $root_id = 'root';   // id of root folder in db
    public $remote_addr;               // current client ip addr
    public $config;                    // parameters from config.php

    protected function oauth_exchange_url() {
        return 'https://accounts.google.com/o/oauth2/token?userIp=' . $this->remote_addr;
    }

    public function augment(&$artefact) {
        $artefact->stream = ! preg_match('/^text|html/', $artefact->filetype);
        $artefact->ttl = 3600;
        $artefact->extra = null;

        if ($artefact->filetype == 'text/html' || $artefact->filetype == 'text/xml') {
            $artefact->document_type = 'html';
        }

        $gdocformats = array('application/msword', 'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel', 'image/png');

        if (!in_array($artefact->filetype, $gdocformats)) {
            $artefact->ttl = 30000;
        }

        // Augment extension to filename if missing.
        $parts = pathinfo($artefact->filename);

        if (!isset($parts['extension'])) {
            $ext = '';

            switch ($artefact->filetype) {
                case 'application/msword': $ext = 'docx'; break;
                case 'application/vnd.ms-powerpoint': $ext ='pptx'; break;
                case 'application/vnd.ms-excel': $ext = 'xlsx'; break;
                case 'image/png': $ext = 'png';
            }

            if (!empty($ext)) {
                $artefact->filename .= "." . $ext;
            }
        }
    }

    public function refresh_artefact(&$artefact) {
        pdo_execute_sql("UPDATE artefact_cloudfile SET dirty = 0 WHERE artefact = ?", $artefact->id);

        $entry = $this->get_entry($artefact);
        $checksum = isset($entry->md5Checksum) ? $entry->md5Checksum : null;

        if ($checksum) {
            $artefact->checksum = $checksum;
            pdo_execute_sql("UPDATE artefact_cloudfile SET checksum = ? WHERE artefact = ?",
                $checksum, $artefact->id);
        }

        $size = $entry->quotaBytesUsed;
        if ($size && $artefact->size != $size) {
            $artefact->size = $size;
            pdo_execute_sql("UPDATE artefact_file_files SET size = ? WHERE artefact = ?",
                $size, $artefact->id);
        }

        $artefact->src = $this->get_src($artefact, $entry);
    }

    public function get_entry($artefact) {
        $url = 'https://www.googleapis.com/drive/v2/files/' . urlencode($artefact->remote_id);
        $url .= '?userIp=' . $this->remote_addr;

        $res = $this->get($url, $this->request_header($artefact));
        if ($res->info['http_code'] != 200) {
            failed('Failed to fetch metadata for ' . $artefact->remote_id, $res);
        }

        return json_decode($res->data);
    }

    public function request_header($artefact) {
        return array(
            'GData-Version: 3.0',
            'Authorization: Bearer ' . $this->get_access_token($artefact->owner),
        );
    }

    public function file_src($artefact) {
        $entry = $this->get_entry($artefact);
        return $this->get_src($artefact, $entry);
    }

    private function get_src($artefact, $entry) {
        if (!empty($entry->downloadUrl)) {
            return $entry->downloadUrl;
        }

        $src = '';
        $exportlinks = isset($entry->exportLinks) ? (array) $entry->exportLinks : array();

        switch ($artefact->filetype) {
            case 'application/msword':
                $src = $exportlinks['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                break;
            case 'image/png':
                $src = $exportlinks['image/png'];
                break;
            case 'application/vnd.ms-excel':
                $src = $exportlinks['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                break;
            case 'application/vnd.ms-powerpoint':
                $src = $exportlinks['application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                break;

        }

        return $src;
    }

    public function remote_filelist($user) {
        $url = 'https://www.googleapis.com/drive/v2/files/?maxResults=1000&q=' . urlencode("'me' in owners");
        $token = $this->get_access_token($user);
        $headers = array('GData-Version: 3.0', 'Authorization: Bearer ' . $token);
        $entries = array();

        $this->gather_entries($url, $headers, $entries);
        return $entries;
    }

    protected function gather_entries($url, $headers, &$entries) {
        $url .= '&userIp=' . $this->remote_addr;
        $res = $this->get($url, $headers);

        if ($res->info['http_code'] != 200) {
            failed('Failed to get file list from remote server. Please wait few minutes and try again.', $res);
        }

        $json = json_decode($res->data);
        $typelookup = array(
            'application/vnd.google-apps.document'      => 'application/msword',
            'application/vnd.google-apps.drawing'       => 'image/png',
            'application/vnd.google-apps.spreadsheet'   => 'application/vnd.ms-excel',
            'application/vnd.google-apps.presentation'  => 'application/vnd.ms-powerpoint',
        );

        foreach ($json->items as $entry) {
            // Skip deleted (trashed) files.
            if ($entry->labels->trashed) {
                continue;
            }

            $id = $entry->id;
            $entries[$id] = array(
                'id'            => $id,
                'title'         => $entry->title,
                'description'   => isset($entry->description) ? $entry->description : '',
                'filetype'      => isset($typelookup[$entry->mimeType]) ? $typelookup[$entry->mimeType] : $entry->mimeType,
                'mtime'         => $entry->modifiedDate,
                'src'           => '', // Google Drive file links are short lived, no point in storing it
                'parent'        => count($entry->parents) > 0 ? $entry->parents[0]->id : null,
                'checksum'      => isset($entry->md5Checksum) ? $entry->md5Checksum : sha1(getmypid() . microtime(true) . $id),
                'filename'      => isset($entry->originalFilename) ? $entry->originalFilename : null,
                'size'          => $entry->quotaBytesUsed,
                'isfolder'      => $entry->mimeType === 'application/vnd.google-apps.folder',
                'isimage'       => preg_match('/^image/', $entry->mimeType)
            );
        }

        if (!empty($json->nextLink)) {
            return $this->gather_entries($json->nextLink, $headers, $entries);
        }
    }

    //TODO Maybe some files should be converted?
    protected function should_convert($filetype) {
        return false;
    }

    public function upload($user, $path, $filetype, $title, $description) {
        $token = $this->get_access_token($user);
        $upload_url = 'https://www.googleapis.com/upload/drive/v2/files?uploadType=resumable&userIp=' . $this->remote_addr;
        $json = json_encode(array('title' => $title, 'description' => $description));
        $filesize = filesize($path);
        $res = $this->request('POST', $upload_url, array(
                    "Authorization: Bearer {$token}",
                    "Content-Length: " . strlen($json),
                    "Content-Type: application/json; charset=UTF-8",
                    "X-Upload-Content-Type: " . $filetype,
                    "X-Upload-Content-Length: " . $filesize
                ), $json
        );

        $url = header_val($res->data, 'Location');

        if ($res->info['http_code'] != 200 || !$url) {
            failed("Failed to create new entry", array('path' => $path, 'json' => $json, 'response' => $res));
        }

        $chunksize = 512 * 1024;
        $multi = $filesize > $chunksize;

        $fh = fopen($path, 'rb');
        $start = 0;
        $end = -1;
        $cx = hash_init('md5');
        while ($bytes = fread($fh, $chunksize)) {
            $start = $end + 1;
            $end += strlen($bytes);

            hash_update($cx, $bytes);

            for ($retry = 6; $retry > 0; $retry--) {

                $headers = array(
                    "Authorization: Bearer {$token}",
                    "Content-Type: {$filetype}",
                    "Content-Length: " . strlen($bytes)
                );

                if ($multi) {
                    $headers[] = "Content-Range: bytes {$start}-{$end}/{$filesize}";
                }

                $res = $this->request('PUT', $url, $headers, $bytes);

                if ($res->info['http_code'] == 308) {
                    $range = header_val($res->data, 'Range');
                    $end = array_pop(explode('-', $range));
                    break;
                }
                if ($res->info['http_code'] == 201 || $res->info['http_code'] == 200) {
                    break 2; //ok, upload done
                }
                if ($res->info['http_code'] == 400) {
                    failed("Bad request", $res);
                }
                if ($res->info['http_code'] == 404) {
                    // TODO: Start upload from the beginning here.
                    failed("Expired upload session ID", $res);
                }
                if ($retry == 1) {
                    failed("Upload failed, not trying again", $res);
                }
                $wait = (int) header_val($res->data, 'Retry-After');
                sleep($wait > 0 ? $wait : 8 - $retry);
            }
        }
        fclose($fh);

        // Now $res should contain information about the new entry.
        $body = substr($res->data, $res->info['header_size']);
        $entryjson = json_decode($body);
        $entry = array(
            'id'            => $entryjson->id,
            'title'         => $entryjson->title,
            'description'   => isset($entryjson->description) ? $entryjson->description : '',
            'filetype'      => $entryjson->mimeType,
            'mtime'         => $entryjson->modifiedDate,
            'src'           => '', // Google Drive file links are short lived, no point in storing it
            'checksum'      => empty($entryjson->md5Checksum) ? sha1(getmypid() . microtime(true) . $entryjson->id) : $entryjson->md5Checksum,
            'filename'      => isset($entry->originalFilename) ? $entry->originalFilename : null,
            'size'          => $entryjson->quotaBytesUsed
        );

        if (!empty($entryjson->md5Checksum) && $entryjson->md5Checksum != hash_final($cx)) {
            failed('Upload checksum mismatch');
        }

        return $entry;
    }

    public function delete($user, $id) {
        if ($id == $this->root_id) {
            return true;
        }

        $res = $this->request('POST', "https://www.googleapis.com/drive/v2/files/{$id}/trash?userIp=" . $this->remote_addr,
            array(
                "If-Match: *",
                "GData-Version: 3.0",
                "Authorization: Bearer " . $this->get_access_token($user),
                "Content-Length: 0"
            ), ''
        );
        if (!in_array($res->info['http_code'], array(200, 204, 404))) {
            failed("Failed to delete artefact $id", $res);
        }
        return true;
    }

    public function update($artefact) {
        $entry = $this->get_entry($artefact);

        $json = json_encode(array(
            'title' => $artefact->title,
            'description' => $artefact->description
        ));

        $res = $this->request('PUT', 'https://www.googleapis.com/drive/v2/files/' .
                $entry->id . '?userIp=' . $this->remote_addr,
            array(
                "If-Match: *",
                "GData-Version: 3.0",
                "Authorization: Bearer " . $this->get_access_token($artefact->owner),
                "Content-Type: application/json",
                "Content-Length: " . strlen($json),
                "Expect:",
            ), $json
        );
        if ($res->info['http_code'] != 200) {
            failed("Failed to update artefact", $res);
        }
        return true;
    }

    public function move($user, $id, $from, $to) {
        if ($from == $to) {
            return true;
        }

        $url = 'https://www.googleapis.com/drive/v2/files/' . $id . '/parents?userIp=' . $this->remote_addr;

        // Google Drive allows us to use 'root' alias.
        $newparent = json_encode(array('id' => $to));
        $token = $this->get_access_token($user);

        // Add a new parent to file.
        $res = $this->request('POST', $url,
            array(
                "GData-Version: 3.0",
                "Authorization: Bearer {$token}",
                "Content-Type: application/json",
                "Content-Length: " . strlen($newparent),
                "Expect:",
            ), $newparent
        );

        if (substr($res->info['http_code'], 0, 1) != '2') {
            failed("Failed to create new entry", $res);
        }

        // Delete old parent from file.
        $url = 'https://www.googleapis.com/drive/v2/files/' . $id . '/parents/' . $from . '?userIp=' . $this->remote_addr;
        $res = $this->request('DELETE', $url,
            array(
                "If-Match: *",
                "GData-Version: 3.0",
                "Authorization: Bearer {$token}",
                "Content-Length: 0",
                "Expect:",
            ), ''
        );
        if (substr($res->info['http_code'], 0, 1) != '2') {
            failed("Failed to delete old entry", $res);
        }
        return true;
    }

    public function create_folder($user, $title, $parent=null) {
        $url = 'https://www.googleapis.com/drive/v2/files?userIp=' . $this->remote_addr;
        $parents = !is_null($parent) && $parent != $this->root_id ? array(array('id' => $parent)) : array();
        $folderjson = json_encode(array(
            'title' => $title,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parents
        ));

        $token = $this->get_access_token($user);
        $res = $this->request('POST', $url,
            array(
                "Authorization: Bearer {$token}",
                "Content-Type: application/json",
                "Content-Length: " . strlen($folderjson)
            ), $folderjson
        );

        if ($res->info['http_code'] == 200) {
            $split = preg_split('/\r?\n\r?\n/', $res->data, 2);
            $file = json_decode($split[1]);
            $id = $file->id;

            return array(
                'id'          => $id,
                'src'         => '',
                'checksum'    => sha1(getmypid() . microtime(true) . $id),
            );
        }
        failed("Failed to create folder", $res);
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * */

class SkyDriveSocket extends CloudFileSocket {
    public $type    = 'skydrive';            // identifier in db
    public $name    = 'Microsoft OneDrive';  // display name of cloud service
    public $root_id = 'folder:root';         // id of root folder in db
    public $remote_addr;                     // current client ip addr
    public $config;                          // parameters from config.php


    protected function oauth_exchange_url() {
        return 'https://login.live.com/oauth20_token.srf';
    }

    public function request_header($artefact) {
        return array(
            'Authorization: BEARER ' . $this->get_access_token($artefact->owner),
        );
    }

    public function remote_filelist($user) {
        $token = $this->get_access_token($user);

        $url = 'https://apis.live.net/v5.0/me/skydrive/files';

        $entries = array();

        $this->gather_entries($url, $token, $entries);
        return $entries;
    }

    protected function gather_entries($url, $token, &$entries) {
        $url .= '?access_token=' . $token;

        $res = $this->get($url);

        if ($res->info['http_code'] != 200) {
            failed('Failed to get file list from remote server. Please wait few minutes and try again.', $res);
        }

        $json = json_decode($res->data);

        foreach ($json->data AS $entry) {

            $isfolder = $entry->type === 'folder' || $entry->type === 'album';

            $filetype = '';
            if ( ! $isfolder) {
                $filetype = 'application/octet-stream';
                $ext = preg_replace('/.+\./', '', $entry->name);
                if ($newtype = mime_by_ext($ext)) {
                    $filetype = $newtype;
                }
            }
            $entries[$entry->id] = array(
                'id'          => $entry->id,
                'title'       => $entry->name,
                'description' => $entry->description,
                'filetype'    => $filetype,
                'mtime'       => $entry->updated_time,
                'src'         => 'https://apis.live.net/v5.0/' . $entry->id . '/content',
                'parent'      => $entry->parent_id,
                'checksum'    => $this->checksum($entry),
                'filename'    => $entry->name,
                'size'        => $isfolder ? 0 : $entry->size,
                'isfolder'    => $isfolder,
                'isimage'     => $entry->type === 'photo' || preg_match('/^image/', $filetype)
            );

            if (@$_GET['DEBUG']) {
                $entries[$entry->id] = $entry; //XXX
            }

            if ($isfolder) {
                $this->gather_entries($entry->upload_location, $token, $entries);
            }
        }
    }

    public function augment(&$artefact) {
        $inline = preg_match('/^(text|image)|html/', $artefact->filetype);

        $artefact->stream = ! $inline;
        $artefact->ttl    = 3600;
        $artefact->extra  = null;
        $artefact->document_type = 'file';

        if ( ! $inline) {
            $artefact->extra = 'download=true';
        }
    }

    public function refresh_artefact(&$artefact) {
        pdo_execute_sql("UPDATE artefact_cloudfile SET dirty = 0 WHERE artefact = ?", $artefact->id);

        $entry = $this->get_entry($artefact);

        $checksum = $this->checksum($entry);
        if ($checksum != $artefact->checksum) {
            $artefact->checksum = $checksum;
            pdo_execute_sql("UPDATE artefact_cloudfile SET checksum = ? WHERE artefact = ?",
                $checksum, $artefact->id);
        }
        $size = $entry->size;
        if ($size && $artefact->size != $size) {
            $artefact->size = $size;
            pdo_execute_sql("UPDATE artefact_file_files SET size = ? WHERE artefact = ?",
                $size, $artefact->id);
        }

        $src = 'https://apis.live.net/v5.0/' . $artefact->remote_id . '/content';
        if (isset($artefact->extra)) {
            $sep = strpos($src, '?') === false ? '?' : '&';
            $src .= $sep . $artefact->extra;
        }
        $artefact->src = $src;
    }

    public function get_entry($artefact) {
        $url = 'https://apis.live.net/v5.0/' . $artefact->remote_id . '?access_token=' . $this->get_access_token($artefact->owner);

        $res = $this->get($url);
        if ($res->info['http_code'] != 200) {
            failed('Failed to fetch metadata for ' . $artefact->remote_id, $res);
        }
        return json_decode($res->data);
    }

    public function create_folder($user, $title, $parent=null) {
        $url = 'https://apis.live.net/v5.0';

        if ($parent && $parent != $this->root_id) {
            $url .= $parent;
        }
        else {
            $url .= '/me/skydrive';
        }

        $res = $this->request('POST', $url,
            array(
                "Authorization: Bearer " . $this->get_access_token($user),
                "Content-Type: application/json",
            ),
            json_encode(array('name' => $title))
        );

        if ($res->info['http_code'] == 201) {
            $split = preg_split('/\r?\n\r?\n/', $res->data, 2);
            $entry = json_decode($split[1]);
            return array(
                'id'          => $entry->id,
                'src'         => '',
                'checksum'    => $this->checksum($entry)
            );
        }
        failed("Failed to create folder", $res);
    }

    public function upload($user, $path, $filetype, $title, $description) {
        $url  = 'https://apis.live.net/v5.0/me/skydrive/files/' . urlencode($title);
        $url .= '?access_token=' . $this->get_access_token($user);

        $size = filesize($path);

        $fh = fopen($path, 'r');
        if (!$fh) {
            failed("Failed to open file $path");
        }

        $param = array(
            CURLOPT_PUT => 1,
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_CONNECTTIMEOUT => 1000,
            CURLOPT_TIMEOUT => 1000,
            CURLOPT_INFILE => $fh,
            CURLOPT_INFILESIZE => $size,
        );

        $res = cloudfile_http_request($param);
        fclose($fh);

        if (substr($res->info['http_code'], 0, 1) != '2') {
            failed("Failed to upload file", $res);
        }

        // Returned data contains only part of what we need. Get new entry by id.
        $entry = json_decode($res->data);

        $url = 'https://apis.live.net/v5.0/' . $entry->id . '?access_token=' . $this->get_access_token($user);

        $res = $this->get($url);
        if ($res->info['http_code'] != 200) {
            failed('Failed to fetch metadata for ' . $entry->id, $res);
        }
        $entry = json_decode($res->data);

        $entry = array(
            'id'          => $entry->id,
            'title'       => $entry->name,
            'description' => $description,
            'filetype'    => $filetype,
            'mtime'       => $entry->updated_time,
            'src'         => 'https://apis.live.net/v5.0/' . $entry->id . '/content',
            'checksum'    => $this->checksum($entry),
            'filename'    => $entry->name,
            'size'        => $entry->size,
        );
        return $entry;
    }

    public function delete($user, $id) {
        if ($id == $this->root_id) {
            return true;
        }
        $url = 'https://apis.live.net/v5.0/' . $id . '?access_token=' . $this->get_access_token($user);
        $res = $this->request('DELETE', $url);

        if ($res->info['http_code'] != 204 && $res->info['http_code'] != 404) {
            failed("Failed to delete artefact $id", $res);
        }
        return true;
    }

    public function update($artefact) {
        $res = $this->request('PUT', 'https://apis.live.net/v5.0/' . $artefact->remote_id,
            array(
                "Authorization: Bearer " . $this->get_access_token($artefact->owner),
                "Content-Type: application/json",
            ),
            json_encode(array('name' => $artefact->title, 'description' => $artefact->description))
        );
        if (substr($res->info['http_code'], 0, 1) != '2') {
            failed("Failed to update artefact", $res);
        }
        return true;
    }

    public function move($user, $id, $from, $to) {
        if ($from == $to) {
            return true;
        }
        $res = $this->request('MOVE', 'https://apis.live.net/v5.0/' . $id,
            array(
                "Authorization: Bearer " . $this->get_access_token($user),
                "Content-Type: application/json",
            ),
            json_encode(array('destination' => $to))
        );
        if (substr($res->info['http_code'], 0, 1) != '2') {
            failed("Failed to move entry", $res);
        }
        return true;
    }

    protected function checksum($entry) {
        return sha1( $entry->id . $entry->name . $entry->size . $entry->updated_time );
    }
}

/* * * * * * * * * * * * * * * * * * * * * * * * * */

class CloudFileSocketException extends Exception { }

function failed($msg, $dump=null) {
    if ($dump) {
        trigger_error(print_r($dump, true), E_USER_WARNING);
    }
    throw new CloudFileSocketException($msg);
}
