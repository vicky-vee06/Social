<?php

session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
  $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}

// ---------- DB config ----------
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'user_system';

// connect
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die('DB connection error: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// ---------- helpers ----------
function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function jsonError($msg, $code = 400) {
    http_response_code($code);
    jsonResponse(['success' => false, 'msg' => $msg]);
}

$loggedIn = false;
$currentUserId = null;
$currentUserEmail = null;

if (!empty($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows) {
        $u = $res->fetch_assoc();
        $loggedIn = true;
        $currentUserId = (int)$u['id'];
        $currentUserEmail = $u['email'];
    }
    $stmt && $stmt->close();
}

// route actions
$action = $_REQUEST['action'] ?? null;

if ($action === 'create_post') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

    $content = trim($_POST['content'] ?? '');
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] !== UPLOAD_ERR_OK) jsonError('Upload error');

        // validate mime
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) jsonError('Invalid image type (jpg/png/webp allowed)');
        if ($file['size'] > 6 * 1024 * 1024) jsonError('Image too large (max 6MB)');

        $ext = $allowed[$mime];
        try {
            $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        } catch (Exception $e) {
            $imageName = time() . '_' . substr(md5(uniqid()),0,12) . '.' . $ext;
        }

        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $dest = $uploadDir . '/' . $imageName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) jsonError('Failed to save image', 500);
    }

    if ($content === '' && $imageName === null) jsonError('Post content or image required');

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $currentUserId, $content, $imageName);
    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'post_id' => $stmt->insert_id]);
    } else {
        jsonError('DB error on insert', 500);
    }
}

/* ------------------- Get posts (GET) ------------------- */
if ($action === 'get_posts') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    if ($limit <= 0 || $limit > 100) $limit = 12;
    $before = isset($_GET['before']) ? (int)$_GET['before'] : 0;

    if ($before > 0) {
        $sql = "SELECT p.id, p.content, p.image, p.created_at, p.user_id, IFNULL(p.shares,0) AS shares, u.email
                 FROM posts p JOIN users u ON p.user_id = u.id
                 WHERE p.id < ?
                 ORDER BY p.id DESC LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $before, $limit);
    } else {
        // This query fetches the latest posts when 'before' is 0 or not set
        $sql = "SELECT p.id, p.content, p.image, p.created_at, p.user_id, IFNULL(p.shares,0) AS shares, u.email
                 FROM posts p JOIN users u ON p.user_id = u.id
                 ORDER BY p.id DESC LIMIT ?";
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
        $l->close();
        // comments count
        $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
        $c->bind_param("i", $postId);
        $c->execute();
        $commentsCount = (int)$c->get_result()->fetch_assoc()['cnt'];
        $c->close();
        // liked by me
        $likedByMe = false;
        if ($loggedIn) {
            $ck = $conn->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
            $ck->bind_param("ii", $postId, $currentUserId);
            $ck->execute();
            $likedByMe = $ck->get_result()->num_rows > 0;
            $ck->close();
        }
        // recent comments (latest 3)
        $commStmt = $conn->prepare("SELECT c.comment, c.created_at, u.email FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC LIMIT 3");
        $commStmt->bind_param("i", $postId);
        $commStmt->execute();
        $commRes = $commStmt->get_result();
        $recentComments = [];
        while ($cr = $commRes->fetch_assoc()) $recentComments[] = $cr;
        $commStmt->close();

        $posts[] = [
            'id' => $postId,
            'content' => $row['content'],
            'image' => $row['image'] ? 'uploads/' . $row['image'] : null,
            'created_at' => $row['created_at'],
            'author' => $row['email'],
            'author_id' => (int)$row['user_id'],
            'likes' => $likes,
            'liked_by_me' => $likedByMe,
            'comments_count' => $commentsCount,
            'recent_comments' => $recentComments,
            'shares' => (int)$row['shares']
        ];
    }
    $stmt->close();
    jsonResponse(['success' => true, 'posts' => $posts]);
}

