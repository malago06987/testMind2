<?php
session_start();

// ตรวจสอบ session admin - ถ้าไม่ใช่ admin ให้กลับไปหน้า login
if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$page_title = 'จัดการผู้ใช้';
$current_page = 'users';

// ดึงข้อมูลผู้ใช้ทั้งหมด
$users_query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.role,
        u.created_at,
        u.last_login,
        COUNT(DISTINCT e.id) as total_entries,
        COALESCE(SUM(e.word_count), 0) as total_words
    FROM users u
    LEFT JOIN entries e ON u.id = e.user_id
    GROUP BY u.id, u.name, u.email, u.role, u.created_at, u.last_login
    ORDER BY u.created_at DESC
";

$users = $conn->query($users_query);

include 'includes/header.php';
?>

<h1>👥 จัดการผู้ใช้</h1>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่อ</th>
                <th>Email</th>
                <th>Role</th>
                <th>บันทึก</th>
                <th>คำทั้งหมด</th>
                <th>สมัครเมื่อ</th>
                <th>Login ล่าสุด</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                </td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <?php if($user['role'] == 'admin'): ?>
                        <span class="badge badge-admin">🔐 Admin</span>
                    <?php else: ?>
                        <span class="badge badge-user">👤 User</span>
                    <?php endif; ?>
                </td>
                <td><?php echo number_format($user['total_entries']); ?></td>
                <td><?php echo number_format($user['total_words']); ?></td>
                <td>
                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?><br>
                    <small style="color: var(--muted);"><?php echo date('H:i', strtotime($user['created_at'])); ?></small>
                </td>
                <td>
                    <?php if($user['last_login']): ?>
                        <?php echo date('d/m/Y', strtotime($user['last_login'])); ?><br>
                        <small style="color: var(--muted);"><?php echo date('H:i', strtotime($user['last_login'])); ?></small>
                    <?php else: ?>
                        <span style="color: var(--muted);">ยังไม่เคยล็อกอิน</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    // ตรวจสอบ active (ล็อกอินภายใน 7 วัน)
                    if($user['last_login'] && strtotime($user['last_login']) > strtotime('-7 days')):
                    ?>
                        <span style="color: #4caf50; font-weight: 600;">🟢 Active</span>
                    <?php elseif($user['last_login']): ?>
                        <span style="color: var(--muted);">⚪ Inactive</span>
                    <?php else: ?>
                        <span style="color: var(--muted);">⚪ New</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
    small {
        font-size: 0.8em;
    }
</style>

    </div>
</body>
</html>
