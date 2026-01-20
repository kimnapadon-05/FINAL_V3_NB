<?php 
// 1. ตั้งค่าการแสดง Error
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php'; 
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. รับค่าตัวแปร (ตรวจสอบให้ชื่อตรงกับ name ในฟอร์ม)
    $reporter_name  = $_POST['reporter_name'] ?? '';
    $reporter_id    = mysqli_real_escape_string($conn, $_POST['reporter_id']);
    $reporter_phone = $_POST['reporter_phone'] ?? '';
    $reporter_email = $_POST['email'] ?? ''; 
    $scanned_asset_id = $_POST['scanned_asset_id'] ?? ''; // รับจาก hidden input

    $device_serial  = mysqli_real_escape_string($conn, $_POST['device_serial']);
    $device_name    = mysqli_real_escape_string($conn, $_POST['device_name']);
    $device_type    = $_POST['device_type'] ?? '';
    $device_model   = mysqli_real_escape_string($conn, $_POST['device_model'] ?? '');

    $building       = $_POST['building'] ?? '';
    $room           = $_POST['room'] ?? '-'; 
    $problem_detail = $_POST['problem_detail'] ?? '';

    $tracking_id = "REP-" . date("Ymd") . "-" . rand(100, 999);
    $initial_status = "รอรับเรื่อง"; // หรือ 'pending' ตามที่พี่ถนัด

    // 3. จัดการรูปภาพ
    $image_path = "";
    if (isset($_FILES['repair_image']) && $_FILES['repair_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $ext = pathinfo($_FILES["repair_image"]["name"], PATHINFO_EXTENSION);
        $new_name = $tracking_id . "." . $ext;
        if (move_uploaded_file($_FILES["repair_image"]["tmp_name"], $target_dir . $new_name)) {
            $image_path = $target_dir . $new_name;
        }
    }

    // 4. SQL Statement (นับช่องให้เป๊ะ: มี 15 ช่องที่ต้องใช้ ?)
    $sql = "INSERT INTO requests (
        tracking_id, 
        status, 
        reported_by, 
        reporter_id, 
        asset_id, 
        tel, 
        reporter_email, 
        device_type, 
        device_model, 
        serial_no, 
        device_name, 
        building, 
        room, 
        problem_description, 
        img_path, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // s ทั้งหมด 15 ตัว (Created_at ใช้ NOW() ไม่ต้องนับ)
        $stmt->bind_param("sssssssssssssss", 
            $tracking_id,       // 1
            $initial_status,    // 2
            $reporter_name,     // 4
            $reporter_id,       // 3
            $scanned_asset_id,  // 5
            $reporter_phone,    // 6
            $reporter_email,    // 7
            $device_type,       // 8
            $device_model,      // 9
            $device_serial,     // 10
            $device_name,       // 11
            $building,          // 12
            $room,              // 13
            $problem_detail,    // 14
            $image_path         // 15
        );

        if ($stmt->execute()) {
            header("Location: success.php?track_id=" . $tracking_id);
            exit();
        } else {
            die("<h1>❌ บันทึกล้มเหลว</h1><p>SQL Error: " . $stmt->error . "</p>");
        }
    } else {
        die("<h1>❌ เตรียม SQL ล้มเหลว</h1><p>สาเหตุ: " . $conn->error . "</p>");
    }
}   
$conn->close();
?>
