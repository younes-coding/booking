<?php  

// إعدادات قاعدة البيانات
$host = 'localhost';         
$db   = 'wedding_system';     
$user = 'root';              
$pass = '';                  
$charset = 'utf8mb4';        

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
