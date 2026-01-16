<?php
// เชื่อมต่อฐานข้อมูล
require_once 'db_connect.php';

// ตั้งค่าให้ส่งข้อมูลกลับเป็น JSON
header('Content-Type: application/json; charset=utf-8');

$asset_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($asset_id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสครุภัณฑ์ที่ส่งมา']);
    exit;
}

// ดึงข้อมูลจากตาราง equipment (ต้องแน่ใจว่าชื่อคอลัมน์ตรงกับ DB จริง)
$sql = "SELECT * FROM equipment WHERE asset_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $asset_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลอุปกรณ์นี้ในระบบ (ID: ' . $asset_id . ')']);
}

$stmt->close();
$conn->close();
?>