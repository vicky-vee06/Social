<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('./inc/config.php');

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo "Database connection missing. Ensure inc/config.php sets \$conn = new mysqli(...)\n";
    exit;
}

function flash($k, $v = null) {
    if ($v === null) { $val = $_SESSION[$k] ?? null; unset($_SESSION[$k]); return $val; }
    $_SESSION[$k] = $v;
}

// helper escape
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// path config
$uploadBaseDir = __DIR__ . '/uploads/stories/';
$uploadUrlBase = 'uploads/stories/';

if (!is_dir($uploadBaseDir)) {
    @mkdir($uploadBaseDir, 0755, true);
}

$cleanup_stmt = $conn->prepare("SELECT id, media_url FROM stories WHERE created_at < (NOW() - INTERVAL 1 DAY)");
if ($cleanup_stmt) {
    $cleanup_stmt->execute();
    $old = $cleanup_stmt->get_result();
    while ($row = $old->fetch_assoc()) {
        // if local file under uploadUrlBase, unlink
        if (!empty($row['media_url']) && strpos($row['media_url'], $uploadUrlBase) === 0) {
            $fpath = __DIR__ . '/' . $row['media_url'];
            if (file_exists($fpath) && is_writable($fpath)) @unlink($fpath);
        }
    }
    // delete DB rows older than 24 hours
    $conn->query("DELETE FROM stories WHERE created_at < (NOW() - INTERVAL 1 DAY)");
    $cleanup_stmt->close();
}

// handle POST: upload story
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_story') {
    // must be logged in
    if (empty($_SESSION['user_id'])) {
        flash('error', 'You must be signed in to add a story.');
        header('Location: story.php');
        exit;
    }

    $user_id = intval($_SESSION['user_id']);
    $caption = trim($_POST['caption'] ?? '');

    // check file
    if (!isset($_FILES['media']) || $_FILES['media']['error'] === UPLOAD_ERR_NO_FILE) {
        flash('error', 'Please choose an image or video to upload.');
        header('Location: story.php');
        exit;
    }

    $f = $_FILES['media'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Upload error code: ' . $f['error']);
        header('Location: story.php');
        exit;
    }

    // validate extension
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $image_exts = ['jpg','jpeg','png','gif','webp'];
    $video_exts = ['mp4','webm','mov','mkv'];
    $allowed = array_merge($image_exts, $video_exts);

    if (!in_array($ext, $allowed)) {
        flash('error', 'Unsupported file type. Allowed: jpg,jpeg,png,gif,webp,mp4,webm,mov,mkv');
        header('Location: story.php');
        exit;
    }

    // limit file size (example: 40MB)
    $maxBytes = 40 * 1024 * 1024;
    if ($f['size'] > $maxBytes) {
        flash('error', 'File too large. Max 40MB.');
        header('Location: story.php');
        exit;
    }

    // create safe filename
    $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/','_', $f['name']);
    $dest = $uploadBaseDir . $safeName;

    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        flash('error', 'Failed to move uploaded file. Check folder permissions.');
        header('Location: story.php');
        exit;
    }

    // determine media_type
    $media_type = in_array($ext, $image_exts) ? 'image' : 'video';
    $media_url = $uploadUrlBase . $safeName;

    // insert into DB
    $ins = $conn->prepare("INSERT INTO stories (user_id, media_type, media_url, caption, created_at) VALUES (?, ?, ?, ?, NOW())");
    if (!$ins) {
        // rollback file if desired
        @unlink($dest);
        flash('error', 'DB prepare failed: ' . $conn->error);
        header('Location: story.php');
        exit;
    }
    $ins->bind_param('isss', $user_id, $media_type, $media_url, $caption);
    $ok = $ins->execute();
    if (!$ok) {
        @unlink($dest);
        flash('error', 'DB insert failed: ' . $ins->error);
        $ins->close();
        header('Location: story.php');
        exit;
    }
    $ins->close();

    flash('success', 'Story uploaded successfully.');
    header('Location: story.php');
    exit;
}


$stories_by_user = [];

