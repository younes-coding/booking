<?php
// login.php — تسجيل الدخول وإنشاء حساب (Backend + Frontend)
session_start();
require_once 'Config/connexion.php';
require_once 'Config/auth_check.php';

// إذا مسجل دخوله بالفعل، وجّهه
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'backend/admin/admin.php' : 'index.php'));
    exit;
}

$errors  = [];
$success = '';
$mode    = 'login'; // الوضع الافتراضي

// ============================================================
// POST: تسجيل الدخول
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'login') {
        $mode  = 'login';
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (empty($email) || empty($pass)) {
            $errors[] = 'يرجى ملء جميع الحقول المطلوبة.';
        } else {
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = $user['role'];
                session_regenerate_id(true);

                if ($user['role'] === 'admin') {
                    header('Location: backend/admin/admin.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $errors[] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
            }
        }
    }

    elseif ($_POST['action'] === 'register') {
        $mode       = 'register';
        $firstName  = trim($_POST['first_name'] ?? '');
        $lastName   = trim($_POST['last_name']  ?? '');
        $email      = trim($_POST['email']       ?? '');
        $phone      = trim($_POST['phone']       ?? '');
        $pass       = $_POST['password']         ?? '';
        $passConf   = $_POST['password_confirm'] ?? '';


        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($pass)) {
            $errors[] = 'يرجى ملء جميع الحقول المطلوبة.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'البريد الإلكتروني غير صالح.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.';
        }
        if ($pass !== $passConf) {
            $errors[] = 'كلمتا المرور غير متطابقتين.';
        }

        if (empty($errors)) {
            // التحقق من وجود البريد مسبقاً
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'هذا البريد الإلكتروني مسجل مسبقاً.';
            } else {
                $hashed = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins = $pdo->prepare(
                    "INSERT INTO users (first_name, last_name, email, phone, password, role)
                     VALUES (?, ?, ?, ?, ?, 'user')"
                );
                $ins->execute([$firstName, $lastName, $email, $phone, $hashed]);

                $newId = $pdo->lastInsertId();
                $_SESSION['user_id']    = $newId;
                $_SESSION['user_name']  = "$firstName $lastName";
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = 'user';
                session_regenerate_id(true);

                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>تسجيل الدخول - Luxury Wedding</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css"/>
</head>
<body>
<div class="auth-page">

  <!-- ── اليمين: الديكور ── -->
  <div class="auth-panel">
    <div class="panel-logo">💛</div>
    <h2 class="panel-title">Luxury<br/><span>Wedding</span></h2>
    <div class="panel-divider"></div>
    <p class="panel-sub">انضم إلينا واجعل زفافك تجربة لا تُنسى مع أفضل معدات التأجير الفاخرة</p>
    <div class="panel-divider"></div>
    <ul class="panel-features">
      <li><div class="feat-icon">♥</div> احجز معدات زفافك بسهولة</li>
      <li><div class="feat-icon">📅</div> تتبع حجوزاتك في الوقت الفعلي</li>
      <li><div class="feat-icon">🔒</div> بيانات آمنة ومحمية</li>
    </ul>
  </div>

  <!-- ── اليسار: النموذج ── -->
  <div class="auth-form-side">
    <div class="auth-card">

      <!-- العنوان -->
      <div class="auth-header">
        <h2 id="form-title"><?= $mode === 'register' ? 'إنشاء حساب جديد' : 'مرحباً بعودتك 👋' ?></h2>
        <p id="form-sub"><?= $mode === 'register' ? 'انضم إلينا وابدأ تخطيط زفافك' : 'سجّل دخولك للوصول إلى حجوزاتك' ?></p>
      </div>

      <!-- رسائل الخطأ -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): ?>
            <p>⚠️ <?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- التبويبات -->
      <div class="auth-tabs">
        <button class="auth-tab <?= $mode === 'login'    ? 'active' : '' ?>" onclick="switchMode('login', this)">تسجيل الدخول</button>
        <button class="auth-tab <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchMode('register', this)">إنشاء حساب</button>
      </div>

      <!-- ── نموذج تسجيل الدخول ── -->
      <div id="login-form" class="form-body" style="display:<?= $mode === 'login' ? 'block' : 'none' ?>">
        <form method="POST" action="login.php" novalidate>
          <input type="hidden" name="action" value="login">

          <div class="field">
            <label>البريد الإلكتروني <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input type="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
            </div>
          </div>
          <div class="field">
            <label>كلمة المرور <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="login-pass" name="password" placeholder="••••••••" required/>
              <button type="button" class="toggle-pass" onclick="togglePass('login-pass')">👁</button>
            </div>
          </div>
         
          <button type="submit" class="btn-submit">تسجيل الدخول →</button>
          <p class="switch-link">ليس لديك حساب؟ <button type="button" onclick="switchMode('register', document.querySelectorAll('.auth-tab')[1])">إنشاء حساب جديد</button></p>
        </form>
      </div>

      <!-- ── نموذج إنشاء الحساب ── -->
      <div id="register-form" class="form-body" style="display:<?= $mode === 'register' ? 'block' : 'none' ?>">
        <form method="POST" action="login.php" novalidate>
          <input type="hidden" name="action" value="register">

          <div class="form-row">
            <div class="field">
              <label>الاسم الأول <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="input-icon">👤</span>
                <input type="text" name="first_name" placeholder="محمد" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required/>
              </div>
            </div>
            <div class="field">
              <label>اسم العائلة <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="input-icon">👤</span>
                <input type="text" name="last_name" placeholder="أحمد" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required/>
              </div>
            </div>
          </div>
          <div class="field">
            <label>البريد الإلكتروني <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon">✉️</span>
              <input type="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
            </div>
          </div>
          <div class="field">
            <label>رقم الهاتف</label>
            <div class="input-wrap">
              <span class="input-icon">📞</span>
              <input type="tel" name="phone" placeholder="+213 555 000-000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
            </div>
          </div>
          <div class="field">
            <label>كلمة المرور <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="reg-pass" name="password" placeholder="8 أحرف على الأقل" required/>
              <button type="button" class="toggle-pass" onclick="togglePass('reg-pass')">👁</button>
            </div>
          </div>
          <div class="field">
            <label>تأكيد كلمة المرور <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon">🔒</span>
              <input type="password" id="confirm-pass" name="password_confirm" placeholder="أعد كتابة كلمة المرور" required/>
              <button type="button" class="toggle-pass" onclick="togglePass('confirm-pass')">👁</button>
            </div>
          </div>
         
          <button type="submit" class="btn-submit">إنشاء الحساب +</button>
          <p class="switch-link">لديك حساب؟ <button type="button" onclick="switchMode('login', document.querySelectorAll('.auth-tab')[0])">تسجيل الدخول</button></p>
        </form>
      </div>

    </div>
  </div>

</div>

<script>
function switchMode(mode, clickedTab) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  clickedTab.classList.add('active');
  if (mode === 'login') {
    document.getElementById('login-form').style.display = 'block';
    document.getElementById('register-form').style.display = 'none';
    document.getElementById('form-title').textContent = 'مرحباً بعودتك 👋';
    document.getElementById('form-sub').textContent = 'سجّل دخولك للوصول إلى حجوزاتك';
  } else {
    document.getElementById('login-form').style.display = 'none';
    document.getElementById('register-form').style.display = 'block';
    document.getElementById('form-title').textContent = 'إنشاء حساب جديد';
    document.getElementById('form-sub').textContent = 'انضم إلينا وابدأ تخطيط زفافك';
  }
}
function togglePass(id) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>