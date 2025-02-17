<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "กรุณาเข้าสู่ระบบ"]);
        exit;
    }

    $post_id = $_POST["id"] ?? null;
    $new_content = trim($_POST["content"] ?? "");

    if (!$post_id || empty($new_content)) {
        echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ถูกต้อง"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // ตรวจสอบว่าโพสต์เป็นของผู้ใช้ที่ล็อกอินอยู่หรือไม่
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['user_id'] != $user_id) {
            echo json_encode(["status" => "error", "message" => "คุณไม่มีสิทธิ์แก้ไขโพสต์นี้"]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบโพสต์"]);
        exit;
    }

    // อัปเดตโพสต์
    $stmt = $conn->prepare("UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_content, $post_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "โพสต์ถูกแก้ไขเรียบร้อย", "content" => htmlspecialchars($new_content)]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาด"]);
    }
}
?>
