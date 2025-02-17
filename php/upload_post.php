<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php'; // เชื่อมต่อฐานข้อมูล

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "คุณต้องล็อกอินก่อนโพสต์!";
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_text = htmlspecialchars($_POST['post_text'], ENT_QUOTES, 'UTF-8');
$image_path = null;

// ตรวจสอบว่ามีข้อมูลอย่างใดอย่างหนึ่ง (ข้อความหรือรูปภาพ)
if (empty($post_text) && empty($_FILES['post_image']['name'])) {
    $_SESSION['error'] = "ต้องกรอกข้อความหรืออัปโหลดรูปภาพอย่างน้อย 1 อย่าง!";
    header("Location: ../index.php");
    exit();
}

// ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
if (!empty($_FILES['post_image']['name'])) {
    $target_dir = "uploads/";

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // ตรวจสอบไฟล์ว่าเป็นภาพจริงหรือไม่
    $check = getimagesize($_FILES["post_image"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ!";
        header("Location: ../index.php");
        exit();
    }

    // จำกัดขนาดไฟล์ (สูงสุด 5MB)
    if ($_FILES["post_image"]["size"] > 5 * 1024 * 1024) {
        $_SESSION['error'] = "ไฟล์มีขนาดใหญ่เกินไป (ต้องไม่เกิน 5MB)!";
        header("Location: ../index.php");
        exit();
    }

    // สร้างชื่อไฟล์แบบสุ่ม
    $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);
    $imageFileType = strtolower(pathinfo($_FILES["post_image"]["name"], PATHINFO_EXTENSION)); // ดึงนามสกุลไฟล์
    $new_image_name = time() . "_" . $random . "." . $imageFileType; // ตั้งชื่อใหม่ เช่น 1700000000_XyZ12.jpg
    $target_file = $target_dir . $new_image_name;

    // ตรวจสอบประเภทไฟล์
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        $_SESSION['error'] = "รองรับเฉพาะไฟล์ JPG, JPEG, PNG, GIF เท่านั้น!";
        header("Location: ../index.php");
        exit();
    }

    // ย้ายไฟล์ไปยังโฟลเดอร์
    if (move_uploaded_file($_FILES["post_image"]["tmp_name"], $target_file)) {
        $image_path = "php/" . $target_file; // บันทึกเส้นทางของไฟล์
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์!";
        header("Location: ../index.php");
        exit();
    }
}

// บันทึกโพสต์ลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $post_text, $image_path);

if ($stmt->execute()) {
    $_SESSION['success'] = "โพสต์ของคุณถูกเผยแพร่เรียบร้อย!";
} else {
    $_SESSION['error'] = "เกิดข้อผิดพลาดในการโพสต์ กรุณาลองใหม่!";
}

$stmt->close();
$conn->close();

header("Location: ../index.php");
exit();
