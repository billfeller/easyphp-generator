<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:

class loadClass {
    static function load($className) {
        $filename = SITE_INTERFACE_ROOT . $className . '.php';
        if (is_file($filename)) {
            return require_once($filename);
        }

        $filename = SITE_LIB_ROOT . $className . '.php';
        if (is_file($filename)) {
            return require_once($filename);
        }

        $filename = SITE_API_ROOT . $className . '.php';
        if (is_file($filename)) {
            return require_once($filename);
        }

        die('Loadclass Include File Error!');
    }
}

spl_autoload_register(array('loadClass', 'load'));
