<?php
include '../php/config.php';

if (isset($_POST["username"])) {
    $username = trim($_POST["username"]);
    if (!preg_match("/^[a-z_.]{3,20}$/", $username)) {
        echo json_encode(["status" => "error", "message" => "❌ ชื่อผู้ใช้ต้องมี a-z, _ และ . เท่านั้น!"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "❌ ชื่อผู้ใช้นี้ถูกใช้งานแล้ว!"]);
    } else {
        echo json_encode(["status" => "success", "message" => "✅ ใช้ชื่อนี้ได้!"]);
    }
    exit;
}

if (isset($_POST["email"])) {
    $email = trim($_POST["email"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "❌ รูปแบบอีเมลไม่ถูกต้อง!"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "อีเมลนี้ถูกใช้งานแล้ว!"]);
    } else {
        echo json_encode(["status" => "success", "message" => "✅ ใช้อีเมลนี้ได้!"]);
    }
    exit;
}
?>
