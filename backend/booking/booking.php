<?php
// backend/booking/booking.php
session_start();
require_once '../../Config/connexion.php';
require_once '../../Config/auth_check.php';
require_once '../../Config/mailer.php';

// التحقق من تسجيل الدخول — إذا لم يكن مسجلاً أعده للـ login
if (!isLoggedIn()) {
    $currentUrl = urlencode($_SERVER['REQUEST_URI']);
    header('Location: ../../login.php?redirect=' . $currentUrl);
    exit;
}

// جلب معرف المنتج
$productId = intval($_GET['product_id'] ?? 0);
$product   = null;
$features  = [];

if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        $stmtF = $pdo->prepare("SELECT feature_text FROM features WHERE product_id = ?");
        $stmtF->execute([$productId]);
        $features = $stmtF->fetchAll(PDO::FETCH_COLUMN);
    }
}

// ============================================================
// POST: حفظ الحجز
// ============================================================
$bookingSuccess = false;
$bookingError   = '';
$newBookingId   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $pid        = intval($_POST['product_id']    ?? 0);
    $fullName   = trim($_POST['full_name']       ?? '');
    $phone      = trim($_POST['phone']           ?? '');
    $address    = trim($_POST['address']         ?? '');
    $eventDate  = trim($_POST['event_date']      ?? '');
    $startDate  = trim($_POST['start_date']      ?? '');
    $endDate    = trim($_POST['end_date']        ?? '');
    $qty        = max(1, intval($_POST['quantity'] ?? 1));
    $notes      = trim($_POST['notes']           ?? '');

    // Validation
    if (!$pid || !$fullName || !$phone || !$startDate || !$endDate || !$eventDate) {
        $bookingError = 'يرجى ملء جميع الحقول المطلوبة.';
    } elseif ($endDate < $startDate) {
        $bookingError = 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.';
    } else {
        // 1. جلب المخزون الكلي وسعر الوحدة
        $stmtStock = $pdo->prepare("SELECT quantity, price FROM products WHERE id = ?");
        $stmtStock->execute([$pid]);
        $prodData   = $stmtStock->fetch();
        $totalStock = (int)($prodData['quantity'] ?? 0);
        $unitPrice  = (float)($prodData['price']    ?? 0);

        // 2. الكميات المحجوزة في نفس الفترة
        $stmtBooked = $pdo->prepare("
        SELECT COALESCE(SUM(quantity), 0) 
        FROM bookings 
        WHERE product_id = ? 
          AND status IN ('pending', 'confirmed') 
          AND start_date <= ? 
          AND end_date >= ?
    ");
        $stmtBooked->execute([$pid, $endDate, $startDate]);
        $bookedQty  = (int)$stmtBooked->fetchColumn();
        $available  = $totalStock - $bookedQty;

        // 3. التحقق من الكمية
        if ($qty > $available) {
            $bookingError = $available <= 0
                ? 'عذراً، هذا المنتج محجوز بالكامل في هذه الفترة.'
                : "الكمية المطلوبة غير متوفرة. المتاح حالياً في هذه الفترة: {$available}";
        } else {
            // 4. حساب الإجمالي
            $sDate = new DateTime($startDate);
            $eDate = new DateTime($endDate);
            $days  = max(1, (int)$sDate->diff($eDate)->days);
            $total = $unitPrice * $qty * $days;

            // 5. إدراج الحجز
            $ins = $pdo->prepare("
            INSERT INTO bookings 
                (user_id, product_id, customer_name, phone, event_date, 
                 start_date, end_date, quantity, total_amount, address, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
            $ins->execute([
                $_SESSION['user_id'],
                $pid,
                $fullName,
                $phone,
                $eventDate,
                $startDate,
                $endDate,
                $qty,
                $total,
                $address,
                $notes
            ]);

            $newBookingId   = $pdo->lastInsertId();
            $bookingSuccess = true;
            
              $stmtName = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $stmtName->execute([$pid]);
            $productName = $stmtName->fetchColumn();

            sendBookingNotificationToAdmin([
                'booking_id'    => $newBookingId,
                'customer_name' => $fullName,
                'phone'         => $phone,
                'address'       => $address,
                'product_name'  => $productName,
                'event_date'    => $eventDate,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'quantity'      => $qty,
                'total_amount'  => $total,
                'notes'         => $notes,
            ]);
  
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أكمل حجزك — Luxury Wedding</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="booking.css">
</head>

<body>
    <div class="bg-canvas">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="site-wrap">

        <!-- NAVBAR -->
        <nav class="navbar">

            <a class="brand" href="../../index.php">
                <div class="brand-gem">💍</div>
                <div>
                    <span class="brand-name">Luxury Wedding</span>
                    <span class="brand-sub">LUXURY EVENTS</span>
                </div>
            </a>
            <div class="menu-toggle" id="menu-toggle">☰</div>
            <div class="nav-user" id="nav-user">
                <span>مرحباً، <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong></span>
                <a href="../user/my_bookings.php" class="btn-nav-outline">حجوزاتي</a>
                <a href="../../logout.php" class="btn-nav-outline">خروج</a>
            </div>
        </nav>

        <main>
           <a href="../page_products/detailproduct.php?id=<?= $productId ?>" class="back-link">
    ← العودة إلى المنتج
</a>

            <?php if ($bookingSuccess): ?>
                <!-- ====== SUCCESS STATE ====== -->
                <div class="card">
                    <div class="success-state">
                        <div class="success-icon">🎉</div>
                        <h2>تم تقديم حجزك بنجاح!</h2>
                        <p>سيتواصل معك فريقنا قريباً لتأكيد الحجز.</p>
                        <p>رقم الحجز الخاص بك:</p>
                        <div class="booking-ref">#<?= str_pad($newBookingId, 6, '0', STR_PAD_LEFT) ?></div>
                        <p style="color:var(--text-muted);font-size:13px;">يمكنك متابعة حالة حجزك من صفحة حجوزاتي</p>
                        <div class="success-actions">
                            <a href="../user/my_bookings.php" class="btn-action btn-primary">📋 عرض حجوزاتي</a>
                            <a href="../page_products/products.php" class="btn-action btn-outline">🛍️ تصفح المنتجات</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>

                <!-- HERO -->
                <div class="page-hero">
                    <div class="hero-ornament">تأكيد الحجز</div>
                    <h1>أكمل حجزك الفاخر</h1>
                    <p>خطوات بسيطة لتأمين معدات حفلك الاستثنائي</p>
                </div>

                <!-- PROGRESS -->
                <div class="progress-steps">
                    <div class="step done">
                        <div class="step-circle">✓</div>
                        <span class="step-label">اختيار المنتج</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step active">
                        <div class="step-circle">2</div>
                        <span class="step-label">بيانات الحجز</span>
                    </div>
                    <div class="step-line pending"></div>
                    <div class="step pending">
                        <div class="step-circle">3</div>
                        <span class="step-label">التأكيد</span>
                    </div>
                </div>

                <!-- Error Alert -->
                <?php if ($bookingError): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($bookingError) ?></div>
                <?php endif; ?>

                <!-- PRODUCT INFO -->
                <?php if ($product): ?>
                    <div class="card">
                        <h2 class="card-title">
                            <div class="card-icon">🎁</div> المنتج المختار
                        </h2>
                        <div class="product-box">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../../images/<?= htmlspecialchars(basename($product['image'])) ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>"
                                    class="product-thumb"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="product-thumb-placeholder" style="display:none">✨</div>
                            <?php else: ?>
                                <div class="product-thumb-placeholder">✨</div>
                            <?php endif; ?>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                <div class="product-meta">
                                    <span>🏷️ <?= htmlspecialchars($product['category']) ?></span>
                                    <span>📦 المخزون: <?= intval($product['quantity']) ?></span>
                                </div>
                                <div class="product-price"><?= number_format((float)$product['price'], 2) ?> DA <small style="font-size:14px;color:var(--text-muted)">/اليوم</small></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- BOOKING FORM -->
                <form class="card" method="POST" action="booking.php?product_id=<?= $productId ?>">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">

                    <h2 class="card-title">
                        <div class="card-icon">👤</div> معلومات الاتصال
                    </h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <div class="label-icon">👤</div> الاسم الكامل <span class="req">*</span>
                            </label>
                            <input type="text" name="full_name" class="form-input"
                                placeholder="أدخل اسمك الكامل"
                                value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <div class="label-icon">📞</div> رقم الهاتف <span class="req">*</span>
                            </label>
                            <input type="tel" name="phone" class="form-input" placeholder="+213 5XX XXX XXX" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <div class="label-icon">📍</div> موقع الحفل / العنوان <span class="req">*</span>
                        </label>
                        <input type="text" name="address" class="form-input" placeholder="أدخل عنوان مكان الحفل" required>
                    </div>

                    <div class="divider">
                        <div class="divider-gem"></div>
                    </div>
                    <h3 style="font-family:'Playfair Display',serif; color:var(--text-main); margin-bottom:20px; display:flex;align-items:center;gap:12px;">
                        <div class="card-icon">📅</div> تواريخ الحفل
                    </h3>

                    <div class="form-group">
                        <label class="form-label">
                            <div class="label-icon">📅</div> تاريخ المناسبة <span class="req">*</span>
                        </label>
                        <input type="date" name="event_date" class="form-input" required min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <div class="label-icon">▶</div> تاريخ البداية (التوصيل) <span class="req">*</span>
                            </label>
                            <input type="date" id="startDate" name="start_date" class="form-input" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <div class="label-icon">⏹</div> تاريخ النهاية (الاسترداد) <span class="req">*</span>
                            </label>
                            <input type="date" id="endDate" name="end_date" class="form-input" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <div class="label-icon">📦</div> الكمية المطلوبة
                        </label>
                        <div class="qty-control">
                            <button type="button" class="qty-btn" id="decreaseQty">−</button>
                            <input type="number" id="qtyInput" name="quantity" class="qty-input" value="1" min="1" max="<?= intval($product['quantity'] ?? 100) ?>">
                            <button type="button" class="qty-btn" id="increaseQty">+</button>
                           
                        </div>

                         <!-- مؤشر الكمية المتاحة — يظهر بعد اختيار التواريخ -->
                            <div id="availabilityBox" style="display:none; margin-top:12px; padding:12px 16px;
                                border-radius:10px; background:var(--color-background-secondary);
                                border:1px solid var(--color-border-tertiary)">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                                    <span style="font-size:13px; color:var(--text-muted)">الكمية المتاحة في هذه الفترة</span>
                                    <span id="availText" style="font-weight:600; font-size:14px"></span>
                                </div>
                                <div style="height:8px; background:var(--color-border-tertiary); border-radius:4px; overflow:hidden">
                                    <div id="availBar" style="height:100%; border-radius:4px; transition:width 0.4s ease, background 0.3s"></div>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color:var(--text-muted)">
                                    <span id="bookedText"></span>
                                    <span id="totalText"></span>
                                </div>
                            </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <div class="label-icon">📝</div> ملاحظات إضافية
                        </label>
                        <textarea name="notes" class="form-textarea" placeholder="أي تفاصيل إضافية تريد إضافتها..."></textarea>
                    </div>

                    <!-- Price Summary -->
                    <?php if ($product): ?>
                        <div class="price-summary" id="priceSummary">
                            <div class="summary-row">
                                <span class="summary-label">سعر الوحدة</span>
                                <span class="summary-value" id="unitPriceDisplay"><?= number_format((float)$product['price'], 2) ?> DA / يوم</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">عدد الأيام</span>
                                <span class="summary-value" id="daysDisplay">—</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">الكمية</span>
                                <span class="summary-value" id="qtyDisplay">1</span>
                            </div>
                            <div style="height:1px;background:var(--border);margin:12px 0"></div>
                            <div class="summary-row">
                                <span class="summary-label" style="font-size:15px;color:var(--text-main);font-weight:600">الإجمالي</span>
                                <span class="summary-total" id="totalDisplay">—</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="notice-box">
                        <div class="notice-dot"></div>
                        سيتم التواصل معك لتأكيد الحجز والدفع — لا يُطلب منك الدفع الآن.
                    </div>

                    <button type="submit" class="btn-submit">✦ تأكيد الحجز الآن →</button>
                </form>

            <?php endif; ?>
        </main>

        <!-- FOOTER -->
        <footer>
            <div class="footer-inner">
                <div class="footer-grid">
                    <div>
                        <h4>Luxury Wedding</h4>
                        <p style="color:var(--text-muted);font-size:13px;line-height:1.8">نقدم تجارب زفاف لا تُنسى بمعدات تأجير فاخرة وخدمة استثنائية.</p>
                    </div>
                    <div class="footer-col">
                        <h4>روابط سريعة</h4>
                        <ul class="footer-links">
                            <li><a href="../../index.php">الصفحة الرئيسية</a></li>
                            <li><a href="../page_products/products.php">المنتجات</a></li>
                            <li><a href="../user/my_bookings.php">حجوزاتي</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>تواصل معنا</h4>
                        <ul class="footer-links">
                            <li><a href="tel:+213555123456">📞 +213 555 123-456</a></li>
                            <li><a href="mailto:info@luxurywedding.com">✉️ info@luxurywedding.com</a></li>
                            <li>📍 الجزائر</li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <h4>ساعات العمل</h4>
                        <ul class="footer-links" style="list-style:none">
                            <li style="color:var(--text-muted);font-size:13px">الاثنين - الجمعة: 9ص - 6م</li>
                            <li style="color:var(--text-muted);font-size:13px">السبت: 10ص - 4م</li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>© 2025 Luxury Wedding. جميع الحقوق محفوظة.</p>
                </div>
            </div>
        </footer>

    </div><!-- /site-wrap -->

    <script>
        const unitPrice = <?= (float)($product['price'] ?? 0) ?>;
        const productId = <?= $productId ?>;
        const startInput = document.getElementById('startDate');
        const endInput = document.getElementById('endDate');
        const qtyInput = document.getElementById('qtyInput');
        const qtyDisplay = document.getElementById('qtyDisplay');
        const daysDisplay = document.getElementById('daysDisplay');
        const totalDisplay = document.getElementById('totalDisplay');
        const decreaseBtn = document.getElementById('decreaseQty');
        const increaseBtn = document.getElementById('increaseQty');

        // --- عناصر الكمية المتاحة ---
        const availBox = document.getElementById('availabilityBox');
        const availBar = document.getElementById('availBar');
        const availText = document.getElementById('availText');
        const bookedTxt = document.getElementById('bookedText');
        const totalTxt = document.getElementById('totalText');

       function calcDays(s, e) {
            if (!s || !e) return 0;
            const ms = new Date(e) - new Date(s);
            // إذا كان التاريخان متطابقين أو الفرق صحيح، نجعل الحد الأدنى 1 يوم
            return ms >= 0 ? Math.max(1, Math.floor(ms / 86400000)) : 0;
        }

        function updateTotal() {
            const qty = parseInt(qtyInput?.value) || 1;
            const days = calcDays(startInput?.value, endInput?.value);
            
            // قمنا بإزالة شرط days > 0 لأنه سيكون على الأقل 1 أو 0
            if (daysDisplay) daysDisplay.textContent = days > 0 ? days + ' يوم' : '—';
            if (qtyDisplay) qtyDisplay.textContent = qty;
            if (totalDisplay) {
                totalDisplay.textContent = days > 0 ? 
                    (unitPrice * qty * days).toFixed(2) + ' DA' : 
                    '—';
            }
        }

        // --- AJAX: جلب الكمية المتاحة ---
        let availCheckTimer = null;

        function checkAvailability() {
            const s = startInput?.value;
            const e = endInput?.value;
            if (!s || !e || e < s) {
                availBox.style.display = 'none';
                return;
            }

            clearTimeout(availCheckTimer);
            availCheckTimer = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `check_availability.php?product_id=${productId}&start_date=${s}&end_date=${e}`
                    );
                    const data = await res.json();
                    if (data.error) return;

                    const {
                        available,
                        booked,
                        total
                    } = data;
                    const pct = total > 0 ? Math.round((available / total) * 100) : 0;

                    // لون الشريط بناءً على النسبة
                    let barColor = '#22c55e'; // أخضر — متاح جيد
                    if (pct <= 0) barColor = '#ef4444'; // أحمر — لا يوجد
                    else if (pct <= 30) barColor = '#f97316'; // برتقالي — قليل

                    availBar.style.width = pct + '%';
                    availBar.style.background = barColor;
                    availText.textContent = available > 0 ? `${available} متاح` : 'غير متاح';
                    availText.style.color = barColor;
                    bookedTxt.textContent = `محجوز: ${booked}`;
                    totalTxt.textContent = `الإجمالي: ${total}`;

                    // تعديل الحد الأقصى لحقل الكمية
                    qtyInput.max = available;
                    if (parseInt(qtyInput.value) > available && available > 0) {
                        qtyInput.value = available;
                        updateTotal();
                    }

                    availBox.style.display = 'block';
                } catch (err) {
                    console.error('خطأ في التحقق من التوفر:', err);
                }
            }, 400); // تأخير 400ms لتجنب طلبات متكررة
        }

        startInput?.addEventListener('change', function() {
            if (endInput) endInput.min = this.value;
            if (endInput?.value && endInput.value < this.value) endInput.value = this.value;
            updateTotal();
            checkAvailability();
        });
        endInput?.addEventListener('change', () => {
            updateTotal();
            checkAvailability();
        });
        qtyInput?.addEventListener('input', updateTotal);

        decreaseBtn?.addEventListener('click', () => {
            if (parseInt(qtyInput.value) > 1) {
                qtyInput.value--;
                updateTotal();
            }
        });
        increaseBtn?.addEventListener('click', () => {
            const max = parseInt(qtyInput.max) || 9999;
            if (parseInt(qtyInput.value) < max) {
                qtyInput.value++;
                updateTotal();
            }
        });

        const today = new Date().toISOString().split('T')[0];
        if (startInput) startInput.min = today;
        if (endInput) endInput.min = today;

        updateTotal();



        const toggle = document.getElementById("menu-toggle");
const links = document.getElementById("nav-user");

if (toggle && links) {
    toggle.addEventListener("click", () => {
        links.classList.toggle("active");
    });
}

    </script>
</body>

</html>