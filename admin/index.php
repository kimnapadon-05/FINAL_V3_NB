<?php
session_start();

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป Dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// เชื่อมต่อฐานข้อมูล (ถอยหลัง 1 ชั้น ../ เพราะไฟล์นี้อยู่ใน folder admin)
require_once '../db_connect.php'; 

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 1. เตรียมคำสั่ง SQL เพื่อค้นหา User
    $sql = "SELECT id, username, password_hash FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 2. ตรวจสอบรหัสผ่าน (เทียบรหัสที่กรอก กับ Hash ใน DB)
            if (password_verify($password, $user['password_hash'])) {
                // ✅ รหัสถูกต้อง! สร้าง Session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['username'];
                
                // ส่งไปหน้า Dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "รหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $error_message = "ไม่พบชื่อผู้ใช้นี้ในระบบ";
        }
        $stmt->close();
    } else {
        $error_message = "Database Error: " . $conn->error;
    }
    $conn->close();
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
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #E0F7FA;
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
    </style>
</head>
<body>

    <div class="card login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="mt-3 fw-bold text-primary">Admin System</h2>
            <p class="text-muted small">ระบบจัดการงานซ่อม </p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center p-2 mb-3 small">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small">รหัสผ่าน (Password)</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm">เข้าสู่ระบบ</button>
            </div>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <a href="../index.php" class="text-decoration-none text-muted small">← กลับหน้าแจ้งซ่อม</a>
            <br>
        </div>
    </div>

</body>
</html>