<?php
// Database connection
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "user_system";

session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Assume student is logged in and their ID is stored in session
$student_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Fetch student info
$student_sql = "SELECT first_name FROM users WHERE id = $student_id";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();

// Fetch institutions
$inst_sql = "SELECT * FROM student_institutions WHERE student_id = $student_id ORDER BY is_primary DESC";
$inst_result = $conn->query($inst_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Institutions</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="css/student_profileinstitution.css">
    <style>
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

/* Sidebar */
.sidebar {
    width: 280px;
    padding: 22px;
    border-right: 1px solid var(--border-color);
    flex-shrink: 0;
    background: linear-gradient(180deg, #FFF 0%, #FBF9FF 100%);
    font-weight: 700;
    position: sticky;
    top: 24px;
    align-self: flex-start;
}
.sidebar-title { font-size: 20px; color: var(--text-color); margin: 0 0 10px 0; }
.profile-summary { text-align:center; margin-bottom:20px; padding:14px; border-radius:12px; background:linear-gradient(180deg, rgba(121,61,220,0.04), rgba(121,61,220,0.02)); box-shadow: var(--shadow-sm);}
.profile-pic { width:88px; height:88px; border-radius:50%; background-color:#b39ddb; margin:0 auto 10px; border:3px solid rgba(121,61,220,0.12); box-shadow:0 6px 18px rgba(121,61,220,0.06);}
.profile-summary h2 { font-size:18px; margin:6px 0 0 0; color: var(--text-color); text-transform:capitalize; letter-spacing:0.2px;}
.friends-count { font-size:13px; color:var(--light-text); margin-top:6px; display:inline-flex; gap:8px; align-items:center;}
.friends-count i { color: rgba(121,61,220,0.9);}
.edit-profile { font-size:13px; color:white; background:var(--primary-color); text-decoration:none; display:inline-flex; gap:8px; align-items:center; padding:6px 10px; border-radius:8px; margin-top:8px; font-weight:700;}
.edit-profile i { font-size:12px }
.sidebar-nav { margin-top:8px; padding-top:6px; border-top:1px solid rgba(0,0,0,0.03);}
.nav-link { display:flex; align-items:center; gap:10px; padding:10px 14px; margin-bottom:8px; color: var(--light-text); text-decoration:none; border-radius:10px; font-weight:700;}
.nav-link i { color: var(--primary-color); width:18px; text-align:center}
.nav-link:hover { background: rgba(121,61,220,0.04); color: rgba(0,0,0,0.85); transform: translateX(4px);}
.nav-link.active { background: rgba(121,61,220,0.08); color: var(--primary-color); box-shadow: inset 4px 0 0 rgba(121,61,220,0.12);}

/* Main Content */
.main-content { flex-grow:1; padding:0 30px 30px 30px;}
.header { width:100%; display:flex; justify-content:flex-end; padding:15px 0; border-bottom:1px solid var(--border-color); background-color: var(--card-background); position: sticky; top:0; z-index:10;}
.header-actions button { background:none; border:none; cursor:pointer; font-size:20px; color: var(--light-text); margin-left:15px;}
.profile-header-strip { height:120px; background-color: var(--primary-color); border-radius:0 0 15px 15px; margin:0 -30px 20px -30px;}
.profile-tabs { display:flex; gap:12px; border-bottom:1px solid var(--border-color); margin-bottom:26px; padding-top:10px; align-items:center;}
.tab-button { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; cursor:pointer; font-weight:600; color:var(--light-text); border-bottom:3px solid transparent; border-radius:6px 6px 0 0; transition: color 0.12s, background 0.12s, transform 0.08s; text-decoration:none;}
.tab-button:hover { color: rgba(0,0,0,0.85); transform:translateY(-1px);}
.tab-button.active { color: var(--primary-color); border-bottom-color: var(--primary-color); background: rgba(121,61,220,0.04);}

.section-title { font-size:24px; color: var(--text-color); margin-top:0; margin-bottom:16px;}

/* Institution List */
.institution-list { display:flex; flex-direction:column; gap:15px;}
.institution-card { background-color: var(--card-background); padding:18px; border-radius:12px; border:1px solid var(--border-color); display:flex; align-items:center; justify-content:space-between; box-shadow:var(--shadow-sm); position:relative; transition: transform 0.12s ease, box-shadow 0.12s ease;}
.institution-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(26,18,44,0.06);}
.institution-details { display:flex; align-items:center;}
.logo-placeholder { width:56px; height:56px; border-radius:50%; background-color:#ddd; margin-right:15px; display:flex; align-items:center; justify-content:center; color:white; font-size:20px;}
.logo-placeholder i { font-size:20px;}
.institution-name { font-size:18px; font-weight:bold; color: var(--text-color); margin:0;}
.institution-role { font-size:14px; color: var(--light-text); margin:3px 0 0 0;}
.action-link { color: var(--primary-color); text-decoration:none; font-weight:600; font-size:14px; cursor:pointer;}
.primary-tag { position:absolute; top:0; right:0; background-color: var(--primary-color); color:white; padding:6px 14px; border-radius:0 12px 0 12px; font-size:12px; font-weight:800;}
.add-institution-card { border:1px dashed var(--border-color); text-align:center; padding:28px; color:var(--light-text); cursor:pointer; transition: all 0.12s ease;}
.add-institution-card:hover { border-color: var(--primary-color); color:var(--primary-color); transform: translateY(-3px);}
.add-institution-card span { font-size:28px;}
.add-institution-card p { margin-top:8px; font-weight:700;}

    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h1 class="sidebar-title">Profile</h1>
            <div class="profile-summary">
                <div class="profile-pic"></div>
                <h2><?php echo htmlspecialchars($student['first_name']); ?></h2>
                <p class="friends-count"><i class="fas fa-user-friends"></i> 50 friends</p>
                <a href="#" class="edit-profile"><i class="fas fa-pen"></i>Edit Profile</a>
            </div>

            <div class="sidebar-nav">
                <a href="./home_feed.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="./auth/profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
                <a href="./auth/public-explore.php" class="nav-link"><i class="fas fa-search"></i> Explore</a>
            </div>
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
                <a class="tab-button active" href="student_profileinstitution.php">Institutions</a>
                <a class="tab-button" href="student_profilecommunity.php">Communities</a>
                <a class="tab-button" href="student_profilefriends.php">Friends</a>
            </div>

            <h2 class="section-title">My Institutions</h2>

            <div class="institution-list">
                <?php
                if ($inst_result->num_rows > 0) {
                    while ($inst = $inst_result->fetch_assoc()) {
                        $primaryTag = $inst['is_primary'] ? '<div class="primary-tag">Primary</div>' : '';
                        $logoColor = $inst['logo_color'] ?? '#793DDC';
                        $logoIcon = $inst['logo_icon'] ?? 'fas fa-graduation-cap';
                        echo '<div class="institution-card">'
                            .$primaryTag
                            .'<div class="institution-details">'
                            .'<div class="logo-placeholder" style="background-color: '.$logoColor.';"><i class="'.$logoIcon.'"></i></div>'
                            .'<div>'
                            .'<p class="institution-name">'.htmlspecialchars($inst['name']).'</p>'
                            .'<p class="institution-role">'.htmlspecialchars($inst['role']).'</p>'
                            .'</div></div>'
                            .'<a href="#" class="action-link">'.($inst['is_primary'] ? 'View Profile <i class="fas fa-arrow-right" style="margin-left:8px"></i>' : 'Make Primary').'</a>'
                            .'</div>';
                    }
                }
                ?>

                <div class="add-institution-card" onclick="window.location.href='add_institution.php'">
                    <span><i class="fas fa-plus"></i></span>
                    <p>Add New Institution</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
