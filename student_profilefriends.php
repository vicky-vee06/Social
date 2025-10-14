<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}include('./inc/config.php');

// Get logged-in user ID from session
$current_user_id = $_SESSION['user_id'] ?? 1; // fallback to 1 for testing

$sql = "
SELECT u.id, u.first_name, u.last_name, u.faculty, u.institution, u.department
FROM users u
JOIN friends f ON u.id = f.friend_id
WHERE f.student_id = $current_user_id
";
$result = $conn->query($sql);
$friends = [];
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $friends[] = $row;
    }
}

// Fetch student info
$student_sql = "SELECT first_name FROM users WHERE id = $current_user_id";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Friends</title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
<style>
/* Include all your existing CSS here from your previous friends page */
:root {
            --primary-color: #793DDC;
            --background-color: #F8F7FF;
            --card-background: #FFFFFF;
            --text-color: #333333;
            --light-text: #666666;
            --border-color: #EEEEEE;
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

        /* --- Header & Sidebar (Reusable Style Block) --- */
        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            font-size: 20px;
            color: var(--light-text);
        }

        .sidebar {
            width: 280px;
            padding: 24px;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            background-color: var(--card-background);
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            font-weight: 600; /* make sidebar text a bit bolder */
        }

        .profile-summary {
            text-align: center;
            margin-bottom: 35px;
            padding: 20px;
            background-color: rgba(121, 61, 220, 0.03);
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .profile-summary:hover {
            transform: translateY(-2px);
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #b39ddb;
            margin: 0 auto 15px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(121, 61, 220, 0.15);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        .profile-summary h2 {
            font-size: 20px;
            margin: 0;
            color: var(--text-color);
            font-weight: 700; /* stronger profile name */
        }

        .edit-profile {
            font-size: 13px;
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 12px;
            padding: 6px 14px;
            border-radius: 20px;
            background-color: rgba(121, 61, 220, 0.08);
            transition: all 0.3s ease;
        }

        .edit-profile i {
            margin-left: 6px;
            font-size: 12px;
        }

        .edit-profile:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            margin-bottom: 10px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 700; /* bolder nav items */
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: rgba(121, 61, 220, 0.08);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.2);
        }
        
        /* --- Main Content --- */
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

        .profile-tabs { display:flex; gap:20px; border-bottom:1px solid var(--border-color); margin-bottom:20px; padding-top:10px; align-items:center }

        .tab-button { display:inline-flex; align-items:center; gap:8px; padding:10px 12px; cursor:pointer; font-weight:600; color:var(--light-text); border-bottom:3px solid transparent; transition: color 0.18s, transform 0.08s; position:relative; border-radius:6px 6px 0 0 }
        /* Remove underline in all anchor states */
        .tab-button, .tab-button:visited, .tab-button:hover, .tab-button:focus, .tab-button:active { text-decoration:none }
        .tab-button:hover { color: rgba(0,0,0,0.85); transform: translateY(-1px) }
        .tab-button.active { color:var(--primary-color); border-bottom-color:var(--primary-color); background: rgba(121,61,220,0.04) }

        .tab-button::after { content:''; position:absolute; bottom:-3px; left:0; width:100%; height:3px; background-color:var(--primary-color); transform:scaleX(0); transition:transform 0.28s ease }
        .tab-button:hover::after { transform:scaleX(0.35) }
        .tab-button.active::after { transform:scaleX(1) }

        /* --- Friends List Specific Styles --- */
        .friends-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .search-bar {
            padding: 12px 24px;
            border: 2px solid var(--border-color);
            border-radius: 30px;
            display: flex;
            align-items: center;
            width: 300px;
            transition: all 0.3s ease;
            background-color: var(--background-color);
        }

        .search-bar:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(121, 61, 220, 0.1);
            transform: translateY(-1px);
        }
        
        .search-bar input {
            border: none;
            outline: none;
            margin-left: 12px;
            flex-grow: 1;
            font-size: 14px;
            background: transparent;
        }

        .search-bar i {
            color: var(--primary-color);
            font-size: 16px;
        }

        .friends-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 columns for desktop */
            gap: 20px;
        }

        .friend-card {
            background-color: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .friend-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(121, 61, 220, 0.1);
            border-color: rgba(121, 61, 220, 0.3);
        }

        .friend-card .avatar {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background-color: #ddd;
            margin: 0 auto 15px;
            border: 3px solid rgba(121, 61, 220, 0.1);
            transition: transform 0.3s ease;
        }

        .friend-card:hover .avatar {
            transform: scale(1.05);
            border-color: rgba(121, 61, 220, 0.3);
        }
        
        .friend-card h4 {
            margin: 5px 0 0 0;
            font-size: 17px;
            color: var(--text-color);
            font-weight: 600;
        }
        
        .friend-card p {
            margin: 8px 0 15px 0;
            font-size: 13px;
            color: var(--light-text);
        }

        .friend-card button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .friend-card button:before {
            content: '\f27a';
            font-family: 'Font Awesome 5 Free';
            margin-right: 8px;
            font-size: 14px;
        }
        
        .friend-card button:hover {
            background-color: #6a34c1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.2);
        }
        
</style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h1 style="font-size: 24px; color: var(--text-color);">Profile</h1>
        <div class="profile-summary">
            <div class="profile-pic"></div>
            <h2><?php echo htmlspecialchars($student['first_name']); ?></h2>
            <p style="font-size: 14px; color: var(--light-text); margin-top: 5px;"><?= count($friends) ?> friends</p>
            <a href="./settings_profileinfo.php" class="edit-profile">edit profile <i class="fas fa-edit"></i></a>
        </div>

        <nav>
            <a href="./home_feed.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="./student_aboutprofile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="./auth/public-explore.php" class="nav-link"><i class="fas fa-search"></i> Explore</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <div style="font-size: 24px; font-weight: bold;"></div>
            <div class="header-actions">
                <button><i class="fas fa-bell"></i></button>
                <button><i class="fas fa-cog"></i></button>
            </div>
        </div>

        <div class="profile-header-strip"></div>

        <div class="profile-tabs">
            <a class="tab-button" href="profile_timeline.php">Timeline</a>
            <a class="tab-button" href="student_aboutprofile.php">About</a>
            <a class="tab-button" href="student_profileinstitution.php">Institutions</a>
            <a class="tab-button" href="student_profilecommunity.php">Communities</a>
            <a class="tab-button active" href="student_profilefriends.php">Friends</a>
        </div>

        <div class="friends-header">
            <h2 style="font-size: 24px; color: var(--text-color); margin: 0;">Friends (<?= count($friends) ?>)</h2>
            <div class="search-bar">
                <span><i class="fas fa-search"></i></span>
                <input type="text" placeholder="Search friends...">
            </div>
        </div>

        <div class="friends-grid">
            <?php if(!empty($friends)): ?>
                <?php foreach($friends as $friend): ?>
                    <div class="friend-card">
                        <div class="avatar"></div>
                        <h4><?= htmlspecialchars($friend['first_name'] . ' ' . $friend['last_name']) ?></h4>
                        <p><?= htmlspecialchars($friend['faculty'] . ', ' . $friend['institution']) ?></p>
                        <button>Message</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No friends found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
