<?php
session_start();
include '../inc/config.php';

// For now, we'll use a placeholder institution_id
$institution_id = $_SESSION['institution_id'] ?? 1;

// Fetch communities from database
$communities = [];
$totalCommunities = 0;

$sql = "SELECT 
            c.id,
            c.name,
            c.description,
            c.created_at,
            COUNT(DISTINCT cm.user_id) as member_count,
            COUNT(DISTINCT p.id) as post_count
        FROM student_communities c
        LEFT JOIN community_members cm ON c.id = cm.community_id
        LEFT JOIN posts p ON c.id = p.community_id
        GROUP BY c.id
        ORDER BY c.created_at DESC";

$result = @$conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $communities[] = $row;
    }
    $totalCommunities = count($communities);
}

// If no communities found, use sample data
if (empty($communities)) {
    $communities = [
        [
            'id' => 1,
            'name' => 'Biology 101',
            'description' => 'Introduction to biological sciences for first-year students...',
            'icon' => '🧪',
            'member_count' => 87,
            'post_count' => 50,
            'status' => 'active'
        ],
        [
            'id' => 2,
            'name' => 'Art & Design Studio',
            'description' => 'Creative space for art and design students to share work...',
            'icon' => '🎨',
            'member_count' => 87,
            'post_count' => 15,
            'status' => 'active'
        ],
        [
            'id' => 3,
            'name' => 'CS Club',
            'description' => 'Computer Science student organization focused on coding...',
            'icon' => '💻',
            'member_count' => 87,
            'post_count' => 47,
            'status' => 'active'
        ]
    ];
    $totalCommunities = count($communities);
}

