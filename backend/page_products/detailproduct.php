<?php
session_start();
require_once("../../Config/connexion.php");
require_once("../../Config/auth_check.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("معرف المنتج غير صالح");
}
$productId = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
$stmtFeatures = $pdo->prepare("SELECT feature_text FROM features WHERE product_id = ?");
$stmtFeatures->execute([$productId]);
$features = $stmtFeatures->fetchAll(PDO::FETCH_ASSOC);
if (!$product) {
    die("المنتج غير موجود");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المنتج - Luxury Wedding</title>
    <link rel="stylesheet" href="detail.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="../../index.php" class="nav-brand">
                <div class="nav-logo-circle">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z" />
                    </svg>
                </div>
                <div class="nav-brand-text">
                    <h2>Luxury Wedding</h2>
                    <p>نجعل الأحلام حقيقة</p>
                </div>
            </a>
            <div class="breadcrumb-container">
                <a href="products.php" class="breadcrumb-link">← العودة إلى المنتجات</a>
            </div>
        </div>
    </header>


    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Product Section -->

            <section class="product-section" itemscope itemtype="https://schema.org/Product">
                <?php if ($product): ?>
                    <div class="product-grid">
                        <!-- Product Image -->

                        <div class="product-image-container">

                            <img
                                src="../../images/<?php echo basename($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="product-image"
                                itemprop="image"
                                onerror="this.src='../../images/table.jfif'">
                        </div>

                        <!-- Product Details -->
                        <div class="product-details">
                            <!-- Category Badge -->
                            <span class="category-badge"><?php echo $product['category']; ?></span>

                            <!-- Title -->
                            <h2 class="product-title" itemprop="name"><?php echo $product['name']; ?></h2>

                            <!-- Price -->
                            <div class="price-section">
                                <span class="currency">DA</span>
                                <span class="price" itemprop="price" id="productPrice"><?php echo $product['price']; ?></span>
                                <span class="price-unit">/الحفل</span>
                            </div>

                            <!-- Description -->
                            <p class="product-description" itemprop="description">
                                <?php echo $product['description']; ?>
                            </p>

                            <!-- Features -->
                            <div class="features-section">
                                <h3 class="section-title">المميزات</h3>
                                <ul class="features-list">
                                    <?php if (!empty($features)): ?>
                                        <ul class="features-list">
                                            <?php foreach ($features as $feature): ?>
                                                <li class="feature-item">
                                                    <span class="feature-icon">✓</span>
                                                    <span><?= htmlspecialchars($feature['feature_text']) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>لا توجد مميزات متاحة لهذا المنتج</p>
                                    <?php endif; ?>
                                </ul>
                            </div>

                        </div>

                    </div>
                <?php endif; ?>
            </section>

            <!-- Booking Section -->
            <section class="booking-section">
                <div class="booking-container">
                    <h2 class="booking-title">📅 التواريخ المحجوزة</h2>

                    <form id="bookingForm" class="booking-form" method="POST" action="/api/book-product.php">
                        <!-- Hidden Fields -->
                        <div id="calendar"></div>

                        <p id="dateStatus"></p>

                        <input type="hidden" id="product_id" value="<?= $productId ?>">
                        <input type="hidden" id="start_date" name="start_date">
                        <input type="hidden" id="end_date" name="end_date">

                        <!-- Date Selection -->
                        

                        <!-- Submit Button -->
                        <?php if (isLoggedIn()): ?>
                        <a
                            href="../booking/booking.php?product_id=<?= $productId ?>"
                            class="btn-reserve"
                            id="reserveBtn">
                            🛒 احجز الآن
                        </a>
                        <?php else: ?>
                        <a
                            href="../../login.php?redirect=<?= urlencode('../../login.php?product_id=' . $productId) ?>"
                            class="btn-reserve"
                            style="background:#c9a84c">
                            🔐 سجّل دخولك للحجز
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-column">
                    <a href="../../index.php" class="nav-brand">
                <div class="nav-logo-circle">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z" />
                    </svg>
                </div>
                <div class="nav-brand-text">
                    <h2>Luxury Wedding</h2>
                    <p>نجعل الأحلام حقيقة</p>
                </div>
            </a>
                    <p class="footer-description">
                        نحن نخلق تجارب زفاف لا تنسى مع أفضل تجهيزات الإيجار وخدمة استثنائية.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-title">روابط سريعة</h4>
                    <ul class="footer-links">
                        <li><a href="../../index.php" class="footer-link">الرئيسية</a></li>
                        <li><a href="products.php" class="footer-link">المنتجات</a></li>
                        <li><a href="../../login.php" class="footer-link">احجز الآن</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-column">
                    <h4 class="footer-title">تواصل معنا</h4>
                    <ul class="contact-list">
                        <li>
                            <span class="contact-icon">📞</span>
                            <a href="tel:+213555123456">+213 555 123 456</a>
                        </li>
                        <li>
                            <span class="contact-icon">✉️</span>
                            <a href="mailto:hello@ahlam.com">hello@ahlam.com</a>
                        </li>
                        <li>
                            <span class="contact-icon">📍</span>
                            <span>تلمسان، الجزائر</span>
                        </li>
                    </ul>
                </div>

                <!-- Business Hours -->
                <div class="footer-column">
                    <h4 class="footer-title">ساعات العمل</h4>
                    <ul class="hours-list">
                        <li>الاثنين - الجمعة: 9:00 صباحاً - 6:00 مساءً</li>
                        <li>السبت: 10:00 صباحاً - 4:00 مساءً</li>
                        <li>الأحد: بموعد مسبق</li>
                    </ul>
                </div>
            </div>

            <!-- Social Links -->
            <div class="footer-social">
                
                <a href="" class="social-link" aria-label="فيسبوك">f</a>
                <a href="" class="social-link" aria-label="واتساب">📞</a>
            </div>

            <!-- Copyright -->
            <div class="footer-copyright">
                <p>&copy; 2025 أحلام اليوم. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <script>
       






        document.addEventListener('DOMContentLoaded', function() {

            const calendarEl = document.getElementById('calendar');
            const productId = document.getElementById('product_id').value;
            const dateStatus = document.getElementById('dateStatus');

            let selectedStart = null;
            let selectedEnd = null;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',

                selectable: true,

                events: `get_bookings.php?product_id=${productId}`,

                select: function(info) {

                    selectedStart = info.startStr;
                    selectedEnd = info.endStr;

                    // تحقق من التداخل
                    fetch('check_date.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `product_id=${productId}&start_date=${selectedStart}&end_date=${selectedEnd}`
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.status === 'taken') {
                                dateStatus.textContent = "❌ هذه الفترة محجوزة";
                                dateStatus.style.color = "red";
                                calendar.unselect();
                            } else {
                                dateStatus.textContent = "✅ الفترة متاحة";
                                dateStatus.style.color = "green";

                                document.getElementById('start_date').value = selectedStart;
                                document.getElementById('end_date').value = selectedEnd;
                            }

                        });

                }

            });

            calendar.render();
        });
    </script>
</body>

</html>