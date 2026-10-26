<?php
// Config/auth_check.php
// Middleware للتحقق من تسجيل الدخول
// الاستخدام: require_once ROOT . '/Config/auth_check.php';
// أو بتمرير $requiredRole = 'admin';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحديد المسار الجذر من أي صفحة
if (!defined('BASE_URL')) {
    define('BASE_URL', ''); // جذر الدومين مباشرة
}

/**
 * يتحقق أن المستخدم مسجل الدخول.
 * إذا لم يكن مسجلاً يعيد التوجيه لصفحة login.
 */
function requireLogin(string $redirectTo = null): void
{
    if (empty($_SESSION['user_id'])) {
        $redirect = $redirectTo ?? BASE_URL . '/login.php';
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * يتحقق أن المستخدم أدمن.
 */
function requireAdmin(string $redirectTo = null): void
{
    requireLogin($redirectTo);
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * هل المستخدم مسجل دخوله؟
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * هل المستخدم أدمن؟
 */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}
