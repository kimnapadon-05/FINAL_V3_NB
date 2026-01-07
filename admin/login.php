<?php 
session_start();
// ตรวจสอบ Username/Password ง่ายๆ (ในใช้งานจริงควรใช้ Database)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // กำหนดรหัสผ่านตรงนี้ (เปลี่ยนได้ตามใจชอบ)
    if ($username == "admin" && $password == "1234") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบผู้ดูแล</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; display: flex; align-items: center; height: 100vh; }
        .login-card { max-width: 400px; width: 100%; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="card login-card p-4 bg-white border-0">
            <h3 class="text-center mb-4 fw-bold text-primary">Admin Login</h3>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3">เข้าสู่ระบบ</button>
            </form>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted small text-decoration-none">← กลับหน้าแจ้งซ่อม</a>
            </div>
        </div>
    </div>
</body>
</html>