/* ------------------- Like toggle ------------------- */
if ($action === 'like') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');

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
        $del->close();
    } else {
        $ins = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $ins->bind_param("ii", $post_id, $currentUserId);
        $ins->execute();
        $actionDone = 'liked';
        $ins->close();
    }
    $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = ?");
    $c->bind_param("i", $post_id);
    $c->execute();
    $likesCount = (int)$c->get_result()->fetch_assoc()['cnt'];
    $c->close();
    jsonResponse(['success' => true, 'action' => $actionDone, 'likes' => $likesCount]);
}

/* ------------------- Comment create ------------------- */
if ($action === 'comment') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $comment = trim($_POST['comment'] ?? '');
    if ($post_id <= 0 || $comment === '') jsonError('Invalid data');

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $currentUserId, $comment);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $q = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.email FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
        $q->bind_param("i", $newId);
        $q->execute();
        $commentRow = $q->get_result()->fetch_assoc();
        $q->close();

        $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = ?");
        $c->bind_param("i", $post_id);
        $c->execute();
        $commentsCount = (int)$c->get_result()->fetch_assoc()['cnt'];
        $c->close();

        jsonResponse(['success' => true, 'comment' => $commentRow, 'comments_count' => $commentsCount]);
    } else {
        jsonError('DB error on insert', 500);
    }
}

/* ------------------- Get comments ------------------- */
if ($action === 'get_comments') {
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');
    $stmt = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.email FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = [];
    while ($r = $res->fetch_assoc()) $comments[] = $r;
    $stmt->close();
    jsonResponse(['success' => true, 'comments' => $comments]);
}

/* ------------------- Follow toggle ------------------- */
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
        $del->close();
        jsonResponse(['success' => true, 'action' => 'unfollowed']);
    } else {
        $ins = $conn->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (?, ?)");
        $ins->bind_param("ii", $currentUserId, $followee);
        $ins->execute();
        $ins->close();
        jsonResponse(['success' => true, 'action' => 'followed']);
    }
}

/* ------------------- Share (increment) ------------------- */
if ($action === 'share') {
    if (!$loggedIn) jsonError('Not logged in', 401);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    if ($post_id <= 0) jsonError('Invalid post id');

    $up = $conn->prepare("UPDATE posts SET shares = IFNULL(shares,0) + 1 WHERE id = ?");
    $up->bind_param("i", $post_id);
    $up->execute();
    $up->close();

    $c = $conn->prepare("SELECT shares FROM posts WHERE id = ?");
    $c->bind_param("i", $post_id);
    $c->execute();
    $shares = (int)$c->get_result()->fetch_assoc()['shares'];
    $c->close();

    jsonResponse(['success' => true, 'shares' => $shares]);
}

