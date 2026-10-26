<?php 
session_start();
require_once("../../Config/connexion.php");
require_once("../../Config/auth_check.php");

$sql = "SELECT * FROM products";
$result = $pdo->prepare($sql);
$result->execute();
$products = $result->fetchAll(PDO::FETCH_ASSOC);


?>





<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>المنتجات - Luxury Wedding</title>
  <link rel="stylesheet" href="page_products.css"/>
</head>
<body>

  <!-- ── NAVBAR ── -->
  <nav class="navbar">
    <a href="../../index.php" class="nav-brand">
      <div class="nav-logo-circle">
        <svg viewBox="0 0 24 24" fill="#C9A84C">
          <path d="M12 21.6C6.4 16.1 1 11.3 1 7.2c0-3.8 3.1-5.2 5.3-5.2 1.3 0 4.2.5 5.7 4.5 1.6-4 4.5-4.5 5.7-4.5C20.2 2 23.3 3.4 23.3 7.2 23.3 11.3 17.6 16.1 12 21.6z"/>
        </svg>
      </div>
      <div class="nav-brand-text">
        <h2>Luxury Wedding</h2>
        <p>نجعل الأحلام حقيقة</p>
      </div>
    </a>
    <div class="menu-toggle" id="menu-toggle">☰</div>
    <ul class="nav-links" id="nav-links">
      <li><a href="../../index.php">الصفحة الرئيسية </a></li>
      <li><a href="" class="active">المنتجات</a></li>
      <?php if (isLoggedIn()): ?>
        <li><a href="../user/my_bookings.php">حجوزاتي</a></li>
        <?php if (isAdmin()): ?>
          <li><a href="../admin/admin.php">لوحة التحكم</a></li>
        <?php endif; ?>
        <button class="nav-book" onclick="location.href='../../logout.php'">خروج (<?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? '')[0]) ?>)</button>
      <?php else: ?>
        <button class="nav-book" onclick="location.href='../../login.php'">دخول / تسجيل</button>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- ── HERO BANNER ── -->
  <div class="hero-banner">
    <h1>تأجير معدات زفافنا</h1>
    <p>تصفح مجموعتنا الكاملة من معدات الزفاف الفاخرة</p>
  </div>

  <!-- ── FILTER BAR ── -->
  <div class="filter-bar">
    <div class="filter-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
      </svg>
      تصفية حسب:
    </div>
   <div class="filter-btns">
      <button class="filter-btn active-filter" onclick="filterProducts('الكل', this)">الكل</button>
      <button class="filter-btn" onclick="filterProducts('الخيام', this)">خيام</button>
      <button class="filter-btn" onclick="filterProducts('الطاولات', this)">طاولات</button>
      <button class="filter-btn" onclick="filterProducts('الكراسي', this)">كراسي</button>
      <button class="filter-btn" onclick="filterProducts('الديكورات', this)">ديكور</button>
      </div>
  </div>

  <!-- ── PRODUCTS SECTION ── -->
  <section class="products-section ">
    <p class="products-count">عرض <span id="count">8</span> منتجات</p>
    <div class="products-grid" id="products-grid">

      <!-- بطاقة 1 -->
       <?php foreach($products as $product) { ?>
      <div class="product-card" data-filter="<?php echo $product['category']; ?>">
        <div class="card-img">
          <img src="../../images/<?php echo basename($product['image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='../../images/table.jfif'">
          <span class="cat-badge"><?php echo $product['category']; ?></span>
          <span class="price-badge"><?php echo $product['price']; ?><span class="per">/حفل</span></span>
        </div>
        <div class="card-body">
          <h3><?php echo $product['name']; ?></h3>
          <p><?php echo $product['description']; ?></p>
          <div class="card-footer">
            <a href="detailproduct.php?id=<?php echo $product['id']; ?>" class="view-link">عرض التفاصيل →</a>
          </div>
        </div>
      </div>
      <?php } ?>

    </div>
  </section>

  <!-- ── CAN'T FIND ── -->
  <div class="cant-find">
    <h2>لم تجد ما تبحث عنه؟</h2>
    <p>تواصل معنا وسنساعدك في إيجاد العناصر المثالية ليومك الخاص</p>
    <button class="btn-contact">تواصل معنا →</button>
  </div>

  <!-- ── FOOTER ── -->
  <footer>
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <div class="footer-logo-circle">
            <svg viewBox="0 0 24 24" fill="#C9A84C"><path d="M12 21.6C6.4 16.1 1 11.3 1 7.2c0-3.8 3.1-5.2 5.3-5.2 1.3 0 4.2.5 5.7 4.5 1.6-4 4.5-4.5 5.7-4.5C20.2 2 23.3 3.4 23.3 7.2 23.3 11.3 17.6 16.1 12 21.6z"/></svg>
          </div>
          <div class="footer-brand-text">
            <h3>Luxury Wedding</h3>
            <p>نجعل الأحلام حقيقة</p>
          </div>
        </div>
        <p class="footer-desc">نقدم تجارب زفاف لا تُنسى بمعدات تأجير فاخرة وخدمة استثنائية.</p>
      </div>
      <div class="footer-col">
        <h4>روابط سريعة</h4>
        <ul class="footer-links">
          <li><a href="../../index.php">الصفحة الرئيسية</a></li>
          <li><a href="products.php">المنتجات</a></li>
          <li><a href="../user/my_bookings.php">حجوزاتي</a></li>
          
        </ul>
      </div>
      <div class="footer-col">
        <h4>تواصل معنا</h4>
        <p>📞 +213 555 123-456</p>
        <p>✉️ info@luxurywedding.com</p>
        <p>📍  شارع فلاوسن الرمشي-تلمسان</p>
      </div>
      <div class="footer-col">
        <h4>ساعات العمل</h4>
        <p>الاثنين - الجمعة: 9:00 ص - 6:00 م</p>
        <p>السبت: 10:00 ص - 4:00 م</p>
        <p>الأحد: بالموعد فقط</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 Luxury Wedding. جميع الحقوق محفوظة.</p>
    </div>
  </footer>

  <script>
    const toggle = document.getElementById("menu-toggle");
const links = document.getElementById("nav-links");
    toggle.addEventListener("click", () => {
    links.classList.toggle("active");
});
document.querySelectorAll(".nav-links a").forEach(link => {
    link.addEventListener("click", () => {
        links.classList.remove("active");
    });
});



    function filterProducts(category, btn) {
      // تحديث الزر النشط
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active-filter'));
      btn.classList.add('active-filter');

      // تصفية البطاقات
      const cards = document.querySelectorAll('.product-card');
      let visible = 0;
      cards.forEach(card => {
        const show = category === 'الكل' || card.dataset.filter === category;
        card.style.display = show ? 'block' : 'none';
        if (show) visible++;
      });
      document.getElementById('count').textContent = visible;
    }
  </script>
</body>
</html>