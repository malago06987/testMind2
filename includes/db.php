<?php
/**
 * ⚠️ คำเตือนด้านความปลอดภัย:
 * 
 * สำหรับ Production ควรสร้าง MySQL User เฉพาะสำหรับแอปพลิเคชันนี้
 * และจำกัดสิทธิ์ตามที่จำเป็น ตามขั้นตอนดังนี้:
 * 
 * 1. เข้า phpMyAdmin หรือใช้ MySQL command line
 * 2. รันคำสั่งต่อไปนี้:
 * 
 *    CREATE USER 'mindvault_user'@'localhost' IDENTIFIED BY 'YourStrongPassword123!';
 *    GRANT SELECT, INSERT, UPDATE, DELETE ON mindvault.* TO 'mindvault_user'@'localhost';
 *    FLUSH PRIVILEGES;
 * 
 * 3. แก้ไข $username และ $password ด้านล่างให้เป็นค่าใหม่
 * 
 * ⚠️ อย่าใช้ root user ในระบบ Production!
 */

$servername = "localhost";
$username = "root"; // ⚠️ เปลี่ยนเป็น 'mindvault_user' สำหรับ Production
$password = ""; // ⚠️ เปลี่ยนเป็นรหัสผ่านที่แข็งแกร่งสำหรับ Production
$dbname = "mindvault";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
