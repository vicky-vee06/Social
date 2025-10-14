<?php
session_start();
include('./inc/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit;
}

$me = intval($_SESSION['user_id']);

if (!isset($_GET['receiver_id']) || empty($_GET['receiver_id'])) {
    die("Invalid user: receiver_id missing in URL.");
}

$receiver_id = intval($_GET['receiver_id']);

// Check if the receiver exists
$stmt = $conn->prepare("SELECT id, name, first_name, last_name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Invalid user: no such user in database.");
}

$receiver = $res->fetch_assoc();
$receiver_name = $receiver['name'] ?: trim(($receiver['first_name'] ?? '') . ' ' . ($receiver['last_name'] ?? ''));
if ($receiver_name === '') $receiver_name = 'User ' . $receiver_id;

// Mark their messages as read
$conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = $receiver_id AND receiver_id = $me");

// Handle message send
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    $imgPath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/messages/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
        $imgPath = $targetFile;
    }

    if ($msg !== '' || $imgPath) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, image, created_at, is_read) VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("iiss", $me, $receiver_id, $msg, $imgPath);
        $stmt->execute();
    }

    header("Location: dm.php?receiver_id=" . $receiver_id);
    exit;
}

// Fetch conversation
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $me, $receiver_id, $receiver_id, $me);
$stmt->execute();
$messages = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat with <?= htmlspecialchars($receiver_name) ?></title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
<style>
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f5f5f5;}
.chat-container{max-width:800px;margin:30px auto;background:white;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,0.1);display:flex;flex-direction:column;height:85vh;}
.chat-header{display:flex;align-items:center;padding:15px;border-bottom:1px solid #eee;background:#4b0082;color:white;border-top-left-radius:10px;border-top-right-radius:10px;}
.chat-header img{width:40px;height:40px;border-radius:50%;margin-right:10px;object-fit:cover;}
.chat-body{flex:1;padding:20px;overflow-y:auto;}
.message{max-width:70%;margin-bottom:10px;padding:10px 14px;border-radius:10px;font-size:15px;line-height:1.4;}
.sent{background:#4b0082;color:white;margin-left:auto;border-bottom-right-radius:0;}
.received{background:#e4e4e4;color:black;margin-right:auto;border-bottom-left-radius:0;}
.message img{max-width:180px;border-radius:8px;display:block;margin-top:5px;}
.chat-footer{padding:10px;border-top:1px solid #eee;display:flex;align-items:center;gap:10px;background:#fafafa;border-bottom-left-radius:10px;border-bottom-right-radius:10px;}
.chat-footer textarea{flex:1;resize:none;padding:10px;border:1px solid #ccc;border-radius:8px;font-family:inherit;font-size:15px;}
.chat-footer button{background:#4b0082;color:white;border:none;padding:10px 18px;border-radius:8px;cursor:pointer;font-weight:bold;}
.chat-footer button:hover{opacity:0.9;}
.back-btn{margin-right:10px;color:white;text-decoration:none;font-weight:bold;}
</style>
</head>
<body>

<div class="chat-container">
  <div class="chat-header">
    <a href="message.php" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <?php if (!empty($receiver['profile_pic'])): ?>
      <img src="<?= htmlspecialchars($receiver['profile_pic']) ?>" alt="">
    <?php else: ?>
      <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100" alt="">
    <?php endif; ?>
    <h3><?= htmlspecialchars($receiver_name) ?></h3>
  </div>

  <div class="chat-body">
    <?php if ($messages->num_rows === 0): ?>
      <p style="text-align:center;color:#888;">No messages yet. Start the conversation!</p>
    <?php else: ?>
      <?php while($msg = $messages->fetch_assoc()): ?>
        <div class="message <?= $msg['sender_id'] == $me ? 'sent' : 'received' ?>">
          <?= nl2br(htmlspecialchars($msg['message'])) ?>
          <?php if (!empty($msg['image'])): ?>
            <img src="<?= htmlspecialchars($msg['image']) ?>" alt="sent image">
          <?php endif; ?>
          <div style="font-size:12px;color:#aaa;margin-top:3px;"><?= date('M j, H:i', strtotime($msg['created_at'])) ?></div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <form class="chat-footer" method="POST" enctype="multipart/form-data">
    <textarea name="message" placeholder="Type a message..."></textarea>
    <input type="file" name="image" accept="image/*">
    <button type="submit">Send</button>
  </form>
</div>

</body>
</html>