/* ------------------- If no action -> render page ------------------- */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>SocialThreads — Home</title>
  <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
  <style>
    :root {
      --primary: #793DDC;
      --primary-hover: #6930c3;
      --bg: #F8F7FF;
      --card: #fff;
      --text: #333;
      --muted: #777;
      --border: #eee;
      --shadow-sm: 0 2px 6px rgba(0,0,0,0.06);
      --shadow-md: 0 6px 20px rgba(0,0,0,0.08);
      --hover-bg: rgba(121,61,220,0.07);
    }
    *{box-sizing:border-box}
    body{font-family:Inter,system-ui,Arial,sans-serif;background:var(--bg);margin:0;display:flex;justify-content:center;}
    .container{width:92%;max-width:1200px;display:flex;min-height:100vh;padding:20px 0;}
    .left-sidebar{width:250px;padding:20px;background:var(--card);border-right:1px solid var(--border);height:100vh;position:sticky;top:0;}
    .logo{font-size:26px;font-weight:700;color:var(--primary);margin-bottom:18px;}
    .nav-link{display:flex;gap:10px;align-items:center;padding:10px 12px;border-radius:8px;color:var(--muted);text-decoration:none;font-weight:600;margin-bottom:8px;transition:all .18s;}
    .nav-link:hover{background:var(--hover-bg);color:var(--primary);transform:translateX(6px)}
    .nav-link.active{background:rgba(121,61,220,0.1);color:var(--primary)}
    .main-feed{flex:1;padding:20px 30px;overflow:auto;}
    .right-sidebar{width:300px;padding:20px;background:var(--card);border-left:1px solid var(--border);height:100vh;position:sticky;top:0}
    .post-composer{background:var(--card);padding:18px;border-radius:12px;box-shadow:var(--shadow-sm);border:1px solid var(--border);margin-bottom:20px;transition:all .18s}
    .post-composer:focus-within{box-shadow:var(--shadow-md);transform:translateY(-3px)}
    .composer-input{display:flex;gap:12px;align-items:center;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:12px}
    .profile-pic-tiny{width:48px;height:48px;border-radius:50%;background:#ddd;border:2px solid white;box-shadow:var(--shadow-sm)}
    .composer-input input[type="text"]{flex:1;border:0;outline:none;font-size:15px;color:var(--text)}
    .composer-actions{display:flex;justify-content:space-between;align-items:center;gap:12px}
    .composer-actions label{cursor:pointer;color:var(--primary);display:flex;align-items:center;gap:8px;font-weight:700}
    .post-btn{background:var(--primary);color:white;border:0;padding:10px 14px;border-radius:10px;cursor:pointer;font-weight:700}
    .post-btn:hover{background:var(--primary-hover);transform:translateY(-2px)}
    .post-card{background:var(--card);padding:18px;border-radius:12px;border:1px solid var(--border);margin-bottom:16px;box-shadow:var(--shadow-sm);transition:all .18s}
    .post-card:hover{box-shadow:var(--shadow-md);transform:translateY(-3px)}
    .post-header{display:flex;gap:12px;align-items:center;margin-bottom:8px}
    .post-header strong{font-size:15px;color:var(--text)}
    .post-header small{display:block;color:var(--muted);font-size:12px}
    .post-body{font-size:15px;color:var(--text);line-height:1.5}
    .post-image{width:100%;margin-top:12px;border-radius:8px;object-fit:cover}
    .post-actions{display:flex;gap:12px;border-top:1px solid var(--border);padding-top:10px;margin-top:12px}
    .action-btn{background:none;border:0;color:var(--muted);padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:700;display:flex;gap:8px;align-items:center}
    .action-btn:hover{background:var(--hover-bg);color:var(--primary)}
    .action-btn.active{color:var(--primary)}
    .comments{margin-top:12px}
    .comment{background:#fbfbfb;padding:10px;border-radius:8px;margin-bottom:8px;border:1px solid #f0f0f0}
    .right-sidebar h3{margin:0 0 12px 0}
    .suggestion-card{display:flex;align-items:center;justify-content:space-between;background:transparent;padding:8px;border-radius:10px;margin-bottom:10px}
    .suggestion-details{display:flex;gap:10px;align-items:center}
    .follow-btn{background:var(--primary);color:white;border:0;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:700}
    .follow-btn:hover{background:var(--primary-hover)}
    #preview{max-width:300px;margin-top:12px;border-radius:8px;display:none}
    @media(max-width:900px){.container{flex-direction:column}.left-sidebar,.right-sidebar{position:relative;height:auto;width:100%}}
  </style>
</head>
<body>
  <div class="container">
    <div class="left-sidebar">
      <div class="logo">SocialThreads</div>
      <a class="nav-link active" href="#"><i class="fas fa-home"></i> Home</a>
      <a class="nav-link" href="./student_aboutprofile.php"><i class="fas fa-user"></i> Profile</a>
      <a class="nav-link" href="./public-explore.php"><i class="fas fa-compass"></i> Explore</a>

      <h4 style="font-size:13px;color:var(--muted);margin-top:20px">MY COMMUNITIES</h4>
      <a class="nav-link" href="./community_timeline.php" style="padding-left:10px"><i class="fas fa-book"></i> Campus Book Club</a>
      <a class="nav-link" href="#" style="padding-left:10px"><i class="fas fa-laptop-code"></i> Campus Coding Club</a>
    </div>

    <div class="main-feed">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
        <h2 style="margin:0">Good Afternoon, <?= htmlspecialchars($currentUserEmail ?? ($_SESSION['email'] ?? 'User')) ?></h2>
        <div style="display:flex;gap:10px">
          <a href="./settings.php"><button style="border:0;background:none;font-size:18px;color:var(--muted)"><i class="fas fa-cog"></i></button></a>
          <button style="border:0;background:none;font-size:18px;color:var(--muted)"><i class="fas fa-bell"></i></button>
        </div>
      </div>

      <div class="post-composer" aria-label="Create post">
        <div class="composer-input">
          <div class="profile-pic-tiny" style="background-image:url('<?= htmlspecialchars($_SESSION['profile_pic']) ?>');background-size:cover"></div>
          <input id="composerText" type="text" placeholder="What's on your mind?">
        </div>
        <div class="composer-actions">
          <label>
            <i class="fas fa-image"></i><span id="imgLabel" style="font-weight:600;margin-left:6px">Add photo</span>
            <input id="composerImage" type="file" accept="image/*" style="display:none">
          </label>
          <button id="postBtn" class="post-btn"><i class="fas fa-paper-plane"></i> Post</button>
        </div>
        <img id="preview" src="" alt="preview"/>
      </div>

      <!--  -->




      <div id="feedContainer">
<?php
$feedLimit = 8;
$sql = "SELECT p.id, p.content, p.image, p.created_at, p.user_id, IFNULL(p.shares,0) AS shares, u.email
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        ORDER BY p.id DESC 
        LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $feedLimit);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()):
    $postId = (int)$row['id'];

    // likes count
    $l = $conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id=?");
    $l->bind_param("i", $postId);
    $l->execute();
    $likes = (int)$l->get_result()->fetch_assoc()['cnt'];
    $l->close();

    // comments count
    $c = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE post_id=?");
    $c->bind_param("i", $postId);
    $c->execute();
    $commentsCount = (int)$c->get_result()->fetch_assoc()['cnt'];
    $c->close();

    // liked by me
    $likedByMe = false;
    if ($loggedIn) {
        $ck = $conn->prepare("SELECT 1 FROM likes WHERE post_id=? AND user_id=? LIMIT 1");
        $ck->bind_param("ii", $postId, $currentUserId);
        $ck->execute();
        $likedByMe = $ck->get_result()->num_rows > 0;
        $ck->close();
    }

    $imgPath = $row['image'] ? 'uploads/' . $row['image'] : null;
