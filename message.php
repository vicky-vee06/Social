<?php
// dm.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('./inc/config.php');

if (!isset($conn) || !($conn instanceof mysqli)) {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    echo "Database connection not found. Make sure inc/config.php sets \$conn = new mysqli(...)\n";
    exit;
}

// require login
if (empty($_SESSION['user_id'])) {
    header('Location: sign.php');
    exit;
}

$me = intval($_SESSION['user_id']);

// helper
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

// ensure uploads folder
$uploadDir = __DIR__ . '/uploads/messages/';
$uploadUrlBase = 'uploads/messages/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// Determine receiver_id (GET fallback)
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null;

// If AJAX POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    // SEND message (normal form POST also falls here)
    if ($action === 'send') {
        if (empty($_POST['receiver_id'])) {
            echo json_encode(['status'=>'error','message'=>'receiver_required']); exit;
        }
        $to = intval($_POST['receiver_id']);
        $text = trim($_POST['message'] ?? '');

        // handle image if provided via file upload
        $image_path = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['image'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['status'=>'error','message'=>'invalid_image']); exit;
                }
                $safe = time().'_'.preg_replace('/[^A-Za-z0-9\._-]/','_', $f['name']);
                $dest = $uploadDir . $safe;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $image_path = $uploadUrlBase . $safe;
                } else {
                    echo json_encode(['status'=>'error','message'=>'upload_failed']); exit;
                }
            } else {
                echo json_encode(['status'=>'error','message'=>'upload_err_code_'.$f['error']]); exit;
            }
        }

        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, image, is_read, created_at) VALUES (?, ?, ?, ?, 0, CURRENT_TIMESTAMP)");
        $stmt->bind_param('iiss', $me, $to, $text, $image_path);
        $ok = $stmt->execute();
        if (!$ok) {
            echo json_encode(['status'=>'error','message'=>'db_error','db'=>$stmt->error]); exit;
        }
        $mid = $conn->insert_id;

        // Return the saved message
        $row = $conn->query("SELECT m.*, u.first_name, u.last_name, u.name, u.profile_pic FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.id = ".intval($mid))->fetch_assoc();
        echo json_encode(['status'=>'success','message'=>$row]);
        exit;
    }

    // FETCH messages between me and receiver_id
    if ($action === 'fetch') {
        $other = intval($_POST['receiver_id'] ?? 0);
        if ($other <= 0) { echo json_encode(['status'=>'error','message'=>'missing_receiver']); exit; }

        // optional since timestamp
        $since = isset($_POST['since']) ? $_POST['since'] : null;

        if ($since) {
            $stmt = $conn->prepare("
                SELECT m.*, u.first_name, u.last_name, u.name, u.profile_pic
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
                  AND m.created_at > ?
                ORDER BY m.created_at ASC
            ");
            $stmt->bind_param('iiiis', $me, $other, $other, $me, $since);
        } else {
            $stmt = $conn->prepare("
                SELECT m.*, u.first_name, u.last_name, u.name, u.profile_pic
                FROM messages m
                JOIN users u ON u.id = m.sender_id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC
            ");
            $stmt->bind_param('iiii', $me, $other, $other, $me);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $msgs = [];
        while ($r = $res->fetch_assoc()) $msgs[] = $r;
        $stmt->close();

        // mark unread messages (where receiver is me and sender is other) as read
        $u = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
        $u->bind_param('ii', $other, $me);
        $u->execute();
        $u->close();

        echo json_encode(['status'=>'success','messages'=>$msgs]);
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'unknown_action']);
    exit;
}

// If GET view:
$receiver = null;
$receiver_user = null;
if ($receiver_id) {
    // fetch user
    $s = $conn->prepare("SELECT id, first_name, last_name, name, profile_pic FROM users WHERE id = ? LIMIT 1");
    $s->bind_param('i', $receiver_id);
    $s->execute();
    $receiver_user = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$receiver_user) {
        // invalid receiver id
        echo "<p>Invalid user.</p><p><a href='message.php'>Back to messages</a></p>";
        exit;
    }
}

