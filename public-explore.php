<?php
// explore.php  — single file Explore page with backend endpoints
// Requirements: include('./inc/config.php') which MUST create $conn = new mysqli(...)
// Session must set $_SESSION['user_id'] for actions (likes/comments/bookmark)

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('./inc/config.php');

if (!isset($conn) || !($conn instanceof mysqli)) {
  header('Content-Type: text/plain; charset=utf-8', true, 500);
  echo "Database connection not found. Make sure inc/config.php sets \$conn = new mysqli(...)\n";
  exit;
}

// ensure bookmarks table exists (safe-create if missing)
$conn->query("
CREATE TABLE IF NOT EXISTS bookmarks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY(post_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// utility: escape output
function esc($s)
{
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// helper: get current user's profile pic or fallback
function current_profile_pic($uid = null)
{
  global $conn;
  if ($uid === null) $uid = $_SESSION['user_id'] ?? null;
  if (!$uid) return 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
  $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ? LIMIT 1");
  if (!$stmt) return 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $r = $stmt->get_result()->fetch_assoc();
  return !empty($r['profile_pic']) ? $r['profile_pic'] : 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}

// AJAX endpoints
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json; charset=utf-8');
  $action = $_POST['action'];

  // Like / Unlike
  if ($action === 'like') {
    if (empty($_SESSION['user_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'login_required']);
      exit;
    }
    $post_id = intval($_POST['post_id'] ?? 0);
    $user_id = intval($_SESSION['user_id']);
    if ($post_id <= 0) {
      echo json_encode(['status' => 'error']);
      exit;
    }

    // toggle
    $chk = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $chk->bind_param("ii", $post_id, $user_id);
    $chk->execute();
    $res = $chk->get_result();
    if ($res && $res->num_rows > 0) {
      $del = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
      $del->bind_param("ii", $post_id, $user_id);
      $del->execute();
      $liked = false;
    } else {
      $ins = $conn->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
      $ins->bind_param("ii", $post_id, $user_id);
      $ins->execute();
      $liked = true;
    }
    $cnt = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = " . intval($post_id))->fetch_assoc()['cnt']);
    echo json_encode(['status' => 'success', 'liked' => $liked, 'count' => $cnt]);
    exit;
  }

  // Add comment
  if ($action === 'add_comment') {
    if (empty($_SESSION['user_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'login_required']);
      exit;
    }
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $user_id = intval($_SESSION['user_id']);
    if ($post_id <= 0 || $comment === '') {
      echo json_encode(['status' => 'error', 'message' => 'invalid']);
      exit;
    }

    $ins = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    if (!$ins) {
      echo json_encode(['status' => 'error', 'message' => 'db_prepare_failed']);
      exit;
    }
    $ins->bind_param("iis", $post_id, $user_id, $comment);
    $ok = $ins->execute();
    if (!$ok) {
      echo json_encode(['status' => 'error', 'message' => 'db_insert_failed']);
      exit;
    }
    $cid = $conn->insert_id;

    // fetch inserted comment with user info
    $q = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.first_name, u.last_name, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 1");
    $q->bind_param("i", $cid);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    echo json_encode(['status' => 'success', 'comment' => $row]);
    exit;
  }

  // Toggle bookmark
  if ($action === 'bookmark') {
    if (empty($_SESSION['user_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'login_required']);
      exit;
    }
    $post_id = intval($_POST['post_id'] ?? 0);
    $user_id = intval($_SESSION['user_id']);
    if ($post_id <= 0) {
      echo json_encode(['status' => 'error']);
      exit;
    }

    $chk = $conn->prepare("SELECT id FROM bookmarks WHERE post_id = ? AND user_id = ? LIMIT 1");
    $chk->bind_param("ii", $post_id, $user_id);
    $chk->execute();
    $cres = $chk->get_result();
    if ($cres && $cres->num_rows > 0) {
      $del = $conn->prepare("DELETE FROM bookmarks WHERE post_id = ? AND user_id = ?");
      $del->bind_param("ii", $post_id, $user_id);
      $del->execute();
      echo json_encode(['status' => 'success', 'bookmarked' => false]);
    } else {
      $ins = $conn->prepare("INSERT INTO bookmarks (post_id, user_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
      $ins->bind_param("ii", $post_id, $user_id);
      $ins->execute();
      echo json_encode(['status' => 'success', 'bookmarked' => true]);
    }
    exit;
  }

  // Fetch posts (infinite scroll); accepts sort = recent|trending
  if ($action === 'fetch_posts') {
    $page = max(1, intval($_POST['page'] ?? 1));
    $per = 10;
    $offset = ($page - 1) * $per;
    $sort = $_POST['sort'] ?? 'recent';
    if ($sort === 'trending') {
      // trending by likes in last 7 days (fallback to all-time)
      $sql = "
                SELECT p.*, u.first_name,u.last_name,u.profile_pic,u.name,
                  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count
                FROM posts p
                JOIN users u ON p.user_id = u.id
                ORDER BY like_count DESC, p.created_at DESC
                LIMIT ? OFFSET ?
            ";
    } else {
      $sql = "
                SELECT p.*, u.first_name,u.last_name,u.profile_pic,u.name
                FROM posts p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $per, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($r = $res->fetch_assoc()) {
      $pid = intval($r['id']);
      $r['likes'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid")->fetch_assoc()['cnt']);
      $r['comments'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = $pid")->fetch_assoc()['cnt']);
      $r['username'] = !empty($r['name']) ? $r['name'] : trim($r['first_name'] . ' ' . $r['last_name']);
      // whether current user liked/bookmarked
      $r['liked'] = false;
      $r['bookmarked'] = false;
      if (!empty($_SESSION['user_id'])) {
        $u = intval($_SESSION['user_id']);
        $r['liked'] = (bool) $conn->query("SELECT id FROM likes WHERE post_id=$pid AND user_id=$u LIMIT 1")->fetch_assoc();
        $r['bookmarked'] = (bool) $conn->query("SELECT id FROM bookmarks WHERE post_id=$pid AND user_id=$u LIMIT 1")->fetch_assoc();
      }
      $out[] = $r;
    }
    echo json_encode(['status' => 'success', 'posts' => $out]);
    exit;
  }

  // Unknown action
  echo json_encode(['status' => 'error', 'message' => 'unknown_action']);
  exit;
}

// Server-render initial data
$per = 10;
$recent_posts = [];
$stmt = $conn->prepare("
    SELECT p.*, u.first_name, u.last_name, u.profile_pic, u.name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT ?
");
$stmt->bind_param("i", $per);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $pid = intval($row['id']);
  $row['likes'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid")->fetch_assoc()['cnt']);
  $row['comments'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = $pid")->fetch_assoc()['cnt']);
  $row['username'] = !empty($row['name']) ? $row['name'] : trim($row['first_name'] . ' ' . $row['last_name']);
  $row['liked'] = (!empty($_SESSION['user_id']) && $conn->query("SELECT id FROM likes WHERE post_id=$pid AND user_id=" . intval($_SESSION['user_id']) . " LIMIT 1")->fetch_assoc()) ? true : false;
  $row['bookmarked'] = (!empty($_SESSION['user_id']) && $conn->query("SELECT id FROM bookmarks WHERE post_id=$pid AND user_id=" . intval($_SESSION['user_id']) . " LIMIT 1")->fetch_assoc()) ? true : false;
  $recent_posts[] = $row;
}

// trending by likes (top 6)
$trending_posts = [];
$q = $conn->query("
    SELECT p.*, u.first_name,u.last_name,u.profile_pic,u.name, (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY like_count DESC, p.created_at DESC
    LIMIT 6
");
if ($q) {
  while ($r = $q->fetch_assoc()) {
    $pid = intval($r['id']);
    $r['likes'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid")->fetch_assoc()['cnt']);
    $r['comments'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = $pid")->fetch_assoc()['cnt']);
    $r['username'] = !empty($r['name']) ? $r['name'] : trim($r['first_name'] . ' ' . $r['last_name']);
    $trending_posts[] = $r;
  }
}

// trending creators/communities fallback (top users by posts)
$trending_comms = [];
$qc = $conn->query("
    SELECT u.id, u.name, u.profile_pic, COUNT(p.id) AS post_count
    FROM users u
    LEFT JOIN posts p ON p.user_id = u.id
    GROUP BY u.id
    ORDER BY post_count DESC
    LIMIT 6
");
if ($qc) while ($c = $qc->fetch_assoc()) $trending_comms[] = $c;

// popular tags (simple)
$popular_tags = [];
$tag_q = $conn->query("SELECT content FROM posts WHERE content IS NOT NULL AND content <> '' ORDER BY created_at DESC LIMIT 200");
$freq = [];
if ($tag_q) {
  while ($row = $tag_q->fetch_assoc()) {
    if (preg_match_all('/#([A-Za-z0-9_]+)/', $row['content'], $m)) {
      foreach ($m[1] as $t) {
        $k = strtolower($t);
        if (!isset($freq[$k])) $freq[$k] = 0;
        $freq[$k]++;
      }
    }
  }
  arsort($freq);
  $i = 0;
  foreach ($freq as $k => $v) {
    $popular_tags[] = '#' . $k;
    if (++$i >= 10) break;
  }
}

$me_profile = current_profile_pic();
$logged_in_uid = $_SESSION['user_id'] ?? null;

?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Explore — Scholarthreads</title>
  <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
  <style>
    /* Styling taken & adapted from your design */
    * {
      box-sizing: border-box
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #fef9ff;
      color: #222;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh
    }

    a {
      color: #581980;
      text-decoration: none
    }

    .container {
      width: 100%;
      max-width: 900px;
      margin: 16px;
      display: flex;
      flex-direction: column;
      gap: 20px
    }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0
    }

    .logo {
      display: flex;
      align-items: center
    }

    .logo img {
      width: 100px;
      height: 60px;
      object-fit: contain;
      margin-right: 10px
    }

    .logo span {
      font-weight: 900;
      font-size: 26px;
      color: #4b0f7a;
      letter-spacing: 1.5px
    }

    .action-buttons button,
    .action-buttons a.btn {
      background: none;
      border: 1px solid #581980;
      color: #581980;
      font-weight: 600;
      font-size: 14px;
      margin-left: 8px;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: all .2s
    }

    .action-buttons button:hover,
    .action-buttons a.btn:hover {
      background: #581980;
      color: #fff
    }

    .purple-box {
      background: #f5e9ff;
      border-radius: 16px;
      padding: 28px 24px 40px 24px;
      text-align: center;
      color: #360a66;
      font-weight: 600;
      font-size: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 24px
    }

    .purple-box .images-row {
      position: relative;
      width: min(640px, 92%);
      height: 160px;
      margin: 0 auto;
      display: block
    }

    .purple-box .images-row img {
      position: absolute;
      width: 72px;
      height: 72px;
      border-radius: 12px;
      object-fit: cover;
      box-shadow: 0 0 4px rgba(104, 35, 157, 0.5);
      transition: transform .22s, box-shadow .22s;
      cursor: default
    }

    .purple-box p {
      max-width: 400px;
      margin: 0 auto 0;
      line-height: 1.4
    }

    .purple-box .btn {
      margin-top: 8px;
      padding: 10px 24px;
      background: #6300e5;
      border: none;
      color: #fff;
      font-weight: 700;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block
    }

    .purple-box .btn:hover {
      background: #3a00b6
    }

    .posts {
      width: 100%;
      max-width: 600px;
      display: flex;
      flex-direction: column;
      gap: 24px;
      margin-left: auto;
      margin-right: auto
    }

    .post {
      background: white;
      border-radius: 16px;
      padding: 18px 16px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05)
    }

    .author {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 700;
      font-size: 14px;
      color: #5f5f5f;
      margin-bottom: 12px
    }

    .author img {
      width: 36px;
      height: 36px;
      border-radius: 18px;
      object-fit: cover
    }

    .post img.post-image {
      display: block;
      width: 100%;
      border-radius: 16px;
      object-fit: cover;
      max-height: 420px;
      margin-bottom: 12px
    }

    .interactions {
      display: flex;
      gap: 20px;
      color: #989898;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      align-items: center
    }

    .interactions i {
      color: #989898;
      margin-right: 8px;
      font-size: 16px;
      vertical-align: middle;
      transition: color 160ms
    }

    .interactions button {
      background: none;
      border: 0;
      font-weight: 700;
      color: inherit;
      cursor: pointer;
      padding: 6px 8px;
      border-radius: 8px
    }

    .interactions button.liked i {
      color: #e0245e
    }

    .sidebar {
      position: fixed;
      top: 100px;
      right: 20px;
      width: 240px;
      border-radius: 16px;
      background: white;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
      padding: 18px 22px;
      font-size: 15px;
      color: #525252;
      font-weight: 600
    }

    .sidebar h3 {
      margin-top: 0;
      margin-bottom: 16px;
      font-weight: 700;
      color: #581980;
      font-size: 18px
    }

    .community-list a {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid #eee;
      padding: 6px 8px;
      border-radius: 14px;
      color: #581980;
      font-weight: 600;
      font-size: 14px;
      transition: background .15s
    }

    .community-list a:hover {
      background: #581980;
      color: #fff
    }

    .community-icon {
      width: 22px;
      height: 22px;
      border-radius: 3px;
      object-fit: cover
    }

    .tags-list span {
      background: #f5e9ff;
      border-radius: 12px;
      padding: 6px 14px;
      color: #6300e5;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer
    }

    @media (max-width:1100px) {
      .sidebar {
        position: static;
        width: 100%;
        margin-top: 16px
      }

      .container {
        max-width: 700px;
        margin: 0 16px 32px
      }

      .posts {
        max-width: 100%
      }
    }

    .muted {
      color: #888;
      font-weight: 600;
      font-size: 13px
    }

    .center {
      text-align: center
    }

    /* comment popup */
    .comment-popup {
      position: fixed;
      left: 50%;
      transform: translateX(-50%);
      bottom: 28px;
      width: min(720px, 92%);
      max-width: 720px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      display: none;
      z-index: 9999;
      padding: 12px;
    }

    .comment-popup.open {
      display: block;
    }

    .comment-row {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .comment-row input[type="text"] {
      flex: 1;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #eee;
      font-size: 14px;
    }

    .comment-row button {
      background: #581980;
      color: #fff;
      border: 0;
      padding: 10px 14px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
    }
  </style>
</head>

<body>
  <div class="container" role="main">
    <header>
      <div class="logo">
        <img src="./img/scholarthreads-high-resolution-logo-transparent (2).png" alt="Scholarthreads Logo" />
        <span>Scholarthreads</span>
      </div>
      <div class="action-buttons">
        <?php if (!empty($logged_in_uid)): ?>
          <a class="btn" href="./student_aboutprofile.php">Profile</a>
          <a class="btn" href="./logout.php">Logout</a>
          <img src="<?= esc($me_profile) ?>" alt="me" style="width:40px;height:40px;border-radius:50%;object-fit:cover;margin-left:8px;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.08)">
        <?php else: ?>
          <a class="btn" href="./sign.php">Sign In</a>
          <a class="btn" href="./signup.php">Sign up</a>
        <?php endif; ?>
      </div>
    </header>

    <section class="purple-box" aria-label="Connect section">
      <div class="images-row" aria-hidden="true">
        <img src="./img/medium-shot-students-classroom.jpg" alt="" style="left:0%;bottom:8px;transform:rotate(-14deg)">
        <img src="./img/students-studying-together-medium-shot.jpg" alt="" style="left:18%;bottom:34px;transform:rotate(-7deg)">
        <img src="./img/austin-0qvfWGTxOas-unsplash.jpg" alt="" style="left:38%;bottom:64px;transform:rotate(0deg)">
        <img src="./img/close-up-students-studying-together.jpg" alt="" style="left:58%;bottom:34px;transform:rotate(7deg)">
        <img src="./img/serwin365-avMyDRq-9fU-unsplash.jpg" alt="" style="left:78%;bottom:8px;transform:rotate(14deg)">
      </div>
      <p>Connect with Students, Professors and Institutions worldwide.</p>
      <a class="btn" href="<?= (!empty($logged_in_uid) ? 'community_timeline.php' : 'signup.php') ?>"><?= (!empty($logged_in_uid) ? 'Explore communities' : 'Sign up now') ?></a>
    </section>

    <div style="display:flex;gap:12px;align-items:center">
      <div style="font-weight:700">View:</div>
      <button id="viewRecent" class="btn" style="background:#fff;color:#581980;border-radius:8px;padding:6px 10px;border:1px solid #ddd">Recent</button>
      <button id="viewTrending" class="btn" style="background:#fff;color:#581980;border-radius:8px;padding:6px 10px;border:1px solid #ddd">Trending</button>
    </div>

    <section class="posts" aria-label="Posts" id="postsContainer">
      <?php if (count($recent_posts) === 0): ?>
        <div class="post center muted">No posts yet — be the first to share!</div>
        <?php else: foreach ($recent_posts as $post): ?>
          <article class="post" data-post-id="<?= intval($post['id']) ?>">
            <div class="author">
              <img src="<?= esc($post['profile_pic'] ?: current_profile_pic($post['user_id'])) ?>" alt="<?= esc($post['username']) ?>">
              <span><?= esc($post['username']) ?></span>
              <span style="margin-left:auto;font-weight:600;font-size:12px;color:#999"><?= esc(date('M j, H:i', strtotime($post['created_at']))) ?></span>
            </div>

            <?php if (!empty($post['content'])): ?>
              <div style="margin-bottom:10px; color:#333; font-weight:600"><?= nl2br(esc($post['content'])) ?></div>
            <?php endif; ?>

            <?php if (!empty($post['image'])): ?>
              <img class="post-image" src="<?= esc($post['image']) ?>" alt="post image" />
            <?php elseif (!empty($post['file_path'])): ?>
              <div style="margin-bottom:8px"><a href="<?= esc($post['file_path']) ?>" target="_blank">Download attachment</a></div>
            <?php endif; ?>

            <div class="interactions" data-post-id="<?= intval($post['id']) ?>">
              <button class="likeBtn <?= $post['liked'] ? 'liked' : '' ?>">
                <i class="fa-regular fa-heart"></i> <span class="like-count"><?= intval($post['likes']) ?></span> Likes
              </button>

              <button class="openCommentBtn" style="background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px">
                <i class="fa-regular fa-comment"></i> <span class="comment-count"><?= intval($post['comments']) ?></span> Comments
              </button>

              <button class="bookmarkBtn" style="margin-left:auto;background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px">
                <i class="fa-regular fa-bookmark"></i> <span class="bookmark-label"><?= $post['bookmarked'] ? 'Saved' : 'Save' ?></span>
              </button>
            </div>
          </article>
      <?php endforeach;
      endif; ?>
    </section>
  </div>

  <aside class="sidebar" aria-label="Trending Communities and Tags">
    <div class="trending">
      <h3>Trending Creators</h3>
      <div class="community-list">
        <?php foreach ($trending_comms as $c): ?>
          <a href="profile.php?u=<?= intval($c['id']) ?>" title="<?= esc($c['name']) ?>">
            <img class="community-icon" src="<?= esc($c['profile_pic'] ?: current_profile_pic($c['id'])) ?>" alt="">
            <?= esc($c['name'] ?: 'User ' . $c['id']) ?> <span style="margin-left:auto;font-weight:700;color:#333"><?= intval($c['post_count']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="tags" style="margin-top:32px;">
      <h3>Popular tags</h3>
      <div class="tags-list" aria-hidden="true">
        <?php if (count($popular_tags) === 0): ?>
          <div class="muted">No tags yet</div>
          <?php else: foreach ($popular_tags as $t): ?>
            <span><?= esc($t) ?></span>
        <?php endforeach;
        endif; ?>
      </div>
    </div>
  </aside>

  <!-- Comment popup -->
  <div class="comment-popup" id="commentPopup">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <div style="font-weight:700">Add a comment</div>
      <button id="closeCommentPopup" style="background:#eee;border:0;padding:6px 10px;border-radius:8px;cursor:pointer">Close</button>
    </div>
    <div class="comment-row">
      <input type="text" id="commentInput" placeholder="Write a comment..." />
      <button id="sendCommentBtn">Send</button>
    </div>
  </div>

  <script>
    (async function() {
      const postsContainer = document.getElementById('postsContainer');
      const ajax = async (fd) => {
        const r = await fetch('', {
          method: 'POST',
          body: fd
        });
        return r.json();
      };

      // Like/unlike
      document.addEventListener('click', async (e) => {
        const like = e.target.closest('.likeBtn');
        if (like) {
          const postEl = like.closest('[data-post-id]');
          const postId = postEl.getAttribute('data-post-id');
          const fd = new FormData();
          fd.append('action', 'like');
          fd.append('post_id', postId);
          const res = await ajax(fd);
          if (!res) return;
          if (res.status === 'success') {
            const span = like.querySelector('.like-count');
            if (span) span.textContent = res.count;
            if (res.liked) like.classList.add('liked');
            else like.classList.remove('liked');
          } else if (res.message === 'login_required') {
            window.location = 'sign.php';
          }
        }
        // open comment popup
        const openComment = e.target.closest('.openCommentBtn');
        if (openComment) {
          const postEl = openComment.closest('[data-post-id]');
          openCommentPopupFor(postEl.getAttribute('data-post-id'));
        }
        // bookmark
        const bm = e.target.closest('.bookmarkBtn');
        if (bm) {
          const postEl = bm.closest('[data-post-id]');
          const postId = postEl.getAttribute('data-post-id');
          const fd = new FormData();
          fd.append('action', 'bookmark');
          fd.append('post_id', postId);
          const res = await ajax(fd);
          if (res && res.status === 'success') {
            bm.querySelector('.bookmark-label').textContent = res.bookmarked ? 'Saved' : 'Save';
          } else if (res && res.message === 'login_required') {
            window.location = 'sign.php';
          }
        }
      });

      // infinite scroll + fetch sort
      let page = 1;
      let loading = false;
      let sortMode = 'recent';
      async function fetchMore() {
        if (loading) return;
        loading = true;
        page++;
        const fd = new FormData();
        fd.append('action', 'fetch_posts');
        fd.append('page', page);
        fd.append('sort', sortMode);
        const res = await ajax(fd);
        if (res && res.status === 'success' && res.posts && res.posts.length) {
          for (const p of res.posts) {
            const art = document.createElement('article');
            art.className = 'post';
            art.setAttribute('data-post-id', p.id);
            const name = p.name || (p.first_name + ' ' + p.last_name);
            const created = new Date(p.created_at).toLocaleString();
            let content = p.content ? `<div style="margin-bottom:10px; color:#333; font-weight:600">${escapeHtml(p.content)}</div>` : '';
            let media = '';
            if (p.image) media = `<img class="post-image" src="${p.image}" alt="post image" />`;
            else if (p.file_path) media = `<div style="margin-bottom:8px"><a href="${p.file_path}" target="_blank">Download attachment</a></div>`;
            art.innerHTML = `
          <div class="author">
            <img src="${p.profile_pic ? p.profile_pic : '<?= esc($me_profile) ?>'}" alt="${escapeHtml(name)}">
            <span>${escapeHtml(name)}</span>
            <span style="margin-left:auto;font-weight:600;font-size:12px;color:#999">${escapeHtml(created)}</span>
          </div>
          ${content}
          ${media}
          <div class="interactions" data-post-id="${p.id}">
            <button class="likeBtn ${p.liked ? 'liked' : ''}"><i class="fa-regular fa-heart"></i> <span class="like-count">${p.likes}</span> Likes</button>
            <button class="openCommentBtn" style="background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px"><i class="fa-regular fa-comment"></i> <span class="comment-count">${p.comments}</span> Comments</button>
            <button class="bookmarkBtn" style="margin-left:auto;background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px"><i class="fa-regular fa-bookmark"></i> <span class="bookmark-label">${p.bookmarked ? 'Saved' : 'Save'}</span></button>
          </div>
        `;
            postsContainer.appendChild(art);
          }
        } else {
          // no more posts: do nothing
        }
        loading = false;
      }

      window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
          fetchMore();
        }
      });

      // view toggles
      document.getElementById('viewRecent').addEventListener('click', () => {
        sortMode = 'recent';
        page = 0;
        postsContainer.innerHTML = '';
        loadInitial();
      });
      document.getElementById('viewTrending').addEventListener('click', () => {
        sortMode = 'trending';
        page = 0;
        postsContainer.innerHTML = '';
        loadInitial();
      });

      async function loadInitial() {
        const fd = new FormData();
        fd.append('action', 'fetch_posts');
        fd.append('page', 1);
        fd.append('sort', sortMode);
        const res = await ajax(fd);
        if (res && res.status === 'success') {
          postsContainer.innerHTML = '';
          for (const p of res.posts) {
            const art = document.createElement('article');
            art.className = 'post';
            art.setAttribute('data-post-id', p.id);
            const name = p.name || (p.first_name + ' ' + p.last_name);
            const created = new Date(p.created_at).toLocaleString();
            let content = p.content ? `<div style="margin-bottom:10px; color:#333; font-weight:600">${escapeHtml(p.content)}</div>` : '';
            let media = '';
            if (p.image) media = `<img class="post-image" src="${p.image}" alt="post image" />`;
            else if (p.file_path) media = `<div style="margin-bottom:8px"><a href="${p.file_path}" target="_blank">Download attachment</a></div>`;
            art.innerHTML = `
          <div class="author">
            <img src="${p.profile_pic ? p.profile_pic : '<?= esc($me_profile) ?>'}" alt="${escapeHtml(name)}">
            <span>${escapeHtml(name)}</span>
            <span style="margin-left:auto;font-weight:600;font-size:12px;color:#999">${escapeHtml(created)}</span>
          </div>
          ${content}
          ${media}
          <div class="interactions" data-post-id="${p.id}">
            <button class="likeBtn ${p.liked ? 'liked' : ''}"><i class="fa-regular fa-heart"></i> <span class="like-count">${p.likes}</span> Likes</button>
            <button class="openCommentBtn" style="background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px"><i class="fa-regular fa-comment"></i> <span class="comment-count">${p.comments}</span> Comments</button>
            <button class="bookmarkBtn" style="margin-left:auto;background:none;border:0;font-weight:700;color:#989898;cursor:pointer;padding:6px 8px;border-radius:8px"><i class="fa-regular fa-bookmark"></i> <span class="bookmark-label">${p.bookmarked ? 'Saved' : 'Save'}</span></button>
          </div>
        `;
            postsContainer.appendChild(art);
          }
          page = 1;
        }
      }

      // comment popup logic
      const commentPopup = document.getElementById('commentPopup');
      const commentInput = document.getElementById('commentInput');
      const sendCommentBtn = document.getElementById('sendCommentBtn');
      const closeCommentPopup = document.getElementById('closeCommentPopup');
      let currentCommentPostId = null;

      window.openCommentPopupFor = function(postId) {
        currentCommentPostId = postId;
        commentInput.value = '';
        commentPopup.classList.add('open');
        commentInput.focus();
      };

      closeCommentPopup.addEventListener('click', () => {
        commentPopup.classList.remove('open');
        currentCommentPostId = null;
      });

      sendCommentBtn.addEventListener('click', async () => {
        if (!currentCommentPostId) return;
        const text = commentInput.value.trim();
        if (!text) return;
        const fd = new FormData();
        fd.append('action', 'add_comment');
        fd.append('post_id', currentCommentPostId);
        fd.append('comment', text);
        const res = await ajax(fd);
        if (res && res.status === 'success') {
          // update comment count shown on post
          const postEl = document.querySelector(`[data-post-id="${currentCommentPostId}"]`);
          if (postEl) {
            const cc = postEl.querySelector('.comment-count');
            if (cc) cc.textContent = parseInt(cc.textContent || '0') + 1;
          }
          commentPopup.classList.remove('open');
          currentCommentPostId = null;
          commentInput.value = '';
        } else if (res && res.message === 'login_required') {
          window.location = 'sign.php';
        } else {
          alert('Could not add comment');
        }
      });

      // helper: escape
      function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function(m) {
          return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
          } [m];
        });
      }

      // initial load already has server posts; initialize page variable
      page = 1;
    })();
  </script>
</body>

</html>