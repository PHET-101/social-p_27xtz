<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "คุณต้องเข้าสู่ระบบก่อน"]));
}

$user_id = $_SESSION['user_id'];
$comment_id = $_POST['id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$comment_id || $content === '') {
    die(json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]));
}

// ตรวจสอบว่าเป็นเจ้าของคอมเมนต์หรือไม่
$stmt = $conn->prepare("SELECT id FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "ไม่มีสิทธิ์แก้ไขคอมเมนต์นี้"]));
}

$stmt->close();

// อัปเดตคอมเมนต์
$stmt = $conn->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $content, $comment_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "content" => htmlspecialchars($content)]);
exit();
?>
