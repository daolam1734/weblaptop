<?php
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json; charset=utf-8');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.sku, p.price, pi.url AS image, b.name AS brand
        FROM products p
        LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.position = 0
        LEFT JOIN brands b ON b.id = p.brand_id
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 10");
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($results, JSON_UNESCAPED_UNICODE);
