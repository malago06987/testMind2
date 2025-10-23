<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

// ประมวลผลหน้าที่จะแสดง (สำหรับทดสอบ)
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = ['dashboard', 'profile', 'entries', 'analytics'];

if(!in_array($page, $allowedPages)){
    $page = 'dashboard';
}

// ข้อมูลผู้ใช้สำหรับทดสอบ
$user_id = $_SESSION['user_id'] ?? 1; // ใช้ ID 1 สำหรับทดสอบ
$user_name = $_SESSION['user_name'] ?? 'ผู้ทดสอบ'; // ชื่อทดสอบ
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MindVault - บันทึกความคิดและความรู้สึก</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1 class="header-title">MindVault</h1>
                <nav class="main-nav">
                    <a href="?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">หน้าหลัก</a>
                    <a href="?page=entries" class="nav-link <?php echo $page === 'entries' ? 'active' : ''; ?>">บันทึกทั้งหมด</a>
                    <a href="?page=analytics" class="nav-link <?php echo $page === 'analytics' ? 'active' : ''; ?>">สถิติ</a>
                    <a href="?page=profile" class="nav-link <?php echo $page === 'profile' ? 'active' : ''; ?>">โปรไฟล์</a>
                </nav>
                <div class="header-user">
                    <span>สวัสดี, <?php echo htmlspecialchars($user_name); ?></span>
                    <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="page-content">
            <?php 
            // เรียกใช้หน้าที่ต้องการ
            $pageFile = "pages/{$page}.php";
            if(file_exists($pageFile)){
                include $pageFile;
            } else {
                include "pages/dashboard.php";
            }
            ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>