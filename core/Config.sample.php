<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define("DEFAULT_CONTROLLER", "main");
define("DEFAULT_ACTION", "index");
define("DATABASE_HOST", "localhost");
define("DATABASE_USER", "");
define("DATABASE_PASSWORD", "");
define("DATABASE_DB", "");

spl_autoload_register(function ($class) {
    $segments = explode("\\", $class);
    $path = '';
    for ($i = 0; $i < count($segments) - 1; $i++) {
        $path .= strtolower($segments[$i]) . '/';
    }
    $finalPath = __DIR__ . '/../' . $path . '/' . $segments[$i] . '.php';
    if (file_exists($finalPath)) {
        include_once $finalPath;
    }
});