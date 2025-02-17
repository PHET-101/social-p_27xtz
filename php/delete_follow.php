<?php
include 'config.php';

if (isset($_POST['follower_id'], $_POST['followed_id'])) {
    $follower_id = intval($_POST['follower_id']);
    $followed_id = intval($_POST['followed_id']);

    $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
