<?php
session_start();
include '../inc/config.php';
$institution_id = $_SESSION['institution_id'] ?? 1;

// Counts
$communityCount = $conn->query("SELECT COUNT(*) AS total FROM student_communities WHERE status=1")->fetch_assoc()['total'] ?? 0;
$studentCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;
$postCount = $conn->query("SELECT COUNT(*) AS total FROM posts WHERE WEEK(created_at)=WEEK(NOW())")->fetch_assoc()['total'] ?? 0;

// Recent communities
$recentCommunities = [];
$result = $conn->query("SELECT id,name,description,members_count FROM student_communities WHERE status=1 ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) $recentCommunities[] = $row;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNILAG Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- Your existing CSS from before --- */
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
            --primary-color: #7f00ff;
            --primary-dark: #6a00d6;
            --light-bg: #ffffff;
            --main-bg: #f5f5f7;
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
                    <strong>University of Lagos (UNILAG)</strong>
                    <p><i class="fa-solid fa-circle-check verified-icon"></i> Verified Institution</p>
                    <p><i class="fa-solid fa-location-dot"></i> Lagos, Nigeria</p>
                </div>
            </div>
            <nav class="side-nav">
                <a href="institution_admindashboard.php" class="nav-link active-sidebar"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="institution_adminabout.php" class="nav-link"><i class="fa-solid fa-circle-info"></i> About</a>
                <a href="institution_admincommunity.php" class="nav-link"><i class="fa-solid fa-users"></i> Communities</a>
                <a href="institution_admin_faculty.php" class="nav-link"><i class="fa-solid fa-building"></i> Faculty</a>
            </nav>
        </aside>

        <div style="flex-grow:1; display:flex; flex-direction:column; overflow-y:auto;">
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
                        <strong id="communityCount"><?php echo $communityCount; ?></strong>
                    </div>
                    <div class="stat-card">
                        <h3>Active Students</h3>
                        <strong><?php echo $studentCount; ?></strong>
                    </div>
                    <div class="stat-card">
                        <h3>Posts this Week</h3>
                        <strong id="postCount"><?php echo $postCount; ?></strong>
                    </div>
                </div>

                <div class="dashboard-body">
                    <div class="left-panel">
                        <div class="post-box">
                            <form id="dashboardForm">
                                <input type="text" id="community_name" class="post-input" placeholder="Community Name">
                                <textarea id="community_description" class="post-input" placeholder="Community Description"></textarea>
                                <textarea id="post_content" class="post-input" placeholder="Announcement Content (optional)"></textarea>
                                <div class="post-actions">
                                    <button type="button" class="btn-create" data-action="create_community"><i class="fa-solid fa-plus"></i> Create Community</button>
                                    <button type="button" class="btn-announce" data-action="announce">Announce</button>
                                </div>
                            </form>
                            <div id="formMessage" style="margin-top:10px;color:green;"></div>
                        </div>

                        <div class="communities-grid" id="communitiesGrid">
                            <?php foreach ($recentCommunities as $c): ?>
                                <div class="community-card">
                                    <div class="community-image" style="width:100%; height:120px; background:#f0e6ff; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                                        <i class="fa-solid fa-users" style="font-size:40px; color:#793DDC;"></i>
                                    </div>
                                    <h4><?php echo htmlspecialchars($c['name']); ?></h4>
                                    <p><?php echo $c['members_count']; ?> Members</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="right-panel recent-activity" id="activityFeed">
                        <h3>Recent Activity</h3>
                        <p>No recent activity yet.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(function() {
            function refreshDashboard() {
                $.get(location.href, function(html) {
                    let newCommunities = $(html).find('#communitiesGrid').html();
                    let newCommunityCount = $(html).find('#communityCount').text();
                    let newPostCount = $(html).find('#postCount').text();
                    $('#communitiesGrid').html(newCommunities);
                    $('#communityCount').text(newCommunityCount);
                    $('#postCount').text(newPostCount);
                });
            }

            $('.btn-create').click(function() {
                let name = $('#community_name').val().trim();
                let description = $('#community_description').val().trim();
                if (!name || !description) {
                    $('#formMessage').text('Name and Description are required').css('color', 'red');
                    return;
                }

                $.post('dashboard_actions.php', {
                    action: 'create_community',
                    name: name,
                    description: description,
                    icon: '📚'
                }, function(res) {
                    $('#formMessage').text(res.message).css('color', res.success ? 'green' : 'red');
                    if (res.success) {
                        $('#community_name, #community_description').val('');
                        refreshDashboard();
                    }
                }, 'json');
            });

            $('.btn-announce').click(function() {
                let content = $('#post_content').val().trim();
                if (!content) {
                    $('#formMessage').text('Announcement content is required').css('color', 'red');
                    return;
                }

                $.post('dashboard_actions.php', {
                    action: 'announce',
                    content: content,
                    community_id: null
                }, function(res) {
                    $('#formMessage').text(res.message).css('color', res.success ? 'green' : 'red');
                    if (res.success) $('#post_content').val('');
                    refreshDashboard();
                }, 'json');
            });
        });
    </script>
</body>

</html>