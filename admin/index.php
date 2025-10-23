<?php
session_start();

// ตรวจสอบ session admin - ถ้าไม่ใช่ admin ให้กลับไปหน้า login
if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$page_title = 'Admin Dashboard';
$current_page = 'dashboard';

// ดึงข้อมูลสถิติ
$stats = [];

// จำนวนผู้ใช้ทั้งหมด
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

// จำนวน Admin
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$stats['total_admins'] = $result->fetch_assoc()['total'];

// จำนวนบันทึกทั้งหมด
$result = $conn->query("SELECT COUNT(*) as total FROM entries");
$stats['total_entries'] = $result->fetch_assoc()['total'];

// จำนวนบันทึกวันนี้
$result = $conn->query("SELECT COUNT(*) as total FROM entries WHERE DATE(created_at) = CURDATE()");
$stats['today_entries'] = $result->fetch_assoc()['total'];

// จำนวน Reflections ทั้งหมด
$result = $conn->query("SELECT COUNT(*) as total FROM reflections");
$stats['total_reflections'] = $result->fetch_assoc()['total'];

// ผู้ใช้ที่ active ล่าสุด (ล็อกอินภายใน 7 วัน)
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['active_users'] = $result->fetch_assoc()['total'];

// ดึงข้อมูลผู้ใช้ล่าสุด 5 คน
$recent_users = $conn->query("
    SELECT id, name, email, role, created_at, last_login 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
");

// ดึงข้อมูลบันทึกล่าสุด 5 รายการ
$recent_entries = $conn->query("
    SELECT e.id, e.content, e.mood, e.created_at, u.name as user_name 
    FROM entries e 
    JOIN users u ON e.user_id = u.id 
    ORDER BY e.created_at DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<h1>📊 ภาพรวมระบบ MindVault</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>👥 ผู้ใช้ทั้งหมด</h3>
        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
        <div class="stat-label">Active: <?php echo number_format($stats['active_users']); ?> (7 วันล่าสุด)</div>
    </div>
    
    <div class="stat-card">
        <h3>🔐 ผู้ดูแลระบบ</h3>
        <div class="stat-value"><?php echo number_format($stats['total_admins']); ?></div>
        <div class="stat-label">Admins</div>
    </div>
    
    <div class="stat-card">
        <h3>📝 บันทึกทั้งหมด</h3>
        <div class="stat-value"><?php echo number_format($stats['total_entries']); ?></div>
        <div class="stat-label">วันนี้: <?php echo number_format($stats['today_entries']); ?> รายการ</div>
    </div>
    
    <div class="stat-card">
        <h3>💭 Reflections</h3>
        <div class="stat-value"><?php echo number_format($stats['total_reflections']); ?></div>
        <div class="stat-label">การสะท้อนทั้งหมด</div>
    </div>
</div>

<h2>👥 ผู้ใช้ล่าสุด</h2>
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่อ</th>
                <th>Email</th>
                <th>Role</th>
                <th>สมัครเมื่อ</th>
                <th>Login ล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $recent_users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <?php if($user['role'] == 'admin'): ?>
                        <span class="badge badge-admin">Admin</span>
                    <?php else: ?>
                        <span class="badge badge-user">User</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<h2 style="margin-top: 2rem;">📝 บันทึกล่าสุด</h2>
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ผู้ใช้</th>
                <th>เนื้อหา</th>
                <th>อารมณ์</th>
                <th>สร้างเมื่อ</th>
            </tr>
        </thead>
        <tbody>
            <?php while($entry = $recent_entries->fetch_assoc()): ?>
            <tr>
                <td><?php echo $entry['id']; ?></td>
                <td><?php echo htmlspecialchars($entry['user_name']); ?></td>
                <td><?php echo htmlspecialchars(mb_substr($entry['content'], 0, 80)) . '...'; ?></td>
                <td>
                    <?php 
                    $mood_icons = ['happy' => '😊', 'neutral' => '😐', 'sad' => '😢'];
                    echo $mood_icons[$entry['mood']] ?? '😐';
                    ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($entry['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

    </div>
</body>
</html>
