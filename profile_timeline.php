<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}include('./inc/config.php'); // Your DB connection

// For testing, if no session, fallback
if(!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // example logged-in student ID
}

$current_user_id = $_SESSION['user_id'];

// Handle new post submission
if(isset($_POST['new_post'])){
    $content = $conn->real_escape_string($_POST['content']);
    if(!empty($content)){
        $conn->query("INSERT INTO posts (user_id, content, created_at) VALUES ($current_user_id, '$content', NOW())");
    }
}

// Handle like
if(isset($_GET['like_post_id'])){
    $post_id = intval($_GET['like_post_id']);
    $check = $conn->query("SELECT * FROM likes WHERE post_id=$post_id AND user_id=$current_user_id");
    if($check->num_rows == 0){
        $conn->query("INSERT INTO likes (post_id, user_id) VALUES ($post_id, $current_user_id)");
    } else {
        $conn->query("DELETE FROM likes WHERE post_id=$post_id AND user_id=$current_user_id");
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle comment
if(isset($_POST['comment_post_id'])){
    $comment_content = $conn->real_escape_string($_POST['comment_content']);
    $post_id = intval($_POST['comment_post_id']);
    if(!empty($comment_content)){
        $conn->query("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES ($post_id, $current_user_id, '$comment_content', NOW())");
    }
}

// Fetch logged-in user info
$user_result = $conn->query("SELECT first_name FROM users WHERE id=$current_user_id");
$user = $user_result->fetch_assoc();

// Fetch all posts
$posts_result = $conn->query("SELECT p.id, p.user_id, p.content, p.created_at, u.first_name FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Timeline</title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
<style>
/* ==== Paste your exact CSS here ==== */
:root {
    --primary-color: #793DDC;
    --primary-light: #9665e3;
    --secondary-color: #A362FF;
    --background-color: #F8F7FF;
    --card-background: #FFFFFF;
    --text-color: #333333;
    --light-text: #666666;
    --border-color: #EEEEEE;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --transition-speed: 0.2s;
}
body { font-family: Arial, sans-serif; margin:0; padding:0; background-color: var(--background-color); display:flex; justify-content:center;}
.container { width: 90%; max-width: 1400px; display:flex; background-color: var(--card-background); box-shadow: 0 0 20px rgba(0,0,0,0.05); min-height:100vh;}
.header { width:100%; display:flex; justify-content:space-between; align-items:center; padding:15px 30px; border-bottom:1px solid var(--border-color); background-color: var(--card-background); position:sticky; top:0; z-index:10;}
.header h1 { font-size:24px; color: var(--text-color); margin:0;}
.header-actions { display:flex; gap:20px;}
.header-actions button, .icon-btn { background:none; border:none; cursor:pointer; font-size:20px; color: var(--light-text);}
.header-actions .icon-btn:hover { color: var(--primary-color); transform: translateY(-2px);}
.icon-btn { transition: all var(--transition-speed) ease; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:50%; color: var(--light-text);}
.icon-btn:hover { background-color: rgba(121,61,220,0.1); color: var(--primary-color);}
.sidebar { width:280px; padding:30px; border-right:1px solid var(--border-color); flex-shrink:0; background: linear-gradient(to bottom, var(--card-background) 0%, rgba(248,247,255,0.5) 100%); position:sticky; top:0; height:100vh; overflow-y:auto; font-weight:600;}
.profile-summary { text-align:center; margin-bottom:40px; padding:20px; border-radius:16px; background:linear-gradient(145deg, var(--card-background) 0%, var(--background-color) 100%); box-shadow: var(--shadow-md);}
.profile-pic { width:120px; height:120px; border-radius:50%; margin:0 auto 15px; border:3px solid var(--primary-color); overflow:hidden; box-shadow: var(--shadow-md); transition: transform var(--transition-speed) ease;}
.profile-pic:hover { transform:scale(1.05);}
.profile-pic img { width:100%; height:100%; object-fit:cover;}
.profile-summary h2 { font-size:18px; margin:0; color: var(--text-color); font-weight:700;}
.profile-summary p { font-size:14px; color: var(--light-text); margin:5px 0 0;}
.edit-profile { font-size:12px; color: var(--primary-color); text-decoration:none; display:block; margin-top:5px;}
.nav-link { display:flex; align-items:center; padding:12px 20px; margin-bottom:10px; color: var(--light-text); text-decoration:none; border-radius:12px; transition: all var(--transition-speed) ease; font-weight:700;}
.nav-link:hover, .nav-link.active { background-color: rgba(121,61,220,0.1); color: var(--primary-color); font-weight:bold; transform: translateX(5px); box-shadow: var(--shadow-md);}
.nav-link.active { border-left:3px solid var(--primary-color);}
.nav-link span { margin-left:10px;}
.main-content { flex-grow:1; padding:0 30px 30px 30px;}
.profile-header-strip { height:120px; background-color: var(--primary-color); border-radius:0 0 15px 15px; margin:0 -30px 20px -30px; position:relative;}
.profile-tabs { display:flex; gap:20px; border-bottom:1px solid var(--border-color); margin-bottom:20px; top:60px; background-color: var(--card-background); z-index:5; padding-top:10px; align-items:center;}
.tab-button { display:inline-flex; align-items:center; gap:8px; padding:10px 12px; cursor:pointer; font-weight:600; color: var(--light-text); border-bottom:3px solid transparent; transition: color 0.18s, transform 0.08s; position:relative; border-radius:6px 6px 0 0;}
.tab-button:hover { color: rgba(0,0,0,0.85); transform: translateY(-1px);}
.tab-button.active { color: var(--primary-color); border-bottom-color: var(--primary-color); background: rgba(121,61,220,0.04);}
.timeline-container { display:flex; gap:20px;}
.left-column { flex:2;}
.right-column { flex:1;}
.card { background-color: var(--card-background); padding:20px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.03); margin-bottom:20px;}
.post-input-container { display:flex; align-items:center; border:1px solid var(--border-color); border-radius:25px; padding:10px 20px; background-color: var(--background-color); margin-bottom:20px;}
.post-input-container input { border:none; flex-grow:1; padding:10px 0; font-size:16px; outline:none; background-color:transparent;}
.post-input-container .icon-btn { font-size:20px; color: var(--primary-color); margin-left:10px;}
.post { border-bottom:1px solid var(--border-color); padding-bottom:20px; margin-bottom:20px;}
.post:last-child { border-bottom:none; margin-bottom:0;}
.post-user-info { display:flex; align-items:center; margin-bottom:10px;}
.post-avatar { width:40px; height:40px; border-radius:50%; background-color: var(--secondary-color); margin-right:10px;}
.post-username { font-weight:bold; color: var(--text-color);}
.post-timestamp { font-size:12px; color: var(--light-text); margin-left:10px;}
.post-content { color: var(--text-color); line-height:1.6; margin-bottom:15px;}
.post-actions { display:flex; gap:20px; color: var(--light-text); font-size:14px;}
.action-button { cursor:pointer; display:flex; align-items:center; gap:5px;}
</style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="profile-summary">
            <div class="profile-pic"><img src="./img/medium-shot-students-classroom.jpg" alt="Profile Pic"></div>
            <h2><?php echo $user['first_name']; ?></h2>
            <?php 
            $friend_count_result = $conn->query("SELECT COUNT(*) as cnt FROM friends WHERE student_id=$current_user_id");
            $friend_count = $friend_count_result->fetch_assoc();
            ?>
            <p><?php echo $friend_count['cnt']; ?> friends</p>
            <a href="#" class="edit-profile">edit profile <i class="fas fa-pencil-alt"></i></a>
        </div>

        <nav>
            <a href="../home_feed.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="./auth/public-explore.php" class="nav-link">
                <i class="fas fa-search"></i>
                <span>Explore</span>
            </a>
            <a href="./auth/community.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Communities</span>
            </a>
            <a href="./auth/message.php" class="nav-link">
                <i class="fas fa-comment-alt"></i>
                <span>Message</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Profile</h1>
            <div class="header-actions">
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
                <button class="icon-btn"><i class="fas fa-cog"></i></button>
                <button class="icon-btn"><i class="fas fa-ellipsis-v"></i></button>
            </div>
        </div>

        <div class="profile-header-strip"></div>

        <div class="profile-tabs">
            <a class="tab-button active" href="profile_timeline.php">Timeline</a>
            <a class="tab-button" href="student_aboutprofile.php">About</a>
            <a class="tab-button" href="student_profileinstitution.php">Institutions</a>
            <a class="tab-button" href="student_profilecommunity.php">Communities</a>
            <a class="tab-button" href="student_profilefriends.php">Friends</a>
        </div>

        <div class="timeline-container">
            <div class="left-column">
                <div class="card post-area">
                    <form method="POST">
                        <div class="post-input-container">
                            <input type="text" name="content" placeholder="Share your thoughts or study notes...">
                            <button class="icon-btn" type="submit" name="new_post"><i class="fas fa-paperclip"></i></button>
                        </div>
                    </form>
                </div>

                <?php while($post = $posts_result->fetch_assoc()): ?>
                    <?php
                        $post_id = $post['id'];
                        $like_result = $conn->query("SELECT COUNT(*) as cnt FROM likes WHERE post_id=$post_id");
                        $like_count = $like_result->fetch_assoc()['cnt'];

                        $comment_result = $conn->query("SELECT * FROM comments WHERE post_id=$post_id ORDER BY created_at ASC");
                    ?>
                    <div class="card post-card">
                        <div class="post-user-info">
                            <div class="post-avatar"></div>
                            <span class="post-username"><?php echo $post['first_name']; ?></span>
                            <span class="post-timestamp"><?php echo date('M d, H:i', strtotime($post['created_at'])); ?></span>
                        </div>
                        <p class="post-content"><?php echo nl2br($post['content']); ?></p>
                        <div class="post-actions">
                            <a href="?like_post_id=<?php echo $post['id']; ?>" class="action-button">👍 <span><?php echo $like_count; ?></span></a>
                        </div>
                        <div style="margin-top:10px;">
                            <?php while($comment = $comment_result->fetch_assoc()): ?>
                                <div style="padding:5px 0; border-bottom:1px solid #eee;">
                                    <b><?php 
                                    $cuser = $conn->query("SELECT first_name FROM users WHERE id=".$comment['user_id']);
                                    echo $cuser->fetch_assoc()['first_name'];
                                    ?>:</b> <?php echo htmlspecialchars($comment['comment']); ?>
                                </div>
                            <?php endwhile; ?>
                            <form method="POST" style="margin-top:10px;">
                                <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                                <input type="text" name="comment_content" placeholder="Write a comment..." style="width:80%; padding:5px;">
                                <button type="submit" style="padding:5px 10px;">Comment</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
