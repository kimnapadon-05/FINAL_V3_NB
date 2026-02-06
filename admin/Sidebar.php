<?php
// Sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<button class="mobile-toggle-btn" id="sidebarToggle">
    <i class="bi bi-list" style="font-size: 1.5rem;"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<nav class="sidebar" id="mainSidebar">
    <div class="container ps-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
        <a class="navbar-brand mb-0" href="dashboard.php">
            <img src="../logo/logo.png" alt="Logo" 
             class="d-inline-block align-text-top"
             style="height: 40px; width: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #e2e8f0;">
            <span>ระบบเเจ้งซ่อมอุปกรณ์ IT</span>
        </a>
        <i class="bi bi-x-lg d-md-none text-muted" id="closeSidebarBtn" style="cursor: pointer;"></i>
    </div>
</div>
    
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

    <div class="user-profile-card">
        <img src="../logo/AD.png" alt="User Avatar" class="user-avatar">
        <div class="user-info">
            <h6>Admin User</h6>
            <span>ผู้ดูแลระบบ</span>
        </div>
        <a href="logout.php" class="logout-btn" title="ออกจากระบบ" onclick="return confirm('ยืนยันออกจากระบบ?');">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // อ้างอิง Element ต่างๆ
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('closeSidebarBtn'); // ปุ่ม X ในเมนู
        const sidebar = document.getElementById('mainSidebar');      // ตัว Sidebar
        const overlay = document.getElementById('sidebarOverlay');   // ฉากหลังมืดๆ

        // ฟังก์ชันสลับ เปิด/ปิด
        function toggleSidebar() {
            // สลับ class active เพื่อเลื่อน Sidebar เข้า/ออก
            if(sidebar) sidebar.classList.toggle('active');
            if(overlay) overlay.classList.toggle('active');

            // ✅ ไฮไลท์: สั่งซ่อน/แสดงปุ่ม Hamburger
            if(toggleBtn) toggleBtn.classList.toggle('d-none');
        }

        // เพิ่มตัวจับเหตุการณ์ (Event Listener)
        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);
    });
</script>