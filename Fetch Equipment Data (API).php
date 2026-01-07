<?php
// เชื่อมต่อฐานข้อมูล
require_once 'db_connect.php';

// ตั้งค่าให้ส่งข้อมูลกลับเป็น JSON
header('Content-Type: application/json');

$asset_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($asset_id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสครุภัณฑ์']);
    exit;
}

// ดึงข้อมูลจากตาราง equipment
$sql = "SELECT * FROM equipment WHERE asset_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $asset_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    // ส่งข้อมูลกลับไปให้ JavaScript
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลอุปกรณ์นี้ในระบบ']);
}

$stmt->close();
$conn->close();
?>