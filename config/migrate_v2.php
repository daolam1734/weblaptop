<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "DEBUG: Script started...\n";
require_once __DIR__ . '/database.php';
echo "DEBUG: Database required...\n";

$queries = [
    "UPDATE orders SET order_status = 'PENDING' WHERE order_status = 'dang_cho'",
    "UPDATE orders SET order_status = 'CONFIRMED' WHERE order_status = 'da_xac_nhan'",
    "UPDATE orders SET order_status = 'PROCESSING' WHERE order_status = 'dang_xu_ly'",
    "UPDATE orders SET order_status = 'SHIPPING' WHERE order_status = 'da_gui'",
    "UPDATE orders SET order_status = 'DELIVERED' WHERE order_status = 'da_giao'",
    "UPDATE orders SET order_status = 'COMPLETED' WHERE order_status = 'hoan_thanh'",
    "UPDATE orders SET order_status = 'CANCELLED' WHERE order_status = 'huy'",
    
    "UPDATE orders SET payment_status = 'UNPAID' WHERE payment_status = 'dang_cho'",
    "UPDATE orders SET payment_status = 'PAID' WHERE payment_status = 'da_thanh_toan'",
    "UPDATE orders SET payment_status = 'FAILED' WHERE payment_status = 'that_bai'",
    "UPDATE orders SET payment_status = 'REFUNDED' WHERE payment_status = 'da_hoan_tien'",

    "ALTER TABLE orders MODIFY COLUMN order_status ENUM('PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPING', 'DELIVERED', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'PENDING'",
    "ALTER TABLE orders MODIFY COLUMN payment_status ENUM('UNPAID', 'PAID', 'FAILED', 'REFUNDED') NOT NULL DEFAULT 'UNPAID'",
    
    "ALTER TABLE orders ADD COLUMN IF NOT EXISTS shipping_status ENUM('NOT_SHIPPED', 'SHIPPING', 'DELIVERED', 'FAILED', 'RETURNED') NOT NULL DEFAULT 'NOT_SHIPPED' AFTER payment_status",
    "ALTER TABLE orders MODIFY COLUMN shipping_status ENUM('NOT_SHIPPED', 'SHIPPING', 'DELIVERED', 'FAILED', 'RETURNED') NOT NULL DEFAULT 'NOT_SHIPPED'",

    "UPDATE shipments SET shipment_status = 'NOT_SHIPPED' WHERE shipment_status = 'dang_cho'",
    "UPDATE shipments SET shipment_status = 'SHIPPING' WHERE shipment_status = 'dang_van_chuyen' OR shipment_status = 'da_gui'",
    "UPDATE shipments SET shipment_status = 'DELIVERED' WHERE shipment_status = 'da_giao'",
    "UPDATE shipments SET shipment_status = 'RETURNED' WHERE shipment_status = 'tra_lai'",
    
    "ALTER TABLE shipments MODIFY COLUMN shipment_status ENUM('NOT_SHIPPED', 'SHIPPING', 'DELIVERED', 'FAILED', 'RETURNED') NOT NULL DEFAULT 'NOT_SHIPPED'"
];

foreach ($queries as $q) {
    try {
        echo "Executing: $q ... ";
        $pdo->exec($q);
        echo "OK\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        // If it's the ADD COLUMN IF NOT EXISTS failing due to old MySQL, we might need a workaround, but MariaDB (XAMPP) usually supports IF NOT EXISTS in some contexts or we can ignore error.
    }
}

echo "Migration script finished.\n";


