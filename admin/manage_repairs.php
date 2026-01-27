<?php
session_start();
// ตรวจสอบสิทธิ์
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

include '../db_connect.php';

// --- เรียกใช้ PHPMailer ---
// ตรวจสอบ Path ให้ถูกต้อง (สมมติว่าคุณเก็บไว้ใน includes/PHPMailer/src/)
require '../includes/PHPMailer/src/Exception.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- ฟังก์ชันส่งอีเมลด้วย PHPMailer ---
function sendEmailNotification($to, $name, $tracking_id, $device) {
    $mail = new PHPMailer(true);

    try {
        // ตั้งค่า Server (SMTP)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // ใช้ Gmail SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = '67319090008@lbtech.ac.th'; // 📧 ใส่อีเมลของคุณ
        $mail->Password   = 'tkjb xped lwop vuzi'; // 🔑 ใส่ App Password 16 หลัก
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // หรือ ENCRYPTION_SMTPS
        $mail->Port       = 587; // หรือ 465

        // ตั้งค่าผู้รับ-ผู้ส่ง
        $mail->setFrom('67319090008@lbtech.ac.th', 'IT Service Support');
        $mail->addAddress($to, $name); // ผู้รับ

        // ตั้งค่าเนื้อหา (รองรับภาษาไทย)
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = "แจ้งสถานะการซ่อม: เสร็จสิ้นเรียบร้อยแล้ว ($tracking_id)";
        
        $bodyContent = "
        <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 600px;'>
            <h2 style='color: #28a745;'>งานซ่อมเสร็จสิ้นแล้ว ✅</h2>
            <p>เรียนคุณ <strong>$name</strong>,</p>
            <p>งานแจ้งซ่อมของคุณ รหัส: <strong>$tracking_id</strong></p>
            <p>อุปกรณ์: <strong>$device</strong></p>
            <hr>
            <p>เจ้าหน้าที่ได้ดำเนินการตรวจสอบและแก้ไขเรียบร้อยแล้ว สถานะปัจจุบันคือ <strong style='color:green'>เสร็จสิ้น</strong></p>
            <p>คุณสามารถติดต่อรับอุปกรณ์คืนได้ที่แผนก IT ครับ</p>
            <br>
            <small style='color: #999;'>อีเมลนี้เป็นการแจ้งเตือนอัตโนมัติ กรุณาอย่าตอบกลับ</small>
        </div>
        ";
        $mail->Body = $bodyContent;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // บันทึก Error ไว้ดู (ไม่แสดงหน้าเว็บเพื่อความสวยงาม)
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// --- Logic Update Status ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['update_status'];
    $req_id = $_POST['request_id'];
    
    $sql = "UPDATE requests SET status = ?, updated_at = NOW()";
    
    if ($new_status == 'กำลังซ่อม') {
        $sql .= ", repair_started_at = NOW()";
    } elseif ($new_status == 'เสร็จสิ้น') {
        $sql .= ", completed_at = NOW()";
    }
    
    $sql .= " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $req_id);
    
    if ($stmt->execute()) {
        
        // ถ้าสถานะเป็น "เสร็จสิ้น" -> เรียกฟังก์ชันส่งอีเมล
        if ($new_status == 'เสร็จสิ้น') {
            $sql_user = "SELECT reporter_email, reported_by, tracking_id, device_type FROM requests WHERE id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $req_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            $user_data = $result_user->fetch_assoc();
            
            if ($user_data && !empty($user_data['reporter_email'])) {
                // เรียกใช้ฟังก์ชัน PHPMailer ด้านบน
                sendEmailNotification(
                    $user_data['reporter_email'],
                    $user_data['reported_by'],
                    $user_data['tracking_id'],
                    $user_data['device_type']
                );
            }
            $stmt_user->close();
        }

        header("Location: manage_repairs.php");
        exit();
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

$sql = "SELECT * FROM requests ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Manage Repairs</title>
    <!-- ... (CSS เดิมของคุณ) ... -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f3f4f6; }
        .main-content { margin-left: 280px; padding: 2rem; min-height: 100vh; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
        .table-card { background: #ffffff; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .status-pill { padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 500; display: inline-block; min-width: 100px; text-align: center; }
        
        .info-label { font-size: 0.85rem; color: #94a3b8; margin-bottom: 2px; }
        .info-value { font-weight: 500; color: #334155; font-size: 0.95rem; }
        .info-group { margin-bottom: 1rem; }
        .section-header { font-size: 0.9rem; font-weight: 600; color: #4e54c8; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">จัดการรายการแจ้งซ่อม</h2>
                <p class="text-muted small">Manage Repair Requests</p>
            </div>
            <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small">
                <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
            </div>
        </div>

        <div class="table-card">
            <table id="manageTable" class="table table-hover align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th width="15%">รหัสงาน</th>
                        <th width="20%">ผู้แจ้ง</th>
                        <th width="20%">ปัญหา</th>
                        <th width="15%">วันที่แจ้ง</th> <th width="15%">สถานะ</th>
                        <th width="10%" class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="fw-bold text-primary"><?php echo $row['tracking_id']; ?></span></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo $row['reported_by']; ?></div>
                            <div class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo $row['building'] . " / " . $row['room']; ?></div>
                        </td>
                        <td><span class="text-secondary text-truncate d-inline-block" style="max-width: 180px;"><?php echo $row['problem_description']; ?></span></td>
                        <td>
                            <div class="fw-medium text-dark"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></div>
                            <div class="text-muted small"><i class="bi bi-clock me-1"></i><?php echo date('H:i', strtotime($row['created_at'])); ?> น.</div>
                        </td>
                        <td>
                            <?php 
                                $s = $row['status'];
                                $c = 'bg-secondary';
                                if ($s == 'เสร็จสิ้น') $c = 'bg-success';
                                elseif ($s == 'กำลังซ่อม') $c = 'bg-primary';
                                elseif ($s == 'รอรับเรื่อง') $c = 'bg-warning text-dark';
                                elseif ($s == 'ยกเลิก') $c = 'bg-danger';
                            ?>
                            <span class="status-pill shadow-sm text-white <?php echo $c; ?>">
                                <?php echo $s; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-light btn-sm text-primary border shadow-sm btn-view-case" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-tracking="<?php echo $row['tracking_id']; ?>"
                                    data-reporter="<?php echo $row['reported_by']; ?>"
                                    data-reporter-id="<?php echo isset($row['asset_id']) ? $row['asset_id'] : '-'; ?>" 
                                    data-tel="<?php echo isset($row['tel']) ? $row['tel'] : '-'; ?>"
                                    data-email="<?php echo isset($row['reporter_email']) ? $row['reporter_email'] : '-'; ?>"
                                    data-location="<?php echo $row['building'] . ' ' . $row['room']; ?>"
                                    data-device-type="<?php echo $row['device_type']; ?>"
                                    data-device-model="<?php echo isset($row['device_model']) ? $row['device_model'] : '-'; ?>"
                                    data-equip-id="<?php echo isset($row['equipment_id']) ? $row['equipment_id'] : '-'; ?>"
                                    data-problem="<?php echo $row['problem_description']; ?>"
                                    data-date="<?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>"
                                    data-image="<?php echo $row['img_path']; ?>"
                                    data-status="<?php echo $row['status']; ?>">
                                <i class="bi bi-search"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal จัดการงานซ่อม (เหมือนเดิม) -->
    <div class="modal fade" id="repairModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                <div class="modal-header bg-light border-bottom-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>รายละเอียดงานซ่อม
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-10 font-monospace" id="modalTrackingId">...</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <!-- ฝั่งซ้าย: ข้อมูล -->
                        <div class="col-lg-7 p-4">
                            <!-- 1. ข้อมูลผู้แจ้ง -->
                            <div class="section-header">
                                <i class="bi bi-person-circle"></i> ข้อมูลผู้แจ้ง
                            </div>
                            <div class="row mb-4">
                                <div class="col-6 info-group">
                                    <div class="info-label">ชื่อ-นามสกุล</div>
                                    <div class="info-value" id="modalReporter">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">รหัสประจำตัว</div>
                                    <div class="info-value" id="modalReporterId">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">เบอร์โทร</div>
                                    <div class="info-value text-primary" id="modalTel">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">อีเมล</div>
                                    <div class="info-value text-break" id="modalEmail">...</div>
                                </div>
                            </div>
                            <!-- 2. ข้อมูลอุปกรณ์และสถานที่ -->
                            <div class="section-header">
                                <i class="bi bi-pc-display"></i> อุปกรณ์ & สถานที่
                            </div>
                            <div class="row mb-4">
                                <div class="col-6 info-group">
                                    <div class="info-label">ประเภท</div>
                                    <div class="info-value" id="modalDeviceType">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">ชื่อรุ่น/Model</div>
                                    <div class="info-value" id="modalDeviceModel">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">รหัสครุภัณฑ์</div>
                                    <div class="info-value" id="modalEquipId">...</div>
                                </div>
                                <div class="col-6 info-group">
                                    <div class="info-label">สถานที่ตั้ง</div>
                                    <div class="info-value" id="modalLocation">...</div>
                                </div>
                            </div>
                            <!-- 3. ปัญหา -->
                            <div class="p-3 bg-light rounded-3 border border-secondary border-opacity-10">
                                <div class="info-label mb-1"><i class="bi bi-exclamation-circle me-1"></i> อาการเสีย/ปัญหา</div>
                                <div class="text-dark" id="modalProblem">...</div>
                            </div>
                            <div class="mt-2 text-end">
                                <span class="small text-muted">แจ้งเมื่อ: <span id="modalDate">...</span></span>
                            </div>
                        </div>

                        <!-- ฝั่งขวา: สถานะ & รูปภาพ -->
                        <div class="col-lg-5 bg-light border-start p-4 d-flex flex-column">
                            
                            <!-- Form Update -->
                            <form method="POST" class="mb-4">
                                <input type="hidden" name="request_id" id="modalRequestId">
                                <div class="text-center mb-3">
                                    <span class="badge rounded-pill bg-secondary px-3 py-2" id="modalCurrentStatusBadge">...</span>
                                </div>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <label class="form-label fw-bold small">อัปเดตสถานะ</label>
                                        <select name="update_status" class="form-select mb-3">
                                            <option value="รอดำเนินการ">รอดำเนินการ</option>
                                            <option value="กำลังซ่อม">กำลังซ่อม</option>
                                            <option value="เสร็จสิ้น">เสร็จสิ้น (แจ้งเตือน Email)</option>
                                            <option value="ยกเลิก">ยกเลิก</option>
                                        </select>
                                        <button class="btn btn-primary w-100">บันทึก</button>
                                    </div>
                                </div>
                            </form>

                            <!-- รูปภาพ -->
                            <div id="modalImgArea" class="d-none flex-grow-1 d-flex flex-column">
                                <h6 class="section-header mb-2"><i class="bi bi-image"></i> รูปประกอบ</h6>
                                <div class="rounded-3 bg-white p-2 border shadow-sm flex-grow-1 d-flex align-items-center justify-content-center overflow-hidden position-relative">
                                    <img id="modalImgShow" src="" style="max-width: 100%; max-height: 200px; object-fit: contain; cursor: pointer;" onclick="document.getElementById('modalImgLink').click()"> 
                                </div>
                                <div class="text-end mt-2">
                                    <a id="modalImgLink" href="" target="_blank" class="small fw-bold text-primary text-decoration-none"><i class="bi bi-arrows-fullscreen me-1"></i> ดูรูปขนาดเต็ม</a>
                                </div>
                            </div>
                        </div>                                     
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#manageTable').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" }, "order": [[ 0, "desc" ]] });
            $(document).on('click', '.btn-view-case', function() {
                const b = $(this);
                $('#modalRequestId').val(b.data('id'));
                $('#modalTrackingId').text(b.data('tracking'));
                $('#modalReporter').text(b.data('reporter'));
                $('#modalReporterId').text(b.data('reporter-id'));
                $('#modalTel').text(b.data('tel'));
                $('#modalEmail').text(b.data('email'));
                $('#modalLocation').text(b.data('location'));
                $('#modalDeviceType').text(b.data('device-type'));
                $('#modalDeviceModel').text(b.data('device-model'));
                $('#modalEquipId').text(b.data('equip-id'));
                $('#modalProblem').text(b.data('problem'));
                $('#modalDate').text(b.data('date'));
                const imgPath = b.data('image'); 
                const imgArea = $('#modalImgArea');
                const imgShow = $('#modalImgShow');
                const imgLink = $('#modalImgLink');
                if (imgPath && imgPath.trim() !== '') {
                    let finalPath = imgPath;
                    if (!finalPath.startsWith('../') && !finalPath.startsWith('http')) finalPath = '../' + finalPath; 
                    imgShow.attr('src', finalPath);
                    imgLink.attr('href', finalPath);
                    imgArea.removeClass('d-none'); 
                } else { imgArea.addClass('d-none'); }
                const s = b.data('status');
                $('#modalCurrentStatusBadge').text(s);
                let cls = (s === 'เสร็จสิ้น') ? 'bg-success' : ((s === 'กำลังซ่อม') ? 'bg-primary' : ((s === 'รอรับเรื่อง') ? 'bg-warning text-dark' : 'bg-danger'));
                $('#modalCurrentStatusBadge').removeClass().addClass('badge rounded-pill px-3 py-2 ' + cls);
                $('select[name="update_status"]').val(s);
                new bootstrap.Modal(document.getElementById('repairModal')).show();
            });
        });
    </script>
</body>
</html>