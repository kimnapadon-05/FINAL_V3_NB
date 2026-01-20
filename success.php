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
            // 1. สร้าง URL สำหรับเช็คสถานะ
            const trackingUrl = window.location.origin + window.location.pathname.replace("success.php", "index.php") + "?tracking_id=" + trackingId;

            const qrContainer = document.getElementById("qrcode");
            qrContainer.innerHTML = "";

            // 2. สั่งสร้าง QR Code
            const qrcode = new QRCode(qrContainer, {
                text: trackingUrl,
                width: 256,  
                height: 256,
                colorDark : "#000000", 
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            // 3. ฟังก์ชันดาวน์โหลด (ปรับปรุงใหม่)
            document.getElementById('downloadQrBtn').addEventListener('click', function() {
                // หา Canvas ที่ qrcode.js สร้างขึ้นมา
                const qrCanvas = qrContainer.querySelector('canvas');
                
                if (qrCanvas) {
                    const borderSize = 40; // ขนาดขอบขาว
                    const finalSize = qrCanvas.width + (borderSize * 2);

                    // สร้าง Canvas สำหรับดาวน์โหลด
                    const downloadCanvas = document.createElement('canvas');
                    downloadCanvas.width = finalSize;
                    downloadCanvas.height = finalSize;
                    const ctx = downloadCanvas.getContext('2d');

                    // ระบายพื้นหลังสีขาว (สำคัญมาก)
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, finalSize, finalSize);

                    // วาดรูป QR จาก Canvas ต้นฉบับลงไปตรงกลาง
                    ctx.drawImage(qrCanvas, borderSize, borderSize);

                    // ดาวน์โหลด
                    const link = document.createElement('a');
                    link.href = downloadCanvas.toDataURL("image/png");
                    link.download = `Repair_Status_${trackingId}.png`;
                    link.click();
                } else {
                    // กรณีเครื่องช้าแล้ว Canvas ยังไม่มา ให้ลองดึงจากก img แทน
                    const qrImg = qrContainer.querySelector('img');
                    if (qrImg && qrImg.src) {
                        const link = document.createElement('a');
                        link.href = qrImg.src;
                        link.download = `Repair_Status_${trackingId}.png`;
                        link.click();
                    } else {
                        alert("กรุณารอสักครู่ กำลังเตรียมรูปภาพ...");
                    }
                }
            });
        }
    });
</script>

<?php include 'includes/footer.php'; ?>