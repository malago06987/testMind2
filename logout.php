<?php
session_start();

// ลบ session ทั้งหมด
session_unset();
session_destroy();

// ลบ cookie session (ถ้ามี)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// กลับไปหน้า login
header("Location: login.php");
exit;
?>