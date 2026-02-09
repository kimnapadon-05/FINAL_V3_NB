<?php
session_start();
session_destroy();
// ถ้ามี flag timeout ให้ส่งต่อไปยังหน้า index.php เพื่อแสดงข้อความแจ้งเตือน
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
	header("Location: ../index.php?timeout=1");
} else {
	header("Location: ../index.php");
}
exit();
?>