<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

/**
 * 本地文件缓存类
 * 兼容Memcached API协议: Memcached::set, Memcached:get, Memcached::delete
 */

class FileCache {
    const RES_SUCCESS = 0;
    const RES_FAILURE = 1;
    const RES_DELETE_FAILURE = 2;
    const RES_NOTFOUND = 4;

    private $resultCode;

    public function __constructor($config = array()) {
        $this->resultCode = self::RES_SUCCESS;
    }

    // Returns TRUE on success or FALSE on failure. Use Memcached::getResultCode() if necessary.
    public function set($path, $value, $expire = 0) {
        if (!file_exists($path)) {
            $dir = dirname($path);
            if (!file_exists($dir)) {
                umask(0);
                mkdir($dir, 0777, TRUE);
            }
        }

        $array = array();
        $array['data'] = $value;
        $array['expire'] = $expire > 0 ? $expire : 0;
        $ret = file_put_contents($path, json_encode($array));
        if ($ret === FALSE) {
            $this->resultCode = self::RES_FAILURE;
            return FALSE;
        }

        return TRUE;
    }

    // Returns the value stored in the cache or FALSE otherwise. The Memcached::getResultCode() will return Memcached::RES_NOTFOUND if the key does not exist.
    public function get($path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $array = json_decode($content, TRUE);
            $data = $array['data'];
            $expire = $array['expire'];
            $mtime = filemtime($path);
            if ($expire === 0 || $mtime + $expire > time()) {
                return $data;
            }
        }

        $this->resultCode = self::RES_NOTFOUND;
        return FALSE;
    }

    // Returns TRUE on success or FALSE on failure. The Memcached::getResultCode() will return Memcached::RES_NOTFOUND if the key does not exist.
    public function delete($path) {
        if (file_exists($path)) {
            $ret = unlink($path);
            if ($ret === FALSE) {
                $this->resultCode = self::RES_DELETE_FAILURE;
                return FALSE;
            }

            return TRUE;
        }

        $this->resultCode = self::RES_NOTFOUND;
        return TRUE;
    }

    public function getResultCode() {
        return $this->resultCode;
    }
}

// $path = './test.log';
// $fileCache = new FileCache();
// $fileCache->set($path, array('test' => 2), 0);
// $data = $fileCache->get($path);
// var_dump($data);
// $fileCache->delete($path);