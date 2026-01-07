<?php
require_once "db_connect.php";
require_once "phpqrcode/qrlib.php"; // อย่าลืมเช็ค Path นี้ให้ถูกนะครับ

if (!isset($_POST['selected_ids']) || empty($_POST['selected_ids'])) {
    die("<h3 style='text-align:center; margin-top:50px;'>⚠️ กรุณาเลือกรายการอย่างน้อย 1 รายการ</h3>");
}

$selected_ids = $_POST['selected_ids'];
$ids_string = "'" . implode("','", $selected_ids) . "'";
$sql = "SELECT * FROM equipment WHERE asset_id IN ($ids_string)";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Assets</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@500;700&family=Sarabun:wght@600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Sarabun', sans-serif; background-color: #555; margin: 0; padding: 20px; display: flex; justify-content: center; }
        .a4-page { width: 210mm; min-height: 297mm; background: white; padding: 10mm; display: grid; grid-template-columns: 1fr 1fr; grid-auto-rows: min-content; gap: 5mm; align-content: start; }
        .sticker-strip { width: 100%; height: 20mm; background-color: #FFEA00; border: 1px solid #d4c600; display: flex; align-items: center; justify-content: space-between; padding: 0 2mm; box-sizing: border-box; border-radius: 2px; page-break-inside: avoid; }
        .qr-box { width: 16mm; height: 16mm; background: #fff; display: flex; align-items: center; justify-content: center; border: 1px solid #000; }
        .qr-box img { width: 90%; height: 90%; object-fit: contain; }
        .info-box { flex-grow: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; overflow: hidden; }
        .asset-id { font-family: 'Roboto Mono', monospace; font-size: 14pt; font-weight: 700; letter-spacing: 0.5px; }
        .dept-text { font-size: 8pt; font-weight: 800; text-transform: uppercase; }
        @media print { body { background: none; padding: 0; } .no-print { display: none !important; } * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 20px; left: 20px; z-index:99;">
        <button onclick="window.print()" class="btn btn-dark btn-lg shadow">🖨️ สั่งพิมพ์</button>
    </div>

    <div class="a4-page">
        <?php 
        $printed_ids = array();

        if ($result->num_rows > 0): 
            while($row = $result->fetch_assoc()):
                if (in_array($row['asset_id'], $printed_ids)) continue;
                $printed_ids[] = $row['asset_id'];

                // --- 🛠️ ระบบอัจฉริยะ: ตามหาไฟล์ หรือ สร้างใหม่ ---
                
                // 1. สร้างชื่อไฟล์มาตรฐานที่ "ควรจะเป็น" (QR_AssetID.png)
                $safe_asset_id = preg_replace('/[^A-Za-z0-9\-]/', '_', $row['asset_id']);
                $expectedPath = "qrcodes/QR_" . $safe_asset_id . ".png";
                
                $finalQrPath = "";

                // 2. เช็คว่ามีไฟล์นี้อยู่จริงไหม?
                if (file_exists($expectedPath)) {
                    // เย้! เจอไฟล์เก่า ใช้ได้เลย ไม่ต้องสร้างใหม่
                    $finalQrPath = $expectedPath;
                } 
                else {
                    // 3. ถ้าไม่เจอ (หรือเป็นไฟล์ชื่อเก่าที่หาไม่เจอ) -> สร้างใหม่เดี๋ยวนั้นเลย
                    $qrDir = "qrcodes/";
                    if (!is_dir($qrDir)) mkdir($qrDir);
                    
                    $qrData = "IT|" . $row['asset_id'] . "|" . $row['equipment_type'] . "|" . $row['serial_no'] . "|" . $row['equipment_name'] . "|" . $row['location'];
                    
                    QRcode::png($qrData, $expectedPath, QR_ECLEVEL_L, 4);
                    $finalQrPath = $expectedPath; // ใช้ไฟล์ใหม่ที่เพิ่งสร้าง
                }
                
                // 4. (Optional) อัปเดต Database ให้ตรงกันเป๊ะๆ เพื่อความชัวร์ในอนาคต
                if ($row['qr_path'] != $finalQrPath) {
                    $conn->query("UPDATE equipment SET qr_path = '$finalQrPath' WHERE id = " . $row['id']);
                }
                // ------------------------------------------------
        ?>
            <div class="sticker-strip">
                <div class="qr-box"><img src="<?php echo $finalQrPath; ?>" alt="QR"></div>
                <div class="info-box">
                    <div class="asset-id"><?php echo $row['asset_id']; ?></div>
                    <div class="dept-text">IT DEPARTMENT</div>
                </div>
                <div class="qr-box"><img src="<?php echo $finalQrPath; ?>" alt="QR"></div>
            </div>
        <?php 
            endwhile;
        endif; 
        ?>
    </div>

</body>
</html>