<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
  $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}

include('./inc/config.php'); 

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
$user_id = intval($_SESSION['user_id']);

$uploadDir = __DIR__ . '/uploads/';
$uploadUrlBase = 'uploads/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

$conn->query("
CREATE TABLE IF NOT EXISTS polls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  question TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$conn->query("
CREATE TABLE IF NOT EXISTS poll_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  option_text VARCHAR(255) NOT NULL,
  votes INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$conn->query("
CREATE TABLE IF NOT EXISTS poll_votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  poll_id INT NOT NULL,
  option_id INT NOT NULL,
  user_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
// Add poll_id column to posts if missing
$check = $conn->query("SHOW COLUMNS FROM posts LIKE 'poll_id'");
if ($check && $check->num_rows === 0) {
    @$conn->query("ALTER TABLE posts ADD COLUMN poll_id INT NULL AFTER image");
}

// ------------------ Load current user ------------------
$currentUser = ['id'=>$user_id, 'first_name'=>'You','last_name'=>'User','profile_pic'=>null];
if ($stmt = $conn->prepare("SELECT id, first_name, last_name, profile_pic FROM users WHERE id=? LIMIT 1")) {
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $currentUser = $row;
    $stmt->close();
}

// ------------------ AJAX / POST handlers ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    // JOIN / LEAVE
    if ($action === 'join_leave') {
        $s = $conn->prepare("SELECT id FROM group_members WHERE user_id = ?");
        $s->bind_param("i",$user_id); $s->execute(); $r = $s->get_result();
        if ($r->num_rows > 0) {
            $d = $conn->prepare("DELETE FROM group_members WHERE user_id = ?");
            $d->bind_param("i",$user_id); $d->execute();
            echo json_encode(['status'=>'left']); exit;
        } else {
            $i = $conn->prepare("INSERT INTO group_members (user_id, joined_at) VALUES (?, CURRENT_TIMESTAMP)");
            $i->bind_param("i",$user_id); $i->execute();
            echo json_encode(['status'=>'joined']); exit;
        }
    }

    // CREATE POST (text + optional file + optional poll_id)
    if ($action === 'create_post') {
        $content = trim($_POST['content'] ?? '');
        $poll_id = isset($_POST['poll_id']) && $_POST['poll_id'] !== '' ? intval($_POST['poll_id']) : null;
        $file_path = null; $image_path = null;

        if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['file'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','pdf','doc','docx','ppt','pptx','txt'];
            if (!in_array($ext, $allowed)) {
                echo json_encode(['status'=>'error','message'=>'File type not allowed.']); exit;
            }
            $safe = time().'_'.preg_replace('/[^A-Za-z0-9\._-]/','_', $f['name']);
            $dest = $uploadDir . $safe;
            if (move_uploaded_file($f['tmp_name'],$dest)) {
                $rel = $uploadUrlBase . $safe;
                if (in_array($ext, ['jpg','jpeg','png','gif'])) $image_path = $rel;
                else $file_path = $rel;
            }
        }

        if ($poll_id === null) {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, file_path, image, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->bind_param("isss", $user_id, $content, $file_path, $image_path);
        } else {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, file_path, image, poll_id, created_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->bind_param("isssi", $user_id, $content, $file_path, $image_path, $poll_id);
        }
        if (!$stmt->execute()) {
            echo json_encode(['status'=>'error','message'=>'DB insert failed: '.$stmt->error]); exit;
        }
        $post_id = $conn->insert_id;

        echo json_encode(['status'=>'success','post'=>[
            'id'=>$post_id,
            'user_id'=>$user_id,
            'name'=>$currentUser['first_name'].' '.$currentUser['last_name'],
            'content'=>htmlspecialchars($content),
            'file_path'=>$file_path,
            'image'=>$image_path,
            'poll_id'=>$poll_id,
            'created_at'=>date('Y-m-d H:i:s')
        ]]);
        exit;
    }

    // CREATE POLL
    if ($action === 'create_poll') {
        $question = trim($_POST['question'] ?? '');
        // accept options[] or other variants
        $options = [];
        if (isset($_POST['options']) && is_array($_POST['options'])) $options = $_POST['options'];
        else {
            foreach ($_POST as $k=>$v) {
                if (strpos($k,'options') === 0) {
                    if (is_array($v)) $options = array_merge($options,$v);
                    else $options[] = $v;
                }
            }
        }
        $options = array_values(array_filter(array_map('trim',$options), function($x){ return $x !== ''; }));
        if ($question === '' || count($options) < 2) {
            echo json_encode(['status'=>'error','message'=>'Poll needs a question and at least 2 options.']); exit;
        }

        $p = $conn->prepare("INSERT INTO polls (user_id, question, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $p->bind_param("is", $user_id, $question);
        if (!$p->execute()) { echo json_encode(['status'=>'error','message'=>'Could not create poll: '.$p->error]); exit; }
        $poll_id = $conn->insert_id;

        $opt_stmt = $conn->prepare("INSERT INTO poll_options (poll_id, option_text, votes) VALUES (?, ?, 0)");
        foreach ($options as $opt) {
            $t = trim($opt); if ($t === '') continue;
            $opt_stmt->bind_param("is", $poll_id, $t);
            $opt_stmt->execute();
        }

        // link a post to the poll
        $post_content = "Poll: ". (mb_strlen($question) > 200 ? mb_substr($question,0,200).'...' : $question);
        $post_stmt = $conn->prepare("INSERT INTO posts (user_id, content, poll_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $post_stmt->bind_param("isi", $user_id, $post_content, $poll_id);
        $post_stmt->execute();
        $post_id = $conn->insert_id;

        // fetch options for response
        $opts = [];
        $res = $conn->query("SELECT id, option_text, votes FROM poll_options WHERE poll_id = $poll_id");
        while ($r = $res->fetch_assoc()) $opts[] = $r;

        echo json_encode(['status'=>'success','poll'=>['id'=>$poll_id,'question'=>$question,'options'=>$opts],'post'=>['id'=>$post_id,'content'=>$post_content,'created_at'=>date('Y-m-d H:i:s')]]);
        exit;
    }

    // VOTE
    if ($action === 'vote') {
        $option_id = intval($_POST['option_id'] ?? 0);
        if ($option_id <= 0) { echo json_encode(['status'=>'error']); exit; }

        // poll id from option
        $q = $conn->prepare("SELECT poll_id FROM poll_options WHERE id = ?");
        $q->bind_param("i",$option_id); $q->execute(); $qr = $q->get_result(); $row = $qr->fetch_assoc();
        if (!$row) { echo json_encode(['status'=>'error']); exit; }
        $poll_id = intval($row['poll_id']);

        // prevent double vote
        $c = $conn->prepare("SELECT id FROM poll_votes WHERE poll_id = ? AND user_id = ?");
        $c->bind_param("ii",$poll_id,$user_id); $c->execute(); $cr = $c->get_result();
        if ($cr->num_rows > 0) {
            echo json_encode(['status'=>'error','message'=>'You already voted.']); exit;
        }

        $ins = $conn->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $ins->bind_param("iii",$poll_id,$option_id,$user_id); $ins->execute();

        // update denormalized votes column (optional)
        $upd = $conn->prepare("UPDATE poll_options SET votes = votes + 1 WHERE id = ?");
        $upd->bind_param("i",$option_id); $upd->execute();

        // return fresh counts
        $counts_q = $conn->prepare("
            SELECT po.id, po.option_text, COUNT(pv.id) AS votes
            FROM poll_options po
            LEFT JOIN poll_votes pv ON pv.option_id = po.id
            WHERE po.poll_id = ?
            GROUP BY po.id
        ");
        $counts_q->bind_param("i",$poll_id); $counts_q->execute(); $cres = $counts_q->get_result();
        $counts = [];
        while ($r = $cres->fetch_assoc()) $counts[] = $r;
        echo json_encode(['status'=>'success','counts'=>$counts]);
        exit;
    }

    // LIKE / UNLIKE
    if ($action === 'like_post') {
        $post_id = intval($_POST['post_id'] ?? 0);
        if ($post_id <= 0) { echo json_encode(['status'=>'error']); exit; }
        $ch = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $ch->bind_param("ii",$post_id,$user_id); $ch->execute(); $cres = $ch->get_result();
        if ($cres->num_rows > 0) {
            $del = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
            $del->bind_param("ii",$post_id,$user_id); $del->execute();
            $liked = false;
        } else {
            $ins = $conn->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
            $ins->bind_param("ii",$post_id,$user_id); $ins->execute();
            $liked = true;
        }
        $cnt = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $post_id")->fetch_assoc()['cnt']);
        echo json_encode(['status'=>'success','liked'=>$liked,'count'=>$cnt]);
        exit;
    }

    // ADD COMMENT
    if ($action === 'add_comment') {
        $post_id = intval($_POST['post_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($post_id <= 0 || $comment === '') { echo json_encode(['status'=>'error']); exit; }
        $ins = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $ins->bind_param("iis",$post_id,$user_id,$comment); $ins->execute();
        $cid = $conn->insert_id;
        $q = $conn->prepare("SELECT c.id, c.comment, c.created_at, u.first_name, u.last_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
        $q->bind_param("i",$cid); $q->execute(); $r = $q->get_result()->fetch_assoc();
        echo json_encode(['status'=>'success','comment'=>$r]);
        exit;
    }

    // FETCH POSTS (AJAX helper)
    if ($action === 'fetch_posts') {
        $out = [];
        $pq = $conn->query("SELECT p.*, u.first_name, u.last_name FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        while ($row = $pq->fetch_assoc()) {
            $pid = intval($row['id']);
            $row['likes'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid")->fetch_assoc()['cnt']);
            $row['comments'] = intval($conn->query("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = $pid")->fetch_assoc()['cnt']);
            $row['username'] = $row['first_name'].' '.$row['last_name'];
            $out[] = $row;
        }
        echo json_encode(['status'=>'success','posts'=>$out]);
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'unknown action']); exit;
}

// ------------------ Render page (GET) ------------------
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Community - Babcock Linguistics</title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
<style>
:root{--primary:#793DDC;--primary-hover:#6930c3;--background:#F8F7FF;--card:#fff;--text:#333;--muted:#666;--border:#EEE;}
*{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;margin:0;background:var(--background);display:flex;justify-content:center}
.container{width:90%;max-width:1200px;margin:20px auto;background:var(--card);border-radius:16px;box-shadow:0 10px 20px rgba(0,0,0,.08);overflow:hidden}
.community-cover-strip{height:200px;background:url('img/medium-shot-students-classroom.jpg') center/cover no-repeat;position:relative;margin-bottom:80px}
.community-cover-strip::before{content:'';position:absolute;inset:0;background:linear-gradient(rgba(0,0,0,.3),rgba(0,0,0,.5))}
.community-profile{position:absolute;bottom:-70px;left:30px;display:flex;align-items:flex-end;gap:20px}
.community-logo-large{width:120px;height:120px;border-radius:20px;background:#ff9800;display:flex;align-items:center;justify-content:center;font-size:50px;color:#fff;border:4px solid #fff}
.community-details h1{color:#fff;margin:0;font-size:32px;text-shadow:0 2px 4px rgba(0,0,0,.3)}
.community-details p{color:#fff;margin:5px 0 0;text-shadow:0 1px 2px rgba(0,0,0,.3)}
.join-status{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:20px;background:#e8f5e9;color:#4caf50;font-weight:700}
.action-button{background:var(--primary);color:#fff;padding:10px 16px;border-radius:8px;border:0;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.action-button:hover{background:var(--primary-hover)}
.community-content{padding:0 30px 30px}
.community-tabs{display:flex;gap:20px;border-bottom:1px solid var(--border);margin-bottom:30px}
.tab-button{padding:12px 0;cursor:pointer;color:var(--muted);font-weight:700}
.tab-button.active{color:var(--primary);border-bottom:3px solid var(--primary)}
.timeline-grid{display:grid;grid-template-columns:2fr 1fr;gap:30px}
.post-composer{background:var(--card);padding:24px;border-radius:12px;border:1px solid var(--border);box-shadow:0 4px 8px rgba(0,0,0,.04)}
.composer-input{display:flex;align-items:center;border-bottom:1px solid var(--border);padding-bottom:12px;margin-bottom:12px}
.composer-input input{border:0;outline:0;font-size:16px;color:var(--muted);flex:1}
.profile-pic-tiny{width:40px;height:40px;border-radius:50%;background:#b39ddb;margin-right:10px}
.composer-actions{display:flex;align-items:center;gap:12px}
.composer-actions label,.composer-actions button{background:none;border:0;color:var(--primary);font-weight:700;cursor:pointer;padding:8px 10px;border-radius:8px}
.post-btn{background:var(--primary);color:#fff;padding:10px 12px;border-radius:8px;border:0;cursor:pointer}
.post-card{background:var(--card);padding:18px;border-radius:12px;border:1px solid var(--border);margin-bottom:18px;transition:transform .12s ease,box-shadow .12s ease}
.post-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,.06)}
.post-header{display:flex;align-items:center;gap:12px;margin-bottom:10px}
.post-header strong{font-size:15px}
.post-body p{margin:0 0 10px}
.post-actions{display:flex;gap:8px;margin-top:10px}
.post-actions button{background:none;border:0;color:var(--muted);font-weight:700;padding:8px 10px;border-radius:8px;cursor:pointer}
.post-actions button.liked{color:var(--primary)}
.poll{background:#fbfbff;border-radius:10px;padding:10px;border:1px solid #f0f0f0;margin-top:10px}
.poll-option{display:flex;justify-content:space-between;gap:12px;padding:8px;border-radius:8px;background:#fff;border:1px solid #f4f4f4;margin-bottom:8px;cursor:pointer}
.poll-option.disabled{opacity:.7;cursor:default}
.info-card{background:var(--card);padding:18px;border-radius:12px;border:1px solid var(--border);margin-bottom:18px}
.comments{margin-top:12px;border-top:1px solid #f5f5f5;padding-top:12px;display:none}
.comment{margin-bottom:10px}
.comment .meta{font-size:12px;color:var(--muted)}
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:9999}
.modal{background:#fff;padding:16px;border-radius:10px;width:90%;max-width:540px}
.modal input[type="text"], .modal textarea{width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);margin-bottom:10px}
@media(max-width:900px){.timeline-grid{grid-template-columns:1fr}.community-profile{left:16px}}
</style>
</head>
<body>
<div class="container">
  <div class="community-cover-strip">
    <div class="community-profile">
      <div class="community-logo-large">📚</div>
      <div class="community-details">
        <h1>Babcock Linguistics Students</h1>
        <p>Official group for Linguistics students at Babcock University.</p>
        <div style="display:flex;align-items:center;gap:10px">
          <span class="join-status" id="joinStatus"><?php
            $jm = $conn->prepare("SELECT id FROM group_members WHERE user_id = ?");
            $jm->bind_param("i",$user_id); $jm->execute(); $jmres = $jm->get_result();
            echo $jmres->num_rows ? '<i class="fas fa-check-circle"></i> Joined' : 'Not Joined';
          ?></span>
          <button class="action-button" id="joinBtn"><?php echo $jmres->num_rows ? 'Leave Group' : 'Join Group'; ?></button>
          <button class="action-button" id="createPostTop"><i class="fas fa-pen-to-square"></i> Create Post</button>
        </div>
      </div>
    </div>
  </div>

  <div class="community-content">
    <div class="community-tabs">
      <div class="tab-button active"><i class="fas fa-clock-rotate-left"></i> Timeline</div>
      <div class="tab-button"><i class="fas fa-circle-info"></i> About</div>
      <div class="tab-button"><i class="fas fa-users"></i> Members</div>
      <div class="tab-button"><i class="fas fa-folder-open"></i> Resources</div>
    </div>

    <div class="timeline-grid">
      <div class="timeline-feed">
        <div class="post-composer" id="composer">
          <div class="composer-input">
            <div class="profile-pic-tiny" style="<?php echo $currentUser['profile_pic'] ? "background-image:url('".htmlspecialchars($currentUser['profile_pic'],ENT_QUOTES)."');background-size:cover" : ''; ?>"></div>
            <input id="postInput" type="text" placeholder="Share an article, question, or announcement...">
          </div>
          <div class="composer-actions">
            <label for="fileInput"><i class="fas fa-paperclip"></i> File</label>
            <button id="openPollBtn"><i class="fas fa-poll"></i> Poll</button>
            <button id="postSubmit" class="post-btn"><i class="fas fa-paper-plane"></i> Post to Group</button>
          </div>

          <input type="file" id="fileInput" style="display:none" />
          <div id="filePreview" style="margin-top:10px;color:var(--muted)"></div>
        </div>

        <div id="postsContainer">
<?php
// Render posts server-side initially
$postsQ = $conn->query("SELECT p.*, u.first_name, u.last_name FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
while ($post = $postsQ->fetch_assoc()):
    $pid = intval($post['id']);
    $likes = intval($conn->query("SELECT COUNT(*) AS cnt FROM likes WHERE post_id = $pid")->fetch_assoc()['cnt']);
    $comments = intval($conn->query("SELECT COUNT(*) AS cnt FROM comments WHERE post_id = $pid")->fetch_assoc()['cnt']);
?>
<div class="post-card" data-id="<?php echo $pid; ?>">
  <div class="post-header">
    <div class="profile-pic-tiny"></div>
    <div>
      <strong><?php echo htmlspecialchars($post['first_name'].' '.$post['last_name']); ?></strong>
      <div style="font-size:12px;color:var(--muted)"><?php echo $post['created_at']; ?></div>
    </div>
  </div>

  <div class="post-body">
    <?php if (!empty($post['content'])): ?><p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p><?php endif; ?>

    <?php if (!empty($post['image'])): ?>
      <div style="margin-top:10px"><img src="<?php echo htmlspecialchars($post['image']); ?>" style="max-width:100%;border-radius:8px;border:1px solid #eee" /></div>
    <?php elseif (!empty($post['file_path'])): ?>
      <div style="margin-top:10px"><a href="<?php echo htmlspecialchars($post['file_path']); ?>" target="_blank" style="color:var(--primary);font-weight:700">Download attachment</a></div>
    <?php endif; ?>

    <?php if (!empty($post['poll_id'])):
        $pollId = intval($post['poll_id']);
        $poll = $conn->query("SELECT * FROM polls WHERE id = $pollId")->fetch_assoc();
        if ($poll):
            $optQ = $conn->query("SELECT po.id, po.option_text, (SELECT COUNT(*) FROM poll_votes pv WHERE pv.option_id = po.id) AS votes FROM poll_options po WHERE po.poll_id = $pollId");
?>
<div class="poll" data-poll-id="<?php echo $pollId; ?>">
  <div style="font-weight:700;margin-bottom:8px"><?php echo htmlspecialchars($poll['question']); ?></div>
  <?php while ($opt = $optQ->fetch_assoc()): ?>
    <div class="poll-option" data-option-id="<?php echo $opt['id']; ?>">
      <div><?php echo htmlspecialchars($opt['option_text']); ?></div>
      <div class="poll-count"><?php echo intval($opt['votes']); ?></div>
    </div>
  <?php endwhile; ?>
</div>
<?php endif; endif; ?>
  </div>

  <div class="post-actions">
    <button class="likeBtn"><i class="fas fa-thumbs-up"></i> <span class="like-count"><?php echo $likes; ?></span> Likes</button>
    <button class="commentBtn"><i class="fas fa-comment"></i> <span class="comment-count"><?php echo $comments; ?></span> Comments</button>
  </div>

  <div class="comments">
    <div class="existing-comments" style="margin-bottom:10px">
<?php
$commP = $conn->prepare("SELECT c.*, u.first_name, u.last_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
$commP->bind_param("i",$pid); $commP->execute(); $cr = $commP->get_result();
while ($c = $cr->fetch_assoc()):
?>
      <div class="comment">
        <div style="font-weight:700"><?php echo htmlspecialchars($c['first_name'].' '.$c['last_name']); ?></div>
        <div><?php echo nl2br(htmlspecialchars($c['comment'])); ?></div>
        <div class="meta"><?php echo $c['created_at']; ?></div>
      </div>
<?php endwhile; ?>
    </div>

    <div class="add-comment" style="display:flex;gap:8px">
      <input class="comment-input" type="text" placeholder="Write a comment..." style="flex:1;padding:8px;border-radius:8px;border:1px solid #eee" />
      <button class="comment-submit" style="background:var(--primary);color:#fff;border:0;padding:8px 12px;border-radius:8px">Send</button>
    </div>
  </div>
</div>
<?php endwhile; ?>
        </div> 
      </div> 

      <div class="community-info-panel">
        <div class="info-card">
          <h4>Group Admins</h4>
          <div style="display:flex;gap:10px;align-items:center">
            <div class="member-avatar" style="width:35px;height:35px;border-radius:50%;background:#ddd"></div>
            <div class="member-avatar" style="width:35px;height:35px;border-radius:50%;background:#ddd"></div>
            <span style="color:var(--primary)">+2 more</span>
          </div>
        </div>
        <div class="info-card">
          <h4>Community Description</h4>
          <p style="color:var(--muted)">A space for all Linguistics students to collaborate, share resources, and discuss topics related to language and communication.</p>
        </div>
      </div>

    </div> 
  </div> 
</div> 

<!-- Poll Modal -->
<div class="modal-bg" id="pollModal">
  <div class="modal">
    <h3>Create a Poll</h3>
    <input type="text" id="pollQuestion" placeholder="Poll question" />
    <div id="pollOptionsWrap">
      <div class="opt-row"><input class="poll-opt" type="text" placeholder="Option 1" /><button class="removeOpt" style="display:none">✕</button></div>
      <div class="opt-row"><input class="poll-opt" type="text" placeholder="Option 2" /><button class="removeOpt">✕</button></div>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px">
      <button id="addOpt" style="background:#eee;padding:8px;border-radius:8px;border:0">Add option</button>
      <button id="createPoll" style="background:var(--primary);color:#fff;padding:8px;border-radius:8px;border:0">Create Poll</button>
      <button id="closePoll" style="background:#ddd;padding:8px;border-radius:8px;border:0">Cancel</button>
    </div>
  </div>
</div>

<script>
// Helper - post FormData and parse JSON
async function postAction(fd){
  const res = await fetch('', { method:'POST', body: fd });
  return res.json();
}

// Composer elements
const postInput = document.getElementById('postInput');
const fileInput = document.getElementById('fileInput');
const filePreview = document.getElementById('filePreview');
const postSubmit = document.getElementById('postSubmit');
const postsContainer = document.getElementById('postsContainer');
let selectedFile = null;

fileInput.addEventListener('change', e=>{
  const f = e.target.files[0]; selectedFile = f || null;
  filePreview.innerText = f ? (f.name + ' (will attach)') : '';
});

// Create Post
postSubmit.addEventListener('click', async ()=>{
  const content = postInput.value.trim();
  if (!content && !selectedFile) {
    alert('Add text or attach a file.');
    return;
  }
  const fd = new FormData();
  fd.append('action','create_post');
  fd.append('content', content);
  if (selectedFile) fd.append('file', selectedFile);
  const res = await postAction(fd);
  if (res.status === 'success') {
    const p = res.post;
    const el = document.createElement('div'); el.className='post-card'; el.setAttribute('data-id', p.id);
    let fileHtml = '';
    if (p.image) fileHtml = `<div style="margin-top:10px"><img src="${p.image}" style="max-width:100%;border-radius:8px" /></div>`;
    else if (p.file_path) fileHtml = `<div style="margin-top:10px"><a href="${p.file_path}" target="_blank" style="color:var(--primary);font-weight:700">Download attachment</a></div>`;
    el.innerHTML = `<div class="post-header"><div class="profile-pic-tiny"></div><div><strong>${p.name}</strong><div style="font-size:12px;color:var(--muted)">${p.created_at}</div></div></div><div class="post-body">${p.content?'<p>'+p.content+'</p>':''}${fileHtml}</div><div class="post-actions"><button class="likeBtn"><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span> Likes</button><button class="commentBtn"><i class="fas fa-comment"></i> <span class="comment-count">0</span> Comments</button></div><div class="comments" style="display:none"><div class="existing-comments" style="margin-bottom:10px"></div><div class="add-comment" style="display:flex;gap:8px"><input class="comment-input" type="text" placeholder="Write a comment..." style="flex:1"/><button class="comment-submit" style="background:var(--primary);color:#fff;padding:8px;border-radius:8px;border:0">Send</button></div></div>`;
    postsContainer.prepend(el);
    postInput.value=''; selectedFile=null; fileInput.value=''; filePreview.innerText='';
  } else {
    alert(res.message || 'Could not create post.');
  }
});

// Join/Leave group
document.getElementById('joinBtn').addEventListener('click', async ()=>{
  const fd = new FormData(); fd.append('action','join_leave');
  const res = await postAction(fd);
  if (res.status === 'joined' || res.status === 'left') location.reload();
});

// create post top scroll
document.getElementById('createPostTop').addEventListener('click', ()=>{ document.getElementById('composer').scrollIntoView({behavior:'smooth'}); postInput.focus(); });

// Delegated listeners for like, comment, vote, comment submit
document.addEventListener('click', async (e) => {
  // Toggle comments
  if (e.target.closest('.commentBtn')) {
    const card = e.target.closest('.post-card'); const c = card.querySelector('.comments');
    c.style.display = (c.style.display === 'block') ? 'none' : 'block';
  }

  // Submit comment
  if (e.target.closest('.comment-submit')) {
    const btn = e.target.closest('.comment-submit'); const card = btn.closest('.post-card');
    const pid = card.getAttribute('data-id'); const input = card.querySelector('.comment-input'); const text = input.value.trim();
    if (!text) return;
    const fd = new FormData(); fd.append('action','add_comment'); fd.append('post_id', pid); fd.append('comment', text);
    const res = await postAction(fd);
    if (res.status === 'success') {
      const c = res.comment; const wrap = card.querySelector('.existing-comments');
      const d = document.createElement('div'); d.className='comment';
      d.innerHTML = `<div style="font-weight:700">${c.first_name} ${c.last_name}</div><div>${c.comment}</div><div class="meta">${c.created_at}</div>`;
      wrap.appendChild(d);
      const cc = card.querySelector('.comment-count'); cc.innerText = parseInt(cc.innerText||0) + 1;
      input.value='';
    } else alert('Could not post comment.');
  }

  // Like
  if (e.target.closest('.likeBtn')) {
    const btn = e.target.closest('.likeBtn'); const pid = btn.closest('.post-card').getAttribute('data-id');
    const fd = new FormData(); fd.append('action','like_post'); fd.append('post_id', pid);
    const res = await postAction(fd);
    if (res.status === 'success') {
      btn.classList.toggle('liked', res.liked);
      btn.querySelector('.like-count').innerText = res.count;
    }
  }

  if (e.target.closest('.poll-option') && !e.target.closest('.poll-option').classList.contains('disabled')) {
    const opt = e.target.closest('.poll-option'); const option_id = opt.getAttribute('data-option-id');
    const fd = new FormData(); fd.append('action','vote'); fd.append('option_id', option_id);
    const res = await postAction(fd);
    if (res.status === 'success') {
      const pollEl = opt.closest('.poll');
      res.counts.forEach(c => {
        const el = pollEl.querySelector(`[data-option-id="${c.id}"]`);
        if (el) el.querySelector('.poll-count').innerText = c.votes;
      });
      pollEl.querySelectorAll('.poll-option').forEach(o => o.classList.add('disabled'));
    } else {
      if (res.message) alert(res.message);
    }
  }
});

const pollModal = document.getElementById('pollModal');
document.getElementById('openPollBtn').addEventListener('click', ()=> pollModal.style.display='flex');
document.getElementById('closePoll').addEventListener('click', ()=> pollModal.style.display='none');
document.getElementById('addOpt').addEventListener('click', ()=>{
  const wrap = document.getElementById('pollOptionsWrap');
  const div = document.createElement('div'); div.className='opt-row';
  div.innerHTML = `<input class="poll-opt" type="text" placeholder="Option" /><button class="removeOpt">✕</button>`;
  wrap.appendChild(div);
});
document.getElementById('pollOptionsWrap').addEventListener('click', (e)=>{
  if (e.target.classList.contains('removeOpt')) e.target.parentElement.remove();
});

// Create poll
document.getElementById('createPoll').addEventListener('click', async ()=>{
  const question = document.getElementById('pollQuestion').value.trim();
  const opts = Array.from(document.querySelectorAll('.poll-opt')).map(i=>i.value.trim()).filter(Boolean);
  if (!question || opts.length < 2) { alert('Question and at least 2 options required'); return; }
  const fd = new FormData(); fd.append('action','create_poll'); fd.append('question', question);
  opts.forEach(o => fd.append('options[]', o));
  const btn = document.getElementById('createPoll'); btn.disabled = true; btn.textContent = 'Creating...';
  const res = await postAction(fd);
  btn.disabled = false; btn.textContent = 'Create Poll';
  if (res.status === 'success') {
    const p = res.post; const pol = res.poll;
    const el = document.createElement('div'); el.className='post-card'; el.setAttribute('data-id', p.id);
    let optionsHtml=''; pol.options.forEach(o => optionsHtml += `<div class="poll-option" data-option-id="${o.id}"><div>${o.option_text}</div><div class="poll-count">0</div></div>`);
    el.innerHTML = `<div class="post-header"><div class="profile-pic-tiny"></div><div><strong><?php echo htmlspecialchars($currentUser['first_name'].' '.$currentUser['last_name']); ?></strong><div style="font-size:12px;color:var(--muted)">${p.created_at}</div></div></div><div class="post-body"><p>${p.content}</p><div class="poll" data-poll-id="${pol.id}"><div style="font-weight:700;margin-bottom:8px">${pol.question}</div>${optionsHtml}</div></div><div class="post-actions"><button class="likeBtn"><i class="fas fa-thumbs-up"></i> <span class="like-count">0</span> Likes</button><button class="commentBtn"><i class="fas fa-comment"></i> <span class="comment-count">0</span> Comments</button></div><div class="comments" style="display:none"><div class="existing-comments" style="margin-bottom:10px"></div><div class="add-comment" style="display:flex;gap:8px"><input class="comment-input" type="text" placeholder="Write a comment..." style="flex:1"/><button class="comment-submit" style="background:var(--primary);color:#fff;padding:8px;border-radius:8px;border:0">Send</button></div></div>`;
    postsContainer.prepend(el);
    pollModal.style.display='none';
    document.getElementById('pollQuestion').value=''; document.querySelectorAll('.poll-opt').forEach((el,i)=>{ if(i>1) el.parentElement.remove(); else el.value=''; });
  } else {
    alert(res.message || 'Could not create poll.');
  }
});
</script>
</body>
</html>
