<?php
if (session_status() == PHP_SESSION_NONE) session_start();
unset($_SESSION['admin_logged_in']);
header('Location: login.php'); exit;
