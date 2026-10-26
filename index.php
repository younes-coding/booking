<?php
// index.php
session_start();
require_once("Config/connexion.php");
require_once("Config/auth_check.php");
$sql = "SELECT * FROM products WHERE featured = 1 ";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
  
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Wedding</title>
    <link rel="stylesheet" href="style_home_page.css">
</head>

<body>

    <nav>
        <a href="index.php" class="nav-brand">
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
        <div class="menu-toggle" id="menu-toggle">☰</div>
        <ul class="nav-links" id="nav-links">
            <li><a href="index.php" class="active">الصفحة الرئيسية</a></li>
            <li><a href="backend/page_products/products.php">المنتجات</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="backend/user/my_bookings.php">حجوزاتي</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="backend/admin/admin.php">لوحة التحكم</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="nav-btn" style="color: black , background-color: #C9A84C">خروج (<?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? '')[0]) ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php" class="nav-btn">دخول / تسجيل</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <section class="hero ">
        <div class="hero-bg" ></div>
        <div class="hero-content">
            <h1>اجعل زفاف أحلامك<br>يتحول الى حقيقة</h1>
            <p>تأجير معدات زفاف فاخرة ليومك المثالي</p>
            <div class="hero-btns">
                <a href="backend/page_products/products.php"><button class="btn-secondary">تصفح المنتجات</button></a>
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php"><button class="btn-secondary" style="background:transparent;border:2px solid #c9a84c;color:#c9a84c;margin-right:12px">سجّل مجاناً</button></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-mouse">
                <div class="scroll-dot"></div>
            </div>
        </div>
    </section>



    <section class="how-it-works reveal">
        <h2>كيف يعمل</h2>
        <p class="subtitle">خطوات بسيطة لاستئجار إعداد زفافك المثالي</p>

        <div class="steps-grid">

            <!-- البطاقة 01 -->
            <div class="step-card reveal">
                <div class="step-number">01</div>
                <div class="step-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                        <line x1="12" y1="22.08" x2="12" y2="12" />
                    </svg>
                </div>
                <h3>اختر المنتجات</h3>
                <p>تصفّح كتالوجنا الواسع واختر العناصر التي تحتاجها ليومك الخاص.</p>
            </div>

            <!-- البطاقة 02 -->
            <div class="step-card reveal">
                <div class="step-number">02</div>
                <div class="step-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                </div>
                <h3>اختر التاريخ</h3>
                <p>حدّد تاريخ حفلتك وتحقق من التوفر في الوقت الفعلي.</p>
            </div>

            <!-- البطاقة 03 -->
            <div class="step-card reveal">
                <div class="step-number">03</div>
                <div class="step-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3>احجز</h3>
                <p>أكمل حجزك وسيتولى فريقنا الباقي، بما في ذلك التوصيل والتركيب.</p>
            </div>

        </div>
    </section>
    <section class="featured reveal">
        <h2>المنتجات المميزة</h2>
        <p class="subtitle">أكثر عناصر تأجير الزفاف شعبية لدينا</p>

        <div class="products-grid ">

            <!-- البطاقة 01 -->
            <?php foreach($products as $product){ ?>
                <div class="product-card reveal" data-filter="<?php echo $product['category']; ?>">
                    
                    <div class="product-img">
                        <img src="images/<?php echo basename($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/table.jfif'">
                    <span class="price-badge"><?php echo $product['price']; ?>DA </span>
                </div>
                <div class="product-body">
                    <h3><?php echo $product['name']; ?></h3>
                    <p><?php echo $product['description']; ?></p>
                    <div class="product-footer">
                        <span class="per-event">لكل حفل</span>
                        <a href="backend/page_products/detailproduct.php?id=<?php echo $product['id']; ?>" class="view-link">عرض التفاصيل ←</a>
                    </div>
                </div>
            </div>
            <?php } ?>
            
           
          
        </div> 
         <div class="view-all-wrap">
                <button class="btn-view-all"><a href="backend/page_products/products.php">عرض جميع المنتجات ← </a></button>
            </div>
    </section>
   
    <section class="testimonials reveal">
        <h2>ماذا يقول عملاؤنا</h2>
        <p class="subtitle">آراء حقيقية من أفراح حقيقية</p>

        <div class="reviews-grid">

            <!-- البطاقة 01 -->
            <div class="review-card reveal">
                <div class="quote-icon">❝❝</div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                <p class="review-text">"جعلت خدمة تأجير معداتهم زفافنا مثالياً تماماً! كانت المعدات رائعة والخدمة استثنائية."</p>
                <div class="reviewer-name">سارة ومحمد</div>
                <div class="reviewer-date">يونيو 2025</div>
            </div>

            <!-- البطاقة 02 -->
            <div class="review-card reveal">
                <div class="quote-icon">❝❝</div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                <p class="review-text">"احترافيون وموثوقون ومنتجاتهم خلّابة. حوّلوا قاعتنا إلى قصة خيالية!"</p>
                <div class="reviewer-name">إيمان رودريغيز</div>
                <div class="reviewer-date">أغسطس 2025</div>
            </div>

            <!-- البطاقة 03 -->
            <div class="review-card reveal">
                <div class="quote-icon">❝❝</div>
                <div class="stars">
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                    <span class="star">★</span>
                </div>
                <p class="review-text">"من الحجز حتى الإعداد، كل شيء سار بسلاسة. أنصح بهم بشدة لكل من يخطط لزفافه!"</p>
                <div class="reviewer-name">جيسيكا وداود</div>
                <div class="reviewer-date">سبتمبر 2025</div>
            </div>

        </div>
    </section>


