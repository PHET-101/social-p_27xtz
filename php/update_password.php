<?php
session_start();
include 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $passwordOld = trim($_POST["passwordOld"]);
    $passwordNew = trim($_POST["passwordNew"]);
    $confirm_password = trim($_POST["passwordConfirm"]);

    // ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
    if (!isset($_SESSION['user_id'])) {
        $message = "กรุณาเข้าสู่ระบบก่อน";
    } else {
        $user_id = $_SESSION['user_id'];

        // ดึงรหัสผ่านเก่าจากฐานข้อมูล
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($db_password);
        $stmt->fetch();

        // ตรวจสอบว่ารหัสผ่านปัจจุบันถูกต้องหรือไม่
        if (!password_verify($passwordOld, $db_password)) {
            $message = "รหัสผ่านปัจจุบันไม่ถูกต้อง!";
        } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $passwordNew)) {
            $message = "รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัว, มีตัวพิมพ์ใหญ่, ตัวเลข และอักขระพิเศษ!";
        } elseif ($passwordNew !== $confirm_password) {
            $message = "รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน!";
        } else {
            // อัปเดตรหัสผ่านใหม่
            $hashed_password = password_hash($passwordNew, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $message = "เปลี่ยนรหัสผ่านสำเร็จ!";
                header("Location: ../index.php");
                exit;
            } else {
                $message = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    }
}

echo $message;
?>
