<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $display_name = trim($_POST["display_name"]); // แก้ไขตัวสะกด
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $description = trim($_POST["description"]);
    $password = $_POST["password"]; // ใช้รหัสผ่านเมื่อจำเป็น
    $image = $_SESSION["avatar"]; // ใช้รูปเดิมหากไม่มีการอัปโหลดใหม่

    // ตรวจสอบว่า username มีเฉพาะตัวพิมพ์เล็ก, ตัวเลข, _ และ . และมีความยาว 3-20 ตัวอักษร
    if (!preg_match('/^[a-z0-9_.]{3,20}$/', $username)) {
        echo "<script>alert('ชื่อผู้ใช้ต้องมีความยาว 3-20 ตัวอักษร และใช้ได้เฉพาะตัวพิมพ์เล็ก (a-z), ตัวเลข (0-9), จุด (.) และขีดล่าง (_) เท่านั้น!'); window.history.back();</script>";
        exit();
    }

    if (strlen($description) > 100) {
        echo "<script>alert('คำอธิบายต้องมีไม่เกิน 100 ตัวอักษร!'); window.history.back();</script>";
        exit();
    }

    // ดึงข้อมูลเดิมของผู้ใช้
    $sql = "SELECT username, email, password, avatar FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('ไม่พบข้อมูลผู้ใช้!'); window.history.back();</script>";
        exit();
    }

    // ตรวจสอบว่า username ซ้ำหรือไม่ (ยกเว้นของผู้ใช้เดิม)
    $sql_check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt_check_username = $conn->prepare($sql_check_username);
    $stmt_check_username->bind_param("si", $username, $user_id);
    $stmt_check_username->execute();
    $stmt_check_username->store_result();

    if ($stmt_check_username->num_rows > 0) {
        echo "<script>alert('ชื่อผู้ใช้นี้ถูกใช้งานแล้ว!'); window.history.back();</script>";
        exit();
    }

    // ตรวจสอบว่า email ซ้ำหรือไม่ (ยกเว้นของผู้ใช้เดิม)
    $sql_check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("si", $email, $user_id);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        echo "<script>alert('อีเมลนี้ถูกใช้งานแล้ว!'); window.history.back();</script>";
        exit();
    }

    // ถ้าอีเมลเปลี่ยน ตรวจสอบรหัสผ่าน
    if ($email !== $user["email"]) {
        if (empty($password) || !password_verify($password, $user["password"])) {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง! กรุณาลองใหม่'); window.history.back();</script>";
            exit();
        }
    }

    // ตรวจสอบและอัปโหลดรูปใหม่
    if (!empty($_FILES["avatar"]["name"])) {
        $target_dir = "uploads/profiles/";
        $file_name = basename($_FILES["avatar"]["name"]);
        $file_tmp = $_FILES["avatar"]["tmp_name"];
        $file_size = $_FILES["avatar"]["size"];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // ตรวจสอบประเภทไฟล์ที่อนุญาต
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('ไฟล์รูปภาพต้องเป็น JPG, JPEG, PNG หรือ GIF เท่านั้น!'); window.history.back();</script>";
            exit();
        }

        // จำกัดขนาดไฟล์ (2MB)
        if ($file_size > 2 * 1024 * 1024) {
            echo "<script>alert('ไฟล์มีขนาดใหญ่เกินไป (เกิน 2MB)!'); window.history.back();</script>";
            exit();
        }

        // สร้างชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
        $new_file_name = uniqid("profile_", true) . "." . $file_type;
        $target_file = $target_dir . $new_file_name;

        // ลบไฟล์เก่าก่อนอัปโหลดใหม่ (ยกเว้นค่าเริ่มต้น)
        if (!empty($user["avatar"]) && file_exists($user["avatar"]) && strpos($user["avatar"], "default") === false) {
            unlink($user["avatar"]); // ลบไฟล์เก่า
        }

        // อัปโหลดไฟล์ใหม่
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image = "php/" . $target_file;
        } else {
            echo "<script>alert('อัปโหลดไฟล์ไม่สำเร็จ!'); window.history.back();</script>";
            exit();
        }
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE users SET display_name=?, username=?, email=?, description=?, avatar=?, updated_at = NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $display_name, $username, $email, $description, $image, $user_id);

    if ($stmt->execute()) {
        // อัปเดต session ใหม่
        $_SESSION["display_name"] = $display_name;
        $_SESSION["username"] = $username;
        $_SESSION["email"] = $email;
        $_SESSION["description"] = $description;
        $_SESSION["avatar"] = $image;
        header("Location: ../profile.php?user_id=$user_id");
        exit();
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด กรุณาลองใหม่'); window.history.back();</script>";
    }
}
