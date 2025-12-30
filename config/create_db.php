<?php
// Run this once (in browser or CLI) to create DB, tables and admin user
// Edit DB credentials below if different
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'weblaptop';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = file_get_contents(__DIR__ . '/../database.sql');
    $pdo->exec($sql);
    echo "Database and tables created successfully.<br>";

    // Insert admin user
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role) VALUES (:u, :p, 'admin')");
    $stmt->execute([':u'=>$username, ':p'=>$password]);
    echo "Admin user created (username: admin, password: admin123). Please change password after first login.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
