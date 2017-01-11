<?php
/**
 * $Id: Autoload.php,v 1.4 2008/09/03 13:51:15 charlles.sousa Exp $
 */
if (!function_exists('__autoload')) {
    /**
     * load class
     * @param string $className : the class to load
     * @throws Exception
     */
    function __autoload($className) {
        if (class_exists('Zend', false)) {
            if (is_int(strrpos($className, '_Interface'))) {
                Zend :: loadClass($className);
            } else {
                Zend :: loadInterface($className);
            }
        } else {
            if (!defined('__CLASS_PATH__')) {
                define('__CLASS_PATH__', realpath(dirname(__FILE__)));
            }
            $file = __CLASS_PATH__ . '/' . str_replace('_', '/', $className) . '.php';
            if (!file_exists($file)) {
                throw new Exception('Cannot load class file ' . $className);
            } else {
                require_once $file;
            }
        }
    }
}