<?php  

// إعدادات قاعدة البيانات
$host = 'sql306.infinityfree.com';          // عادة localhost
$db   = 'if0_41638354_wedding';     // اسم قاعدة البيانات التي أنشأتها
$user = 'if0_41638354';               // اسم المستخدم (عادة root على XAMPP)
$pass = 'younesmh200513';                   // كلمة المرور (فارغة على XAMPP)
$charset = 'utf8mb4';         // الترميز، مهم لدعم العربية

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // يعطيك رسائل خطأ واضحة
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // يرجع البيانات كمصفوفة associative
    PDO::ATTR_EMULATE_PREPARES   => false,                  // يستخدم prepared statements حقيقية
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // إذا فشل الاتصال، يظهر رسالة خطأ
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
?>
