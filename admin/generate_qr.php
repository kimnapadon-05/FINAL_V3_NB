<?php
require_once "../db_connect.php"; // เรียก DB จากโฟลเดอร์หลัก
require_once "../phpqrcode/qrlib.php"; // เรียก QR Lib

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $asset_id = $_POST['asset_id'];
    $equipment_name = $_POST['equipment_name'];
    $model_name = $_POST['model_name']; 
    $equipment_type = $_POST['equipment_type'];
    $serial_no = $_POST['serial_no'];
    $location = $_POST['location'];

    // --- 1. จัดการรูปภาพ (Image) ---
    // เก็บไว้ที่ ../uploads/ (ถอยออกไปที่ Root)
    $targetDir = "uploads/"; 
    if (!is_dir($targetDir)) mkdir($targetDir);
    
    // ตั้งชื่อไฟล์รูปตาม Asset ID (Img_รหัส.jpg)
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $safe_asset_id = preg_replace('/[^A-Za-z0-9\-]/', '_', $asset_id); 
    $newImageName = "Img_" . $safe_asset_id . "." . $imageFileType;
    
    $targetFileUpload = $targetDir . $newImageName; // Path สำหรับอัปโหลด (มี ../)
    $targetFileDB = "uploads/" . $newImageName;     // Path สำหรับลง DB (ไม่มี ../)
    
    move_uploaded_file($_FILES["image"]["tmp_name"], $targetFileUpload);


    // --- 2. จัดการ QR Code ---
    // เก็บไว้ที่ ../qrcodes/ (ถอยออกไปที่ Root)
    $qrDir = "../qrcodes/";
    if (!is_dir($qrDir)) mkdir($qrDir);
    
    // ตั้งชื่อไฟล์ QR ตาม Asset ID (QR_รหัส.png)
    $newQrName = "QR_" . $safe_asset_id . ".png";
    $qrFileUpload = $qrDir . $newQrName;      // Path สำหรับสร้างไฟล์ (มี ../)
    $qrFileDB = "qrcodes/" . $newQrName;      // Path สำหรับลง DB (ไม่มี ../)
    
    // ข้อมูลใน QR
    $qrData = "IT|$asset_id|$equipment_type|$serial_no|$equipment_name|$location";
    
    // สร้าง QR Code
    QRcode::png($qrData, $qrFileUpload, QR_ECLEVEL_L, 4);


    // --- 3. บันทึก/อัปเดต ลงฐานข้อมูล ---
    // เช็คก่อนว่ามี ID นี้อยู่แล้วไหม
    $check = $conn->query("SELECT id FROM equipment WHERE asset_id = '$asset_id'");
    
    if ($check->num_rows > 0) {
        // มีแล้ว -> Update
        $stmt = $conn->prepare("UPDATE equipment SET equipment_name=?, model_name=?, equipment_type=?, serial_no=?, location=?, image_path=?, qr_path=? WHERE asset_id=?");
        $stmt->bind_param("ssssssss", $equipment_name, $model_name, $equipment_type, $serial_no, $location, $targetFileDB, $qrFileDB, $asset_id);
    } else {
        // ยังไม่มี -> Insert
        $stmt = $conn->prepare("INSERT INTO equipment (asset_id, equipment_name, model_name, equipment_type, serial_no, location, image_path, qr_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $asset_id, $equipment_name, $model_name, $equipment_type, $serial_no, $location, $targetFileDB, $qrFileDB);
    }
    
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // --- ส่งไปหน้า Print (แก้ Syntax Error ตรงนี้ให้แล้วครับ) ---
    echo "<script>
        alert('บันทึกและสร้าง QR Code เรียบร้อย!');
        window.location.href='manage_equipment.php?qrcode=" . urlencode($qrFileDB) . "&asset=" . urlencode($asset_id) . "';
    </script>";
}
?>