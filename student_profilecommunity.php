<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
  }
include('./inc/config.php');

$student_id = 1; 

$result = $conn->query("SELECT * FROM student_communities WHERE student_id = $student_id ORDER BY id DESC");
$communities = $result->fetch_all(MYSQLI_ASSOC);
$total_communities = count($communities);

// Count how many friends this student follows
$friends_count_sql = "SELECT COUNT(*) AS total_friends FROM friends WHERE student_id = $student_id";
$friends_count_result = $conn->query($friends_count_sql);

$total_friends = 0;
if($friends_count_result && $friends_count_result->num_rows > 0){
    $row = $friends_count_result->fetch_assoc();
    $total_friends = $row['total_friends'];
}

// Fetch friends details for friend list/grid
$friends_sql = "
SELECT u.id, u.first_name, u.last_name, u.faculty, u.institution, u.department
FROM users u
JOIN friends f ON u.id = f.friend_id
WHERE f.student_id = $student_id
";
$friends_result = $conn->query($friends_sql);

$friends = [];
if($friends_result && $friends_result->num_rows > 0){
    while($row = $friends_result->fetch_assoc()){
        $friends[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Communities</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        /* Keep all your CSS from the HTML you sent above */
        :root {
            --primary-color: #793DDC;
            --background-color: #F8F7FF;
            --card-background: #FFFFFF;
            --text-color: #333333;
            --light-text: #666666;
            --border-color: #EEEEEE;
            --shadow-sm: 0 6px 18px rgba(26,18,44,0.06);
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
        }

        .container {
            width: 90%;
            max-width: 1400px;
            display: flex;
            background-color: var(--card-background);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            min-height: 100vh;
        }

        /* Reusable Sidebar & Header Styles */
        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 15px 15px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--card-background);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--light-text);
            margin-left: 12px;
            padding: 8px;
            border-radius: 8px;
            transition: background-color 0.12s, color 0.12s;
        }
        .header-actions button:hover { background: rgba(0,0,0,0.03); color: var(--primary-color) }

        .sidebar {
            width: 260px;
            padding: 22px;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            background: linear-gradient(180deg, #FFF 0%, #FBF9FF 100%);
        }

        .profile-summary {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #b39ddb;
            margin: 0 auto 10px;
            border: 2px solid var(--primary-color);
        }

        .profile-summary h2 {
            font-size: 18px;
            margin: 0;
            color: var(--text-color);
        }

        .edit-profile {
            font-size: 12px;
            color: var(--primary-color);
            text-decoration: none;
            display: block;
            margin-top: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
        }
        .nav-link i { color: var(--primary-color); width: 20px; text-align: center }
        .nav-link.active { background: rgba(121,61,220,0.06); color: var(--primary-color) }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 0 30px 30px 30px;
        }

        .profile-header-strip {
            height: 120px;
            background-color: var(--primary-color);
            border-radius: 0 0 15px 15px;
            margin: 0 -30px 20px -30px;
        }

        .profile-tabs { display:flex; gap:12px; border-bottom:1px solid var(--border-color); margin-bottom:24px; padding-top:10px; align-items:center }
        .profile-tabs .tab-button { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; font-weight:700; color:var(--light-text); text-decoration:none; border-bottom:3px solid transparent; border-radius:6px 6px 0 0; transition: color 0.12s, background 0.12s, transform 0.08s }
        /* Ensure no underline in any state (visited/hover/focus/active) */
        .profile-tabs .tab-button, .profile-tabs .tab-button:visited, .profile-tabs .tab-button:hover, .profile-tabs .tab-button:focus, .profile-tabs .tab-button:active { text-decoration: none }
        .profile-tabs .tab-button:hover { color: rgba(0,0,0,0.85); transform: translateY(-1px) }
        .profile-tabs .tab-button.active { color:var(--primary-color); border-bottom-color:var(--primary-color); background: rgba(121,61,220,0.04) }

        /* Communities List Specific Styles */
        .communities-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:20px }

        .community-card { background-color:var(--card-background); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; box-shadow:var(--shadow-sm); text-align:center }

        .community-cover {
            height: 100px;
            background-color: #f0e6ff; /* Placeholder color */
            position: relative;
        }

        .community-logo {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 4px solid var(--card-background);
            position: absolute;
            bottom: -32px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 6px 18px rgba(26,18,44,0.06);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: white;
        }
        .community-logo i { font-size: 22px }

        .community-info {
            padding: 40px 15px 15px;
        }

        .community-info h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: var(--text-color);
        }

        .community-info p {
            font-size: 13px;
            color: var(--light-text);
            margin: 0 0 15px 0;
        }

        .community-stats {
            display: flex;
            justify-content: space-around;
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            margin-top: 10px;
        }

        .community-stats div {
            font-size: 14px;
        }

        .community-stats strong {
            display: block;
            font-size: 16px;
            color: var(--primary-color);
        }

        .join-status { display:inline-block; background-color:#e8f5e9; color:#4caf50; padding:6px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:10px }

        /* Responsive */
        @media (max-width: 920px) { .communities-grid { grid-template-columns: repeat(2, 1fr) } .sidebar { display:none } .container { width:96% } }
        @media (max-width: 560px) { .communities-grid { grid-template-columns: 1fr } .profile-pic { width:64px; height:64px } }
    </style>
</head>
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h1 style="font-size: 24px; color: var(--text-color);">Profile</h1>
        <div class="profile-summary">
    <div class="profile-pic"></div>
    <h2><?php echo $_SESSION['first_name']; ?></h2>
    <p style="font-size: 14px; color: var(--light-text); margin-top: 5px;">
        <?php echo $total_friends; ?> friends
    </p>
    <a href="./settings_profileinfo.php" class="edit-profile">edit profile <i class="fas fa-edit"></i></a>
</div>


        <nav>
            <a href="./home_feed.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="./auth/profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="./auth/public-explore.php" class="nav-link"><i class="fas fa-search"></i> Explore</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-actions">
                <button title="Notifications"><i class="fas fa-bell"></i></button>
                <button title="Settings"><i class="fas fa-cog"></i></button>
                <button title="More"><i class="fas fa-ellipsis-h"></i></button>
            </div>
        </div>

        <div class="profile-header-strip"></div>

        <div class="profile-tabs">
            <a class="tab-button" href="profile_timeline.php">Timeline</a>
            <a class="tab-button" href="student_aboutprofile.php">About</a>
            <a class="tab-button" href="student_profileinstitution.php">Institutions</a>
            <a class="tab-button active" href="student_profilecommunity.php">Communities</a>
            <a class="tab-button" href="student_profilefriends.php">Friends</a>
        </div>

        <h2 style="font-size: 24px; color: var(--text-color); margin-top: 0;">My Communities (<?php echo $total_communities; ?>)</h2>

        <div class="communities-grid">
            <?php foreach($communities as $community): ?>
            <div class="community-card">
                <div class="community-cover" style="background-color: <?php echo $community['cover_color']; ?>;"></div>
                <div class="community-logo" style="background-color: <?php echo $community['logo_color']; ?>;">
                    <i class="<?php echo $community['logo_icon']; ?>"></i>
                </div>
                <div class="community-info">
                    <?php if($community['joined']): ?>
                    <div class="join-status">Joined</div>
                    <?php else: ?>
                    <div class="join-status" style="background-color:#fff3e0; color:#ff9800;">Not Joined</div>
                    <?php endif; ?>
                    <h3><?php echo $community['name']; ?></h3>
                    <p><?php echo $community['description']; ?></p>
                    <div class="community-stats">
                        <div><strong><?php echo number_format($community['members_count']); ?></strong> Members</div>
                        <div><strong><?php echo number_format($community['posts_count']); ?></strong> Posts</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>
</body>
</html>
