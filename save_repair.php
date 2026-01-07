<?php 
// 🚨 ตั้งค่า PHP Error Reporting ให้สูงสุด เพื่อให้เห็นปัญหาทั้งหมด
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบไฟล์เชื่อมต่อ
if (!file_exists('db_connect.php')) {
    die("<h1>❌ Fatal Error: ไม่พบไฟล์ db_connect.php!</h1><p>โปรดตรวจสอบว่าไฟล์เชื่อมต่อฐานข้อมูลมีอยู่จริง</p>");
}
require_once 'db_connect.php'; 

// ตรวจสอบการเชื่อมต่อ
if (empty($conn) || $conn->connect_error) {
    die("<h1>❌ Fatal Error: การเชื่อมต่อฐานข้อมูลล้มเหลว!</h1><p>สาเหตุ: " . $conn->connect_error . "</p>");
}
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // รับค่าตัวแปรจากฟอร์ม
    $reporter_name  = $_POST['reporter_name'] ?? '';
    $reporter_id    = $_POST['reporter_id'] ?? '';
    $reporter_phone = $_POST['reporter_phone'] ?? '';
    $reporter_email = $_POST['email'] ?? ''; 
    $device_type    = $_POST['device_type'] ?? '';
    $building       = $_POST['building'] ?? '';
    $room           = isset($_POST['room']) ? $_POST['room'] : '-'; 
    $problem_detail = $_POST['problem_detail'] ?? '';

    // สร้าง Tracking ID และ Status เริ่มต้น
    $tracking_id = "REP-" . date("Ymd") . "-" . rand(100, 999);
    $initial_status = "รอรับเรื่อง"; // สถานะเริ่มต้น

    // จัดการรูปภาพ (โค้ดสร้างโฟลเดอร์และบันทึกไฟล์)
    $image_path = "";
    if (isset($_FILES['repair_image']) && $_FILES['repair_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // สร้างโฟลเดอร์ uploads/ ถ้ายังไม่มี และให้สิทธิ์ 0777 (สูงสุด) ชั่วคราว
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                error_log("Failed to create uploads directory.");
            }
        }
        
        $ext = pathinfo($_FILES["repair_image"]["name"], PATHINFO_EXTENSION);
        $new_name = $tracking_id . "." . $ext;
        $target_file = $target_dir . $new_name;
        
        if (move_uploaded_file($_FILES["repair_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // บันทึกข้อมูล (ต้องตรงกับโครงสร้างตาราง requests)
    $sql = "INSERT INTO requests (
        tracking_id, status, reported_by, asset_id, tel, reporter_email, 
        device_type, building, room, problem_description, img_path, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // sssssssssss (11 ตัว)
        $stmt->bind_param("sssssssssss", 
            $tracking_id,    
            $initial_status, 
            $reporter_name,  
            $reporter_id,    
            $reporter_phone, 
            $reporter_email, 
            $device_type,    
            $building,       
            $room,           
            $problem_detail, 
            $image_path      
        );

        if ($stmt->execute()) {
            // บันทึกสำเร็จ -> ส่งไปหน้า success
            header("Location: success.php?track_id=" . $tracking_id);
            exit();
        } else {
            // 🚨 แสดง SQL Error ที่ชัดเจนที่สุด
            die("<h1>❌ บันทึกข้อมูลล้มเหลว (SQL Execute Error)</h1><p>สาเหตุ: " . $stmt->error . "</p>");
        }
        $stmt->close();
    } else {
        // 🚨 แสดง Prepare Error ที่ชัดเจนที่สุด
        die("<h1>❌ เตรียมคำสั่ง SQL ล้มเหลว (Prepare Error)</h1><p>สาเหตุ: " . $conn->error . "</p><p>SQL Query ที่ล้มเหลว: " . htmlspecialchars($sql) . "</p>");
    }
    $conn->close();
}
?>