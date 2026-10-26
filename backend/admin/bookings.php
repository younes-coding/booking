<?php
session_start();
require_once("../../Config/connexion.php");
require_once("../../Config/auth_check.php");
requireAdmin('../../login.php');

$bookings = [];
$stats    = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'cancelled' => 0];

try {
    $stmt = $pdo->query(
        "SELECT b.id, b.customer_name, b.phone, b.event_date, b.start_date, b.end_date,
                b.quantity, b.total_amount, b.status, b.notes, b.created_at,
                p.image AS product_image,
                p.name AS product_name
         FROM bookings b
         LEFT JOIN products p ON p.id = b.product_id
         ORDER BY b.id DESC"
    );
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إحصائيات
    $stats['total'] = count($bookings);
    foreach ($bookings as $b) {
        if (isset($stats[$b['status']])) $stats[$b['status']]++;
    }
} catch (Exception $e) {
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الحجوزات</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="page_admin.css">
</head>

<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <span class="brand-dot"></span>
                <h2>لوحة المدير</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="admin.php" class="sidebar-link">
                    <span class="sidebar-icon">📦</span>
                    <span>المنتوجات</span>
                </a>
                <a href="bookings.php" class="sidebar-link active">
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

        <main class="container">
            <div class="dashboard-header">
                <button class="sidebar-toggle" type="button" aria-label="فتح القائمة">☰</button>
                <h2 class="dashboard-title">لوحة التحكم - إدارة الحجوزات</h2>
            </div>

            <!-- إحصائيات -->
            <section class="bookings-tools">
                <div class="booking-stat-card">
                    <p>إجمالي الحجوزات</p>
                    <h3><?= $stats['total'] ?></h3>
                </div>
                <div class="booking-stat-card">
                    <p>المؤكدة</p>
                    <h3 style="color:#4caf8a"><?= $stats['confirmed'] ?></h3>
                </div>
                <div class="booking-stat-card">
                    <p>قيد الانتظار</p>
                    <h3 style="color:#c9a84c"><?= $stats['pending'] ?></h3>
                </div>
                <div class="booking-stat-card">
                    <p>الملغاة</p>
                    <h3 style="color:#c0515a"><?= $stats['cancelled'] ?></h3>
                </div>
            </section>

            <!-- فلاتر البحث -->
            <section class="bookings-filter">
                <input type="text" id="bookingSearch" placeholder="ابحث بالاسم ...">
                <select id="bookingStatusFilter">
                    <option value="all">كل الحالات</option>
                    <option value="confirmed">مؤكد</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="cancelled">ملغى</option>
                </select>
            </section>

            <div class="table-wrapper">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>المنتج</th>

                            <th>الهاتف</th>
                            <th>تاريخ المناسبة</th>
                            <th>الفترة</th>
                            <th>الكمية</th>
                            <th>المبلغ</th>
                            <th>الملاحظة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="bookingsBody">
                        <?php foreach ($bookings as $booking): ?>
                            <tr
                                data-name="<?= htmlspecialchars(mb_strtolower($booking['customer_name'])) ?>"
                                data-phone="<?= htmlspecialchars($booking['phone']) ?>"
                                data-product="<?= htmlspecialchars(mb_strtolower($booking['product_name'] ?? '')) ?>"
                                data-status="<?= htmlspecialchars($booking['status']) ?>">

                                <td data-label="#">#<?= htmlspecialchars($booking['id']) ?></td>
                                <td data-label="العميل"><strong><?= htmlspecialchars($booking['customer_name']) ?></strong></td>
                                <td data-label="المنتج">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <?php if (!empty($booking['product_image'])): ?>
                                            <img src="../../<?= htmlspecialchars($booking['product_image']) ?>"
                                                alt="صورة المنتج"
                                                style="width:48px; height:48px; object-fit:cover; 
                        border-radius:8px; flex-shrink:0;
                        border:1px solid rgba(201,168,76,0.3);">
                                        <?php else: ?>
                                            <div style="width:48px; height:48px; border-radius:8px;
                        background:rgba(255,255,255,0.05);
                        display:flex; align-items:center; 
                        justify-content:center; font-size:20px;">
                                                📦
                                            </div>
                                        <?php endif; ?>
                                        <span style="font-size:13px; font-weight:500;">
                                            <?= htmlspecialchars($booking['product_name'] ?? '—') ?>
                                        </span>
                                    </div>
                                </td>

                                <td data-label="الهاتف"><?= htmlspecialchars($booking['phone']) ?></td>
                                <td data-label="تاريخ المناسبة"><?= htmlspecialchars($booking['event_date']) ?></td>
                                <td data-label="الفترة" style="font-size:12px">
                                    <?= htmlspecialchars($booking['start_date']) ?>
                                    <br>← <?= htmlspecialchars($booking['end_date']) ?>
                                </td>
                                <td data-label="الكمية"><?= intval($booking['quantity']) ?></td>
                                <td data-label="المبلغ">
                                    <span style="font-weight:700; color:#c9a84c; font-size:14px;">
                                        <?= number_format((float)$booking['total_amount'], 2) ?>
                                    </span>
                                    <span style="font-size:11px; color:#888;"> DA</span>
                                </td>
                                <td data-label="الملاحظة">
                                    <?= !$booking['notes'] ? '<span style="color:#888;">لا توجد ملاحظات</span>' : htmlspecialchars($booking['notes']) ?>
                                </td>
                                <td data-label="الحالة">
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <span class="booking-status status-confirmed">مؤكد</span>
                                    <?php elseif ($booking['status'] === 'pending'): ?>
                                        <span class="booking-status status-pending">قيد الانتظار</span>
                                    <?php else: ?>
                                        <span class="booking-status status-cancelled">ملغى</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="الإجراءات">
                                    <div class="action-buttons" style="gap:6px; display:flex; flex-wrap:wrap;">
                                        <?php if ($booking['status'] !== 'confirmed'): ?>
                                            <button onclick="changeStatus(<?= $booking['id'] ?>, 'confirmed')"
                                                class="btn-status btn-confirm" title="تأكيد">✔ تأكيد</button>
                                        <?php endif; ?>
                                        <?php if ($booking['status'] !== 'cancelled'): ?>
                                            <button onclick="changeStatus(<?= $booking['id'] ?>, 'cancelled')"
                                                class="btn-status btn-cancel-booking" title="إلغاء">✖ إلغاء</button>
                                        <?php endif; ?>
                                        <?php if ($booking['status'] !== 'pending'): ?>
                                            <button onclick="changeStatus(<?= $booking['id'] ?>, 'pending')"
                                                class="btn-status btn-pending" title="إعادة للانتظار">↺ انتظار</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="10" style="text-align:center; padding:40px; color:#888;">
                                    لا توجد حجوزات بعد
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast" style="display:none"></div>

    <script src="bookings.js"></script>
    <script>
        // تغيير حالة الحجز عبر AJAX
        function changeStatus(bookingId, newStatus) {
            const labels = {
                confirmed: 'تأكيد',
                cancelled: 'إلغاء',
                pending: 'إعادة للانتظار'
            };
            if (!confirm(`هل تريد ${labels[newStatus]} هذا الحجز؟`)) return;

            fetch('update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${bookingId}&status=${newStatus}`
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(() => showToast('حدث خطأ في الشبكة', 'error'));
        }

        function showToast(msg, type) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast toast-' + type;
            t.style.display = 'block';
            setTimeout(() => {
                t.style.display = 'none';
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('bookingSearch');
            const statusFilter = document.getElementById('bookingStatusFilter');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const selectedStatus = statusFilter.value;
                const tableRows = document.querySelectorAll('#bookingsBody tr');

                tableRows.forEach(row => {
                    // تخطي رسالة "لا توجد حجوزات"
                    if (row.cells.length === 1) return;

                    // جلب البيانات من الخصائص المخبأة وتحويلها لحروف صغيرة لتطابق البحث الإنجليزي
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    const phone = row.getAttribute('data-phone') || '';
                    const product = (row.getAttribute('data-product') || '').toLowerCase();
                    const status = row.getAttribute('data-status') || '';

                    // التحقق من المطابقة
                    const matchesSearch = name.includes(searchTerm) || phone.includes(searchTerm) || product.includes(searchTerm);
                    const matchesStatus = (selectedStatus === 'all' || status === selectedStatus);

                    // إضافة أو إزالة كلاس الإخفاء بدلاً من تعديل الستايل مباشرة
                    if (matchesSearch && matchesStatus) {
                        row.classList.remove('hide-row');
                    } else {
                        row.classList.add('hide-row');
                    }
                });
            }

            // تشغيل الدالة عند الكتابة أو تغيير الفلتر
            if (searchInput && statusFilter) {
                searchInput.addEventListener('input', filterTable);
                statusFilter.addEventListener('change', filterTable);
            }
        });





        // ── Sidebar Mobile Drawer Toggle ──
        // أضف هذا الكود في <script> في نهاية كل صفحة PHP
        (function() {
            const toggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');

            if (!toggle || !sidebar) return;

            function openSidebar() {
                sidebar.classList.add('open');
                backdrop.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', function() {
                sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
            });

            backdrop.addEventListener('click', closeSidebar);

            // إغلاق بـ Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeSidebar();
            });
        })();
    </script>
</body>

</html>