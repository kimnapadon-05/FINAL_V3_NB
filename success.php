<?php include 'includes/header.php'; ?>
<!-- เรียกใช้ไลบรารี qrcode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<?php
// ตรวจสอบว่ามีรหัสติดตามส่งมาหรือไม่
$tracking_id = isset($_GET['track_id']) ? htmlspecialchars($_GET['track_id']) : null;
?>

<div class="row justify-content-center py-4">
    <div class="col-lg-8">
        <div class="card p-4 p-md-5 shadow-lg border-0 rounded-4 text-center">
            
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h2 class="h3 fw-bold mt-3 text-success">แจ้งซ่อมสำเร็จเรียบร้อย!</h2>
            <p class="text-muted">โปรดบันทึก QR Code นี้ไว้ เพื่อใช้ติดตามสถานะ</p>

            <?php if ($tracking_id): ?>
                
                <hr class="my-4">
                
                <h3 class="h5 fw-bold mb-3 text-primary">สแกน QR Code เพื่อติดตามสถานะ</h3>
                
                <div class="d-flex justify-content-center">
                    <!-- ส่วนที่จะแสดง QR Code -->
                    <div id="qrcode" class="p-4 border rounded-3 shadow-sm bg-white"></div>
                </div>
                
                <!-- ข้อความกำกับ -->
                <div class="mt-3">
                    <p class="text-dark fw-medium mb-1">ใช้สำหรับติดตามสถานะการซ่อม</p>
                    <small class="text-muted">รหัสอ้างอิง: <?php echo $tracking_id; ?></small>
                </div>

                <hr class="my-4">
                
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <!-- ปุ่มดาวน์โหลด -->
                    <button type="button" id="downloadQrBtn" class="btn btn-success px-4">
                        <i class="bi bi-download me-2"></i> บันทึกรูปภาพ
                    </button>

                    <a href="index.php" class="btn btn-outline-primary px-4">
                        <i class="bi bi-arrow-left me-2"></i> กลับหน้าแจ้งซ่อม
                    </a>
                </div>

            <?php else: ?>
                <div class="alert alert-danger mt-4">
                    ไม่พบรหัสติดตามงาน กรุณาแจ้งซ่อมใหม่อีกครั้ง
                </div>
                <a href="index.php" class="btn btn-primary mt-3">กลับหน้าแจ้งซ่อม</a>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trackingId = "<?php echo $tracking_id; ?>";
        
        if (trackingId) {
            // สร้าง URL ที่ถูกต้อง (Absolute URL)
            // ตรวจสอบ path ให้แน่ใจว่า /track.php ถูกต้อง
            const trackingUrl = window.location.origin + window.location.pathname.replace("success.php", "track.php") + "?tracking_id=" + trackingId;

            // สร้าง QR Code
            const qrContainer = document.getElementById("qrcode");
            
            // เคลียร์ QR Code เก่า (ถ้ามี)
            qrContainer.innerHTML = "";

            new QRCode(qrContainer, {
                text: trackingUrl,
                width: 250,  
                height: 250,
                colorDark : "#000000", 
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H, // ความละเอียดของ QR Code
                margin: 4
            });
            
            // Debug URL ใน Console
            console.log("QR Data:", trackingUrl);

            // ฟังก์ชันดาวน์โหลดรูปภาพ
            // --- ฟังก์ชันดาวน์โหลดแบบมีกรอบขาว (ใหม่) ---
            document.getElementById('downloadQrBtn').addEventListener('click', function() {
                setTimeout(() => {
                    // หา element ที่เก็บรูป QR Code (อาจเป็น img หรือ canvas)
                    const qrElement = qrContainer.querySelector('img') || qrContainer.querySelector('canvas');
                    
                    if (qrElement) {
                        // กำหนดขนาดกรอบและขนาดรูป
                        const borderSize = 20; // ขนาดขอบขาว (พิกเซล)
                        const qrSize = 250;    // ขนาด QR Code
                        const finalSize = qrSize + (borderSize * 2); // ขนาดภาพสุดท้าย

                        // สร้าง Canvas ใหม่ในหน่วยความจำ
                        const canvas = document.createElement('canvas');
                        canvas.width = finalSize;
                        canvas.height = finalSize;
                        const ctx = canvas.getContext('2d');

                        // เทสีขาวลงไปให้เต็มเป็นพื้นหลัง (ทำกรอบ)
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, finalSize, finalSize);

                        // วาด QR Code ทับลงไปตรงกลาง
                        // drawImage(source, x, y, width, height)
                        ctx.drawImage(qrElement, borderSize, borderSize, qrSize, qrSize);

                        // บันทึกภาพจาก Canvas ใหม่
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL("image/png");
                        link.download = `Repair_QR_Bordered_${trackingId}.png`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                    } else {
                        alert("กำลังสร้าง QR Code... กรุณาลองใหม่อีกครั้ง");
                    }
                }, 100);
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>