// Helper function to get icon colors
function getIconColor($index) {
    $colors = [
        ['bg' => '#f0e6ff', 'color' => '#793DDC'],
        ['bg' => '#fff3e0', 'color' => '#ff9800'],
        ['bg' => '#e3f2fd', 'color' => '#2196f3'],
        ['bg' => '#e8f5e9', 'color' => '#4caf50'],
        ['bg' => '#fce4ec', 'color' => '#e91e63']
    ];
    return $colors[$index % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution - Communities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #793DDC;
            --primary-dark: #6a00d6;
            --background-color: #F8F7FF;
            --card-background: #FFFFFF;
            --text-color: #333333;
            --light-text: #666666;
            --border-color: #EEEEEE;
            --font-regular: 500;
            --font-medium: 600;
            --font-bold: 700;
            --header-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --hover-bg: rgba(121, 61, 220, 0.08);
            --transition-normal: 0.3s ease;
            --font-primary: 'Segoe UI', system-ui, -apple-system, sans-serif;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: var(--font-primary);
            margin: 0;
            padding: 20px;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1400px;
            display: flex;
            background-color: var(--card-background);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            min-height: 100vh;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--card-background);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            border: none;
            background: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-normal);
            color: var(--text-color);
        }

        .header-icon:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .header-icon i {
            font-size: 20px;
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
            display: flex;
            flex-direction: column;
            gap: 24px;
            overflow-y: auto;
        }

        .institution-info {
            text-align: left;
            padding: 16px;
            background: var(--background-color);
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .institution-info h2 {
            font-size: 18px;
            color: var(--text-color);
            margin: 0 0 8px 0;
            font-weight: var(--font-bold);
            line-height: 1.4;
        }

        .institution-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 6px;
            font-size: 12px;
            font-weight: var(--font-medium);
            margin: 8px 0;
        }

        .institution-location {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--light-text);
            font-size: 13px;
            margin-top: 8px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 4px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: var(--font-medium);
        }

        .nav-link i {
            font-size: 18px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-link:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .nav-link.active {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            font-weight: var(--font-bold);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 4px;
            background-color: var(--primary-color);
            border-radius: 0 4px 4px 0;
        }

        .main-content {
            flex-grow: 1;
            padding: 0 30px 30px 30px;
        }
        
        .dashboard-tabs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
            padding: 10px 0;
        }

        .tab-navigation {
            display: flex;
            gap: 25px;
        }

        .tab-button {
            padding: 10px 0;
            cursor: pointer;
            font-weight: 600;
            color: var(--light-text);
            border-bottom: 3px solid transparent;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .page-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 32px 0;
            padding: 0 4px;
        }

        .page-title {
            font-size: 22px;
            color: var(--text-color);
            margin: 0;
            font-weight: var(--font-bold);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .community-count {
            font-size: 14px;
            color: var(--light-text);
            background: var(--background-color);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: var(--font-medium);
        }
        
        .create-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: var(--font-medium);
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.2);
            text-decoration: none;
        }

        .create-btn i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .create-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(121, 61, 220, 0.3);
        }

        .communities-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            background-color: transparent;
            margin: 0 auto;
        }

        .communities-table tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .communities-table tbody tr {
            background-color: var(--card-background);
            box-shadow: var(--card-shadow);
            margin-bottom: 8px;
        }

        .communities-table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .communities-table tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .communities-table tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .communities-table th, .communities-table td {
            padding: 16px;
            text-align: left;
        }

        .communities-table th {
            color: var(--light-text);
            font-weight: var(--font-medium);
            font-size: 13px;
            padding: 16px;
            border-bottom: 2px solid var(--border-color);
        }

        .communities-table th.sortable {
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .communities-table th.sortable:hover {
            color: var(--primary-color);
        }

        .communities-table th.sortable i {
            margin-left: 4px;
            font-size: 12px;
            opacity: 0.6;
        }

        .communities-table td {
            background-color: var(--card-background);
            font-size: 14px;
            color: var(--text-color);
        }

        .community-name {
            display: flex;
            align-items: center;
        }

        .community-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            margin-right: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
        }
        
        .community-description {
            color: var(--light-text);
            font-size: 13px;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-button {
            padding: 6px 12px;
            margin-right: 6px;
            border: 1px solid var(--border-color);
            background: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-color);
            height: 32px;
        }

        .action-button:last-child {
            margin-right: 0;
        }

        .action-button i {
            font-size: 14px;
            opacity: 0.8;
        }

        .action-button:hover {
            background-color: var(--hover-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .action-button.delete-btn:hover {
            background-color: #ffebee;
            border-color: #f44336;
            color: #f44336;
        }
        
        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background-color: #e8f5e9;
            color: #4caf50;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--light-text);
        }

        .empty-state i {
            font-size: 64px;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background: var(--card-background);
            border-radius: 12px;
            padding: 0;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-50px);
            transition: transform 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: var(--text-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--light-text);
            padding: 5px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .modal-close:hover {
            background: var(--hover-bg);
            color: var(--primary-color);
        }

        .modal-body {
            padding: 20px;
        }

        .community-detail {
            padding: 20px;
        }

        .community-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .community-stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--light-text);
        }

        .statistics-detail {
            padding: 20px;
        }

        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: var(--background-color);
            padding: 15px;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .stat-trend {
            font-size: 14px;
            color: #4caf50;
        }

        .edit-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(121, 61, 220, 0.1);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .cancel-btn,
        .save-btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .cancel-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-color);
        }

        .save-btn {
            background: var(--primary-color);
            border: none;
            color: white;
        }

        .cancel-btn:hover {
            background: var(--hover-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .save-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="institution-info">
                <h2>University of Lagos (UNILAG)</h2>
                <div class="institution-badge">
                    <i class="fa-solid fa-check-circle"></i>
                    Verified Institution
                </div>
                <div class="institution-location">
                    <i class="fa-solid fa-location-dot"></i>
                    Lagos State, Nigeria
                </div>
            </div>

            <nav>
                <a href="institution_admindashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="institution_adminabout.php" class="nav-link"><i class="fa-solid fa-circle-info"></i> About</a>
                <a href="institution_admincommunity.php" class="nav-link active"><i class="fa-solid fa-users"></i> Communities</a>
                <a href="institution_admin_faculty.php" class="nav-link"><i class="fa-solid fa-building"></i> Faculty</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header" style="padding: 15px 0; border-bottom: none;">
                <div style="font-size: 24px; font-weight: bold; color: var(--primary-color);">My Communities</div>
            </div>
            
            <div class="dashboard-tabs">
                <div class="tab-navigation">
                    <a href="institution_admindashboard.php" class="tab-button">Dashboard</a>
                    <a href="institution_adminabout.php" class="tab-button">About</a>
                    <a href="institution_admincommunity.php" class="tab-button active">Communities</a>
                    <a href="institution_admin_faculty.php" class="tab-button">Faculty</a>
                </div>
                <div class="header-actions">
                    <button class="header-icon" title="Notifications"><i class="fa-regular fa-bell"></i></button>
                    <button class="header-icon" title="Settings"><i class="fa-solid fa-gear"></i></button>
                </div>
            </div>
            
            <div class="page-actions">
                <div class="page-title">
                    Managed Communities
                    <span class="community-count"><?php echo $totalCommunities; ?> communities</span>
                </div>
                <a href="../create_community.php" class="create-btn" role="button">
                    <i class="fa-solid fa-plus"></i>
                    Create Community
                </a>
            </div>

            <?php if (empty($communities)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-users"></i>
                    <h3>No Communities Yet</h3>
                    <p>Create your first community to get started!</p>
                </div>
            <?php else: ?>
                <table class="communities-table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">Community Name <i class="fa-solid fa-sort"></i></th>
                            <th class="sortable" data-sort="members">Members <i class="fa-solid fa-sort"></i></th>
                            <th class="sortable" data-sort="posts">Posts <i class="fa-solid fa-sort"></i></th>
                            <th class="sortable" data-sort="status">Status <i class="fa-solid fa-sort"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($communities as $index => $community): 
                            $colors = getIconColor($index);
                        ?>
                        <tr data-community-id="<?php echo $community['id']; ?>">
                            <td>
                                <div class="community-name">
                                    <div class="community-icon" style="background-color: <?php echo $colors['bg']; ?>; color: <?php echo $colors['color']; ?>;">
                                        <?php echo htmlspecialchars($community['icon'] ?? '📚'); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($community['name']); ?></strong>
                                        <div class="community-description"><?php echo htmlspecialchars($community['description']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $community['member_count']; ?></td>
                            <td><?php echo $community['post_count']; ?></td>
                            <td><span class="status-tag status-active"><?php echo ucfirst($community['status']); ?></span></td>
                            <td>
                                <button class="action-button" onclick="viewCommunity(this)"><i class="fa-solid fa-eye"></i>View</button>
                                <button class="action-button" onclick="showStatistics(this)"><i class="fa-solid fa-chart-line"></i>Statistics</button>
                                <button class="action-button" onclick="editCommunity(this)"><i class="fa-solid fa-pen"></i>Edit</button>
                                <button class="action-button delete-btn" onclick="deleteCommunity(this)"><i class="fa-solid fa-trash"></i>Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Sort functionality
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.sort;
                const tbody = document.querySelector('.communities-table tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const isAsc = !header.classList.contains('sort-asc');

                document.querySelectorAll('.sortable').forEach(h => 
                    h.classList.remove('sort-asc', 'sort-desc'));
                header.classList.add(isAsc ? 'sort-asc' : 'sort-desc');

                rows.sort((a, b) => {
                    let aVal, bVal;
                    switch(column) {
                        case 'name':
                            aVal = a.querySelector('strong').textContent;
                            bVal = b.querySelector('strong').textContent;
                            break;
                        case 'members':
                        case 'posts':
                            aVal = parseInt(a.children[column === 'members' ? 1 : 2].textContent);
                            bVal = parseInt(b.children[column === 'members' ? 1 : 2].textContent);
                            break;
                        case 'status':
                            aVal = a.querySelector('.status-tag').textContent;
                            bVal = b.querySelector('.status-tag').textContent;
                            break;
                    }
                    return isAsc ? aVal > bVal ? 1 : -1 : aVal < bVal ? 1 : -1;
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });

        // Modal functionality
        function showModal(title, content) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>${title}</h2>
                        <button class="modal-close" onclick="closeModal(this)">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                modal.querySelector('.modal-content').style.transform = 'translateY(0)';
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal.querySelector('.modal-close'));
            });
        }

        function closeModal(button) {
            const modal = button.closest('.modal');
            modal.style.opacity = '0';
            modal.querySelector('.modal-content').style.transform = 'translateY(-50px)';
            setTimeout(() => modal.remove(), 300);
        }

        function viewCommunity(button) {
            const communityRow = button.closest('tr');
            const communityName = communityRow.querySelector('strong').textContent;
            const description = communityRow.querySelector('.community-description').textContent;
            const members = communityRow.children[1].textContent;
            const posts = communityRow.children[2].textContent;
            
            const content = `
                <div class="community-detail">
                    <div class="community-header">
                        ${communityRow.querySelector('.community-icon').outerHTML}
                        <h3>${communityName}</h3>
                    </div>
                    <p>${description}</p>
                    <div class="community-stats">
                        <div class="stat-item">
                            <i class="fa-solid fa-users"></i>
                            <span>${members} Members</span>
                        </div>
                        <div class="stat-item">
                            <i class="fa-solid fa-comment"></i>
                            <span>${posts} Posts</span>
                        </div>
                    </div>
                </div>
            `;
            showModal('Community Details', content);
        }

        function showStatistics(button) {
            const communityRow = button.closest('tr');
            const communityName = communityRow.querySelector('strong').textContent;
            const members = parseInt(communityRow.children[1].textContent);
            const posts = parseInt(communityRow.children[2].textContent);
            
            const content = `
                <div class="statistics-detail">
                    <h3>${communityName} Statistics</h3>
                    <div class="statistics-grid">
                        <div class="stat-card">
                            <h4>Total Members</h4>
                            <div class="stat-value">${members}</div>
                            <div class="stat-trend positive">↑ 12% this month</div>
                        </div>
                        <div class="stat-card">
                            <h4>Total Posts</h4>
                            <div class="stat-value">${posts}</div>
                            <div class="stat-trend positive">↑ 8% this month</div>
                        </div>
                    </div>
                </div>
            `;
            showModal('Community Statistics', content);
        }

        function editCommunity(button) {
            const communityRow = button.closest('tr');
            const communityId = communityRow.dataset.communityId;
            const communityName = communityRow.querySelector('strong').textContent;
            const description = communityRow.querySelector('.community-description').textContent;
            
            const content = `
                <form class="edit-form" onsubmit="event.preventDefault(); saveEdit(this, ${communityId});">
                    <div class="form-group">
                        <label>Community Name</label>
                        <input type="text" name="name" value="${communityName}" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" required>${description}</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="closeModal(this.closest('.modal').querySelector('.modal-close'))">Cancel</button>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </form>
            `;
            showModal('Edit Community', content);
        }

        function deleteCommunity(button) {
            const communityRow = button.closest('tr');
            const communityId = communityRow.dataset.communityId;
            const communityName = communityRow.querySelector('strong').textContent;
            
            if (confirm(`Are you sure you want to delete "${communityName}"? This action cannot be undone.`)) {
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `action=delete_community&community_id=${communityId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        communityRow.remove();
                        // Update count
                        const countEl = document.querySelector('.community-count');
                        const currentCount = parseInt(countEl.textContent);
                        countEl.textContent = `${currentCount - 1} communities`;
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred', 'error');
                    console.error('Error:', error);
                });
            }
        }

        function saveEdit(form, communityId) {
            const formData = new FormData(form);
            formData.append('action', 'update_community');
            formData.append('community_id', communityId);
            
            fetch('', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    closeModal(form.closest('.modal').querySelector('.modal-close'));
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('An error occurred', 'error');
                console.error('Error:', error);
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <i class="fa-solid fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);

            Object.assign(toast.style, {
                position: 'fixed',
                bottom: '20px',
                right: '20px',
                backgroundColor: type === 'success' ? 'var(--primary-color)' : '#f44336',
                color: 'white',
                padding: '12px 24px',
                borderRadius: '8px',
                boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                zIndex: '1000',
                transform: 'translateY(100px)',
                opacity: '0',
                transition: 'all 0.3s ease'
            });

            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });

            setTimeout(() => {
                toast.style.transform = 'translateY(100px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
