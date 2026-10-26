<?php
session_start();
require_once('../../Config/connexion.php');
require_once('../../Config/auth_check.php');
requireAdmin('/booking/login.php');

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: admin.php');
    exit;
}

// 1. جلب مسار الصورة قبل الحذف للاحتفاظ به
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

// 2. حذف المميزات (الابن) أولاً لتجنب أخطاء قاعدة البيانات
$pdo->prepare("DELETE FROM features WHERE product_id = ?")->execute([$id]);

// 3. حذف المنتج (الأب)
$del = $pdo->prepare("DELETE FROM products WHERE id = ?");
$del->execute([$id]);
 
// 4. حذف الصورة من المجلد (Filesystem) إن وجدت
if ($product && !empty($product['image'])) {
    $imgFile = __DIR__ . '/../../images/' . basename($product['image']);
    if (file_exists($imgFile)) {
        @unlink($imgFile);
    }
}

// 5. العودة لصفحة الإدارة
header('Location: admin.php');
exit;