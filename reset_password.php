<?php
// ไฟล์ช่วยเหลือสำหรับรีเซ็ตรหัสผ่าน
include 'includes/db.php';

echo "<h2>🔧 MindVault Password Reset Tool</h2>";

// สร้างรหัสผ่านใหม่
$newPassword = '123456';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

echo "<p><strong>รหัสผ่านใหม่:</strong> $newPassword</p>";
echo "<p><strong>Hash:</strong> $hashedPassword</p><br>";

// อัพเดทรหัสผ่านสำหรับผู้ใช้ทดสอบ
$emails = ['test@mindvault.com', 'somchai@example.com', 'somying@example.com'];

foreach($emails as $email) {
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if($stmt->execute()) {
        echo "✅ อัพเดทรหัสผ่านสำเร็จสำหรับ: $email<br>";
    } else {
        echo "❌ เกิดข้อผิดพลาดสำหรับ: $email<br>";
    }
    $stmt->close();
}

echo "<br><h3>📋 ขั้นตอนการทดสอบ:</h3>";
echo "<ol>";
echo "<li>กลับไปหน้า <a href='login.php'>login.php</a></li>";
echo "<li>ใช้อีเมล: <code>test@mindvault.com</code></li>";
echo "<li>ใช้รหัสผ่าน: <code>123456</code></li>";
echo "<li>หากยังไม่ได้ ให้ลองผู้ใช้อื่น</li>";
echo "</ol>";

echo "<br><h3>🔍 ตรวจสอบข้อมูลผู้ใช้ในฐานข้อมูล:</h3>";

$result = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY id");
if($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>ชื่อ</th><th>อีเมล</th><th>สร้างเมื่อ</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ ไม่พบผู้ใช้ในฐานข้อมูล</p>";
    echo "<p>กรุณา import ไฟล์ <code>sql/mindvault.sql</code> ก่อน</p>";
}

echo "<br><h3>🧪 ทดสอบการตรวจสอบรหัสผ่าน:</h3>";
$testResult = $conn->query("SELECT email, password FROM users WHERE email = 'test@mindvault.com'");
if($testResult->num_rows > 0) {
    $user = $testResult->fetch_assoc();
    $verify = password_verify('123456', $user['password']);
    
    echo "<p><strong>อีเมล:</strong> " . $user['email'] . "</p>";
    echo "<p><strong>การตรวจสอบรหัสผ่าน:</strong> " . ($verify ? "✅ ถูกต้อง" : "❌ ไม่ถูกต้อง") . "</p>";
} else {
    echo "<p style='color: red;'>❌ ไม่พบผู้ใช้ test@mindvault.com</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
table { border-collapse: collapse; }
th, td { padding: 8px 12px; border: 1px solid #ddd; }
th { background: #f0f0f0; }
</style>