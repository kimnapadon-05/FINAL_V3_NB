<?php
// หาชื่อไฟล์ปัจจุบัน (เช่น dashboard.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../logo/logo.png" alt="โลโก้" height="55" class="d-inline-block align-text-top me-2">
            <span>ระบบแจ้งซ่อมอุปกรณ์ IT</span>
        </a>
    </div>
    &emsp;
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill"></i>
                <span>ภาพรวม</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="manage_repairs.php" class="nav-link <?php echo ($current_page == 'manage_repairs.php') ? 'active' : ''; ?>">
                <i class="bi bi-tools"></i>
                <span>จัดการงานซ่อม</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="QR_code.php" class="nav-link <?php echo ($current_page == 'QR_code.php') ? 'active' : ''; ?>">
                <i class="bi bi-qr-code"></i>
                <span>เพิ่มอุปกรณ์</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="manage_equipment.php" class="nav-link <?php echo ($current_page == 'manage_equipment.php') ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i>
                <span>จัดการอุปกรณ์</span>
            </a>
        </li>
    </ul>

    <!-- Profile Section ด้านล่าง -->
    <div class="user-profile-card">
        <div class="user-avatar">AD</div>
        <div class="user-info">
            <h6>Admin User</h6>
            <span>ผู้ดูแลระบบ</span>
        </div>
        <a href="logout.php" class="logout-btn" title="ออกจากระบบ">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>