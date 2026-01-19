<?php
// หาชื่อไฟล์ปัจจุบัน เพื่อทำปุ่ม Active (สีม่วงๆ)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --sidebar-width: 280px;
        --primary-color: #4e54c8;
    }

    /* กล่อง Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        background: #ffffff;
        height: 100vh;
        position: fixed; /* ล็อกตำแหน่งซ้ายสุด */
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        box-shadow: 4px 0 24px rgba(0,0,0,0.02);
        z-index: 1000;
        transition: all 0.3s ease;
    }

    /* โลโก้ */
    .sidebar .navbar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        margin-bottom: 2rem;
    }
    
    /* รายการเมนู */
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
        transition: all 0.2s ease;
        font-weight: 500;
        font-family: 'Kanit', sans-serif;
    }

    .nav-link:hover {
        background-color: #f8fafc;
        color: var(--primary-color);
        transform: translateX(4px);
    }

    /* ปุ่มที่ถูกเลือก (Active) */
    .nav-link.active {
        background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(78, 84, 200, 0.25);
    }
    
    .nav-link i { font-size: 1.2rem; }

    /* ส่วน Profile ด้านล่าง */
    .user-profile-card {
        background: #f8fafc;
        padding: 12px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: auto; 
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

    .user-info h6 { margin: 0; font-size: 0.9rem; font-weight: 600; color: #334155; }
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
        font-size: 1.2rem;
    }
    .logout-btn:hover { background: #fee2e2; }
</style>

<nav class="sidebar">
    <div class="container ps-0">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../logo/logo.png" alt="Logo" height="40" class="d-inline-block align-text-top">
            <span>IT Support</span>
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

    <div class="user-profile-card">
        <div class="user-avatar">AD</div>
        <div class="user-info">
            <h6>Admin User</h6>
            <span>ผู้ดูแลระบบ</span>
        </div>
        <a href="logout.php" class="logout-btn" title="ออกจากระบบ" onclick="return confirm('ยืนยันออกจากระบบ?');">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>