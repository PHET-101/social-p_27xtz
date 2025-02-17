<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "คุณต้องเข้าสู่ระบบก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$post_id || empty($content)) {
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

// บันทึกความคิดเห็นลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
$stmt->bind_param("iis", $post_id, $user_id, $content);
$stmt->execute();

$comment_id = $stmt->insert_id;
$stmt->close();

// ดึงข้อมูลผู้ใช้เพื่อแสดงชื่อ, avatar และเวลาที่อัปเดต
$user_query = $conn->prepare("SELECT username, avatar FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();
$user_query->close();

// ดึงเวลาอัปเดตของคอมเมนต์
$comment_query = $conn->prepare("SELECT updated_at FROM comments WHERE id = ?");
$comment_query->bind_param("i", $comment_id);
$comment_query->execute();
$comment_result = $comment_query->get_result();
$comment_data = $comment_result->fetch_assoc();
$comment_query->close();

$conn->close();

// ส่งข้อมูลกลับไปให้ JavaScript
echo json_encode([
    "status" => "success",
    "comment_id" => $comment_id,
    "username" => $user_data['username'] ?? 'ไม่ระบุ',
    "avatar" => !empty($user_data['avatar']) ? $user_data['avatar'] : null,
    "updated_at" => $comment_data['updated_at'] ?? date("Y-m-d H:i:s"),
    "content" => htmlspecialchars($content)
]);
exit;
?>