?>
<div class="post-card" data-id="<?= $postId ?>">
  <div class="post-header">
    <div class="profile-pic-tiny" style="background-image:url('<?= htmlspecialchars($_SESSION['profile_pic']) ?>');background-size:cover"></div>
    <div>
      <strong><?= htmlspecialchars($row['email']) ?></strong>
      <small><?= $row['created_at'] ?></small>
    </div>
  </div>
  <div class="post-body"><p><?= htmlspecialchars($row['content']) ?></p></div>
  <?php if($imgPath): ?>
    <img src="<?= $imgPath ?>" class="post-image" alt="post image">
  <?php endif; ?>
  <div class="post-actions">
    <button class="action-btn like <?= $likedByMe ? 'active' : '' ?>" data-id="<?= $postId ?>">
      <i class="fas fa-thumbs-up"></i> <span class="likes-count"><?= $likes ?></span>
    </button>
    <button class="action-btn toggle-comments" data-id="<?= $postId ?>">
      <i class="fas fa-comment"></i> <span class="comments-count"><?= $commentsCount ?></span>
    </button>
    <button class="action-btn share" data-id="<?= $postId ?>">
      <i class="fas fa-share"></i> <span class="shares-count"><?= $row['shares'] ?></span>
    </button>
  </div>
  <div class="comments" id="comments-<?= $postId ?>" style="display:none"></div>
