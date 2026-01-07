<?php
// db_connect.php
$servername = "localhost"; // หรือ IP เซิร์ฟเวอร์ของคุณ
$username = "root";       // ชื่อผู้ใช้ฐานข้อมูล
$password = "";           // รหัสผ่าน
$dbname = "repair_system"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตั้งค่า charset เป็น utf8
$conn->set_charset("utf8");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
  die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>