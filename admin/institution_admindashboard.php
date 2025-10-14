<?php
session_start();
include '../inc/config.php'; 

// Initialize variables for stats
$totalCommunities = 0;
$activeStudents = 0;
$postsThisWeek = 0;

// Fetch total communities
$sqlCommunities = "SELECT COUNT(*) AS total_communities FROM student_communities"; // Assuming a 'student_communities' table
$resultCommunities = $conn->query($sqlCommunities);
if ($resultCommunities && $resultCommunities->num_rows > 0) {
    $row = $resultCommunities->fetch_assoc();
    $totalCommunities = $row['total_communities'];
}


// Fetch active students (this is a placeholder, actual logic depends on your 'users' table and 'active' status)
$sqlStudents = "SELECT COUNT(*) AS active_students FROM users WHERE status = 'active'"; // Assuming a 'users' table with a 'status' column
$resultStudents = $conn->query($sqlStudents);
if ($resultStudents && $resultStudents->num_rows > 0) {
    $row = $resultStudents->fetch_assoc();
    $activeStudents = $row['active_students'];
}
// Fetch posts this week
$sqlPosts = "SELECT COUNT(*) AS posts_this_week FROM posts WHERE created_at >= CURDATE() - INTERVAL 7 DAY";
$resultPosts = @$conn->query($sqlPosts);
if ($resultPosts && $resultPosts->num_rows > 0) {
    $row = $resultPosts->fetch_assoc();
    $postsThisWeek = $row['posts_this_week'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Lagos - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- Internal CSS --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
        }

        :root {
            --primary-color: #7f00ff; /* Purple for key elements */
            --primary-dark: #6a00d6;
            --light-bg: #ffffff;
            --main-bg: #f5f5f7; /* Off-white page background */
            --text-dark: #1c1c1c;
            --text-light: #666;
            --border-color: #e0e0e0;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --header-height: 60px;
            --font-regular: 500;
            --font-medium: 600;
            --font-bold: 700;
            --transition-normal: 0.3s ease;
            --hover-bg: rgba(127, 0, 255, 0.04);
            --font-primary: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--main-bg);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 20px;
        }

        .page-container {
            display: flex;
            width: 100%;
            max-width: 1300px;
            margin: 0 auto;
            background-color: var(--light-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            min-height: 95vh;
            overflow: hidden;
        }

        /* --- Header (Top Bar) --- */
        .header {
            width: 100%;
            height: var(--header-height);
            background-color: var(--light-bg);
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-nav {
            display: flex;
            gap: 20px;
            padding-left: 20px;
        }

        .header-nav a {
            text-decoration: none;
            color: var(--text-dark);
            padding: 10px 0;
            font-weight: 500;
            position: relative;
        }

        .header-nav .active-nav {
            color: var(--primary-color);
            font-weight: 600;
        }

        .header-nav .active-nav::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px 2px 0 0;
        }

        .header-icons i {
            font-size: 20px;
            margin-left: 15px;
            color: var(--text-dark);
            cursor: pointer;
            transition: color 0.2s;
        }

        .header-icons i:hover {
            color: var(--primary-color);
        }

        /* --- Sidebar (Left Panel) --- */
        .sidebar {
            width: 300px;
            padding: 90px 25px 25px;
            border-right: 1px solid var(--border-color);
            background-color: var(--light-bg);
            position: sticky;
            top: 0;
            height: 95vh;
            transition: var(--transition-normal);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.05);
        }

        .uni-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 15px 25px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
            background: linear-gradient(to bottom, var(--hover-bg), transparent);
            border-radius: 16px;
        }

        .uni-profile-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e0e0e0;
            margin: 0 auto 10px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid var(--primary-color);
        }

        .uni-profile-img i {
            font-size: 30px;
            color: var(--text-light);
        }

        .uni-details strong {
            display: block;
            font-size: 15px;
            margin-bottom: 8px;
            font-weight: var(--font-medium);
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .uni-details p {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
            text-align: left;
            padding-left: 20px;
            font-weight: var(--font-regular);
            letter-spacing: 0.2px;
        }
        
        .verified-icon {
            color: green;
            margin-right: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
            padding: 14px 18px;
            margin: 4px 0;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: var(--font-medium);
            position: relative;
            overflow: hidden;
            font-size: 15px;
            letter-spacing: 0.2px;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .nav-link.active-sidebar {
            background-color: rgba(127, 0, 255, 0.1);
            color: var(--primary-color);
            font-weight: var(--font-bold);
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.12);
        }

        .nav-link:hover {
            background-color: rgba(127, 0, 255, 0.08);
            color: var(--primary-color);
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(127, 0, 255, 0.08);
        }

        .nav-link:hover i {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        /* --- Main Content Area --- */
        .main-content {
            flex-grow: 1;
            padding: var(--header-height) 30px 30px 30px;
            background-color: var(--main-bg);
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

       .welcome-header {
            background-color: #f7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }

        .welcome-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-header p {
            font-size: 16px;
            color: var(--text-dark);
            opacity: 0.9;
        }

        /* Stats Cards Section */
        .stats-grid {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        .stat-card {
            background-color: var(--light-bg);
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            text-align: center;
            min-width: 200px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(127, 0, 255, 0.1);
            border-color: var(--primary-color);
        }

        .stat-card h3 {
            font-size: 15px;
            color: var(--text-light);
            margin-bottom: 8px;
            font-weight: var(--font-medium);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card strong {
            display: block;
            font-size: 38px;
            font-weight: var(--font-bold);
            color: var(--primary-color);
            letter-spacing: -1px;
            text-shadow: 0 1px 2px rgba(127, 0, 255, 0.1);
        }

        /* Main Dashboard Body */
        .dashboard-body {
            display: flex;
            gap: 25px;
        }

        .left-panel {
            flex: 2;
            min-width: 60%;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .right-panel {
            flex: 0 0 auto;
            min-width: 30%;
            max-height: 400px;
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) transparent;
        }

        .right-panel::-webkit-scrollbar {
            width: 6px;
        }

        .right-panel::-webkit-scrollbar-track {
            background: transparent;
        }

        .right-panel::-webkit-scrollbar-thumb {
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        /* Announcement/Post Section */
        .post-box {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .post-input {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            resize: none;
            font-size: 14px;
            height: 50px;
            transition: var(--transition-normal);
            outline: none;
        }

        .post-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(127, 0, 255, 0.1);
        }

        .post-input::placeholder {
            color: var(--text-light);
            opacity: 0.8;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .post-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-create {
            background-color: var(--primary-color);
            color: var(--light-bg);
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            background-color: #6a00c9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.2);
        }

        .btn-announce {
            background-color: var(--light-bg);
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .btn-announce:hover {
            background-color: #f0e6ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.1);
        }

        .image-placeholders {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .image-placeholder {
            width: 80px;
            height: 80px;
            background-color: var(--hover-bg);
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-light);
            font-size: 12px;
            cursor: pointer;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
        }

        .image-placeholder:hover {
            border-color: var(--primary-color);
            background-color: rgba(127, 0, 255, 0.08);
            transform: translateY(-2px);
        }

        .image-placeholder::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 24px;
            color: var(--primary-color);
            opacity: 0.5;
            transition: opacity var(--transition-normal);
        }

        .image-placeholder:hover::before {
            opacity: 1;
        }

        /* Communities Section */
        .communities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .community-card {
            background-color: var(--light-bg);
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            text-align: center;
            transition: all var(--transition-normal);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .community-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(127, 0, 255, 0.1);
            border-color: var(--primary-color);
        }

        .community-card .icon {
            width: 50px;
            height: 50px;
            background-color: #f0e6ff;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .community-card .icon i {
            font-size: 24px;
            color: var(--primary-color);
        }

        .community-card h4 {
            font-size: 17px;
            margin-bottom: 6px;
            font-weight: var(--font-medium);
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .community-card p {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 10px;
            font-weight: var(--font-regular);
        }

        /* Recent Activity (Right Panel) */
        .recent-activity h3 {
            font-size: 20px;
            font-weight: var(--font-bold);
            margin-bottom: 18px;
            color: var(--primary-color);
            letter-spacing: -0.3px;
        }

        .activity-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            margin: 0 -12px;
            transition: var(--transition-normal);
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.5;
            font-weight: var(--font-regular);
        }

        .activity-item:hover {
            background-color: var(--hover-bg);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item .time {
            color: var(--text-light);
            display: block;
            margin-top: 3px;
            font-size: 11px;
        }

        .activity-item strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Screen reader only class for accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>
<body>

    <div class="page-container">
        <aside class="sidebar">
            <div class="uni-info">
                <div class="uni-profile-img">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="uni-details">
                    <strong style="font-size: 16px;">University of Lagos (UNILAG)</strong>
                    <p><i class="fa-solid fa-circle-check verified-icon"></i> Verified Institution</p>
                    <p><i class="fa-solid fa-location-dot"></i> Lagos State, Nigeria</p>
                </div>
            </div>
            
            <nav class="side-nav" aria-label="Main navigation">
                <a href="institution_admindashboard.php" class="nav-link active-sidebar"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="institution_adminabout.php" class="nav-link"><i class="fa-solid fa-circle-info"></i> About</a>
                <a href="institution_admincommunity.php" class="nav-link"><i class="fa-solid fa-users"></i> Communities</a>
                <a href="institution_admin_faculty.php" class="nav-link"><i class="fa-solid fa-building"></i> Faculty</a>
            </nav>
        </aside>

        <div style="flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto;">
            <header class="header">
                <nav class="header-nav">
                    <a href="institution_admindashboard.php" class="active-nav">Dashboard</a>
                    <a href="institution_adminabout.php">About</a>
                    <a href="institution_admincommunity.php">Communities</a>
                    <a href="institution_admin_faculty.php">Faculty</a>
                </nav>
                <div class="header-icons">
                    <i class="fa-regular fa-bell"></i>
                    <i class="fa-solid fa-gear"></i>
                </div>
            </header>

            <main class="main-content">
                <div class="welcome-header">
                    <h1>Welcome, University of Lagos!</h1>
                    <p>Verified Academic Institution · Lagos, Nigeria</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Communities</h3>
                        <strong><?php echo $totalCommunities; ?></strong>
                    </div>
                    <div class="stat-card">
                        <h3>Active Students</h3>
                        <strong><?php echo $activeStudents; ?></strong>
                    </div>
                    <div class="stat-card">
                        <h3>Posts this Week</h3>
                        <strong><?php echo $postsThisWeek; ?></strong>
                    </div>
                </div>

                <div class="dashboard-body">
                    <div class="left-panel">
                        <div class="post-box">
                            <form action="" method="POST">
                                <label for="post_content" class="sr-only">Create community or announcement</label>
                                <textarea id="post_content" class="post-input" name="post_content" placeholder="create a new community or announce something to your students..." aria-label="Create community or announcement"></textarea>
                                <div class="post-actions">
                                    <button type="submit" name="action" value="create_community" class="btn-create"><i class="fa-solid fa-plus"></i> Create</button>
                                    <button type="submit" name="action" value="announce" class="btn-announce">Announce</button>
                                </div>
                            </form>
                        </div>

                        <div class="communities-grid">
                            <!-- <div class="community-card">
                                <div class="image-placeholder" style="width: 100%; height: 100px; margin: 10px 0;">image</div>
                                <h4>Biology 101</h4>
                                <p>87 Members</p>
                            </div> -->
                            <div class="community-card">
                                <div class="community-image" style="width: 100%; height: 120px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <i class="fa-solid fa-code" style="font-size: 40px; color: white;"></i>
                                </div>
                                <h4>CS Club</h4>
                                <p>12 Members</p>
                            </div>
                            <div class="community-card">
                                <div class="community-image" style="width: 100%; height: 120px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <i class="fa-solid fa-laptop-code" style="font-size: 40px; color: white;"></i>
                                </div>
                                <h4>Tech Community</h4>
                                <p>1 Member</p>
                            </div>
                            <div class="community-card">
                                <div class="community-image" style="width: 100%; height: 120px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <i class="fa-solid fa-microscope" style="font-size: 40px; color: white;"></i>
                                </div>
                                <h4>Science Lab</h4>
                                <p>87 Members</p>
                            </div>
                            <div class="community-card">
                                <div class="community-image" style="width: 100%; height: 120px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                    <i class="fa-solid fa-calculator" style="font-size: 40px; color: white;"></i>
                                </div>
                                <h4>Math Tutoring</h4>
                                <p>87 Members</p>
                            </div>
                        </div>
                    </div>

                    <div class="right-panel recent-activity">
                        <h3>Recent Activity</h3>
                        <div class="activity-item">
                            <strong>Biology 101</strong>: 3 new posts today
                            <span class="time">1 hour ago</span>
                        </div>
                        <div class="activity-item">
                            <strong>CS Club</strong>: 12 new members joined
                            <span class="time">4 hours ago</span>
                        </div>
                        <div class="activity-item">
                            <strong>CS Club</strong>: 1 post flagged for review
                            <span class="time">Yesterday</span>
                        </div>
                        <div class="activity-item">
                            <strong>Biology 101</strong>: Member requested to join
                            <span class="time">2 days ago</span>
                        </div>
                        <div class="activity-item">
                            <strong>Math Tutoring</strong>: New resource uploaded
                            <span class="time">3 days ago</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Dashboard loaded.');

            const navLinks = document.querySelectorAll('.nav-link');
            const headerNavLinks = document.querySelectorAll('.header-nav a');
            
            const handleNavClick = (event) => {
                const targetLink = event.currentTarget;
                navLinks.forEach(link => link.classList.remove('active-sidebar'));
                headerNavLinks.forEach(link => link.classList.remove('active-nav'));

                if (targetLink.classList.contains('nav-link')) {
                    targetLink.classList.add('active-sidebar');
                    const linkText = targetLink.textContent.trim();
                    headerNavLinks.forEach(hLink => {
                        if (hLink.textContent.trim() === linkText) {
                            hLink.classList.add('active-nav');
                        }
                    });
                } else if (targetLink.parentElement.classList.contains('header-nav')) {
                     targetLink.classList.add('active-nav');
                    const linkText = targetLink.textContent.trim();
                    navLinks.forEach(sLink => {
                        if (sLink.textContent.trim().includes(linkText)) {
                            sLink.classList.add('active-sidebar');
                        }
                    });
                }
            };

            navLinks.forEach(link => link.addEventListener('click', handleNavClick));
            headerNavLinks.forEach(link => link.addEventListener('click', handleNavClick));

            document.querySelectorAll('.image-placeholder').forEach(placeholder => {
                placeholder.addEventListener('click', (e) => {
                    e.preventDefault();
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = (e) => {
                        const file = e.target.files[0];
                        if (file) {
                            alert(`Selected image: ${file.name}`);
                        }
                    };
                    input.click();
                });
            });
        });
    </script>
</body>
</html>
