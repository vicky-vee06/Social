<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include_once(__DIR__ . './config.php');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = trim($_POST['comment'] ?? '');
$user_id = (int)$_SESSION['id'];
if ($post_id <= 0 || $comment === '') { http_response_code(400); echo json_encode(['success'=>false,'msg'=>'Invalid']); exit; }

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
if ($stmt->execute()) {
  $newId = $stmt->insert_id;
  $q = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.fullname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
  $q->bind_param("i", $newId);
  $q->execute();
  $commentRow = $q->get_result()->fetch_assoc();

  $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
  $c->bind_param("i", $post_id);
  $c->execute();
  $commentsCount = (int)$c->get_result()->fetch_assoc()['cnt'];

  echo json_encode(['success'=>true,'comment'=>$commentRow,'comments_count'=>$commentsCount]);
} else {
  http_response_code(500);
  echo json_encode(['success'=>false,'msg'=>'DB error']);
}
exit;
