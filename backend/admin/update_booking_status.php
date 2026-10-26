<?php
// backend/admin/update_booking_status.php
// API: POST — يغير حالة الحجز (admin only)
session_start();
require_once '../../Config/connexion.php';
require_once '../../Config/auth_check.php';

requireAdmin('../../login.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id     = intval($_POST['id']     ?? 0);
$status = trim($_POST['status']   ?? '');

$allowed = ['pending', 'confirmed', 'cancelled'];

if (!$id || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

$stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'لم يتم العثور على الحجز']);
}
