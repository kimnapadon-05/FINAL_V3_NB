<?php
session_start();
require_once '../db_connect.php'; // ถอยกลับไป Root เพื่อเรียก DB

// ถ้าล็อกอินอยู่แล้ว ให้เด้งไป Dashboard (หรือจะให้ Admin สร้าง Admin อีกคนก็ได้ แล้วแต่ Design)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
     exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation เบื้องต้น
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "รหัสผ่านยืนยันไม่ตรงกัน";
        $message_type = "danger";
    } elseif (strlen($password) < 4) {
        $message = "รหัสผ่านต้องมีความยาวอย่างน้อย 4 ตัวอักษร";
        $message_type = "danger";
    } else {
        // เช็คว่า Username ซ้ำไหม
        $check_sql = "SELECT id FROM admins WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว";
            $message_type = "warning";
        } else {
            // สร้าง Hash Password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // บันทึกลงฐานข้อมูล
            $sql = "INSERT INTO admins (username, password_hash) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password_hash);

            if ($stmt->execute()) {
                $message = "สมัครสมาชิกสำเร็จ! กำลังพาไปหน้าเข้าสู่ระบบ...";
                $message_type = "success";
                // Redirect อัตโนมัติใน 2 วินาที
                header("refresh:2;url=index.php"); 
            } else {
                $message = "เกิดข้อผิดพลาด: " . $conn->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนผู้ดูแล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="card reg-card p-4 p-md-5">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill text-success display-1"></i>
            <h2 class="mt-3 fw-bold text-secondary">สมัครผู้ดูแลระบบ</h2>
            <p class="text-muted small">เพิ่มบัญชีผู้ดูแลใหม่</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> text-center p-2 mb-3 small">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted small">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" class="form-control" placeholder="ตั้งชื่อผู้ใช้" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small">รหัสผ่าน (Password)</label>
                <input type="password" name="password" class="form-control" placeholder="ตั้งรหัสผ่าน" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted small">ยืนยันรหัสผ่าน (Confirm Password)</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg shadow-sm">
                    ลงทะเบียน <i class="bi bi-check-circle ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-4 border-top pt-3">
            <a href="index.php" class="text-decoration-none text-muted small">
                <i class="bi bi-box-arrow-in-right"></i> มีบัญชีแล้ว? เข้าสู่ระบบ
            </a>
        </div>
    </div>

</body>
</html>