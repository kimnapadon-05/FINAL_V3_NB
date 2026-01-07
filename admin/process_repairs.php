<?php
// ========================================================================
// 1. นำเข้า PHPMailer
// ========================================================================
// หากใช้ Composer (แนะนำ)
// require 'vendor/autoload.php';

// หากดาวน์โหลด PHPMailer มาใส่ในโฟลเดอร์เอง (สมมติว่าอยู่ที่ ./PHPMailer/src/)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ========================================================================
// 2. ฟังก์ชันส่งอีเมล
// ========================================================================

/**
 * ฟังก์ชันสำหรับส่งอีเมล
 * @param string $toEmail อีเมลผู้รับ
 * @param string $toName ชื่อผู้รับ
 * @param string $subject หัวข้ออีเมล
 * @param string $body เนื้อหาอีเมล (HTML)
 * @return bool
 */
function send_notification_email($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // *** ตั้งค่า SMTP SERVER (สำคัญมาก) ***
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // ตัวอย่าง: 'smtp.gmail.com' หรือ 'smtp.office365.com'
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@yourdomain.com'; // อีเมลผู้ส่ง
        $mail->Password   = 'YourEmailPassword';      // รหัสผ่านอีเมลผู้ส่ง (App Password ถ้าใช้ Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // หรือ ENCRYPTION_SMTPS สำหรับ Port 465
        $mail->Port       = 587; // หรือ 465

        // ตั้งค่าภาษาและ charset
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('noreply@yourdomain.com', 'ระบบแจ้งซ่อมอัตโนมัติ');
        
        // ผู้รับ
        $mail->addAddress($toEmail, $toName);

        // เนื้อหา
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // เนื้อหาแบบ Plain text สำหรับอีเมลที่ไม่รองรับ HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        // สำหรับการดีบัก
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// ========================================================================
// 3. การประมวลผลฟอร์ม
// ========================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // รับค่าจากฟอร์ม Modal
    $repair_id = $_POST['repair_id'] ?? '';
    $new_status = $_POST['new_status'] ?? '';
    $technician_name = $_POST['technician_name'] ?? '-';
    $note = $_POST['note'] ?? '';

    $reporter_email = 'reporter_mock@example.com'; // อีเมลผู้แจ้ง
    $reporter_name = 'สมชาย ใจดี';                   // ชื่อผู้แจ้ง
    $technician_email = 'technician_mock@example.com'; // อีเมลช่างที่ถูกมอบหมาย
    $repair_title = 'เครื่องปรับอากาศไม่เย็น';           // ชื่อเรื่อง
    
        
        $update_success = true; // สมมติว่าอัปเดตสำเร็จ
        
        // ========================================================================
        // เงื่อนไขการส่งอีเมล
        // ========================================================================
        
        // ส่งอีเมลไปยังช่างที่ถูกมอบหมาย 
        if ($technician_name != '-' && $technician_email) {
            $tech_subject = "[งานซ่อมใหม่/อัปเดต] คุณได้รับมอบหมายงาน: $repair_id - $repair_title";
            $tech_body = "<h3>เรียน คุณ $technician_name,</h3>
                         <p>รายการแจ้งซ่อมรหัส <strong>$repair_id - $repair_title</strong> ได้รับมอบหมายให้คุณเป็นผู้รับผิดชอบแล้ว</p>
                         <ul>
                             <li><strong>สถานะปัจจุบัน:</strong> <span style='color: blue;'>$new_status</span></li>
                             <li><strong>หมายเหตุ:</strong> $note</li>
                             <li><strong>ลิงก์จัดการงาน:</strong> <a href='http://yourdomain.com/admin/manage_repairs.php'>คลิกที่นี่</a></li>
                         </ul>
                         <p>โปรดดำเนินการโดยเร็วที่สุด</p>";
            send_notification_email($technician_email, $technician_name, $tech_subject, $tech_body);
        }

        // ส่งอีเมลกลับไปยังผู้แจ้ง (เมื่อสถานะเป็น 'เสร็จสิ้น')
        if ($new_status == 'เสร็จสิ้น' && $reporter_email) {
            $reporter_subject = "[เสร็จสิ้น] งานซ่อมของคุณ: $repair_id - $repair_title";
            $reporter_body = "<h3>เรียน คุณ $reporter_name,</h3>
                             <p>รายการแจ้งซ่อมรหัส <strong>$repair_id - $repair_title</strong> ได้ดำเนินการเสร็จสิ้นเรียบร้อยแล้ว</p>
                             <ul>
                                 <li><strong>ช่างผู้ดำเนินการ:</strong> $technician_name</li>
                                 <li><strong>หมายเหตุการซ่อม:</strong> $note</li>
                             </ul>
                             <p>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อแผนก IT ได้</p>
                             <p>ขอบคุณที่ใช้บริการ</p>";
            send_notification_email($reporter_email, $reporter_name, $reporter_subject, $reporter_body);
        }

    // } else {
    //     $update_success = false; // อัปเดตล้มเหลว
    // }
    
    // Redirect กลับไปหน้าเดิม
    header("Location: manage_repairs.php?status=" . ($update_success ? 'success' : 'error'));
    exit;
}

?>