<?php

session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0); 

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'socialthreads';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success'=>false,'msg'=>'DB connection failed']);
    exit;
}
$conn->set_charset('utf8mb4');

// Helper to return error
function jsonError($msg, $code=400) {
    http_response_code($code);
    echo json_encode(['success'=>false,'msg'=>$msg]);
    exit;
}

if (!isset($_SESSION['email'])) {
    $loggedIn = false;
    $currentUserId = null;
} else {
    $loggedIn = true;
    $email = $_SESSION['email'];
    $s = $conn->prepare("SELECT id, fullname, avatar FROM users WHERE email = ?");
    $s->bind_param("s", $email);
    $s->execute();
    $res = $s->get_result();
    if ($res->num_rows === 0) {
        $loggedIn = false;
        $currentUserId = null;
        $currentUserName = null;
    } else {
        $u = $res->fetch_assoc();
        $currentUserId = (int)$u['id'];
        $currentUserName = $u['fullname'] ?: $email;
        $currentUserAvatar = $u['avatar'] ?: null;
    }
}

$action = $_REQUEST['action'] ?? ($_GET['action'] ?? null);

if ($action === 'create_post') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

    $content = trim($_POST['content'] ?? '');
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) jsonError('Upload error');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) jsonError('Invalid image type (jpg/png/webp allowed)');
        if ($file['size'] > 5 * 1024 * 1024) jsonError('Image too large (max 5MB)');

        $ext = $allowed[$mime];
        $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $dest = $uploadDir . '/' . $newName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) jsonError('Failed to save image', 500);
        $imagePath = 'uploads/' . $newName;
    }

    if ($content === '' && $imagePath === null) jsonError('Post content or image required');

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $currentUserId, $content, $imagePath);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'post_id'=>$stmt->insert_id]);
        exit;
    } else {
        jsonError('DB error on insert', 500);
    }
}

if ($action === 'get_posts') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    if ($limit <= 0 || $limit > 50) $limit = 8;
    $before = isset($_GET['before']) ? (int)$_GET['before'] : 0;

    if ($before > 0) {
        $sql = "SELECT p.id, p.content, p.image, p.created_at, p.user_id, p.shares, u.fullname, u.avatar
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.id < ?
                ORDER BY p.id DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $before, $limit);
    } else {
        $sql = "SELECT p.id, p.content, p.image, p.created_at, p.user_id, p.shares, u.fullname, u.avatar
                FROM posts p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.id DESC
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $posts = [];

    while ($row = $res->fetch_assoc()) {
        $postId = (int)$row['id'];
        // likes count
        $l = $conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = ?");
        $l->bind_param("i", $postId);
        $l->execute();
        $likes = (int)$l->get_result()->fetch_assoc()['cnt'];

        // comments count
        $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
        $c->bind_param("i", $postId);
        $c->execute();
        $commentsCount = (int)$c->get_result()->fetch_assoc()['cnt'];

        // liked by me?
        $likedByMe = false;
        if ($loggedIn) {
            $ck = $conn->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
            $ck->bind_param("ii", $postId, $currentUserId);
            $ck->execute();
            $likedByMe = $ck->get_result()->num_rows > 0;
        }

        // recent comments (limit 3 newest)
        $commStmt = $conn->prepare("SELECT c.comment, c.created_at, u.fullname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC LIMIT 3");
        $commStmt->bind_param("i", $postId);
        $commStmt->execute();
        $commRes = $commStmt->get_result();
        $recentComments = [];
        while ($cr = $commRes->fetch_assoc()) $recentComments[] = $cr;

        $posts[] = [
            'id' => $postId,
            'content' => $row['content'],
            'image' => $row['image'],
            'created_at' => $row['created_at'],
            'author' => $row['fullname'],
            'author_avatar' => $row['avatar'],
            'likes' => $likes,
            'liked_by_me' => $likedByMe,
            'comments_count' => $commentsCount,
            'recent_comments' => $recentComments,
            'shares' => (int)$row['shares']
        ];
    }

    echo json_encode(['success'=>true,'posts'=>$posts], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- LIKE toggle (POST) ---
if ($action === 'like') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');

    // check existing
    $check = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $check->bind_param("ii", $post_id, $currentUserId);
    $check->execute();
    $r = $check->get_result();
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
        $del->bind_param("i", $row['id']);
        $del->execute();
        $actionDone = 'unliked';
    } else {
        $ins = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $ins->bind_param("ii", $post_id, $currentUserId);
        $ins->execute();
        $actionDone = 'liked';
    }

    $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = ?");
    $c->bind_param("i", $post_id);
    $c->execute();
    $likesCount = (int)$c->get_result()->fetch_assoc()['cnt'];

    echo json_encode(['success'=>true,'action'=>$actionDone,'likes'=>$likesCount]);
    exit;
}

// --- COMMENT create (POST) ---
if ($action === 'comment') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    if ($post_id <= 0 || $comment === '') jsonError('Invalid data');

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $currentUserId, $comment);
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
        exit;
    } else {
        jsonError('DB error on insert', 500);
    }
}

// --- GET COMMENTS (GET) ---
if ($action === 'get_comments') {
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');
    $stmt = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.fullname FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = [];
    while ($r = $res->fetch_assoc()) $comments[] = $r;
    echo json_encode(['success'=>true,'comments'=>$comments]);
    exit;
}

// --- FOLLOW toggle (POST) ---
if ($action === 'follow') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $followee = isset($_POST['followee_id']) ? (int)$_POST['followee_id'] : 0;
    if ($followee <= 0) jsonError('Invalid user to follow');

    if ($followee === $currentUserId) jsonError('Cannot follow yourself');

    $check = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND followee_id = ?");
    $check->bind_param("ii", $currentUserId, $followee);
    $check->execute();
    $r = $check->get_result();
    if ($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $del = $conn->prepare("DELETE FROM follows WHERE id = ?");
        $del->bind_param("i", $row['id']);
        $del->execute();
        echo json_encode(['success'=>true,'action'=>'unfollowed']);
        exit;
    } else {
        $ins = $conn->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (?, ?)");
        $ins->bind_param("ii", $currentUserId, $followee);
        $ins->execute();
        echo json_encode(['success'=>true,'action'=>'followed']);
        exit;
    }
}

// --- SHARE (increment share count) ---
if ($action === 'share') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');
    $up = $conn->prepare("UPDATE posts SET shares = shares + 1 WHERE id = ?");
    $up->bind_param("i", $post_id);
    $up->execute();
    $c = $conn->prepare("SELECT shares FROM posts WHERE id = ?");
    $c->bind_param("i", $post_id);
    $c->execute();
    $shares = (int)$c->get_result()->fetch_assoc()['shares'];
    echo json_encode(['success'=>true,'shares'=>$shares]);
    exit;
}

jsonError('Unknown action', 400);
?>
