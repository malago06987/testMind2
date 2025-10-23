<?php
// หน้าโปรไฟล์ผู้ใช้
// ตัวแปร $conn, $user_id ถูกส่งมาจาก index.php

$message = '';
$messageType = '';

// ประมวลผลการอัพเดทโปรไฟล์
if(isset($_POST['update_profile'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    if(empty($name) || empty($email)){
        $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        $messageType = 'error';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'รูปแบบอีเมลไม่ถูกต้อง';
        $messageType = 'error';
    } else {
        // ตรวจสอบอีเมลซ้ำ
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $user_id);
        $checkStmt->execute();
        
        if($checkStmt->get_result()->num_rows > 0){
            $message = 'อีเมลนี้ถูกใช้งานแล้ว';
            $messageType = 'error';
        } else {
            // อัพเดทข้อมูล
            $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $name, $email, $user_id);
            
            if($updateStmt->execute()){
                $_SESSION['user_name'] = $name;
                $message = 'อัพเดทข้อมูลเรียบร้อยแล้ว';
                $messageType = 'success';
            } else {
                $message = 'เกิดข้อผิดพลาดในการอัพเดท';
                $messageType = 'error';
            }
            $updateStmt->close();
        }
        $checkStmt->close();
    }
}

// ประมวลผลการเปลี่ยนรหัสผ่าน
if(isset($_POST['change_password'])){
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)){
        $message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        $messageType = 'error';
    } elseif($newPassword !== $confirmPassword){
        $message = 'รหัสผ่านใหม่ไม่ตรงกัน';
        $messageType = 'error';
    } elseif(strlen($newPassword) < 6){
        $message = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร';
        $messageType = 'error';
    } else {
        // ตรวจสอบรหัสผ่านปัจจุบัน
        $checkStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $checkStmt->bind_param("i", $user_id);
        $checkStmt->execute();
        $user = $checkStmt->get_result()->fetch_assoc();
        
        if(!password_verify($currentPassword, $user['password'])){
            $message = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
            $messageType = 'error';
        } else {
            // อัพเดทรหัสผ่าน
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $user_id);
            
            if($updateStmt->execute()){
                $message = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว';
                $messageType = 'success';
            } else {
                $message = 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน';
                $messageType = 'error';
            }
            $updateStmt->close();
        }
        $checkStmt->close();
    }
}

// ดึงข้อมูลผู้ใช้
$userStmt = $conn->prepare("SELECT name, email, created_at, last_login FROM users WHERE id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userData = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

// ดึงสถิติผู้ใช้
$statsStmt = $conn->prepare("SELECT 
    COUNT(*) as total_entries,
    COUNT(DISTINCT DATE(created_at)) as active_days,
    MIN(created_at) as first_entry,
    MAX(created_at) as last_entry
    FROM entries WHERE user_id = ?");
$statsStmt->bind_param("i", $user_id);
$statsStmt->execute();
$userStats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();
?>

<div class="profile-page">
    <div class="page-header">
        <h2 class="page-title">👤 โปรไฟล์ของฉัน</h2>
    </div>

    <?php if(!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="profile-content">
        <!-- ข้อมูลส่วนตัว -->
        <div class="profile-section">
            <h3 class="section-title">📝 ข้อมูลส่วนตัว</h3>
            <form method="post" class="profile-form">
                <div class="form-group">
                    <label class="form-label">ชื่อ</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" 
                           required class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" 
                           required class="form-input">
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary">อัพเดทข้อมูล</button>
            </form>
        </div>

        <!-- เปลี่ยนรหัสผ่าน -->
        <div class="profile-section">
            <h3 class="section-title">🔒 เปลี่ยนรหัสผ่าน</h3>
            <form method="post" class="profile-form">
                <div class="form-group">
                    <label class="form-label">รหัสผ่านปัจจุบัน</label>
                    <input type="password" name="current_password" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">รหัสผ่านใหม่</label>
                    <input type="password" name="new_password" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" required class="form-input">
                </div>
                
                <button type="submit" name="change_password" class="btn btn-secondary">เปลี่ยนรหัสผ่าน</button>
            </form>
        </div>

        <!-- สถิติการใช้งาน -->
        <div class="profile-section">
            <h3 class="section-title">📊 สถิติการใช้งาน</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <div class="stat-label">สมาชิกเมื่อ</div>
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($userData['created_at'])); ?></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">🕐</div>
                    <div class="stat-content">
                        <div class="stat-label">เข้าใช้ล่าสุด</div>
                        <div class="stat-value">
                            <?php echo $userData['last_login'] ? date('d/m/Y H:i', strtotime($userData['last_login'])) : 'ไม่มีข้อมูล'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-label">บันทึกทั้งหมด</div>
                        <div class="stat-value"><?php echo $userStats['total_entries']; ?> รายการ</div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">📈</div>
                    <div class="stat-content">
                        <div class="stat-label">วันที่ใช้งาน</div>
                        <div class="stat-value"><?php echo $userStats['active_days']; ?> วัน</div>
                    </div>
                </div>
                
                <?php if($userStats['first_entry']): ?>
                <div class="stat-item">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <div class="stat-label">บันทึกแรก</div>
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($userStats['first_entry'])); ?></div>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-label">บันทึกล่าสุด</div>
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($userStats['last_entry'])); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- การตั้งค่า -->
        <div class="profile-section">
            <h3 class="section-title">⚙️ การตั้งค่า</h3>
            <div class="settings-list">
                <div class="setting-item">
                    <div class="setting-content">
                        <div class="setting-title">ส่งออกข้อมูล</div>
                        <div class="setting-desc">ดาวน์โหลดบันทึกทั้งหมดของคุณ</div>
                    </div>
                    <button class="btn btn-outline" onclick="exportData()">ส่งออก</button>
                </div>
                
                <div class="setting-item">
                    <div class="setting-content">
                        <div class="setting-title">ลบบัญชี</div>
                        <div class="setting-desc">ลบบัญชีและข้อมูลทั้งหมดอย่างถาวร</div>
                    </div>
                    <button class="btn btn-danger" onclick="deleteAccount()">ลบบัญชี</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    color: var(--accent);
    font-size: 2rem;
    margin: 0;
}

