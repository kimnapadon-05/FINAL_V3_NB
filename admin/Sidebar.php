<?php
// หาชื่อไฟล์ปัจจุบัน เพื่อทำปุ่ม Active (สีม่วงๆ)
$current_page = basename($_SERVER['PHP_SELF']);

// ป้องกันการเก็บแคชของหน้า admin เพื่อให้การกลับมาจะแสดงการตรวจสอบ session ใหม่
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// --- Session timeout handling (inactive users will be logged out) ---
$session_timeout = 1800; // เวลาเป็นวินาที (ตัวอย่าง: 1800 = 30 นาที)
// ให้พยายามตั้งค่า session lifetime ก่อนเริ่ม session หากยังไม่เริ่ม
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.gc_maxlifetime', $session_timeout);
    session_set_cookie_params($session_timeout);
    session_start();
} else {
    // session อาจถูกเริ่มในไฟล์อื่นแล้ว
}

// ตรวจสอบกิจกรรมล่าสุด ถ้านานเกิน timeout ให้ทำลาย session และบังคับไปหน้า logout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
    // ตั้ง cookie แจ้งเตือนแบบชั่วคราว เพื่อให้หน้า Dashboard แสดงข้อความว่า session หมดอายุ
    setcookie('session_expired', '1', time() + 300, '/'); // คงค่าสำหรับ 5 นาที
    session_unset();
    session_destroy();
    header("Location: logout.php?timeout=1");
    exit();
}
// อัปเดตเวลากิจกรรมล่าสุด
$_SESSION['LAST_ACTIVITY'] = time();
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
<script>
// ถ้าผู้ใช้คลิกลิงก์ที่จะออกจากโฟลเดอร์ /admin ให้เรียก logout โดยใช้ sendBeacon
document.addEventListener('click', function(e) {
    var a = e.target.closest && e.target.closest('a');
    if (!a) return;
    var href = a.getAttribute('href') || '';
    // ข้ามลิงก์ภายใน (เช่น # หรือ javascript:) และลิงก์ logout เอง
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('logout.php')) return;

    try {
        var url = new URL(href, location.href);
        // ถ้าเป็นลิงก์ไปยังนอก /admin ให้ส่งคำขอ logout แบบ background
        if (!url.pathname.includes('/admin/')) {
            if (navigator.sendBeacon) {
                navigator.sendBeacon('logout.php');
            } else {
                // fall back: fire a synchronous XHR (not ideal but a fallback)
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'logout.php', false);
                xhr.send(null);
            }
        }
    } catch (err) {
        // ignore URL parsing errors
    }
});
</script>