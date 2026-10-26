<?php
// Config/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * إرسال إيميل إشعار للأدمين عند حجز جديد
 */
function sendBookingNotificationToAdmin(array $bookingData): bool
{
    // ====== إعدادات — غيّرها حسب حسابك ======
    define('ADMIN_EMAIL',    'bounouayounes560@gmail.com');      // إيميل الأدمين
    define('SMTP_HOST',      'smtp.gmail.com');
    define('SMTP_USER',      'superadmin60@gmail.com');     // إيميل الإرسال
    define('SMTP_PASS',      'xzdm zrze zrok wbrt');        // App Password من Google
    define('SMTP_PORT',      587);
    define('SITE_NAME',      'Luxury Wedding');
    // ==========================================

    $mail = new PHPMailer(true);

    try {
        // إعداد SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // المرسل والمستقبل
        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->addAddress(ADMIN_EMAIL, 'الإدارة');

        // الموضوع
        $bookingRef = str_pad($bookingData['booking_id'], 6, '0', STR_PAD_LEFT);
        $mail->Subject = "🎉 حجز جديد #{$bookingRef} — " . SITE_NAME;

        // محتوى الإيميل HTML
        $mail->isHTML(true);
        $mail->Body = buildEmailHtml($bookingData, $bookingRef);
        $mail->AltBody = buildEmailText($bookingData, $bookingRef);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // لا توقف العملية إذا فشل الإيميل — فقط سجّل الخطأ
        error_log("PHPMailer Error (booking notification): " . $mail->ErrorInfo);
        return false;
    }
}

// ──────────────────────────────────────────────
// قالب HTML للإيميل
// ──────────────────────────────────────────────
function buildEmailHtml(array $d, string $ref): string
{
    $name       = htmlspecialchars($d['customer_name']);
    $phone      = htmlspecialchars($d['phone']);
    $product    = htmlspecialchars($d['product_name']);
    $eventDate  = htmlspecialchars($d['event_date']);
    $startDate  = htmlspecialchars($d['start_date']);
    $endDate    = htmlspecialchars($d['end_date']);
    $qty        = intval($d['quantity']);
    $total      = number_format((float)$d['total_amount'], 2);
    $address    = htmlspecialchars($d['address'] ?? '—');
    $notes      = htmlspecialchars($d['notes'] ?? '—');
    $adminUrl   = 'https://affrah-dz.infinityfreeapp.com/backend/admin/bookings.php';//ر الرابط

    return <<<HTML
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background:#0f0f0f; margin:0; padding:0; }
        .wrap { max-width:600px; margin:0 auto; background:#1a1a2e; border-radius:16px; overflow:hidden; }
        .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                  border-bottom: 2px solid #c9a84c; padding:32px 24px; text-align:center; }
        .header h1 { color:#c9a84c; margin:0; font-size:22px; }
        .header p  { color:#8a8a9a; margin:8px 0 0; font-size:13px; }
        .badge { display:inline-block; background:#c9a84c; color:#0f0f0f;
                 padding:6px 18px; border-radius:20px; font-weight:700;
                 font-size:16px; margin-top:12px; }
        .body { padding:28px 24px; }
        .section-title { color:#c9a84c; font-size:14px; font-weight:700;
                         letter-spacing:1px; text-transform:uppercase;
                         margin:20px 0 12px; border-right:3px solid #c9a84c; padding-right:10px; }
        .row { display:flex; justify-content:space-between; align-items:center;
               padding:10px 0; border-bottom:1px solid #2a2a3e; }
        .row:last-child { border-bottom:none; }
        .lbl { color:#8a8a9a; font-size:13px; }
        .val { color:#e0e0e0; font-size:13px; font-weight:600; text-align:left; }
        .total-row { background:#c9a84c22; border-radius:10px; padding:14px 16px;
                     display:flex; justify-content:space-between; margin-top:16px; }
        .total-lbl { color:#c9a84c; font-weight:700; font-size:15px; }
        .total-val { color:#c9a84c; font-weight:700; font-size:18px; }
        .btn { display:block; text-align:center; background:#c9a84c; color:#0f0f0f;
               padding:14px; border-radius:10px; text-decoration:none;
               font-weight:700; font-size:15px; margin-top:24px; }
        .footer { text-align:center; padding:18px; color:#555; font-size:11px;
                  border-top:1px solid #2a2a3e; }
      </style>
    </head>
    <body>
      <div class="wrap">
        <div class="header">
          <h1>💍 Luxury Wedding</h1>
          <p>إشعار حجز جديد</p>
          <div class="badge">#{$ref}</div>
        </div>
        <div class="body">

          <div class="section-title">👤 بيانات العميل</div>
          <div class="row"><span class="lbl">الاسم</span><span class="val">{$name}</span></div>
          <div class="row"><span class="lbl">الهاتف</span><span class="val" dir="ltr">{$phone}</span></div>
          <div class="row"><span class="lbl">العنوان</span><span class="val">{$address}</span></div>

          <div class="section-title">📦 تفاصيل الحجز</div>
          <div class="row"><span class="lbl">المنتج</span><span class="val">{$product}</span></div>
          <div class="row"><span class="lbl">تاريخ المناسبة</span><span class="val">{$eventDate}</span></div>
          <div class="row"><span class="lbl">من</span><span class="val">{$startDate}</span></div>
          <div class="row"><span class="lbl">إلى</span><span class="val">{$endDate}</span></div>
          <div class="row"><span class="lbl">الكمية</span><span class="val">{$qty}</span></div>
          <div class="row"><span class="lbl">ملاحظات</span><span class="val">{$notes}</span></div>

          <div class="total-row">
            <span class="total-lbl">إجمالي الحجز</span>
            <span class="total-val">{$total} DA</span>
          </div>

          <a href="{$adminUrl}" class="btn">📋 عرض لوحة الحجوزات</a>
        </div>
        <div class="footer">© 2025 Luxury Wedding — تم الإرسال تلقائياً</div>
      </div>
    </body>
    </html>
    HTML;
}

// ──────────────────────────────────────────────
// نسخة نصية احتياطية (Plain Text)
// ──────────────────────────────────────────────
function buildEmailText(array $d, string $ref): string
{
    return "حجز جديد #{$ref}\n"
         . "العميل: {$d['customer_name']}\n"
         . "الهاتف: {$d['phone']}\n"
         . "المنتج: {$d['product_name']}\n"
         . "من: {$d['start_date']} إلى: {$d['end_date']}\n"
         . "الكمية: {$d['quantity']}\n"
         . "الإجمالي: " . number_format((float)$d['total_amount'], 2) . " DA\n";
}