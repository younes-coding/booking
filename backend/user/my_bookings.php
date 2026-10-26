<?php
// backend/user/my_bookings.php
session_start();
require_once '../../Config/connexion.php';
require_once '../../Config/auth_check.php';
requireLogin('/login.php');

// جلب حجوزات المستخدم الحالي
$stmt = $pdo->prepare("
    SELECT b.id, b.product_id, b.customer_name, b.phone, b.event_date,
           b.start_date, b.end_date, b.quantity, b.total_amount, b.status,
           b.notes, b.created_at,
           p.name AS product_name, p.image AS product_image, p.category
    FROM bookings b
    LEFT JOIN products p ON p.id = b.product_id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات
$total     = count($bookings);
$confirmed = count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed'));
$pending   = count(array_filter($bookings, fn($b) => $b['status'] === 'pending'));
$cancelled = count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>حجوزاتي — Luxury Wedding</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="bookings.css">
</head>
<body>
<div class="bg"></div>
<div class="wrap">

<nav>
    <a href="../../index.php" class="brand">
        <div class="brand-gem">💍</div>
        <span class="brand-name">Luxury Wedding</span>
    </a>
    
    <div class="menu-toggle" id="menu-toggle">☰</div>

    <div class="nav-actions" id="nav-links">
        <span style="font-size:13px;color:var(--text-muted)">مرحباً، <strong style="color:var(--gold)"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong></span>
        <a href="../../index.php" class="btn-nav">الصفحة الرئيسية</a>
        <a href="../page_products/products.php" class="btn-nav">المنتجات</a>
        <a href="../../logout.php" class="btn-nav">خروج (<?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? '')[0]) ?>)</a>
    </div>
</nav>
<main>
    <div class="page-header">
        <h1>📋 حجوزاتي</h1>
        <p>تتبع جميع حجوزاتك ومواعيد مناسباتك</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card stat-gold">
            <div class="stat-num"><?= $total ?></div>
            <div class="stat-label">إجمالي الحجوزات</div>
        </div>
        <div class="stat-card stat-green">
            <div class="stat-num"><?= $confirmed ?></div>
            <div class="stat-label">المؤكدة</div>
        </div>
        <div class="stat-card stat-amber">
            <div class="stat-num"><?= $pending ?></div>
            <div class="stat-label">قيد الانتظار</div>
        </div>
        <div class="stat-card stat-red">
            <div class="stat-num"><?= $cancelled ?></div>
            <div class="stat-label">الملغاة</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
        <button class="f-btn active" onclick="filterBookings('all', this)">الكل (<?= $total ?>)</button>
        <button class="f-btn" onclick="filterBookings('confirmed', this)">مؤكدة (<?= $confirmed ?>)</button>
        <button class="f-btn" onclick="filterBookings('pending', this)">انتظار (<?= $pending ?>)</button>
        <button class="f-btn" onclick="filterBookings('cancelled', this)">ملغاة (<?= $cancelled ?>)</button>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <div class="empty-icon">🛍️</div>
            <h2>لا توجد حجوزات بعد</h2>
            <p>تصفح منتجاتنا الفاخرة وابدأ بحجز معدات زفافك المثالي</p>
            <a href="../page_products/products.php" class="btn-primary">🌟 تصفح المنتجات</a>
        </div>
    <?php else: ?>
        <div class="bookings-grid" id="bookingsGrid">
            <?php foreach ($bookings as $i => $b): ?>
            <div class="booking-card" data-status="<?= $b['status'] ?>" style="animation-delay:<?= $i * .08 ?>s">
                <!-- Thumbnail -->
                <?php if (!empty($b['product_image'])): ?>
                    <img src="../../images/<?= htmlspecialchars(basename($b['product_image'])) ?>"
                         class="product-thumb"
                         alt="<?= htmlspecialchars($b['product_name'] ?? '') ?>"
                         onerror="this.outerHTML='<div class=\'product-thumb-placeholder\'>✨</div>'">
                <?php else: ?>
                    <div class="product-thumb-placeholder">✨</div>
                <?php endif; ?>

                <!-- Info -->
                <div class="booking-info">
                    <div class="booking-product"><?= htmlspecialchars($b['product_name'] ?? '—') ?></div>
                    <div class="booking-meta">
                        <span>📅 <?= htmlspecialchars($b['event_date']) ?></span>
                        <span>📆 <?= htmlspecialchars($b['start_date']) ?> ← <?= htmlspecialchars($b['end_date']) ?></span>
                        <span>📦 الكمية: <?= intval($b['quantity']) ?></span>
                        <span>🏷️ <?= htmlspecialchars($b['category'] ?? '') ?></span>
                    </div>
                    <div class="booking-amount"><?= number_format((float)$b['total_amount'], 2) ?> DA</div>
                    <div class="booking-ref">رقم الحجز: #<?= str_pad($b['id'], 6, '0', STR_PAD_LEFT) ?> — <?= date('d/m/Y', strtotime($b['created_at'])) ?></div>
                </div>

                <!-- Status -->
                <div class="booking-status-side">
                    <?php if ($b['status'] === 'confirmed'): ?>
                        <span class="status-badge status-confirmed">✔ مؤكد</span>
                    <?php elseif ($b['status'] === 'pending'): ?>
                        <span class="status-badge status-pending">⏳ انتظار</span>
                    <?php else: ?>
                        <span class="status-badge status-cancelled">✖ ملغى</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer>
    <p>© 2025 Luxury Wedding. جميع الحقوق محفوظة.</p>
</footer>

</div>

<script>

const toggle = document.getElementById("menu-toggle");
const links = document.getElementById("nav-links");

if (toggle && links) {
    toggle.addEventListener("click", () => {
        links.classList.toggle("active");
    });
}



function filterBookings(status, btn) {
    document.querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.booking-card').forEach(card => {
        const show = status === 'all' || card.dataset.status === status;
        card.style.display = show ? 'grid' : 'none';
    });
}
</script>
</body>
</html>
