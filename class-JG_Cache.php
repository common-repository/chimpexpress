<?php

// no direct access
defined('ABSPATH') or die('Restricted Access');

class chimpxpressJG_Cache {

    private $dir;
    private $useFTP;
    private $handler;

    function __construct($dir, $useFTP = false, $handler = null) {
        global $wp_filesystem;

        $this->dir = $dir;
        $this->useFTP = $useFTP;
        $this->handler = $handler ?? $wp_filesystem;
    }

    private function getPathName($key) {
        return sprintf("%s/%s", $this->dir, sha1($key));
    }

    public function get($key, $expiration = 0) {
        if (!$this->handler->is_dir(ABSPATH . $this->dir)) {
            return false;
        }

        $cachePath = $this->getPathName($key);

        if (!$this->handler->is_file(ABSPATH . $cachePath)) {
            return false;
        }

        if ($expiration > 0 && $this->handler->mtime(ABSPATH . $cachePath) < (time() - $expiration)) {
            $this->clear($key);
            return false;
        }

        if (!$this->handler->is_readable(ABSPATH . $cachePath)) {
            return false;
        }

        $cache = null;
        if ($this->handler->size(ABSPATH . $cachePath) > 0) {
            $cache = unserialize($this->handler->get_contents(ABSPATH . $cachePath));
        }

        return $cache;
    }

    public function set($key, $data) {
        if (!$this->handler->is_dir(ABSPATH . $this->dir)) {
            return false;
        }

        $cachePath = $this->getPathName($key);

        if ($this->useFTP) {
            global $wp_filesystem;

            $temp = tmpfile();
            $wp_filesystem->put_contents($temp, serialize($data));
            rewind($temp);
            if (!ftp_fput($this->handler, $cachePath, $temp, FTP_ASCII)) {
                return false;
            }
        } else {

            if (!$this->handler->is_writable(ABSPATH . $this->dir)) {
                return false;
            }

            if (!$this->handler->put_contents(ABSPATH . $cachePath, serialize($data))) {
                return false;
            }
        }

        return true;
    }

    public function clear($key) {
        $cachePath = $this->getPathName($key);

        if ($this->handler->is_file(ABSPATH . $cachePath)) {
            if ($this->useFTP) {
                ftp_delete($this->handler, $cachePath);
            } else {
                $this->handler->delete($cachePath);
            }
        }
    }
}
