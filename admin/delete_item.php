<?php
require_once "../db_connect.php";

if (isset($_GET['id'])) {
    $asset_id = $_GET['id'];

    // 1. ดึงข้อมูลไฟล์รูปและ QR ออกมาก่อน เพื่อจะตามไปลบทิ้ง (จะได้ไม่รก Server)
    $stmt_get = $conn->prepare("SELECT image_path, qr_path FROM equipment WHERE asset_id = ?");
    $stmt_get->bind_param("s", $asset_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // ลบรูปอุปกรณ์
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']); 
        }
        // ลบรูป QR
        if (file_exists($row['qr_path'])) {
            unlink($row['qr_path']);
        }
    }
    $stmt_get->close();

    // 2. ลบข้อมูลใน Database
    $stmt_del = $conn->prepare("DELETE FROM equipment WHERE asset_id = ?");
    $stmt_del->bind_param("s", $asset_id);
    
    if ($stmt_del->execute()) {
        // ลบสำเร็จ ให้เด้งกลับไปหน้ารายการ
        echo "<script>
            alert('ลบข้อมูลเรียบร้อยแล้ว');
            window.location.href = 'manage_equipment.php';
        </script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt_del->close();
}

$conn->close();
?>