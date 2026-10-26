<?php
// logout.php
session_start();
session_unset();
session_destroy();
// إعادة التوجيه لصفحة الدخول
header('Location: login.php');
exit;