$sth = $conn->prepare("
    SELECT s.*, u.first_name, u.last_name, u.profile_pic, u.name
    FROM stories s
    JOIN users u ON u.id = s.user_id
    WHERE s.created_at >= (NOW() - INTERVAL 1 DAY)
    ORDER BY s.created_at DESC
");
if ($sth) {
    $sth->execute();
    $res = $sth->get_result();
    while ($r = $res->fetch_assoc()) {
        $uid = intval($r['user_id']);
        if (!isset($stories_by_user[$uid])) $stories_by_user[$uid] = [
            'user'=>[
                'id'=>$uid,
                'name'=> ($r['name'] ?: trim($r['first_name'].' '.$r['last_name'])),
                'profile_pic'=>$r['profile_pic']
            ],
            'stories'=>[]
        ];
        // compute seconds remaining
        $created_ts = strtotime($r['created_at']);
        $expires_at = $created_ts + 24*3600;
        $remaining = $expires_at - time();
        if ($remaining > 0) {
            $r['remaining_seconds'] = $remaining;
            $stories_by_user[$uid]['stories'][] = $r;
        }
    }
    $sth->close();
}

// helper: current user info for UI
$current_user = null;
if (!empty($_SESSION['user_id'])) {
    $q = $conn->prepare("SELECT id, name, first_name, last_name, profile_pic FROM users WHERE id = ? LIMIT 1");
    if ($q) {
        $q->bind_param('i', $_SESSION['user_id']);
        $q->execute();
        $current_user = $q->get_result()->fetch_assoc();
        $q->close();
    }
}

// flash messages
$success = flash('success');
$error = flash('error');

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Stories — Scholarthreads</title>
<link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
<style>
/* Keep layout consistent with your other pages: sidebar + main */
*{box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f6f2fa;color:#333;display:flex;min-height:100vh}
.sidebar{width:260px;background:#fff;padding:20px;border-right:1px solid #eee;display:flex;flex-direction:column;gap:18px}
.logo img{width:140px}
.nav a{display:block;color:#5a189a;text-decoration:none;padding:8px 10px;border-radius:8px;font-weight:700;margin-bottom:6px}
.nav a.active, .nav a:hover{background:#f0e8ff}
.main{flex:1;padding:22px;max-width:1100px;margin:0 auto}
.top-row{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.search{flex:1}
.search input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #e6dbff;background:#f3effc}
.upload-area{background:#fff;padding:14px;border-radius:12px;border:1px solid #efe6ff;display:flex;gap:12px;align-items:center}
.profile-small{width:56px;height:56px;border-radius:50%;overflow:hidden;background:#ddd;flex-shrink:0}
.profile-small img{width:100%;height:100%;object-fit:cover}
.upload-controls{flex:1;display:flex;flex-direction:column;gap:8px}
.file-row{display:flex;gap:8px;align-items:center}
.file-row input[type=file]{display:none}
.btn{background:#5a189a;color:#fff;padding:8px 12px;border-radius:10px;border:0;cursor:pointer;font-weight:700}
.btn.ghost{background:transparent;color:#5a189a;border:1px solid #efe6ff}
.small-muted{color:#666;font-size:13px}
.stories-grid{display:flex;gap:12px;margin-top:18px;flex-wrap:wrap}
.user-stories{width:100px;text-align:center}
.story-thumb{width:100px;height:100px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f7f2ff;border:4px solid #fff;box-shadow:0 6px 18px rgba(90,24,154,0.06);cursor:pointer;position:relative}
.story-thumb img, .story-thumb video{width:100%;height:100%;object-fit:cover}
.count-badge{position:absolute;right:-6px;bottom:-6px;background:#5a189a;color:#fff;padding:6px 8px;border-radius:999px;font-weight:700;font-size:12px}
.story-name{margin-top:8px;font-weight:700;font-size:13px;color:#4a2e99}

/* modal */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px}
.modal-backdrop.show{display:flex}
.modal{background:#fff;border-radius:12px;max-width:920px;width:100%;max-height:90vh;overflow:auto;padding:14px;position:relative}
.modal .close{position:absolute;right:12px;top:12px;border:0;background:#eee;padding:6px 8px;border-radius:8px;cursor:pointer}
.modal-media{max-height:70vh;display:block;margin:0 auto;border-radius:8px;max-width:100%}
.caption{margin-top:10px;color:#444;font-weight:600}
.time-left{color:#666;font-size:13px;margin-top:6px}

/* messages */
.flash{padding:10px;border-radius:8px;margin-bottom:12px}
.flash.success{background:#e6f7ee;color:#1b6d2f}
.flash.error{background:#fdecea;color:#7a1b1b}

/* responsive */
@media(max-width:820px){
  .sidebar{display:none}
  .main{padding:12px}
  .user-stories{width:80px}
  .story-thumb{width:80px;height:80px}
}
</style>
</head>
<body>
  <aside class="sidebar" aria-label="Sidebar navigation">
    <div class="logo"><img src="./img/scholarthreads-high-resolution-logo-transparent (2).png" alt="logo"></div>
    <nav class="nav">
      <a href="./homepage.php">Home</a>
      <a href="./story.php" class="active">Stories</a>
      <a href="./public-explore.php">Explore</a>
      <a href="./community_timeline.php">Communities</a>
      <a href="./settings_profile.php">Settings</a>
    </nav>
    <div class="small-muted">Signed in as:</div>
    <div style="display:flex;gap:10px;align-items:center">
      <div style="width:46px;height:46px;border-radius:50%;overflow:hidden;background:#ddd">
        <?php if (!empty($current_user['profile_pic'])): ?>
          <img src="<?= esc($current_user['profile_pic']) ?>" alt="me" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
          <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100" alt="me" style="width:100%;height:100%;object-fit:cover">
        <?php endif; ?>
      </div>
      <div>
        <div style="font-weight:700"><?= esc($current_user['name'] ?? ($current_user['first_name'].' '.$current_user['last_name'] ?? '')) ?></div>
        <div class="small-muted"><?= !empty($current_user['name']) ? esc($current_user['name']) : '' ?></div>
      </div>
    </div>
  </aside>

  <main class="main" role="main">
    <?php if ($success): ?><div class="flash success"><?= esc($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="flash error"><?= esc($error) ?></div><?php endif; ?>

    <div class="top-row">
      <div class="search">
        <input type="search" placeholder="Search stories by name..." id="searchInput" oninput="filterStories()" />
      </div>
      <div>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <button class="btn" id="openUploadBtn">Add Story</button>
        <?php else: ?>
          <a class="btn" href="sign.php">Sign in to add</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="upload-area" aria-hidden="false">
      <div class="profile-small">
        <?php if (!empty($current_user['profile_pic'])): ?>
          <img src="<?= esc($current_user['profile_pic']) ?>" alt="me">
        <?php else: ?>
          <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100" alt="me">
        <?php endif; ?>
      </div>
      <div class="upload-controls">
        <div class="small-muted">Stories disappear after 24 hours. Add an image or short video.</div>
        <div class="file-row">
          <label class="btn ghost" id="chooseFileLabel" for="mediaInput"><i class="fa-regular fa-image"></i>&nbsp;Choose file</label>
          <input type="file" id="mediaInput" accept="image/*,video/*">
          <button class="btn" id="submitStoryBtn">Upload (demo)</button>
          <div style="margin-left:auto" class="small-muted" id="selectedFilename">No file chosen</div>
        </div>
        <input type="text" id="captionInput" placeholder="Add an optional caption..." style="margin-top:6px;padding:8px;border-radius:8px;border:1px solid #efe6ff">
      </div>
    </div>

    <h3 style="margin-top:18px;margin-bottom:6px">Active Stories</h3>
    <div class="stories-grid" id="storiesGrid" aria-live="polite">
      <?php if (empty($stories_by_user)): ?>
        <div class="small-muted">No active stories. Upload yours above!</div>
      <?php else: foreach ($stories_by_user as $uid => $group): 
          $user = $group['user'];
          $count = count($group['stories']);
      ?>
        <div class="user-stories" data-name="<?= esc(strtolower($user['name'])) ?>">
          <div class="story-thumb" tabindex="0" role="button" aria-pressed="false" onclick="openUserStories(<?= intval($uid) ?>)" onkeydown="if(event.key==='Enter') openUserStories(<?= intval($uid) ?>)">
            <?php
              // show most recent story media as thumbnail
              $first = $group['stories'][0];
              if ($first['media_type'] === 'image'):
            ?>
              <img src="<?= esc($first['media_url']) ?>" alt="<?= esc($user['name']) ?>">
            <?php else: ?>
              <video src="<?= esc($first['media_url']) ?>" muted playsinline></video>
            <?php endif; ?>
            <div class="count-badge"><?= $count ?></div>
          </div>
          <div class="story-name"><?= esc($user['name']) ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>

  <!-- modal viewer -->
  <div class="modal-backdrop" id="modal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true">
      <button class="close" id="modalClose">✕</button>
      <div id="modalBody"></div>
    </div>
  </div>

<script>
/* Minimal client-side: handle upload (demo) and viewer */
const mediaInput = document.getElementById('mediaInput');
const captionInput = document.getElementById('captionInput');
const selectedFilename = document.getElementById('selectedFilename');
const submitStoryBtn = document.getElementById('submitStoryBtn');
const openUploadBtn = document.getElementById('openUploadBtn');
const chooseFileLabel = document.getElementById('chooseFileLabel');

mediaInput.addEventListener('change', () => {
  const f = mediaInput.files[0];
  selectedFilename.textContent = f ? f.name : 'No file chosen';
});

submitStoryBtn.addEventListener('click', async () => {
  if (!mediaInput.files || mediaInput.files.length === 0) {
    alert('Choose a file first.');
    return;
  }
  // create form and submit by POST to same page using fetch
  const fd = new FormData();
  fd.append('action', 'upload_story');
  fd.append('media', mediaInput.files[0]);
  fd.append('caption', captionInput.value || '');
  // disable button
  submitStoryBtn.disabled = true;
  submitStoryBtn.textContent = 'Uploading...';
  const resp = await fetch('story.php', { method: 'POST', body: fd });

  window.location.reload();
});

// openUploadBtn focuses file input (if present)
if (openUploadBtn) {
  openUploadBtn.addEventListener('click', () => {
    mediaInput.click();
  });
}

// simple filter by name
function filterStories(){
  const q = (document.getElementById('searchInput').value || '').toLowerCase().trim();
  document.querySelectorAll('#storiesGrid .user-stories').forEach(el=>{
    const name = el.dataset.name || '';
    el.style.display = (name.includes(q) ? '' : 'none');
  });
}

// viewer logic: stories_by_user data embedded from PHP
const storiesData = <?php
  // build a lightweight JS structure: { uid: { user: {name,profile_pic}, stories: [{media_type,media_url,caption,remaining_seconds,created_at}] } }
  $js = [];
  foreach ($stories_by_user as $uid => $g) {
    $entry = [
      'user' => $g['user'],
      'stories' => []
    ];
    foreach ($g['stories'] as $s) {
      $entry['stories'][] = [
        'id' => intval($s['id']),
        'media_type' => $s['media_type'],
        'media_url' => $s['media_url'],
        'caption' => $s['caption'],
        'remaining_seconds' => intval($s['remaining_seconds']),
        'created_at' => $s['created_at']
      ];
    }
    $js[intval($uid)] = $entry;
  }
  echo json_encode($js, JSON_UNESCAPED_SLASHES);
?>;

const modal = document.getElementById('modal');
const modalBody = document.getElementById('modalBody');
const modalClose = document.getElementById('modalClose');

function openUserStories(uid) {
  const group = storiesData[uid];
  if (!group) return;
  // show first story then allow next/prev
  let idx = 0;
  function render() {
    const s = group.stories[idx];
    let html = `<div style="text-align:center">`;
    if (s.media_type === 'image') {
      html += `<img class="modal-media" src="${s.media_url}" alt="story">`;
    } else {
      html += `<video class="modal-media" controls playsinline src="${s.media_url}"></video>`;
    }
    html += `<div class="caption">${escapeHtml(s.caption || '')}</div>`;
    // compute time left display
    const secs = s.remaining_seconds || 0;
    html += `<div class="time-left">${formatTimeLeft(secs)}</div>`;
    html += `<div style="margin-top:12px;display:flex;gap:8px;justify-content:center"><button class="btn ghost" id="prevBtn">Prev</button><button class="btn ghost" id="nextBtn">Next</button></div>`;
    html += `</div>`;
    modalBody.innerHTML = html;
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');

    // attach handlers
    document.getElementById('prevBtn').addEventListener('click', ()=> {
      idx = (idx - 1 + group.stories.length) % group.stories.length;
      render();
    });
    document.getElementById('nextBtn').addEventListener('click', ()=> {
      idx = (idx + 1) % group.stories.length;
      render();
    });
  }
  render();
}

modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
function closeModal(){ modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); modalBody.innerHTML = ''; }

// helpers
function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]); }
function formatTimeLeft(secs){
  if (!secs || secs <= 0) return 'Expired';
  const h = Math.floor(secs/3600); const m = Math.floor((secs%3600)/60);
  if (h>0) return `${h}h ${m}m left`;
  return `${m}m left`;
}

// make videos in thumbnails autoplay muted small preview for better look
document.querySelectorAll('.story-thumb video').forEach(v=>{
  v.muted = true; v.playsInline = true;
  v.addEventListener('mouseenter', ()=> v.play());
  v.addEventListener('mouseleave', ()=> { v.pause(); v.currentTime = 0; });
});
</script>
</body>
</html>
