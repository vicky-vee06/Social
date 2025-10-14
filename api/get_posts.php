<?php
session_start();
include('./config.php');
$limit = intval($_GET['limit'] ?? 6);
$before = intval($_GET['before'] ?? 0);

$q = "SELECT p.*, u.fullname AS author, u.avatar AS author_avatar,
     (SELECT COUNT(*) FROM likes l WHERE l.post_id=p.id) AS likes,
     (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) AS comments_count
     FROM posts p JOIN users u ON p.user_id=u.id";

if ($before > 0) $q .= " WHERE p.id < $before";
$q .= " ORDER BY p.id DESC LIMIT $limit";

$res = $conn->query($q);
$posts = [];

while ($row = $res->fetch_assoc()) {
    $postId = $row['id'];
    $commentsRes = $conn->query("SELECT c.comment, c.created_at, u.fullname FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id=$postId ORDER BY c.id DESC LIMIT 2");
    $row['recent_comments'] = $commentsRes->fetch_all(MYSQLI_ASSOC);
    $row['liked_by_me'] = false; 
    $posts[] = $row;
}

echo json_encode(['success'=>true, 'posts'=>$posts]);
?>
