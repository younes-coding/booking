<?php
// backend/page_products/get_bookings.php
// API: GET — يرجع الحجوزات كـ FullCalendar events (يراعي الكميات)
header('Content-Type: application/json; charset=utf-8');

require_once '../../Config/connexion.php';

$productId = intval($_GET['product_id'] ?? 0);

if (!$productId) {
    echo json_encode([]);
    exit;
}

// 1. جلب المخزون الكلي للمنتج
$stmtStock = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
$stmtStock->execute([$productId]);
$totalStock = (int)$stmtStock->fetchColumn();

// 2. جلب جميع الحجوزات النشطة لهذا المنتج
$stmt = $pdo->prepare("
    SELECT start_date, end_date, quantity
    FROM bookings
    WHERE product_id = ?
      AND status IN ('pending','confirmed')
");
$stmt->execute([$productId]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. حساب الكمية المحجوزة لكل يوم بشكل فردي
$dailyBooked = [];
foreach ($bookings as $b) {
    $start = new DateTime($b['start_date']);
    $end   = new DateTime($b['end_date']);
    
    // حلقة تمر على كل يوم داخل فترة هذا الحجز وتجمع الكميات
    for ($date = clone $start; $date <= $end; $date->modify('+1 day')) {
        $dateString = $date->format('Y-m-d');
        if (!isset($dailyBooked[$dateString])) {
            $dailyBooked[$dateString] = 0;
        }
        $dailyBooked[$dateString] += (int)$b['quantity'];
    }
}

// 4. إرسال الأحداث للتقويم (فقط للأيام التي نفدت فيها الكمية تماماً)
$events = [];
foreach ($dailyBooked as $dateString => $qty) {
    if ($qty >= $totalStock && $totalStock > 0) {
        $events[] = [
            'title'           => 'محجوز بالكامل',
            'start'           => $dateString,
            'color'           => '#c0515a',
            'backgroundColor' => '#c0515a',
            'borderColor'     => '#a03040',
            'display'         => 'background',
        ];
    }
}

echo json_encode($events);