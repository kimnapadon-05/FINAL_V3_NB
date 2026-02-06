<?php
session_start();
// ตรวจสอบ Session (ถ้ามีระบบ Login)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php"); // Uncomment ถ้าต้องการบังคับ Login
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Sidebar (Nav) -->
        <?php include 'Sidebar.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">สร้าง QR Code ครุภัณฑ์</h2>
                <p class="text-muted small">สำหรับติดอุปกรณ์เพื่อแจ้งซ่อม</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small text-nowrap">
                    <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h5 class="section-title text-primary">
                <i class="bi bi-info-circle me-2"></i>ข้อมูลอุปกรณ์
            </h5>
            
            <form action="/Project_Final/admin/generate_qr.php" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">รหัสครุภัณฑ์ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-4"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="asset_id" class="form-control border-start-0" placeholder="เช่น 22005-01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ชื่ออุปกรณ์ <span class="text-danger">*</span></label>
                        <input type="text" name="equipment_name" class="form-control" placeholder="เช่น COM-01 (Computer 01)" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ชื่อรุ่น / Model</label>
                        <input type="text" name="model_name" class="form-control" placeholder="เช่น LENOVO ideapad 330" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ประเภทอุปกรณ์</label>
                        <select name="equipment_type" class="form-select form-control" required>
                            <option value="" disabled selected>เลือกประเภท...</option>
                            <option value="COMPUTER EQUIPMENT">Computer PC / Laptop</option>
                            <option value="ACCESS POINT">Network Device</option>
                            <option value="PROJECTOR">Projector</option>
                            <option value="OTHER">อื่นๆ</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">หมายเลขซีเรียล (S/N)</label>
                        <input type="text" name="serial_no" class="form-control" placeholder="เช่น PW0H8E97" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ตึก <span class="text-danger">*</span></label>
                        <select name="building" id="adminBuildingSelect" class="form-select form-control" required>
                            <option value="" selected disabled>-- เลือกตึก --</option>
                            <option value="ตึก 14">ตึก 14</option>
                            <option value="ตึก 26">ตึก 26</option>
                            <option value="Other">ตึกอื่นๆ</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ห้อง / สถานที่ <span class="text-danger">*</span></label>
                        <select name="room" id="adminRoomSelect" class="form-select form-control" required>
                            <option value="" selected disabled>-- เลือกห้อง --</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">รูปภาพอุปกรณ์</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <div class="form-text text-muted ms-1">รองรับไฟล์ .jpg, .png ขนาดไม่เกิน 5MB</div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-luxury w-100">
                            <i class="bi bi-qr-code me-2"></i>Generate QR Code
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

<script>
    // ข้อมูลห้อง (Copy มาจากหน้า index.php เพื่อให้ข้อมูลตรงกันเป๊ะๆ)
    const roomData = {
        "ตึก 14": ["1411", "1412", "1413", "1414", "1415", "1421", "1422", "1423", "1424", "1425", "1431", "1432", "1433", "1434", "1435", "1441", "1442", "1443", "1444", "1445"],
        "ตึก 26": ["TC201", "TC202", "TC203", "TC204", "TC205"],
        "Other": ["ห้อง IOT", "ห้อง IT 203", "อื่นๆ"]
    };

    const buildingSelect = document.getElementById('adminBuildingSelect');
    const roomSelect = document.getElementById('adminRoomSelect');

    buildingSelect.addEventListener('change', function() {
        const selectedBuilding = this.value;
        roomSelect.innerHTML = '<option value="" selected disabled>-- เลือกห้อง --</option>'; // ล้างค่าเก่า

        if (selectedBuilding && roomData[selectedBuilding]) {
            roomData[selectedBuilding].forEach(room => {
                const option = document.createElement('option');
                option.value = room; // ค่าที่จะส่งไป Gen QR
                option.textContent = room;
                roomSelect.appendChild(option);
            });
        }
    });
</script>