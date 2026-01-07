<?php
// เชื่อมต่อฐานข้อมูล
require_once 'db_connect.php';

// ฟังก์ชันแปลงวันที่เป็นไทย (แบบย่อ)
function thai_date($strDate) {
    if (!$strDate) return "-";
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strDay $strMonthThai $strYear เวลา $strHour:$strMinute น.";
}

$tracking_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($tracking_id)) {
    echo '<div class="alert alert-danger text-center">ไม่พบรหัสติดตามงาน</div>';
    exit;
}

$sql = "SELECT * FROM requests WHERE tracking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tracking_id);
$stmt->execute();
$result = $stmt->get_result();
$repair_data = $result->fetch_assoc();

if (!$repair_data) {
    echo '<div class="alert alert-warning text-center p-4">
            <h4 class="mt-3">ไม่พบข้อมูล</h4>
            <button onclick="resetScanner()" class="btn btn-outline-secondary mt-3">ลองใหม่</button>
          </div>';
    exit;
}

$status = $repair_data['status'];

// แปลงวันที่เป็นไทย
$created_time = thai_date($repair_data['created_at']);
$updated_time = ($repair_data['updated_at']) ? thai_date($repair_data['updated_at']) : '-';
$start_time   = isset($repair_data['repair_started_at']) ? thai_date($repair_data['repair_started_at']) : null;
$end_time     = isset($repair_data['completed_at']) ? thai_date($repair_data['completed_at']) : null;

// กำหนดสี
$icon = 'bi-clock-history';
$status_text_color = 'text-info';

if ($status == 'กำลังซ่อม') {
    $icon = 'bi-tools';
    $status_text_color = 'text-warning';
} elseif ($status == 'เสร็จสิ้น') {
    $icon = 'bi-check-circle-fill';
    $status_text_color = 'text-success';
} elseif ($status == 'ยกเลิก') {
    $icon = 'bi-x-circle-fill';
    $status_text_color = 'text-danger';
}
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <h3 class="h5 fw-bold mb-0">สถานะงานซ่อม</h3>
            <span class="badge bg-light text-dark border"><?php echo $repair_data['tracking_id']; ?></span>
        </div>

        <!-- Status Icon Big -->
        <div class="text-center mb-4">
            <div class="display-3 <?php echo $status_text_color; ?> mb-2">
                <i class="bi <?php echo $icon; ?>"></i>
            </div>
            <h2 class="h3 fw-bold <?php echo $status_text_color; ?>">
                <?php echo $status; ?>
            </h2>
            <!-- ปรับให้ตัวใหญ่ขึ้นเล็กน้อยและสีเข้มขึ้น -->
            <p class="text-secondary mb-0" style="font-size: 0.9rem;">
                อัปเดตล่าสุด: <?php echo ($status == 'รอรับเรื่อง') ? $created_time : $updated_time; ?>
            </p>
        </div>

        <!-- Timeline -->
        <div class="bg-light rounded-3 p-3 mb-4">
            <h6 class="fw-bold mb-3 text-dark">ไทม์ไลน์</h6>
            <div class="ps-2">
                
                <!-- 1. รอรับเรื่อง -->
                <div class="mb-3">
                    <div class="fw-bold text-success mb-1">
                        <i class="bi bi-1-circle-fill me-2"></i> รอรับเรื่อง
                    </div>
                    <!-- เอา ms-4 ออก และใช้ ps-4 แทน เพื่อให้จัดหน้าดีขึ้นในมือถือ -->
                    <div class="ps-4 text-secondary" style="font-size: 0.85rem;">
                        <div>รับเรื่องเข้าระบบเมื่อ:</div>
                        <div class="text-dark"><?php echo $created_time; ?></div>
                    </div>
                </div>

                <!-- 2. กำลังซ่อม -->
                <div class="mb-3">
                    <?php 
                        $step2 = ($status == 'กำลังซ่อม' || $status == 'เสร็จสิ้น');
                        $step2_cls = $step2 ? 'text-warning' : 'text-secondary'; // เปลี่ยน text-muted เป็น text-secondary
                        $step2_icon = $step2 ? 'bi-2-circle-fill' : 'bi-2-circle';
                        
                        $time_show_repair = ($status == 'กำลังซ่อม' && $start_time) ? "เริ่มเมื่อ: $start_time" : "รอเจ้าหน้าที่ดำเนินการ";
                        if ($status == 'เสร็จสิ้น') $time_show_repair = "ดำเนินการเรียบร้อยแล้ว"; 
                    ?>
                    <div class="fw-bold <?php echo $step2_cls; ?> mb-1">
                        <i class="bi <?php echo $step2_icon; ?> me-2"></i> กำลังซ่อม
                    </div>
                    <div class="ps-4 text-secondary" style="font-size: 0.85rem;">
                        <?php echo $time_show_repair; ?>
                    </div>
                </div>

                <!-- 3. เสร็จสิ้น -->
                <div>
                    <?php 
                        $step3 = ($status == 'เสร็จสิ้น');
                        $step3_cls = $step3 ? 'text-success' : 'text-secondary';
                        $step3_icon = $step3 ? 'bi-3-circle-fill' : 'bi-3-circle';
                        
                        $time_done = ($status == 'เสร็จสิ้น' && $end_time) ? "เสร็จสิ้นเมื่อ: $end_time" : "ยังไม่เสร็จสิ้น";
                    ?>
                    <div class="fw-bold <?php echo $step3_cls; ?> mb-1">
                        <i class="bi <?php echo $step3_icon; ?> me-2"></i> เสร็จสิ้น
                    </div>
                    <div class="ps-4 text-secondary" style="font-size: 0.85rem;">
                        <?php echo $time_done; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายละเอียด (ใช้ Grid เพื่อจัดระเบียบในมือถือ) -->
        <h6 class="fw-bold mb-3 border-bottom pb-2">รายละเอียด</h6>
        <div class="row g-2 small mb-4">
            <div class="col-4 text-secondary">ผู้แจ้ง:</div>
            <div class="col-8 fw-medium text-dark"><?php echo $repair_data['reported_by']; ?></div>
            
            <div class="col-4 text-secondary">สถานที่:</div>
            <div class="col-8 fw-medium text-dark"><?php echo $repair_data['building'] . ' ' . $repair_data['room']; ?></div>
            
            <div class="col-4 text-secondary">ปัญหา:</div>
            <div class="col-8 fw-medium text-dark"><?php echo nl2br($repair_data['problem_description']); ?></div>
        </div>

        <?php if (!empty($repair_data['img_path'])): ?>
            <div class="text-center mb-4">
                <img src="<?php echo $repair_data['img_path']; ?>" class="img-fluid rounded-3 shadow-sm" style="max-height: 250px; width: 100%; object-fit: cover;" alt="ภาพประกอบ">
            </div>
        <?php endif; ?>

        <!--div class="text-center">
            <button onclick="resetScanner()" class="btn btn-outline-secondary px-4 rounded-pill">
                <i class="bi bi-house me-2"></i> หน้าแรก
            </button>
        </div>
        -->
    </div>
</div>