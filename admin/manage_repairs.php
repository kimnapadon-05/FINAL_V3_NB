<?php
session_start();
// if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }

include '../db_connect.php';

// --- Logic 1: อัปเดตสถานะ ---
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
        // Redirect เพื่อป้องกันการกด F5 แล้วส่งข้อมูลซ้ำ
        header("Location: manage_repairs.php");
        exit();
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// --- Logic 2: ดึงข้อมูลใส่ตาราง ---
$sql = "SELECT * FROM requests ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการงานซ่อม</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: #f3f4f6; 
        }

        /* เว้นที่ด้านซ้าย 280px ให้ Sidebar (สำคัญมาก!) */
        .main-content {
            margin-left: 280px; 
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        /* บนมือถือ ให้เต็มจอ */
        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
        }

        /* Style ของการ์ดตาราง */
        .table-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        /* เม็ดยาสถานะ */
        .status-pill {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>

    <?php include 'Sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">จัดการรายการแจ้งซ่อม</h2>
                <p class="text-muted small">Manage Repair Requests</p>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small d-flex align-items-center">
                    <i class="bi bi-calendar-event me-2 text-primary"></i> 
                    <span class="me-3"><?php echo date('d M Y'); ?></span>
                    <div class="vr me-3"></div>
                    <i class="bi bi-clock me-2 text-primary"></i>
                    <span><?php echo date('H:i'); ?> น.</span>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title fw-bold m-0">รายการแจ้งซ่อมทั้งหมด</h5>
                <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="location.reload();">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

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
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt"></i> <?php echo $row['building'] . " / " . $row['room']; ?>
                            </div>
                        </td>
                        
                        <td>
                            <span class="text-secondary text-truncate d-inline-block" style="max-width: 180px;">
                                <?php echo $row['problem_description']; ?>
                            </span>
                        </td>

                        <td>
                            <div class="fw-medium text-dark">
                                <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('H:i', strtotime($row['created_at'])); ?> น.
                            </div>
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
                                    data-location="<?php echo $row['building'] . ' ' . $row['room']; ?>"
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

    <div class="modal fade" id="repairModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="bi bi-tools me-2"></i>อัปเดตงาน: <span id="modalTrackingId">...</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-7 p-4 bg-white">
                            <h6 class="text-muted fw-bold small mb-3 border-bottom pb-2">รายละเอียด</h6>
                            <div class="mb-3">
                                <label class="small text-muted">ผู้แจ้ง</label>
                                <div class="fw-bold fs-5" id="modalReporter">...</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="small text-muted">สถานที่</label>
                                    <div class="fw-medium" id="modalLocation">...</div>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">เวลาแจ้ง</label>
                                    <div class="fw-medium" id="modalDate">...</div>
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded border">
                                <label class="small text-muted mb-1">อาการ</label>
                                <div id="modalProblem" class="text-dark">...</div>
                            </div>
                        </div>
                        <div class="col-md-5 p-4 bg-light border-start d-flex flex-column justify-content-center">
                            <form method="POST">
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
                                            <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                                            <option value="ยกเลิก">ยกเลิก</option>
                                        </select>
                                        <button class="btn btn-primary w-100">บันทึก</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div id="modalImgArea" class="mt-3 d-none">
                        <div class="col-md-7 p-4 bg-white">
                        <h6 class="text-muted fw-bold small mb-3 border-bottom pb-2">
                            <i class="bi bi-image me-1"></i> รูปภาพประกอบ
                        </h6>
                            
                         <div class="bg-white p-2 rounded-4 border shadow-sm">
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center position-relative overflow-hidden" 
                                style="width: 100%; height: 200px; border: 1px solid #f1f5f9;">
                                
                                <img id="modalImgShow" src="" 
                                    style="max-width: 100%; max-height: 100%; object-fit: contain; cursor: pointer; transition: transform 0.3s;"
                                    onmouseover="this.style.transform='scale(1.05)'" 
                                    onmouseout="this.style.transform='scale(1)'"
                                    onclick="document.getElementById('modalImgLink').click()"> 
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>คลิกที่รูปเพื่อขยาย</span>
                                
                                <a id="modalImgLink" href="" target="_blank" class="text-decoration-none small fw-bold text-primary py-1 px-2 rounded hover-bg-light">
                                    <i class="bi bi-arrows-fullscreen me-1"></i> ดูรูปขนาดเต็ม
                                </a>
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
            // Setup DataTable
            $('#manageTable').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
                "order": [[ 0, "desc" ]] // เรียงตามรหัสงานล่าสุด
            });

            // Handle Modal
            $(document).on('click', '.btn-view-case', function() {
                const b = $(this);
                // ส่งค่าเข้า Modal
                $('#modalRequestId').val(b.data('id'));
                $('#modalTrackingId').text(b.data('tracking'));
                $('#modalReporter').text(b.data('reporter'));
                $('#modalLocation').text(b.data('location'));
                $('#modalProblem').text(b.data('problem'));
                $('#modalDate').text(b.data('date'));
                // logic เพิ่มเติมสำหรับรูปภาพ
                const imgPath = b.data('image'); // รับค่า path รูป
                const imgArea = $('#modalImgArea');
                const imgShow = $('#modalImgShow');
                const imgLink = $('#modalImgLink');
                
                if (imgPath && imgPath.trim() !== '') {
                    
                    let finalPath = imgPath;
                    if (!finalPath.startsWith('../') && !finalPath.startsWith('http')) {
                        // ถ้ายังไม่มี ../ ให้เติมเข้าไป (เผื่อไฟล์ manage_repairs อยู่ในโฟลเดอร์ admin)
                        finalPath = '../' + finalPath; 
                    }

                    imgShow.attr('src', finalPath);
                    imgLink.attr('href', finalPath);
                    imgArea.removeClass('d-none'); 
                } else {
                    // ถ้าไม่มีรูป ให้ซ่อนกล่อง
                    imgArea.addClass('d-none');
                }
                // จัดการสี Badge ใน Modal
                const s = b.data('status');
                $('#modalCurrentStatusBadge').text(s);
                let cls = 'bg-secondary';
                if(s === 'เสร็จสิ้น') cls = 'bg-success';
                else if(s === 'กำลังซ่อม') cls = 'bg-primary';
                else if(s === 'รอดำเนินการ') cls = 'bg-warning text-dark';
                else if(s === 'ยกเลิก') cls = 'bg-danger';
                
                $('#modalCurrentStatusBadge').removeClass().addClass('badge rounded-pill px-3 py-2 ' + cls);
                
                // Set default select option
                $('select[name="update_status"]').val(s);
                
                new bootstrap.Modal(document.getElementById('repairModal')).show();
            });
        });
    </script>
</body>
</html>