<?php
// Debug helper: open http://localhost/weblaptop/debug.php
header('Content-Type: text/plain; charset=utf-8');
$out = [];
$out[] = 'PHP SAPI: ' . php_sapi_name();
$out[] = 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? '');
$out[] = 'DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? '');
$out[] = 'SCRIPT_FILENAME: ' . (__FILE__);
$out[] = '__DIR__: ' . __DIR__;
$out[] = 'WEBROOT exists: ' . (is_dir(__DIR__) ? 'yes' : 'no');
$out[] = 'auth/login.php exists: ' . (file_exists(__DIR__ . '/auth/login.php') ? 'yes' : 'no');
$out[] = 'auth/login.php readable: ' . (is_readable(__DIR__ . '/auth/login.php') ? 'yes' : 'no');
$out[] = 'Try open URL: /weblaptop/auth/login.php';
echo implode("\n", $out);
