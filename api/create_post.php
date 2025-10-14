<?php
session_start();
include('./config.php');

$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $imagePath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('post_').'.'.$ext;
        $target = '../uploads/'.$fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $imagePath = 'uploads/'.$fileName;
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $user_id, $content, $imagePath);

    if ($stmt->execute()) {
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'msg'=>'Database error']);
    }
}
?>
