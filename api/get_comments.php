<?php
session_start();
include('./config.php');
$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $post_id = intval($_GET['post_id']);
    $res = $conn->query("SELECT c.comment, c.created_at, u.fullname FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id=$post_id ORDER BY c.id DESC");
    echo json_encode(['success'=>true, 'comments'=>$res->fetch_all(MYSQLI_ASSOC)]);
} else {
    $post_id = intval($_POST['post_id']);
    $comment = trim($_POST['comment'] ?? '');
    if ($comment === '') {
        echo json_encode(['success'=>false, 'msg'=>'Empty comment']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?,?,?)");
    $stmt->bind_param('iis', $user_id, $post_id, $comment);
    if ($stmt->execute()) {
        $user = $conn->query("SELECT fullname FROM users WHERE id=$user_id")->fetch_assoc();
        echo json_encode(['success'=>true, 'comment'=>['fullname'=>$user['fullname'], 'comment'=>$comment, 'created_at'=>date('Y-m-d H:i:s')], 'comments_count'=>$conn->query("SELECT COUNT(*) AS c FROM comments WHERE post_id=$post_id")->fetch_assoc()['c']]);
    } else echo json_encode(['success'=>false, 'msg'=>'DB error']);
}
?>
