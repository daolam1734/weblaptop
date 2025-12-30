<?php
require_once __DIR__ . '/../includes/header.php';
// Clear session and remember cookie
session_unset();
session_destroy();
setcookie('weblaptop_remember', '', time() - 3600, '/', '', false, true);
header('Location: /weblaptop');
exit;