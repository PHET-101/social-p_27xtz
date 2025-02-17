<?php
include 'php/config.php';

// ตรวจสอบค่าที่ส่งมาจาก AJAX
if (isset($_POST['followed_id'], $_POST['action'])) {
    $followed_id = intval($_POST['followed_id']);
    $action = $_POST['action'];
    $follower_id = $_GET['user_id']; // สมมติว่าผู้ใช้ที่ล็อกอินอยู่คือผู้ที่ส่งคำขอ

    if ($action == 'follow') {
        // เพิ่มการติดตาม
        $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
    } else if ($action == 'unfollow') {
        // ลบการติดตาม
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
    }

    // ส่งผลลัพธ์กลับไปให้ฝั่ง JavaScript
    echo "success";
} else {
    // ถ้าไม่มีค่าหรือข้อมูลผิดพลาด
    echo "error";
}
?>
