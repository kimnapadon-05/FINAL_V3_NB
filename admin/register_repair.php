<?php
require_once '../db_connect.php';

// ตั้งค่า Username และ Password ที่ต้องการ
$username = "admin";
$password = "admin123";

// สร้างรหัสผ่านแบบ Hash (ปลอดภัยสูง)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// บันทึกลงฐานข้อมูล
$sql = "INSERT INTO admins (username, password_hash) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $password_hash);

if ($stmt->execute()) {
    echo "<h3>🎉 สร้าง Admin สำเร็จ!</h3>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    echo "Password Hash (ที่เก็บใน DB): " . $password_hash;
    echo "<br><br><a href='login.php'>ไปหน้า Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>