</div>
<?php endwhile; ?>
</div>




      <!--  -->

      <div id="feedContainer"></div>
      <div id="loader" style="text-align:center;color:var(--muted);display:none;padding:12px">Loading...</div>
      <div id="sentinel"></div>
    </div>

    <div class="right-sidebar">
      <h3>Who to Follow</h3>
      <?php
      // show some users to follow (exclude current)
      $stmt = $conn->prepare("SELECT id, email FROM users WHERE id <> ? ORDER BY id DESC LIMIT 8");
      $exclude = $currentUserId ?? 0;
      $stmt->bind_param("i", $exclude);
      $stmt->execute();
      $ur = $stmt->get_result();
      while ($row = $ur->fetch_assoc()):
      ?>
      <div class="suggestion-card">
        <div class="suggestion-details">
          <div class="profile-pic-tiny" style="background-image:url('<?= htmlspecialchars($_SESSION['profile_pic']) ?>');background-size:cover"></div>
          <div>
            <strong><?= htmlspecialchars(explode('@',$row['email'])[0]) ?></strong>
            <small style="display:block;color:var(--muted)"><?= htmlspecialchars($row['email']) ?></small>
          </div>
        </div>
        <button class="follow-btn" data-user-id="<?= (int)$row['id'] ?>"><?= 'Follow' ?></button>
      </div>
      <?php endwhile; $stmt->close(); ?>
    </div>
  </div>

<script>
/* Frontend JS — interacts with this same file via ?action=... */
const path = window.location.pathname;
const feed = document.getElementById('feedContainer');
const composerText = document.getElementById('composerText');
const composerImage = document.getElementById('composerImage');
const postBtn = document.getElementById('postBtn');
const preview = document.getElementById('preview');
const imgLabel = document.getElementById('imgLabel');
const loader = document.getElementById('loader');

let loading = false, hasMore = true, before = 0;

composerImage.addEventListener('change', () => {
  const f = composerImage.files[0];
  if (!f) { preview.style.display='none'; imgLabel.textContent='Add photo'; return; }
  imgLabel.textContent = f.name;
  preview.src = URL.createObjectURL(f);
  preview.style.display = 'block';
});

function esc(s){ if(!s) return ''; return s.replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c])); }
function relativeTime(ts){
  const d = new Date(ts);
  const diff = Math.floor((Date.now() - d.getTime())/1000);
  if (diff < 60) return diff + 's';
  if (diff < 3600) return Math.floor(diff/60) + 'm';
  if (diff < 86400) return Math.floor(diff/3600) + 'h';
  return d.toLocaleString();
}

