<?php
include 'includes/db.php';

$message = '';
$messageType = '';

if(isset($_POST['reset'])){
    $email = trim($_POST['email']);
    
    if(empty($email)){
        $message = 'กรุณากรอกอีเมล!';
        $messageType = 'error';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'รูปแบบอีเมลไม่ถูกต้อง!';
        $messageType = 'error';
    } else {
        // ตรวจสอบว่าอีเมลมีในระบบหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            // ในระบบจริงจะส่งอีเมลรีเซ็ตรหัสผ่าน
            $message = 'เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของคุณแล้ว (ฟีเจอร์นี้ยังไม่พร้อมใช้งาน)';
            $messageType = 'success';
        } else {
            $message = 'ไม่พบบัญชีผู้ใช้ที่ใช้อีเมลนี้!';
            $messageType = 'error';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ลืมรหัสผ่าน - MindVault</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  <main class="mv-container">
    <section class="auth-card">
      <h2 class="auth-title">ลืมรหัสผ่าน</h2>
      
      <p style="color: var(--muted); text-align: center; margin-bottom: 1.5rem; font-size: 0.9rem;">
        กรอกอีเมลของคุณ เราจะส่งลิงก์สำหรับรีเซ็ตรหัสผ่านให้
      </p>

      <?php if (!empty($message)): ?>
        <div class="auth-msg <?php echo $messageType === 'success' ? 'success' : ''; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="post" class="auth-form" novalidate>
        <label class="form-label">
          <span class="label-text">อีเมล</span>
          <input name="email" type="email" placeholder="กรอกอีเมลของคุณ" 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                 required class="form-input" autocomplete="email">
        </label>

        <div class="form-actions">
          <button type="submit" name="reset" class="btn btn-primary">ส่งลิงก์รีเซ็ต</button>
          <a class="btn btn-link" href="login.php">กลับไปเข้าสู่ระบบ</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>