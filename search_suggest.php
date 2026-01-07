<?php
require_once __DIR__ . '/functions.php';
header('Content-Type: application/json; charset=utf-8');
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.sku, p.price, b.name AS brand
        FROM products p
        LEFT JOIN brands b ON b.id = p.brand_id
        WHERE p.is_active = 1 AND (p.name LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 10");
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fix image URLs
    foreach ($results as &$r) {
        $r['image'] = getProductImage($r['id']);
    }
}
echo json_encode($results, JSON_UNESCAPED_UNICODE);
