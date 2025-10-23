<?php
session_start();
include 'includes/db.php';

$message = '';
$messageType = '';

// ถ้ามี session อยู่แล้วให้ไปหน้าหลัก
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit;
}

// ถ้ามี admin session อยู่แล้วให้ไปหน้า admin
if(isset($_SESSION['admin_id'])){
    header("Location: admin/index.php");
    exit;
}

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ตรวจสอบว่ากรอกครบไหม
    if(empty($email) || empty($password)){
        $message = 'กรุณากรอกอีเมลและรหัสผ่าน!';
        $messageType = 'error';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'รูปแบบอีเมลไม่ถูกต้อง!';
        $messageType = 'error';
    } else {
        // ดึงข้อมูลผู้ใช้จากฐานข้อมูล (รวม role)
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            $user = $result->fetch_assoc();

            // ตรวจสอบรหัสผ่านจากฐานข้อมูล
            $password_valid = false;
            
            // ตรวจสอบรหัสผ่านที่เข้ารหัสแล้ว (password_hash)
            if(password_verify($password, $user['password'])){
                $password_valid = true;
            }
            // ตรวจสอบรหัสผ่านแบบธรรมดา (สำหรับข้อมูลเก่า)
            else if($password === $user['password']){
                $password_valid = true;
            }
            
            if($password_valid){
                // อัพเดทเวลาล็อกอินล่าสุด
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                $updateStmt->close();

                // ตรวจสอบ role และสร้าง session ตาม role
                if($user['role'] === 'admin'){
                    // สร้าง session สำหรับ Admin
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect ไปหน้า Admin Dashboard
                    header("Location: admin/index.php");
                    exit;
                } else {
                    // สร้าง session สำหรับ User ทั่วไป
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['login_time'] = time();

                    // Redirect ไปหน้า User Dashboard
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $message = 'รหัสผ่านไม่ถูกต้อง!';
                $messageType = 'error';
            }
        } else {
            $message = 'ไม่พบบัญชีผู้ใช้นี้!';
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
  <title>เข้าสู่ระบบ - MindVault</title>
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  <main class="mv-container">
    <section class="auth-card">
      <h2 class="auth-title">เข้าสู่ระบบ</h2>

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

        <label class="form-label">
          <span class="label-text">รหัสผ่าน</span>
          <input name="password" type="password" placeholder="กรอกรหัสผ่าน" 
                 required class="form-input" autocomplete="current-password">
        </label>

        <div class="form-actions">
          <button type="submit" name="login" class="btn btn-primary">เข้าสู่ระบบ</button>
          <a class="btn btn-link" href="register.php">สมัครสมาชิก</a>
        </div>
      </form>

      <div class="forgot-password">
        <a href="forgot-password.php">ลืมรหัสผ่าน?</a>
      </div>
    </section>
  </main>
</body>
</html>
