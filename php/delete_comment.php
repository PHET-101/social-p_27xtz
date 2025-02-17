<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "คุณต้องเข้าสู่ระบบก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['id'] ?? null; // ✅ เปลี่ยนจาก $_GET เป็น $_POST

if (!$comment_id) {
    echo json_encode(["status" => "error", "message" => "ไม่พบคอมเมนต์"]);
    exit;
}

// ✅ ตรวจสอบว่าคอมเมนต์มีอยู่และเป็นของผู้ใช้ที่ล็อกอิน
$stmt = $conn->prepare("SELECT id FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "คุณไม่มีสิทธิ์ลบคอมเมนต์นี้"]);
    exit;
}

$stmt->close();

// ✅ ลบคอมเมนต์และตรวจสอบว่าลบสำเร็จหรือไม่
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "คอมเมนต์ถูกลบเรียบร้อย"]);
} else {
    echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการลบคอมเมนต์"]);
}

$stmt->close();
$conn->close();
exit;