<!-- قسم الدعوة للحجز -->
<section class="cta reveal" >
    <h2>هل أنت مستعد لتخطيط يومك المثالي؟</h2>
    <p>تواصل معنا اليوم ودعنا نحوّل أحلام زفافك إلى حقيقة</p>
    <div class="cta-btns">
        <a href="" class="btn-cta-outline">تواصل معنا</a>
    </div>
</section>

<!-- الفوتر -->
<footer>
    <div class="footer-grid reveal">

        <!-- العمود 1: الشعار والوصف -->
        <div>
            <div class="footer-brand">
                <div class="footer-logo-circle">
                    <svg viewBox="0 0 24 24" fill="#C9A84C">
                        <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>
                    </svg>
                </div>
                <div class="footer-brand-text">
                    <h3>Luxury Wedding</h3>
                    <p>نجعل الأحلام حقيقة</p>
                </div>
            </div>
            <p class="footer-desc">نقدم تجارب زفاف لا تُنسى بمعدات تأجير فاخرة وخدمة استثنائية.</p>
            <div class="footer-socials">
                
                <button class="social-btn">f</button>
                <button class="social-btn">📞</button>
            </div>
        </div>

        <!-- العمود 2: روابط سريعة -->
        <div class="footer-col">
            <h4>روابط سريعة</h4>
            <ul class="footer-links">
                <li><a href="index.php">الصفحة الرئيسية</a></li>
                <li><a href="backend/page_products/products.php">المنتجات</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="backend/user/my_bookings.php">حجوزاتي</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="backend/admin/admin.php">لوحة التحكم</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">تسجيل الخروج</a></li>
                <?php else: ?>
                    <li><a href="login.php">تسجيل الدخول</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- العمود 3: تواصل معنا -->
        <div class="footer-col">
            <h4>تواصل معنا</h4>
            <div class="contact-item">
                <span>📞</span><span>+213 555 123-456</span>
            </div>
            <div class="contact-item">
                <span>✉️</span><span>info@luxurywedding.com</span>
            </div>
            <div class="contact-item">
                <span>📍</span><span>شارع فلاوسن الرمشي-تلمسان</span>
            </div>
        </div>

        <!-- العمود 4: ساعات العمل -->
        <div class="footer-col">
            <h4>ساعات العمل</h4>
            <p class="hours-item">الاثنين - الجمعة: 9:00 ص - 6:00 م</p>
            <p class="hours-item">السبت: 10:00 ص - 4:00 م</p>
            <p class="hours-item">الأحد: بالموعد فقط</p>
        </div>

    </div>
    <div class="footer-bottom">
        <p>© 2025 Luxury Wedding. جميع الحقوق محفوظة.</p>
    </div>
</footer>

<div class="preloader" id="preloader">
    <div class="loader-logo">
        <svg viewBox="0 0 24 24" fill="#C9A84C" width="50" height="50">
            <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/>
        </svg>
    </div>
    <h2 class="loader-text">Luxury Wedding</h2>
</div>


<a href="https://wa.me/213657533005" class="floating-whatsapp" target="_blank" aria-label="تواصل معنا عبر واتساب">
    <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564c.173.087.289.129.332.202.043.073.043.423-.101.827z" />
    </svg>
</a>
    
<script>
// 1. إخفاء شاشة التحميل بلمسة ناعمة
window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    setTimeout(() => {
        preloader.style.opacity = '0';
        preloader.style.visibility = 'hidden';
    }, 1500); // إبقاء الشاشة قليلاً لإظهار الفخامة
});


// 2. تفعيل شريط التنقل الديناميكي (تغيير الحجم والظل عند التمرير)
window.addEventListener('scroll', () => {
    const nav = document.querySelector('nav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// 3. القائمة الجانبية للموبايل
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

// 4. حركات الظهور الاحترافية عند التمرير (Intersection Observer)
const revealElements = document.querySelectorAll(".reveal");
const revealOptions = {
    threshold: 0.15,
    rootMargin: "0px 0px -50px 0px"
};
const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.classList.add("active");
            }, index * 100); // تدرج زمني سريع الظهور
            observer.unobserve(entry.target); // إيقاف المراقبة بعد الظهور لتحسين الأداء
        }
    });
}, revealOptions);

revealElements.forEach(el => observer.observe(el));
</script>
</body>

</html>