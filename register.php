<?php
include 'includes/db.php';

$message = '';

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($name) || empty($email) || empty($password)){
        $message = 'กรุณากรอกข้อมูลให้ครบถ้วน!';
    } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $message = 'รูปแบบอีเมลไม่ถูกต้อง!';
    } else if(strlen($password) < 6){
        $message = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร!';
    } else {

        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if($result->num_rows > 0){
            $message = 'อีเมลนี้ถูกใช้งานไปแล้ว!';
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);
            
            if($stmt->execute()){
                $message = 'สมัครสำเร็จ! <a href="login.php">คลิกที่นี่เพื่อเข้าสู่ระบบ</a>';
            } else {
                $message = 'เกิดข้อผิดพลาดในการสมัครสมาชิก กรุณาลองใหม่อีกครั้ง!';
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}
?>


<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <main class="mv-container">
        <section class="auth-card">
            <h2 class="auth-title">สมัครสมาชิก</h2>
            <?php if (!empty($message)): ?>
                <div class="auth-msg"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post" class="auth-form" novalidate>
                <label class="form-label">
                    <span class="label-text">ชื่อ</span>
                    <input name="name" placeholder="ชื่อ" required class="form-input">
                </label>

                <label class="form-label">
                    <span class="label-text">อีเมล</span>
                    <input name="email" placeholder="อีเมล" required type="email" class="form-input">
                </label>

                <label class="form-label">
                    <span class="label-text">รหัสผ่าน</span>
                    <input name="password" type="password" placeholder="รหัสผ่าน" required class="form-input">
                </label>

                <div class="form-actions">
                    <button type="submit" name="register" class="btn btn-primary">สมัคร</button>
                    <a class="btn btn-link" href="login.php">กลับไปหน้าเข้าสู่ระบบ</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