function makePostNode(p){
  const el = document.createElement('div');
  el.className = 'post-card';
  el.dataset.id = p.id;
  el.innerHTML = `
    <div class="post-header">
      <div class="profile-pic-tiny" style="background-image:url('img/default-avatar.jpg');background-size:cover"></div>
      <div>
        <strong>${esc(p.author)}</strong>
        <small>${relativeTime(p.created_at)} ago</small>
      </div>
    </div>
    <div class="post-body"><p>${esc(p.content || '')}</p></div>
    ${p.image ? `<img src="${esc(p.image)}" class="post-image" alt="post image">` : ''}
    <div class="post-actions">
      <button class="action-btn like ${p.liked_by_me ? 'active' : ''}" data-id="${p.id}"><i class="fas fa-thumbs-up"></i> <span class="likes-count">${p.likes}</span></button>
      <button class="action-btn toggle-comments" data-id="${p.id}"><i class="fas fa-comment"></i> <span class="comments-count">${p.comments_count}</span></button>
      <button class="action-btn share" data-id="${p.id}"><i class="fas fa-share"></i> <span class="shares-count">${p.shares}</span></button>
    </div>
    <div class="comments" id="comments-${p.id}" style="display:none"></div>
  `;
  // like handler
  el.querySelector('.like').addEventListener('click', async (e) => {
    const id = e.currentTarget.dataset.id;
    const fd = new FormData(); fd.append('action','like'); fd.append('post_id', id);
    try {
      const res = await fetch(path, { method:'POST', body: fd });
      const j = await res.json();
      if (j.success) {
        e.currentTarget.querySelector('.likes-count').textContent = j.likes;
        e.currentTarget.classList.toggle('active', j.action === 'liked');
      } else alert(j.msg || 'Action failed');
    } catch (err) { console.error(err); alert('Network error'); }
  });
  // comments toggle & load
  el.querySelector('.toggle-comments').addEventListener('click', async (ev) => {
    const pid = ev.currentTarget.dataset.id;
    const box = document.getElementById('comments-'+pid);
    if (!box) return;
    if (box.style.display === 'none' || box.style.display === '') {
      try {
        const res = await fetch(path + '?action=get_comments&post_id=' + pid);
        const j = await res.json();
        if (j.success) {
          box.innerHTML = `
            <div id="comment-list-${pid}">${j.comments.map(c=>`<div class="comment"><strong>${esc(c.email)}</strong><div style="margin-top:6px">${esc(c.comment)}</div><small style="color:#777">${relativeTime(c.created_at)} ago</small></div>`).join('')}</div>
            <form class="comment-form" data-id="${pid}" style="display:flex;gap:8px;margin-top:8px">
              <input name="comment" placeholder="Write a comment..." style="flex:1;padding:8px;border-radius:8px;border:1px solid #eee" required>
              <button class="post-btn" type="submit" style="padding:8px 12px">Comment</button>
            </form>
          `;
          const form = box.querySelector('.comment-form');
          form.addEventListener('submit', async (ev2) => {
            ev2.preventDefault();
            const txt = form.comment.value.trim();
            if (!txt) return;
            const fd = new FormData(); fd.append('action','comment'); fd.append('post_id', pid); fd.append('comment', txt);
            try {
              const r2 = await fetch(path, { method:'POST', body: fd });
              const j2 = await r2.json();
              if (j2.success) {
                // Prepend new comment to the list
                box.querySelector(`#comment-list-${pid}`).insertAdjacentHTML('afterbegin', `<div class="comment"><strong>${esc(j2.comment.email)}</strong><div style="margin-top:6px">${esc(j2.comment.comment)}</div><small style="color:#777">${relativeTime(j2.comment.created_at)} ago</small></div>`);
                const postNode = document.querySelector('[data-id="'+pid+'"]');
                if (postNode) {
                  const cc = postNode.querySelector('.comments-count');
                  if (cc) cc.textContent = j2.comments_count;
                }
                form.comment.value = '';
              } else alert(j2.msg || 'Could not post comment');
            } catch (err) { console.error(err); alert('Network error'); }
          });
        } else box.innerHTML = '<div style="color:#777">No comments</div>';
      } catch (err) { console.error(err); box.innerHTML = '<div style="color:#777">Network error</div>'; }
      box.style.display = 'block';
    } else box.style.display = 'none';
  });
  // share
  el.querySelector('.share').addEventListener('click', async (e) => {
    const id = e.currentTarget.dataset.id;
    const fd = new FormData(); fd.append('action','share'); fd.append('post_id', id);
    try {
      const r = await fetch(path, { method:'POST', body: fd });
      const j = await r.json();
      if (j.success) {
        e.currentTarget.querySelector('.shares-count').textContent = j.shares;
        try { await navigator.clipboard.writeText(location.href.split('#')[0] + '#post-' + id); alert('Post link copied to clipboard'); } catch(_) {}
      } else alert(j.msg || 'Could not share');
    } catch (err) { console.error(err); alert('Network error'); }
  });
  return el;
}