.message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
}

.message.success {
    background: rgba(76, 175, 80, 0.15);
    border: 1px solid rgba(76, 175, 80, 0.3);
    color: #81c784;
}

.message.error {
    background: rgba(244, 67, 54, 0.15);
    border: 1px solid rgba(244, 67, 54, 0.3);
    color: #e57373;
}

.profile-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.profile-section {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
    border: 1px solid rgba(100, 255, 218, 0.08);
    border-radius: var(--radius);
    padding: 2rem;
}

.section-title {
    color: var(--accent);
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    color: var(--muted);
    font-size: 0.9rem;
    font-weight: 500;
}

.form-input {
    padding: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: rgba(12, 12, 12, 0.5);
    color: var(--text);
    font-size: 1rem;
}

.form-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(100, 255, 218, 0.1);
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(45deg, var(--accent), var(--accent-light));
    color: #071018;
}

.btn-secondary {
    background: rgba(100, 255, 218, 0.1);
    border: 1px solid var(--accent);
    color: var(--accent);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--muted);
    color: var(--muted);
}

.btn-danger {
    background: rgba(244, 67, 54, 0.2);
    border: 1px solid #f44336;
    color: #e57373;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.stat-icon {
    font-size: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-weight: 600;
    color: var(--accent);
}

.settings-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.setting-content {
    flex: 1;
}

.setting-title {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.25rem;
}

.setting-desc {
    font-size: 0.9rem;
    color: var(--muted);
}

@media (max-width: 768px) {
    .profile-page {
        padding: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .setting-item {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<script>
function exportData() {
    if(confirm('คุณต้องการส่งออกข้อมูลทั้งหมดหรือไม่?')) {
        // TODO: สร้างฟีเจอร์ส่งออกข้อมูล
        alert('ฟีเจอร์ส่งออกข้อมูลจะพัฒนาในอนาคต');
    }
}

function deleteAccount() {
    if(confirm('คุณแน่ใจหรือไม่ที่จะลบบัญชี? การกระทำนี้ไม่สามารถยกเลิกได้!')) {
        if(confirm('กรุณายืนยันอีกครั้ง ข้อมูลทั้งหมดจะถูกลบอย่างถาวร!')) {
            // TODO: สร้างฟีเจอร์ลบบัญชี
            alert('ฟีเจอร์ลบบัญชีจะพัฒนาในอนาคต');
        }
    }
}

// Auto-hide messages
const message = document.querySelector('.message');
if(message) {
    setTimeout(() => {
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 300);
    }, 5000);
}
</script>