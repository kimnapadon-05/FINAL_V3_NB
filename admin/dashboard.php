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
    
    <link rel="stylesheet" href="styles.css?v=2">
</head>
<body>

    <?php include 'Sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold m-0">Dashboard</h2>
                <p class="text-muted">ภาพรวมของระบบแจ้งซ่อมอุปกรณ์ IT</p>
            </div>
            <div class="bg-white px-3 py-2 rounded-3 border shadow-sm text-muted small text-nowrap">
                <i class="bi bi-calendar-event me-2"></i> <?php echo date('d M Y'); ?>
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