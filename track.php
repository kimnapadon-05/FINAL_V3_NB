<?php 
include 'includes/header.php'; 
if (!file_exists('db_connect.php')) {
    die("<h1>ไม่พบไฟล์เชื่อมต่อฐานข้อมูล</h1>");
}
require_once 'db_connect.php'; 

$tracking_id = isset($_GET['tracking_id']) ? htmlspecialchars($_GET['tracking_id']) : '';
$repair_data = null;
$error_msg = "";

if ($tracking_id) {
    $sql = "SELECT * FROM requests WHERE tracking_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $tracking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $repair_data = $result->fetch_assoc();
        } else {
            $error_msg = "ไม่พบข้อมูลงานซ่อมสำหรับรหัสนี้";
        }
        $stmt->close();
    }
} else {
    $error_msg = "ไม่ระบุรหัสติดตามงาน";
}
?>

<div class="row justify-content-center py-4">
    <div class="col-lg-7">
        <div class="card p-4 p-md-5 shadow-lg border-0 rounded-4">
            
            <?php if ($repair_data): ?>
                <!-- ส่วนหัวแสดงรหัส -->
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h2 class="h4 mb-0 fw-bold">สถานะงานซ่อม</h2>
                    <span class="badge bg-light text-dark border"><?php echo $repair_data['tracking_id']; ?></span>
                </div>
                
                <!-- ส่วนแสดงสถานะปัจจุบัน (Badge ใหญ่) -->
                <div class="mb-5 text-center">
                    <?php 
                        $status = $repair_data['status'];
                        $status_color = 'secondary'; // สีเทา (ค่าเริ่มต้น)
                        $icon = 'bi-hourglass-split';

                        if ($status == 'เสร็จสิ้น') {
                            $status_color = 'success'; // สีเขียว
                            $icon = 'bi-check-circle-fill';
                        } else if ($status == 'กำลังซ่อม') {
                            $status_color = 'warning text-dark'; // สีเหลือง
                            $icon = 'bi-tools';
                        } else if ($status == 'รอรับเรื่อง') {
                            $status_color = 'info text-dark'; // สีฟ้า
                            $icon = 'bi-clock-history';
                        }
                    ?>
                    
                    <div class="display-1 text-<?php echo str_replace(' text-dark', '', $status_color); ?> mb-3">
                        <i class="bi <?php echo $icon; ?>"></i>
                    </div>
                    
                    <h1 class="display-6 fw-bold text-<?php echo str_replace(' text-dark', '', $status_color); ?>">
                        <?php echo $status; ?>
                    </h1>
                    <p class="text-muted">สถานะปัจจุบัน</p>
                </div>

                <!-- Timeline แสดงขั้นตอน -->
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark mb-4">ลำดับการดำเนินงาน</h5>
                        
                        <div class="position-relative ps-4 border-start border-3 border-secondary">
                            
                            <!-- ขั้นตอนที่ 1: รอรับเรื่อง (เสมอ) -->
                            <div class="mb-4 position-relative">
                                
                                <p class="mb-0 fw-bold text-success">1. รอรับเรื่อง</p>
                                <small class="text-muted">รับเรื่องเข้าระบบเมื่อ <?php echo date('d/m/Y H:i', strtotime($repair_data['created_at'])); ?></small>
                            </div>

                            <!-- ขั้นตอนที่ 2: กำลังซ่อม -->
                            <div class="mb-4 position-relative">
                                <?php 
                                    $step2_active = ($status == 'กำลังซ่อม' || $status == 'เสร็จสิ้น');
                                    $step2_color = $step2_active ? 'text-warning' : 'text-muted';
                                    $step2_dot = $step2_active ? 'bg-warning' : 'bg-secondary';
                                ?>
                                <div class="position-absolute top-0 start-0 translate-middle-x <?php echo $step2_dot; ?> rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                                <p class="mb-0 fw-bold <?php echo $step2_color; ?>">2. กำลังซ่อม</p>
                                <small class="text-muted">เจ้าหน้าที่กำลังตรวจสอบและแก้ไข</small>
                            </div>

                            <!-- ขั้นตอนที่ 3: เสร็จสิ้น -->
                            <div class="position-relative">
                                <?php 
                                    $step3_active = ($status == 'เสร็จสิ้น');
                                    $step3_color = $step3_active ? 'text-success' : 'text-muted';
                                    $step3_dot = $step3_active ? 'bg-success' : 'bg-secondary';
                                ?>
                                <div class="position-absolute top-0 start-0 translate-middle-x <?php echo $step3_dot; ?> rounded-circle" style="width: 12px; height: 12px; left: -2px;"></div>
                                <p class="mb-0 fw-bold <?php echo $step3_color; ?>">3. เสร็จสิ้น</p>
                                <small class="text-muted">การซ่อมเสร็จสมบูรณ์</small>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- รายละเอียดงานซ่อม (เหมือนเดิม) -->
                <div class="mb-4">
                    <h5 class="text-secondary border-bottom pb-2">รายละเอียด</h5>
                    <div class="row g-2 small mt-2">
                        <div class="col-sm-4 text-muted">ผู้แจ้ง:</div>
                        <div class="col-sm-8"><?php echo $repair_data['reported_by']; ?></div>
                        <div class="col-sm-4 text-muted">สถานที่:</div>
                        <div class="col-sm-8"><?php echo $repair_data['building'] . " " . $repair_data['room']; ?></div>
                        <div class="col-sm-4 text-muted">ปัญหา:</div>
                        <div class="col-sm-8"><?php echo $repair_data['problem_description']; ?></div>
                    </div>
                </div>

                <?php if (!empty($repair_data['img_path'])): ?>
                    <div class="text-center mt-3">
                        <img src="<?php echo $repair_data['img_path']; ?>" class="img-fluid rounded shadow-sm" style="max-height: 250px;" alt="รูปภาพประกอบ">
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle text-danger display-1"></i>
                    <h3 class="mt-3 text-danger">ไม่พบข้อมูล</h3>
                    <p class="text-muted"><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 pt-3 border-top">
                 <a href="index.php" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="bi bi-house me-2"></i> หน้าแรก
                </a>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>