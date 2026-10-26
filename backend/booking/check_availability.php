<?php
// backend/booking/check_availability.php
require_once '../../Config/connexion.php';

header('Content-Type: application/json');

$productId = intval($_GET['product_id'] ?? 0);
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';

if (!$productId || !$startDate || !$endDate) {
    echo json_encode(['error' => 'بيانات ناقصة']);
    exit;
}

// جلب المخزون الكلي
$stmtStock = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
$stmtStock->execute([$productId]);
$totalStock = (int)($stmtStock->fetchColumn() ?? 0);

// جلب مجموع الكميات المحجوزة في هذه الفترة
$stmtBooked = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0) 
    FROM bookings 
    WHERE product_id = ? 
      AND status IN ('pending', 'confirmed') 
      AND start_date <= ? 
      AND end_date >= ?
");
$stmtBooked->execute([$productId, $endDate, $startDate]);
$bookedQty = (int)$stmtBooked->fetchColumn();

$available = max(0, $totalStock - $bookedQty);

echo json_encode([
    'total'     => $totalStock,
    'booked'    => $bookedQty,
    'available' => $available,
]);