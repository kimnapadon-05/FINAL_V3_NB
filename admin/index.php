<?php
session_start();

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป Dashboard เลย
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// ตรวจสอบเมื่อมีการกดปุ่ม Login
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- กำหนด Username / Password ตรงนี้ ---
    // (ในอนาคตเปลี่ยนไปเช็คจากฐานข้อมูลได้)
    $admin_user = "admin";
    $admin_pass = "1234"; 

    if ($username === $admin_user && $password === $admin_pass) {
        // ล็อกอินสำเร็จ!
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php"); // ส่งไปหน้า Dashboard
        exit();
    } else {
        $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #E0F7FA; /* ฟ้าอ่อน */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background: white;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .btn-primary {
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="card login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill text-primary display-1"></i>
            <h2 class="mt-3 fw-bold text-secondary">Admin System</h2>
            <p class="text-muted small">ระบบจัดการงานซ่อม (สำหรับเจ้าหน้าที่)</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center p-2 mb-3 small">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Action="" คือส่งข้อมูลกลับมาหน้าเดิมเพื่อเช็ค PHP ด้านบน -->
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" class="form-control" placeholder="ระบุชื่อผู้ใช้" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small">รหัสผ่าน (Password)</label>
                <input type="password" name="password" class="form-control" placeholder="ระบุรหัสผ่าน" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                    เข้าสู่ระบบ <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <a href="../index.php" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left"></i> กลับไปหน้าแจ้งซ่อม (User)
            </a>
        </div>
    </div>

</body>
</html>