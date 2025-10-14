<?php
session_start();
include('./inc/config.php');

// Example logged-in user
$logged_in_user_id = 1;

// Fetch logged-in user info
$user_result = $conn->query("SELECT id, name, avatar FROM users WHERE id=$logged_in_user_id");
$user = $user_result->fetch_assoc();

// Fetch friend requests with followers count
$requests_result = $conn->query("
    SELECT u.id, u.name, u.avatar, u.bio,
           (SELECT COUNT(*) FROM friend_requests fr WHERE fr.receiver_id = u.id AND fr.status='accepted') AS followers_count
    FROM friend_requests f
    JOIN users u ON f.sender_id = u.id
    WHERE f.receiver_id = $logged_in_user_id AND f.status='pending'
");

$requests = [];
if($requests_result){
    while($row = $requests_result->fetch_assoc()){
        $requests[] = $row;
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];
    $logged_in_user_id = 1;

    if($action === 'accept'){
        $conn->query("UPDATE friend_requests SET status='accepted' WHERE id=$request_id AND receiver_id=$logged_in_user_id");
        echo 'success';
    } elseif($action === 'delete'){
        $conn->query("DELETE FROM friend_requests WHERE id=$request_id AND receiver_id=$logged_in_user_id");
        echo 'success';
    } else{
        echo 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Friend Requests</title>
<link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
<style>
:root {
    --primary-purple: #7a3edb;
    --light-purple: #f3effe;
    --light-purple-hover: #e7dbff;
    --text-dark: #333;
    --text-light: #666;
    --border-light: #eee;
    --bg-light: #f9f9f9;
    --bg-white: #fff;
    --font-family: Arial, sans-serif;
}
body { font-family: var(--font-family); margin:0; padding:0; background:var(--bg-light); display:flex; justify-content:center; min-height:100vh; }
.container { width:100%; max-width:1400px; display:flex; background:var(--bg-white); box-shadow:0 0 20px rgba(0,0,0,0.05); min-height:100vh; }
.sidebar { width:360px; background:var(--light-purple); padding:40px 32px; border-right:1px solid var(--border-light); display:flex; flex-direction:column; }
.sidebar h2 { margin:0 0 20px 0; color:var(--text-dark); }
.profile-section { display:flex; gap:18px; align-items:center; margin-bottom:20px; }
.profile-section img { width:88px; height:88px; border-radius:50%; border:2px solid var(--primary-purple); object-fit:cover; }
.profile-meta { display:flex; flex-direction:column; }
.profile-meta .profile-name { font-weight:900; color:var(--text-dark); }
.profile-meta .friends-count { font-size:13px; color:var(--text-light); display:flex; align-items:center; gap:8px; }
nav ul { list-style:none; padding:0; margin:20px 0; display:flex; flex-direction:column; gap:18px; }
nav a.sidebar-link { display:flex; align-items:center; padding:14px 16px; color:var(--text-dark); text-decoration:none; font-weight:800; border-left:4px solid transparent; border-radius:8px; }
nav a.sidebar-link i { margin-right:12px; font-size:16px; color:var(--primary-purple); }
nav a.sidebar-link:hover, nav a.sidebar-link.active { background:var(--light-purple-hover); border-left-color:var(--primary-purple); color:var(--primary-purple); }
.sidebar-footer { margin-top:auto; }
.sidebar-footer a { display:flex; align-items:center; gap:10px; padding:10px 18px; color:var(--text-dark); text-decoration:none; border-radius:8px; font-weight:700; }
.sidebar-footer a:hover { background:var(--light-purple-hover); color:var(--primary-purple); }

.profile-area { flex:1; padding:28px 32px; }
.profile-card { background:var(--bg-white); border-radius:12px; padding:28px; box-shadow:0 8px 24px rgba(0,0,0,0.06); }
.tabs { display:flex; gap:10px; margin-bottom:20px; }
.tab-item { flex:1; padding:10px; text-align:center; border-radius:8px; cursor:pointer; background:var(--bg-light); font-weight:700; }
.tab-item.active { background:var(--primary-purple); color:#fff; box-shadow:0 6px 18px rgba(122,62,219,0.08); }

.friend-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:24px; }
.friend-item { display:flex; align-items:center; gap:18px; padding:18px; border:1px solid var(--border-light); border-radius:12px; background:var(--bg-white); }
.friend-photo { width:80px; height:80px; border-radius:50%; background:#ccc; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#fff; }
.friend-details { flex:1; }
.friend-name { font-weight:700; color:var(--text-dark); }
.friend-bio { font-size:13px; color:var(--text-light); margin-top:4px; }
.friend-followers { font-size:12px; color:var(--text-light); margin-top:2px; display:flex; align-items:center; gap:4px; }
.friend-actions { display:flex; gap:10px; }
.action-button { padding:8px 14px; border:none; border-radius:8px; font-weight:600; cursor:pointer; background:var(--primary-purple); color:#fff; }
.action-button.secondary { background:var(--border-light); color:var(--text-dark); }

@media(max-width:880px){ .friend-list{grid-template-columns:repeat(auto-fit,minmax(240px,1fr));} }
@media(max-width:600px){ .friend-item{flex-direction:column; align-items:stretch;} .friend-actions{justify-content:flex-end; width:100%; margin-top:8px;} }
</style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>Profile</h2>
        <div class="profile-section">
            <img src="<?php echo $user['avatar']; ?>" alt="Avatar">
            <div class="profile-meta">
                <div class="profile-name"><?php echo $user['name']; ?></div>
                <div class="friends-count">
                    <i class="fa fa-user-friends"></i>
                    <?php
                    $followers_count = $conn->query("SELECT COUNT(*) AS cnt FROM friend_requests WHERE receiver_id = $logged_in_user_id AND status='accepted'")->fetch_assoc();
                    echo $followers_count['cnt'] . " followers";
                    ?>
                </div>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="../home_feed.php" class="sidebar-link"><i class="fas fa-home"></i> Feed</a></li>
                <li><a href="./profile.php" class="sidebar-link active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="./public-explore.php" class="sidebar-link"><i class="fas fa-search"></i> Explore</a></li>
                <li><a href="./community.php" class="sidebar-link"><i class="fas fa-users"></i> Communities</a></li>
                <li><a href="./message.php" class="sidebar-link"><i class="fas fa-envelope"></i> Message</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="./sign.php"><i class="fa fa-sign-out-alt"></i> Log out</a>
        </div>
    </div>

    <div class="profile-area">
        <div class="profile-card">
            <div class="tabs">
                <div class="tab-item active">Friend Requests</div>
            </div>

            <div class="friend-list" id="friend-list">
                <?php if(count($requests) > 0): ?>
                    <?php foreach($requests as $req): ?>
                    <div class="friend-item" id="request-<?php echo $req['id']; ?>">
                        <div class="friend-photo">
                            <?php echo $req['avatar'] ? '<img src="'.$req['avatar'].'" style="width:100%;height:100%;border-radius:50%;">' : strtoupper(substr($req['name'],0,2)); ?>
                        </div>
                        <div class="friend-details">
                            <div class="friend-name"><?php echo $req['name']; ?></div>
                            <div class="friend-bio"><?php echo $req['bio']; ?></div>
                            <div class="friend-followers"><i class="fa fa-user-friends"></i> <?php echo $req['followers_count']; ?> followers</div>
                        </div>
                        <div class="friend-actions">
                            <button class="action-button" onclick="handleRequest(<?php echo $req['id']; ?>,'accept')">Accept</button>
                            <button class="action-button secondary" onclick="handleRequest(<?php echo $req['id']; ?>,'delete')">Delete</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding:20px; color:var(--text-light);">No friend requests.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function handleRequest(requestId, action){
    fetch('handle_request.php', {
        method:'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'request_id=' + requestId + '&action=' + action
    }).then(res => res.text()).then(data => {
        // remove the friend-item from list
        if(data === 'success'){
            document.getElementById('request-' + requestId).remove();
        } else {
            alert('Failed to process request.');
        }
    });
}
</script>
</body>
</html>
