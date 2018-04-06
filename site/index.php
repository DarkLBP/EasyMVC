<?php
session_start();
require_once __DIR__ . '/../core/Config.php';
$request = new Core\Request();
$request->dispatch();