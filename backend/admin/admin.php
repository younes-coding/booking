<?php
session_start();
require_once('../../Config/connexion.php');
require_once('../../Config/auth_check.php');
requireAdmin('../../login.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['name'], $_POST['category'], $_POST['price'], $_POST['stock'])) {

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : null;
            $name = trim($_POST['name']);
            $category = trim($_POST['category']);
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $description = $_POST['description'] ?? '';
            $featured = isset($_POST['featured']) ? 1 : 0;

            // ========================
            // تحقق
            // ========================
            if (empty($name) || empty($category) || $price < 0 || $stock < 0) {
                die("بيانات غير صحيحة");
            }

            // ========================
            // INSERT أو UPDATE
            // ========================
            if ($id) {
                // تحديث المنتج - جلب البيانات القديمة
                $sqlSelect = "SELECT image FROM products WHERE id = ?";
                $stmtSelect = $pdo->prepare($sqlSelect);
                $stmtSelect->execute([$id]);
                $oldProduct = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                $imagePath = $oldProduct['image']; // الاحتفاظ بالصورة القديمة

                // رفع الصورة الجديدة إذا وجدت
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $uploadDir = "../../images/";
                    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
                    $targetFile = $uploadDir . $fileName;

                    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
                    $imagePath = $targetFile;
                }

                // UPDATE
                $sql = "UPDATE products SET
                        name = ?,
                        description = ?,
                        price = ?,
                        image = ?,
                        category = ?,
                        quantity = ?,
                        created_at = NOW(),
                        featured = ?
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);

                $success = $stmt->execute([
                    $name,
                    $description,
                    $price,
                    $imagePath,
                    $category,
                    $stock,
                    $featured,
                    $id
                ]);

                // بعد نجاح تحديث المنتج:
                if ($success) {
                    $pdo->prepare("DELETE FROM features WHERE product_id = ?")->execute([$id]);
                    
                    if (!empty($_POST['features'])) {
                        $stmtFeature = $pdo->prepare("INSERT INTO features (product_id, feature_text) VALUES (?, ?)");
                        foreach ($_POST['features'] as $feature) {
                            $feature = trim($feature);
                            if (!empty($feature)) {
                                $stmtFeature->execute([$id, $feature]);
                            }
                        }
                    }
                    // إعادة التوجيه بعد التحديث بنجاح
                    header("Location: admin.php");
                    exit();
                } else {
                    echo "فشل في التحديث";
                }

            } else {
                // إضافة منتج جديد
                $imagePath = "";

                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $uploadDir = "../../images/";
                    $fileName = time() . "_" . basename($_FILES["image"]["name"]);
                    $targetFile = $uploadDir . $fileName;

                    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
                    $imagePath = $targetFile;
                }

                $sql = "INSERT INTO products 
                        (name, description, price, image, category, quantity, created_at, featured) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";

                $stmt = $pdo->prepare($sql);

                $success = $stmt->execute([
                    $name,
                    $description,
                    $price,
                    $imagePath,
                    $category,
                    $stock,
                    $featured
                ]);

                if ($success) {
                    $productId = $pdo->lastInsertId(); // ✅ مهم

                    if (!empty($_POST['features'])) {
                        $stmtFeature = $pdo->prepare("INSERT INTO features (product_id, feature_text) VALUES (?, ?)");

                        foreach ($_POST['features'] as $feature) {
                            $feature = trim($feature);
                            if (!empty($feature)) {
                                $stmtFeature->execute([$productId, $feature]);
                            }
                        }
                    }

                    header("Location: admin.php");
                    exit();
                } else {
                    echo "فشل في الإضافة";
                }
            }
        } catch (Exception $e) {
            echo "خطأ: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة الحفلات</title>
    <link rel="stylesheet" href="page_admin.css?v=<?php echo time(); ?>">
</head>

<body>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <span class="brand-dot"></span>
                <h2>لوحة المدير</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="admin.php" class="sidebar-link active">
                    <span class="sidebar-icon">📦</span>
                    <span>المنتوجات</span>
                </a>
                <a href="bookings.php" class="sidebar-link">
                    <span class="sidebar-icon">📅</span>
                    <span>الحجوزات</span>
                </a>
            </nav>

            <a href="../../logout.php" class="sidebar-logout">
                <span class="sidebar-icon">⎋</span>
                <span>تسجيل الخروج</span>
            </a>
        </aside>

        <div class="sidebar-backdrop"></div>

        <div class="container">
            <div class="dashboard-header">
                <button class="sidebar-toggle" type="button" aria-label="فتح القائمة">☰</button>
                <h2 class="dashboard-title">لوحة التحكم - إدارة المنتجات</h2>
            </div>

            <div class="inventory-header">
                <h3 class="inventory-title">المخزون</h3>
                <button class="btn-add">+ إضافة منتج</button>
            </div>

            <div class="table-wrapper">
                <table class="admin-products-table">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الفئة</th>
                            <th>السعر</th>
                            <th>المخزون</th>
                            <th>مميز</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) { ?>
                            <tr>
                                <td data-label="المنتج">
                                    <div class="product-cell">
                                        <img src="../../images/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img" onerror="this.src='../../images/table.jfif'">
                                        <span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span>
                                    </div>
                                </td>
                                <td data-label="الفئة"><?php echo htmlspecialchars($product['category']); ?></td>
                                <td data-label="السعر" class="price-cell">$<?php echo htmlspecialchars($product['price']); ?></td>
                                <td data-label="المخزون" class="stock-cell"><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td data-label="مميز"><?php if ($product['featured']) { ?>
                                        <span style="color:green;">✔ مميز</span>
                                    <?php } else { ?>
                                        <span style="color:gray;">عادي</span>
                                    <?php } ?>
                                </td>
                                <td data-label="الإجراءات">
                                    <div class="action-buttons">
                                        <a class="btn-edit" title="تعديل" href="admin.php?edit=<?= $product['id'] ?>">✏️</a>
                                        <a href="delete_products.php?id=<?= $product['id'] ?>" class="btn-delete" title="حذف">🗑️</a>
                                    </div>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="addProductModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">إضافة منتج جديد</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form class="modal-body" id="productForm" onsubmit="handleAddProduct(event)" enctype="multipart/form-data" method="post">

                <div class="product-name-row">
                    <div class="form-group">
                        <label>اسم المنتج *</label>
                        <input type="text" id="productName" placeholder="أدخل اسم المنتج" name="name" required>
                    </div>
                    <div class="featured-checkbox">
                        <input type="checkbox" name="featured" value="1" id="featuredCheck">
                        <label for="featuredCheck">مميز ⭐</label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>الفئة *</label>
                        <select id="productCategory" name="category" required>
                            <option value="">اختر فئة</option>
                            <option value="الخيام">الخيام</option>
                            <option value="الكراسي">الكراسي</option>
                            <option value="الطاولات">الطاولات</option>
                            <option value="الديكورات">الديكورات</option>
                            <option value="الإضاءة">الإضاءة</option>
                            <option value="الأثاث">الأثاث</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>السعر / اليوم ($) *</label>
                        <input type="number" id="pricePerDay" placeholder="0" min="0" step="0.01" name="price" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>المخزون الكلي *</label>
                    <input type="number" id="totalStock" name="stock" placeholder="0" min="0" required>
                </div>

                <div class="form-group">
                    <label>الوصف</label>
                    <textarea id="productDescription" placeholder="أضف وصفاً للمنتج..." name="description"></textarea>
                </div>

                <div class="form-group">
                    <label>المميزات (اختياري)</label>
                    <div id="featuresContainer">
                        <input type="text" name="features[]" class="feature-input" placeholder="ميزة 1">
                        <input type="text" name="features[]" class="feature-input" placeholder="ميزة 2">
                        <input type="text" name="features[]" class="feature-input" placeholder="ميزة 3">
                        <input type="text" name="features[]" class="feature-input" placeholder="ميزة 4">
                    </div>
                </div>

                <div class="form-group">
                    <label>صورة المنتج</label>
                    <input type="file" id="imageUrl" name="image" accept="image/*">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">إلغاء</button>
                    <button type="submit" class="btn-submit">إضافة المنتج</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    
</body>

</html>