<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

include '../db_connect.php';

// --- Logic อัปเดตสถานะ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_POST['request_id'])) {
    $new_status = $_POST['update_status'];
    $req_id = $_POST['request_id'];
    
    // เตรียม SQL พื้นฐาน
    $sql = "UPDATE requests SET status = ?, updated_at = NOW()";
    
    // ถ้าสถานะเป็น "กำลังซ่อม" ให้บันทึกเวลาเริ่มซ่อมด้วย
    if ($new_status == 'กำลังซ่อม') {
        $sql .= ", repair_started_at = NOW()";
    }
    // ถ้าสถานะเป็น "เสร็จสิ้น" ให้บันทึกเวลาเสร็จด้วย
    elseif ($new_status == 'เสร็จสิ้น') {
        $sql .= ", completed_at = NOW()";
    }
    
    $sql .= " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $req_id);
    
    if ($stmt->execute()) {
        // สำเร็จ (อาจจะ Redirect หรือ Show alert ก็ได้)
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// ดึงข้อมูลทั้งหมด
$sql = "SELECT * FROM requests ORDER BY created_at DESC";
$result = $conn->query($sql);
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
    
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4e54c8;
            --bg-color: #f3f4f6;
            --text-color: #334155;
        }

        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: var(--bg-color); 
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* === Modern Sidebar Styles === */
        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            box-shadow: 4px 0 24px rgba(0,0,0,0.02);
            z-index: 1000;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2.5rem;
            padding: 0 0.5rem;
        }
        
        .brand-logo i {
            font-size: 1.5rem;
            background: rgba(78, 84, 200, 0.1);
            padding: 8px;
            border-radius: 12px;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: #f8fafc;
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(78, 84, 200, 0.25);
        }
        
        .nav-link i {
            font-size: 1.2rem;
        }

        /* User Profile Section in Sidebar */
        .user-profile-card {
            background: #f8fafc;
            padding: 12px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: auto; /* ดันไปล่างสุด */
            border: 1px solid #e2e8f0;
        }
        
        .user-avatar {
            width: 40px; height: 40px;
            background: #e0e7ff;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info h6 { margin: 0; font-size: 0.9rem; font-weight: 600; }
        .user-info span { font-size: 0.75rem; color: #94a3b8; }
        
        .logout-btn {
            color: #ef4444;
            background: none;
            border: none;
            margin-left: auto;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .logout-btn:hover { background: #fee2e2; }

        /* === Main Content === */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
        }

        /* Dashboard Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.3s;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-5px); }
        
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-pending .stat-icon { background: #fffbeb; color: #fff176; }
        .stat-repairing .stat-icon { background: #eff6ff; color: #ff7043; }
        .stat-completed .stat-icon { background: #f0fdf4; color: #8bc34a; }
        .status-pill {
            padding: 6px 16px;
            border-radius: 50px; /* ตรงนี้ทำให้มน */
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }
        /* Table Card */
        .table-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
    </style>
</head>
<body>

    <!-- Sidebar (Nav) -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">จัดการรายการแจ้งซ่อม</h2>
                <p class="text-muted small">Update Status & Management</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small">
                    <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="card-header-custom">
                <h5 class="card-title">รายการแจ้งซ่อมทั้งหมด</h5>
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>

            <table id="manageTable" class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 15%;">รหัสงาน</th>
                        <th style="width: 20%;">ผู้แจ้ง</th>
                        <th style="width: 30%;">ปัญหา</th>
                        <th style="width: 15%;">สถานะปัจจุบัน</th>
                        <th style="width: 20%;" class="text-center">อัปเดตสถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="#" class="job-id text-decoration-none">
                                <?php echo $row['tracking_id']; ?>
                            </a>
                        </td>
                        
                        <td>
                            <div class="fw-bold text-dark"><?php echo $row['reported_by']; ?></div>
                            <div class="text-muted small">
                                <?php echo $row['building'] . " ห้อง " . $row['room']; ?>
                            </div>
                        </td>
                        
                        <td>
                            <span class="text-secondary"><?php echo $row['problem_description']; ?></span>
                        </td>
                        
                        <td>
                            <?php 
                                $s = $row['status'];
                                // กำหนดสีพื้นหลังและสีตัวอักษรตามสถานะ
                                $bgColor = '#64748b'; // สีเทา (Default)
                                $textColor = '#ffffff';

                                if ($s == 'เสร็จสิ้น') {
                                    $bgColor = '#10b981'; // เขียวมรกต
                                } else if ($s == 'กำลังซ่อม') {
                                    $bgColor = '#3b82f6'; // ฟ้าสดใส 
                                } else if ($s == 'รอรับเรื่อง') {
                                    $bgColor = '#f59e0b'; // เหลืองอำพัน
                                    $textColor = '#000000'; // ตัวหนังสือสีดำ
                                } else if ($s == 'ยกเลิก') {
                                    $bgColor = '#ef4444'; // แดง
                                }
                            ?>
                            <span class="status-pill" style="background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>;">
                            <?php echo $s; ?>
                            </span>
                        </td>
                        
                        <td>
                            <form method="POST" class="d-flex justify-content-center">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <select name="update_status" class="form-select form-select-sm-custom w-auto" onchange="confirmStatusUpdate(this)">
                                    <option value="" disabled selected>เลือกสถานะ</option>
                                    <option value="รอรับเรื่อง">รอรับเรื่อง</option>
                                    <option value="กำลังซ่อม">กำลังซ่อม</option>
                                    <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                                    <option value="ยกเลิก">ยกเลิก</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#manageTable').DataTable({
                "language": {
                    "search": "ค้นหา:",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "paginate": {
                        "next": '<i class="bi bi-chevron-right"></i>',
                        "previous": '<i class="bi bi-chevron-left"></i>'
                    },
                    "emptyTable": "ไม่มีข้อมูลการแจ้งซ่อม"
                },
                "order": [[ 0, "desc" ]], // เรียงตามรหัสล่าสุด
                "pageLength": 10,
                "dom": '<"d-flex justify-content-between align-items-center mb-3"f>t<"d-flex justify-content-between align-items-center mt-3"ip>', // จัด layout ของ datatable ใหม่
            });
        });

        function confirmStatusUpdate(selectElement) {
            const status = selectElement.value;
            Swal.fire({
                title: 'อัปเดตสถานะ?',
                text: `ต้องการเปลี่ยนสถานะเป็น "${status}" ใช่หรือไม่`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                customClass: {
                    popup: 'rounded-4' // ให้ Popup มนสวยๆ
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    selectElement.form.submit();
                } else {
                    selectElement.selectedIndex = 0; 
                }
            });
        }
    </script>
</body>
</html>