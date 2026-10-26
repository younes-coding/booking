<?php
// backend/page_products/check_date.php
// API: POST — يتحقق إذا الفترة المحددة متاحة لمنتج معين
header('Content-Type: application/json; charset=utf-8');

require_once '../../Config/connexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$productId  = intval($_POST['product_id']  ?? 0);
$startDate  = trim($_POST['start_date']    ?? '');
$endDate    = trim($_POST['end_date']      ?? '');

if (!$productId || !$startDate || !$endDate) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات ناقصة']);
    exit;
}

// ⭐ تصحيح هام: مكتبة FullCalendar ترسل تاريخ النهاية بزيادة يوم (Exclusive)
// لذلك يجب أن ننقص يوماً واحداً ليكون الحساب دقيقاً مع قاعدة البيانات (Inclusive)
$realEndDate = date('Y-m-d', strtotime($endDate . ' -1 day'));

// 1. جلب المخزون الكلي للمنتج
$stmtStock = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
$stmtStock->execute([$productId]);
$totalStock = (int)$stmtStock->fetchColumn();

// 2. التصحيح الثاني: إذا كان المخزون 0 من الأساس نرفض الحجز فوراً
if ($totalStock <= 0) {
    echo json_encode(['status' => 'taken', 'message' => 'هذا المنتج نفد من المخزون تماماً']);
    exit;
}

// 3. جلب مجموع الكميات المحجوزة في هذه الفترة المحددة
$stmtBooked = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0) 
    FROM bookings 
    WHERE product_id = ? 
      AND status IN ('pending', 'confirmed') 
      AND start_date <= ? 
      AND end_date >= ?
");
// نمرر التاريخ المصحح هنا بدلاً من endDate القديم
$stmtBooked->execute([$productId, $realEndDate, $startDate]);
$bookedQty = (int)$stmtBooked->fetchColumn();

// 4. التحقق مما إذا كانت الكمية المحجوزة قد استنفدت المخزون
if ($bookedQty >= $totalStock) {
    echo json_encode(['status' => 'taken', 'message' => 'هذه الفترة محجوزة بالكامل']);
} else {
    echo json_encode(['status' => 'available', 'message' => 'الفترة متاحة']);
}