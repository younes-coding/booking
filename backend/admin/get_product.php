<?php

require_once("../../Config/connexion.php");

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // 1. جلب بيانات المنتج الأساسية (أضفنا عمود featured هنا)
        $sql = "SELECT id, name, category, price, quantity, description, image, featured FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // 2. جلب مميزات المنتج المرتبطة به
            $sqlFeatures = "SELECT feature_text FROM features WHERE product_id = ?";
            $stmtFeatures = $pdo->prepare($sqlFeatures);
            $stmtFeatures->execute([$id]);
            
            // FETCH_COLUMN يجلب البيانات كمصفوفة بسيطة [ "ميزة 1", "ميزة 2" ]
            $features = $stmtFeatures->fetchAll(PDO::FETCH_COLUMN);
            
            // دمج المميزات داخل مصفوفة المنتج
            $product['features'] = $features;
            
            // إرسال البيانات النهائية
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'المنتج غير موجود']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'معرّف المنتج غير محدد']);
}