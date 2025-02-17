<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "คุณต้องเข้าสู่ระบบก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['id'] ?? null;

if (!$post_id) {
    echo json_encode(["status" => "error", "message" => "ไม่พบโพสต์"]);
    exit;
}

// ตรวจสอบว่าโพสต์เป็นของผู้ใช้ที่ล็อกอิน และดึง image_path
$stmt = $conn->prepare("SELECT image_path FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "คุณไม่มีสิทธิ์ลบโพสต์นี้"]);
    exit;
}

$row = $result->fetch_assoc();
$image_path = $row['image_path'];

$stmt->close();

// ลบไฟล์ภาพหากมีการอัปโหลด
if (!empty($image_path)) {
    $image_file = str_replace("php/", "", $image_path); // ปรับเส้นทางให้ตรงกับโฟลเดอร์ uploads
    $full_path = __DIR__ . "/" . $image_file;

    if (file_exists($full_path)) {
        unlink($full_path); // ลบไฟล์ภาพ
    }
}

// ลบโพสต์จากฐานข้อมูล
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(["status" => "success", "message" => "โพสต์และรูปภาพถูกลบเรียบร้อย"]);
exit;
?>
