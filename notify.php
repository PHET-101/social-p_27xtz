<?php
include 'config.php';

function addNotification($user_id, $type, $source_id) {
    global $conn;
    $sql = "INSERT INTO notifications (user_id, type, source_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $user_id, $type, $source_id);
    $stmt->execute();
}
?>
