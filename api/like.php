<?php
session_start();
include('./config.php');
$user_id = $_SESSION['user_id'] ?? 1;
$post_id = intval($_POST['post_id'] ?? 0);

$check = $conn->query("SELECT * FROM likes WHERE user_id=$user_id AND post_id=$post_id");
if ($check->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE user_id=$user_id AND post_id=$post_id");
    $conn->query("UPDATE posts SET id=id WHERE id=$post_id");
    $likes = $conn->query("SELECT COUNT(*) AS c FROM likes WHERE post_id=$post_id")->fetch_assoc()['c'];
    echo json_encode(['success'=>true, 'action'=>'unliked', 'likes'=>$likes]);
} else {
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    $likes = $conn->query("SELECT COUNT(*) AS c FROM likes WHERE post_id=$post_id")->fetch_assoc()['c'];
    echo json_encode(['success'=>true, 'action'=>'liked', 'likes'=>$likes]);
}
?>
