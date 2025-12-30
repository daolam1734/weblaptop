<?php
require_once __DIR__ . '/config/database.php';

function getProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProducts() {
    global $pdo;
    return $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
}

function isAdmin() {
    return !empty($_SESSION['admin_logged_in']);
}

/** AUTH HELPERS **/
function findUserByEmailOrUsername($identity) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->execute([$identity, $identity]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function findUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createUser($data) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO users (username,email,password,full_name,phone,role,created_at) VALUES (?,?,?,?,?,? ,NOW())");
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = isset($data['role']) ? $data['role'] : 'user';
    $stmt->execute([$data['username'],$data['email'],$hash,$data['full_name'],$data['phone'],$role]);
    return $pdo->lastInsertId();
}

function setEmailVerificationToken($user_id, $token, $expires_at) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET verification_token = ?, verification_expires = ?, email_verified = 0 WHERE id = ?");
    return $stmt->execute([$token, $expires_at, $user_id]);
}

function sendVerificationEmailSimulated($email, $token) {
    // For local/dev: return the verification URL so dev can view it.
    $link = sprintf('%s/weblaptop/auth/verify_email.php?token=%s', rtrim((isset($_SERVER['HTTP_HOST'])? 'http://'.$_SERVER['HTTP_HOST'] : ''), '/'), urlencode($token));
    // In real setup, use mailer to send. Here we return the link for testing.
    return $link;
}

function createPasswordResetToken($user_id, $token, $expires_at) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $token, $expires_at]);
}

function verifyPasswordResetToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at >= NOW() LIMIT 1");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function markPasswordResetUsed($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

function resetUserPassword($user_id, $new_password) {
    global $pdo;
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$hash, $user_id]);
}

function incrementFailedLogin($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET failed_logins = failed_logins + 1 WHERE id = ?");
    $stmt->execute([$user_id]);
}

function resetFailedLogins($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET failed_logins = 0, locked_until = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
}

function lockAccount($user_id, $minutes = 15) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
    $stmt->execute([$minutes, $user_id]);
}

function isAccountLocked($user) {
    if (!$user) return false;
    if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) return true;
    return false;
}

/** Flash messages (UI) **/
function set_flash($type, $message) {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['flash'])) return [];
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function display_flash() {
    $items = get_flash();
    if (empty($items)) return;
    foreach ($items as $it) {
        $type = $it['type'];
        $msg = $it['message'];
        $cls = 'info';
        if ($type === 'error' || $type === 'danger') $cls = 'danger';
        if ($type === 'success') $cls = 'success';
        if ($type === 'warning') $cls = 'warning';
        echo '<div class="container mt-2">';
        echo '<div class="alert alert-' . $cls . ' flash-alert" role="alert">' . $msg . '</div>';
        echo '</div>';
    }
}

