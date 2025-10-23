<?php
session_start();

// ทำลาย session ของ admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

// ทำลาย session ทั้งหมด
session_destroy();

// กลับไปหน้า login หลัก
header("Location: ../login.php");
exit;
?>
