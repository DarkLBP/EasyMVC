<?php
session_start();
if (file_exists( __DIR__ . '/../core/Config.php')) {
    require_once  __DIR__ . '/../core/Config.php';
} else {
    require_once  __DIR__ . '/../core/ConfigDefault.php';
}
$request = new Core\Request();
$request->dispatch();