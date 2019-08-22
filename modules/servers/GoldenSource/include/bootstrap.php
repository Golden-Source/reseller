<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
require_once __DIR__ . "/functions.php";
 
spl_autoload_register(function ($class_name) {
    $class_name = str_replace('GoldenSourceUI\\', null, $class_name);
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    $filename = dirname(dirname(dirname(__DIR__))) . '/addons/GoldenSource/include/GoldenSourceUI/' . $class_name . '.php';
    if (is_file($filename)) {
        require_once $filename;
        return true;
    }
    return false;
});

spl_autoload_register(function ($class_name) {
    $class_name = str_replace('GoldenSource\\', null, $class_name);
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    $filename = __DIR__ . '/GoldenSource/' . $class_name . '.php';
    if (is_file($filename)) {
        require_once $filename;
        return true;
    }
    return false;
});