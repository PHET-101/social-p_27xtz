<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "คุณต้องเข้าสู่ระบบก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$post_id || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(["status" => "error", "message" => "คำขอไม่ถูกต้อง"]);
    exit;
}

if ($action === 'like') {
    // ตรวจสอบว่ามีไลค์อยู่แล้วหรือไม่
    $check_like = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $check_like->bind_param("ii", $user_id, $post_id);
    $check_like->execute();
    $check_like->store_result();

    if ($check_like->num_rows === 0) {
        // เพิ่มไลค์
        $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    $check_like->close();
} elseif ($action === 'unlike') {
    // ลบไลค์
    $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// นับจำนวนไลค์ใหม่
$like_count_query = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
$like_count_query->bind_param("i", $post_id);
$like_count_query->execute();
$like_count_result = $like_count_query->get_result();
$like_count = $like_count_result->fetch_assoc()['like_count'];
$like_count_query->close();

$conn->close();

echo json_encode(["status" => "success", "action" => $action, "like_count" => $like_count]);
exit;