/**
 * Loads posts from the server.
 * @param {boolean} reset - If true, clears the feed and resets pagination.
 */
async function loadPosts(reset = false) {
  if (loading && !reset) return; // Prevent multiple loads unless explicitly resetting
  if (!hasMore && !reset) return;

  if (reset) {
    before = 0;
    hasMore = true;
    feed.innerHTML = '';
  }

  loading = true;
  loader.style.display = 'block';

  try {
    // We only pass 'before' if it is a subsequent load, not a reset.
    const url = path + '?action=get_posts&limit=8' + (before > 0 ? '&before=' + before : '');
    const res = await fetch(url);
    const j = await res.json();
    
    if (j.success) {
      const posts = j.posts;
      if (posts.length > 0) {
        posts.forEach(p => feed.appendChild(makePostNode(p)));
        // Set 'before' to the ID of the last post received for next page load
        before = posts[posts.length - 1].id;
      } else {
        hasMore = false;
        if (!reset) loader.textContent = 'No more posts'; // Only show if not a fresh load with no content
      }
    } else {
      console.error(j.msg || 'Error loading posts');
      loader.textContent = 'Error loading posts';
    }
  } catch (err) {
    console.error(err);
    loader.textContent = 'Network error';
  } finally {
    loading = false;
    // Only hide if there might be more to load, or if it was a successful reset with content
    if (hasMore || (reset && feed.children.length > 0)) {
        loader.style.display = 'none';
    }
  }
}

// Initial load: Call loadPosts(true) to ensure a clean start on page load
loadPosts(true); 

// infinite scroll sentinel
const sentinel = document.getElementById('sentinel');
if (sentinel) {
  new IntersectionObserver(entries => { entries.forEach(e => { 
    if (e.isIntersecting && !loading && hasMore) loadPosts(); 
  }); }, { threshold: 0.2 }).observe(sentinel);
}

// create post
postBtn.addEventListener('click', async () => {
  const content = composerText.value.trim();
  const file = composerImage.files[0] ?? null;
  if (!content && !file) { alert('Write something or add a photo'); return; }

  postBtn.disabled = true; postBtn.textContent = 'Posting...';
  const fd = new FormData(); fd.append('action','create_post'); fd.append('content', content);
  if (file) fd.append('image', file);
  
  try {
    const res = await fetch(path, { method:'POST', body: fd });
    const j = await res.json();
    if (j.success) {
      // SUCCESS: Clear composer, then reset and reload the feed!
      composerText.value = '';
      composerImage.value = '';
      preview.style.display = 'none';
      imgLabel.textContent = 'Add photo';
      
      // FIX: Reset and reload the feed so the new post appears at the top
      await loadPosts(true); 

    } else {
      alert(j.msg || 'Could not post');
    }
  } catch (err) {
    console.error(err); alert('Network error');
  } finally {
    postBtn.disabled = false; postBtn.textContent = 'Post';
  }
});

// follow buttons in right sidebar
document.querySelectorAll('.follow-btn').forEach(b => {
  b.addEventListener('click', async (e) => {
    const followeeId = e.currentTarget.dataset.userId;
    const fd = new FormData(); fd.append('action','follow'); fd.append('followee_id', followeeId);
    try {
      const res = await fetch(path, { method:'POST', body: fd });
      const j = await res.json();
      if (j.success) e.currentTarget.textContent = j.action === 'followed' ? 'Unfollow' : 'Follow';
      else alert(j.msg || 'Could not follow/unfollow');
    } catch (err) { console.error(err); alert('Network error'); }
  });
});

</script>
</body>
</html>