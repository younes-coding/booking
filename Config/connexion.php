<?php  

// إعدادات قاعدة البيانات التلقائية حسب البيئة (محلي / استضافة InfinityFree)
$isLocalhost = (
    !isset($_SERVER['HTTP_HOST']) ||
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0 ||
    php_sapi_name() === 'cli'
);

if ($isLocalhost) {
    // بيئة التطوير المحلية (Localhost XAMPP)
    $host = 'localhost';         
    $db   = 'wedding_system';     
    $user = 'root';              
    $pass = '';                  
} else {
    // بيئة الاستضافة المباشرة (InfinityFree)
    $host = 'sql306.infinityfree.com';
    $db   = 'if0_41638354_wedding';
    $user = 'if0_41638354';
    $pass = 'younesmh200513';
}

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