// helper to show display name
function display_name_from_row($u) {
    $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
    if ($name === '') $name = ($u['name'] ?? 'User '.$u['id']);
    return $name;
}

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Chat — Scholarthreads</title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
<style>
  body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f4f4f6;display:flex;justify-content:center;padding:18px}
  .wrap{width:100%;max-width:820px;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.06);display:flex;flex-direction:column;height:86vh}
  .header{display:flex;align-items:center;gap:12px;padding:14px 18px;border-bottom:1px solid #f0f0f0}
  .header .avatar{width:48px;height:48px;border-radius:50%;overflow:hidden}
  .header .avatar img{width:100%;height:100%;object-fit:cover}
  .header .meta{font-weight:700}
  .messages{flex:1;padding:18px;overflow-y:auto;background:linear-gradient(#fff,#fbfbff)}
  .bubble{max-width:70%;padding:10px 14px;margin-bottom:12px;border-radius:16px;line-height:1.35}
  .bubble.left{background:#f0eefb;border:1px solid #eee;float:left;clear:both}
  .bubble.right{background:#5a189a;color:#fff;float:right;clear:both}
  .msg-meta{font-size:12px;color:#888;margin-top:6px}
  .composer{display:flex;gap:10px;padding:12px;border-top:1px solid #eee;align-items:center}
  .composer input[type="text"]{flex:1;padding:10px 12px;border-radius:28px;border:1px solid #ddd;outline:none}
  .composer input[type="file"]{display:none}
  .icon-btn{background:none;border:0;color:#5a189a;font-size:18px;cursor:pointer;padding:8px;border-radius:8px}
  .send-btn{background:#5a189a;color:#fff;padding:8px 12px;border-radius:10px;border:0;cursor:pointer;font-weight:700}
  .small-img{display:block;margin-top:8px;max-width:220px;border-radius:10px}
  .muted{color:#888;font-size:13px}
  @media(max-width:700px){.bubble{max-width:86%}}
</style>
</head>
<body>
  <div class="wrap" role="main" aria-label="Direct message">
    <div class="header">
      <a href="message.php" class="icon-btn" title="Back" style="font-size:20px">&#8592;</a>
      <?php if ($receiver_user): ?>
        <div class="avatar"><img src="<?= esc($receiver_user['profile_pic'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100') ?>" alt=""></div>
        <div class="meta"><?= esc(display_name_from_row($receiver_user)) ?></div>
        <div style="flex:1"></div>
        <div class="muted">Chat</div>
      <?php else: ?>
        <div class="meta">New message</div>
      <?php endif; ?>
    </div>

    <div id="messages" class="messages" aria-live="polite" aria-relevant="additions removals">
      <!-- messages will be loaded by JS -->
      <div class="muted center">Loading messages…</div>
    </div>

    <form id="sendForm" class="composer" enctype="multipart/form-data" method="post" action="">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="receiver_id" id="receiver_id" value="<?= esc($receiver_id ?? '') ?>">
      <label for="imageInput" class="icon-btn" title="Attach image"><i class="fa-regular fa-image"></i></label>
      <input type="file" id="imageInput" name="image" accept="image/*" />
      <input id="messageInput" name="message" type="text" placeholder="Type a message..." autocomplete="off" />
      <button type="button" id="sendBtn" class="send-btn">Send</button>
    </form>
  </div>

<script>
(function(){
  const me = <?= json_encode($me) ?>;
  const receiver = <?= json_encode($receiver_id ?: null) ?>;
  const messagesEl = document.getElementById('messages');
  const sendBtn = document.getElementById('sendBtn');
  const messageInput = document.getElementById('messageInput');
  const form = document.getElementById('sendForm');
  const imageInput = document.getElementById('imageInput');
  let polling = true;
  let lastFetchAt = null; // ISO timestamp can be used as since

  // helper DOM escaping
  function escHtml(s){ return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]; }); }

  // render a message object
  function renderMessage(m){
    const isMe = (parseInt(m.sender_id) === parseInt(me));
    const div = document.createElement('div');
    div.className = 'bubble ' + (isMe ? 'right' : 'left');
    let html = '';
    if (m.message && m.message.length) html += '<div>' + escHtml(m.message).replace(/\n/g,'<br>') + '</div>';
    if (m.image) html += '<img class="small-img" src="' + escHtml(m.image) + '" alt="attached image">';
    html += '<div class="msg-meta">'+ (isMe ? 'You' : (m.first_name || m.name || '')) + ' • ' + new Date(m.created_at).toLocaleString() + '</div>';
    div.innerHTML = html;
    return div;
  }

  // scroll to bottom
  function scrollBottom(){
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  // fetch messages (full or recent)
  async function fetchMessages(since){
    const fd = new FormData();
    fd.append('action','fetch');
    fd.append('receiver_id', receiver || '');
    if (since) fd.append('since', since);
    const res = await fetch('', { method: 'POST', body: fd });
    const json = await res.json();
    if (!json || json.status !== 'success') return null;
    return json.messages || [];
  }

  // initial load
  async function loadInitial(){
    messagesEl.innerHTML = '<div class="muted">Loading messages...</div>';
    const msgs = await fetchMessages();
    messagesEl.innerHTML = '';
    if (!msgs || msgs.length === 0) {
      messagesEl.innerHTML = '<div class="muted">No messages yet. Say hi 👋</div>';
      lastFetchAt = new Date().toISOString();
      scrollBottom();
      return;
    }
    msgs.forEach(m => {
      const el = renderMessage(m);
      messagesEl.appendChild(el);
      lastFetchAt = m.created_at;
    });
    scrollBottom();
  }

  // poll for new messages periodically
  async function poll(){
    if (!polling || !receiver) return;
    try {
      const newMsgs = await fetchMessages(lastFetchAt);
      if (newMsgs && newMsgs.length) {
        // append
        const placeholderNoMsg = messagesEl.querySelector('.muted');
        if (placeholderNoMsg) placeholderNoMsg.remove();
        newMsgs.forEach(m => {
          const el = renderMessage(m);
          messagesEl.appendChild(el);
          lastFetchAt = m.created_at;
        });
        scrollBottom();
      }
    } catch(err){
      // ignore
    } finally {
      setTimeout(poll, 3500);
    }
  }

  // send message (AJAX)
  async function sendMessage() {
    if (!receiver) {
      alert('No receiver selected. Open a conversation or provide receiver_id in URL.');
      return;
    }
    const text = messageInput.value.trim();
    const file = imageInput.files[0] || null;
    if (!text && !file) {
      return;
    }
    const fd = new FormData();
    fd.append('action','send');
    fd.append('receiver_id', receiver);
    fd.append('message', text);
    if (file) fd.append('image', file);

    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    try {
      const res = await fetch('', { method: 'POST', body: fd });
      const json = await res.json();
      if (json && json.status === 'success') {
        // append message returned
        const m = json.message;
        const el = renderMessage(m);
        // remove "no messages" placeholder if present
        const placeholder = messagesEl.querySelector('.muted');
        if (placeholder) placeholder.remove();
        messagesEl.appendChild(el);
        lastFetchAt = m.created_at;
        messageInput.value = '';
        imageInput.value = '';
        scrollBottom();
      } else {
        alert('Could not send message: ' + (json.message||'unknown error'));
      }
    } catch (err) {
      alert('Network error sending message');
    } finally {
      sendBtn.disabled = false;
      sendBtn.textContent = 'Send';
    }
  }

  // events
  sendBtn.addEventListener('click', sendMessage);
  messageInput.addEventListener('keydown', (e)=>{ if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });

  // On load: fetch and start polling
  loadInitial().then(()=>{ setTimeout(poll, 1800); });

  // stop polling on page hide/unload
  window.addEventListener('beforeunload', ()=>{ polling = false; });
  document.addEventListener('visibilitychange', ()=>{ if (document.hidden) polling=false; else { polling=true; setTimeout(poll,500); } });

})();
</script>
</body>
</html>
