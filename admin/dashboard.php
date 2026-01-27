<?php
session_start();
// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php"); // ถ้าไม่มีสิทธิ์ ดีดกลับไปหน้า Login
    exit();
}

// เชื่อมต่อฐานข้อมูล
include '../db_connect.php';

// --- ดึงข้อมูลสถิติ (เหมือนเดิม) ---
$sql_stats = "SELECT status, COUNT(*) as count FROM requests GROUP BY status";
$result_stats = $conn->query($sql_stats);
$count_pending = 0; $count_repairing = 0; $count_completed = 0;

while ($row_stat = $result_stats->fetch_assoc()) {
    if ($row_stat['status'] == 'รอรับเรื่อง') $count_pending = $row_stat['count'];
    if ($row_stat['status'] == 'กำลังซ่อม') $count_repairing = $row_stat['count'];
    if ($row_stat['status'] == 'เสร็จสิ้น') $count_completed = $row_stat['count'];
}

// ดึงรายการล่าสุด
$sql_latest = "SELECT * FROM requests ORDER BY created_at DESC LIMIT 5";
$result_latest = $conn->query($sql_latest);
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

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold m-0">Dashboard</h2>
                <p class="text-muted">ยินดีต้อนรับกลับ, มาดูภาพรวมงานซ่อมกันเถอะ</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small">
                    <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
                </div>
            </div>
        </div>

        <!-- Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card stat-pending">
                    <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                    <h5 class="text-muted mb-1">รอรับเรื่อง</h5>
                    <h2 class="fw-bold m-0"><?php echo $count_pending; ?> <span class="fs-6 fw-normal text-muted">รายการ</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-repairing">
                    <div class="stat-icon"><i class="bi bi-tools"></i></div>
                    <h5 class="text-muted mb-1">กำลังซ่อม</h5>
                    <h2 class="fw-bold m-0"><?php echo $count_repairing; ?> <span class="fs-6 fw-normal text-muted">รายการ</span></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-completed">
                    <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="text-muted mb-1">เสร็จสิ้น</h5>
                    <h2 class="fw-bold m-0"><?php echo $count_completed; ?> <span class="fs-6 fw-normal text-muted">รายการ</span></h2>
                </div>
            </div>
        </div>

        <!-- Latest Table -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0">รายการแจ้งซ่อมล่าสุด</h5>
                <a href="manage_repairs.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">ดูทั้งหมด</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 rounded-start">รหัสงาน</th>
                            <th class="border-0">ผู้แจ้ง</th>
                            <th class="border-0">ปัญหา</th>
                            <th class="border-0 rounded-end">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_latest->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?php echo $row['tracking_id']; ?></td>
                            <td>
                                <div class="fw-medium"><?php echo $row['reported_by']; ?></div>
                                <small class="text-muted"><?php echo $row['building']; ?></small>
                            </td>
                            <td><?php echo mb_strimwidth($row['problem_description'], 0, 40, "..."); ?></td>
                            <td>
                                <?php 
                                    $s = $row['status'];
                                    $bg = 'secondary';
                                    if($s == 'รอรับเรื่อง') $bg = 'warning';
                                    elseif($s == 'กำลังซ่อม') $bg = 'primary';
                                    elseif($s == 'เสร็จสิ้น') $bg = 'success';
                                ?>
                                <span class="badge bg-<?php echo $bg; ?> rounded-pill px-3 py-2 fw-normal">
                                    <?php echo $s; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>