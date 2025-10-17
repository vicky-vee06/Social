<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../inc/config.php';

// Fetch institution details
$institution_id = $_SESSION['institution_id'] ?? 1;
$stmt = $conn->prepare("SELECT name, location FROM institution_details WHERE id = ?");
$stmt->bind_param("i", $institution_id);
$stmt->execute();
$result = $stmt->get_result();
$institution = $result->fetch_assoc() ?: [
    'name' => 'University of Lagos (UNILAG)',
    'location' => 'Akoka, Yaba, Lagos State, Nigeria'
];

// Fetch communities
$communities = [];
$totalCommunities = 0;
$sql = "
    SELECT 
        c.id,
        c.name,
        c.description,
        c.icon,
        c.status,
        c.created_at,
        COUNT(DISTINCT cm.user_id) as member_count,
        COUNT(DISTINCT p.id) as post_count
    FROM student_communities c
    LEFT JOIN community_members cm ON c.id = cm.community_id
    LEFT JOIN posts p ON c.id = p.community_id
    WHERE c.institution_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $institution_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $communities[] = $row;
}
$totalCommunities = count($communities);

// Fallback sample data
if (empty($communities)) {
    $communities = [
        [
            'id' => 1,
            'name' => 'Biology 101',
            'description' => 'Introduction to biological sciences for first-year students.',
            'icon' => '🧪',
            'member_count' => 87,
            'post_count' => 50,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'name' => 'Art & Design Studio',
            'description' => 'Creative space for art and design students to share work.',
            'icon' => '🎨',
            'member_count' => 87,
            'post_count' => 15,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'name' => 'CS Club',
            'description' => 'Computer Science student organization focused on coding.',
            'icon' => '💻',
            'member_count' => 87,
            'post_count' => 47,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
    $totalCommunities = count($communities);
}

// Helper function for icon colors
function getIconColor($index)
{
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
    <title><?php echo htmlspecialchars($institution['name']); ?> - Communities</title>
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="../css/faculty-directory.css">

</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="institution-info">
                <h2><?php echo htmlspecialchars($institution['name']); ?></h2>
                <div class="institution-badge">
                    <i class="fa-solid fa-check-circle"></i>
                    Verified Institution
                </div>
                <div class="institution-location">
                    <i class="fa-solid fa-location-dot"></i>
                    <?php echo htmlspecialchars($institution['location']); ?>
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
            <div class="header">
                <div style="font-size: 24px; font-weight: bold; color: var(--primary-color);">My Communities</div>
                <div class="header-actions">
                    <button class="header-icon" title="Notifications"><i class="fa-regular fa-bell"></i></button>
                    <button class="header-icon" title="Settings"><i class="fa-solid fa-gear"></i></button>
                </div>
            </div>
            <div class="dashboard-tabs">
                <div class="tab-navigation">
                    <a href="institution_admindashboard.php" class="tab-button">Dashboard</a>
                    <a href="institution_adminabout.php" class="tab-button">About</a>
                    <a href="institution_admincommunity.php" class="tab-button active">Communities</a>
                    <a href="institution_admin_faculty.php" class="tab-button">Faculty</a>
                </div>
            </div>
            <div class="page-actions">
                <div class="page-title">
                    Managed Communities
                    <span class="community-count"><?php echo $totalCommunities; ?> communities</span>
                </div>
                <button class="create-btn" onclick="openCreateModal()">
                    <i class="fa-solid fa-plus"></i>
                    Create Community
                </button>
            </div>
            <div class="search-bar">
                <input type="text" class="search-input" id="searchInput" placeholder="Search communities...">
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
                    <tbody id="communityTableBody">
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
                                <td><?php echo number_format($community['member_count']); ?></td>
                                <td><?php echo number_format($community['post_count']); ?></td>
                                <td><span class="status-tag status-<?php echo $community['status']; ?>"><?php echo ucfirst($community['status']); ?></span></td>
                                <td>
                                    <button class="action-button" onclick="viewCommunity(<?php echo $community['id']; ?>)"><i class="fa-solid fa-eye"></i> View</button>
                                    <button class="action-button" onclick="showStatistics(<?php echo $community['id']; ?>)"><i class="fa-solid fa-chart-line"></i> Statistics</button>
                                    <button class="action-button" onclick="editCommunity(<?php echo $community['id']; ?>)"><i class="fa-solid fa-pen"></i> Edit</button>
                                    <button class="action-button delete-btn" onclick="deleteCommunity(<?php echo $community['id']; ?>, '<?php echo htmlspecialchars($community['name']); ?>')"><i class="fa-solid fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Community Modal -->
    <div class="modal-overlay" id="createCommunityModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Create Community</div>
                <button class="modal-close" onclick="closeModal('createCommunityModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="createName">Community Name</label>
                    <input type="text" class="form-control" id="createName" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="createDescription">Description</label>
                    <textarea class="form-control" id="createDescription" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="createIcon">Icon (Emoji)</label>
                    <input type="text" class="form-control" id="createIcon" placeholder="e.g., 📚" maxlength="4">
                </div>
                <div class="form-group">
                    <label for="createStatus">Status</label>
                    <select class="form-control" id="createStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal('createCommunityModal')">Cancel</button>
                <button class="btn-primary" onclick="createCommunity()">Create</button>
            </div>
        </div>
    </div>

    <!-- Edit Community Modal -->
    <div class="modal-overlay" id="editCommunityModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Edit Community</div>
                <button class="modal-close" onclick="closeModal('editCommunityModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="editName">Community Name</label>
                    <input type="text" class="form-control" id="editName" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="editDescription">Description</label>
                    <textarea class="form-control" id="editDescription" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="editIcon">Icon (Emoji)</label>
                    <input type="text" class="form-control" id="editIcon" placeholder="e.g., 📚" maxlength="4">
                </div>
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select class="form-control" id="editStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <input type="hidden" id="editCommunityId">
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal('editCommunityModal')">Cancel</button>
                <button class="btn-primary" onclick="saveEditCommunity()">Save</button>
            </div>
        </div>
    </div>

    <!-- View Community Modal -->
    <div class="modal-overlay" id="viewCommunityModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Community Details</div>
                <button class="modal-close" onclick="closeModal('viewCommunityModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body" id="viewCommunityContent"></div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div class="modal-overlay" id="statisticsModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Community Statistics</div>
                <button class="modal-close" onclick="closeModal('statisticsModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body" id="statisticsContent"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Communities page loaded.');

            // Navigation sync
            const navLinks = document.querySelectorAll('.nav-link');
            const tabButtons = document.querySelectorAll('.tab-button');
            const handleNavClick = (event) => {
                navLinks.forEach(link => link.classList.remove('active'));
                tabButtons.forEach(btn => btn.classList.remove('active'));
                const targetLink = event.currentTarget;
                if (targetLink.classList.contains('nav-link')) {
                    targetLink.classList.add('active');
                    const linkText = targetLink.textContent.trim();
                    tabButtons.forEach(btn => {
                        if (btn.textContent.trim() === linkText) {
                            btn.classList.add('active');
                        }
                    });
                } else if (targetLink.classList.contains('tab-button')) {
                    targetLink.classList.add('active');
                    const linkText = targetLink.textContent.trim();
                    navLinks.forEach(link => {
                        if (link.textContent.trim().includes(linkText)) {
                            link.classList.add('active');
                        }
                    });
                }
            };
            navLinks.forEach(link => link.addEventListener('click', handleNavClick));
            tabButtons.forEach(btn => btn.addEventListener('click', handleNavClick));

            // Sorting
            document.querySelectorAll('.sortable').forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.dataset.sort;
                    const tbody = document.querySelector('#communityTableBody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const isAsc = !header.classList.contains('sort-asc');

                    document.querySelectorAll('.sortable').forEach(h =>
                        h.classList.remove('sort-asc', 'sort-desc'));
                    header.classList.add(isAsc ? 'sort-asc' : 'sort-desc');

                    rows.sort((a, b) => {
                        let aVal, bVal;
                        switch (column) {
                            case 'name':
                                aVal = a.querySelector('strong').textContent.toLowerCase();
                                bVal = b.querySelector('strong').textContent.toLowerCase();
                                break;
                            case 'members':
                            case 'posts':
                                aVal = parseInt(a.children[column === 'members' ? 1 : 2].textContent.replace(/,/g, ''));
                                bVal = parseInt(b.children[column === 'members' ? 1 : 2].textContent.replace(/,/g, ''));
                                break;
                            case 'status':
                                aVal = a.querySelector('.status-tag').textContent.toLowerCase();
                                bVal = b.querySelector('.status-tag').textContent.toLowerCase();
                                break;
                        }
                        return isAsc ? aVal > bVal ? 1 : -1 : aVal < bVal ? 1 : -1;
                    });
                    rows.forEach(row => tbody.appendChild(row));
                });
            });

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', () => {
                const query = document.getElementById('searchInput').value.toLowerCase();
                const rows = document.querySelectorAll('#communityTableBody tr');
                rows.forEach(row => {
                    const name = row.querySelector('strong').textContent.toLowerCase();
                    const description = row.querySelector('.community-description').textContent.toLowerCase();
                    row.style.display = name.includes(query) || description.includes(query) ? '' : 'none';
                });
            });
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showToast(message, isSuccess = true) {
            const toast = document.createElement('div');
            toast.className = `toast ${isSuccess ? 'success' : 'error'}`;
            toast.innerHTML = `<i class="fa-solid ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function validateEmoji(input) {
            const emojiRegex = /^[\p{Emoji}\p{Emoji_Presentation}\p{Emoji_Modifier_Base}\p{Emoji_Component}]{1,4}$/u;
            return emojiRegex.test(input) || input === '';
        }

        function createCommunity() {
            const name = document.getElementById('createName').value.trim();
            const description = document.getElementById('createDescription').value.trim();
            const icon = document.getElementById('createIcon').value.trim() || '📚';
            const status = document.getElementById('createStatus').value;

            if (!name || !description) {
                showToast('Name and description are required', false);
                return;
            }
            if (icon && !validateEmoji(icon)) {
                showToast('Invalid emoji icon', false);
                return;
            }

            fetch('community_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=create_community&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}&icon=${encodeURIComponent(icon)}&status=${encodeURIComponent(status)}`
                })
                .then(response => response.text().then(text => ({
                    response,
                    text
                })))
                .then(({
                    response,
                    text
                }) => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    if (data.success) {
                        closeModal('createCommunityModal');
                        showToast(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('Network error: ' + error.message, false);
                });
        }

        function viewCommunity(communityId) {
            const row = document.querySelector(`tr[data-community-id="${communityId}"]`);
            const icon = row.querySelector('.community-icon').outerHTML;
            const name = row.querySelector('strong').textContent;
            const description = row.querySelector('.community-description').textContent;
            const members = row.children[1].textContent;
            const posts = row.children[2].textContent;

            document.getElementById('viewCommunityContent').innerHTML = `
                <div class="community-detail">
                    <div class="community-header">
                        ${icon}
                        <h3>${name}</h3>
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
            openModal('viewCommunityModal');
        }

        function showStatistics(communityId) {
            fetch('community_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_community_stats&community_id=${communityId}`
                })
                .then(response => response.text().then(text => ({
                    response,
                    text
                })))
                .then(({
                    response,
                    text
                }) => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    if (data.success) {
                        const {
                            member_count,
                            post_count,
                            new_members,
                            new_posts
                        } = data.data;
                        const memberTrend = new_members > 0 ? `↑ ${Math.round((new_members / (member_count || 1)) * 100)}% this month` : 'No change';
                        const postTrend = new_posts > 0 ? `↑ ${Math.round((new_posts / (post_count || 1)) * 100)}% this month` : 'No change';
                        document.getElementById('statisticsContent').innerHTML = `
                        <div class="statistics-detail">
                            <h3>${document.querySelector(`tr[data-community-id="${communityId}"] strong`).textContent} Statistics</h3>
                            <div class="statistics-grid">
                                <div class="stat-card">
                                    <h4>Total Members</h4>
                                    <div class="stat-value">${member_count}</div>
                                    <div class="stat-trend ${new_members > 0 ? '' : 'negative'}">${memberTrend}</div>
                                </div>
                                <div class="stat-card">
                                    <h4>Total Posts</h4>
                                    <div class="stat-value">${post_count}</div>
                                    <div class="stat-trend ${new_posts > 0 ? '' : 'negative'}">${postTrend}</div>
                                </div>
                            </div>
                        </div>
                    `;
                        openModal('statisticsModal');
                    } else {
                        showToast(data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('Network error: ' + error.message, false);
                });
        }

        function editCommunity(communityId) {
            const row = document.querySelector(`tr[data-community-id="${communityId}"]`);
            const name = row.querySelector('strong').textContent;
            const description = row.querySelector('.community-description').textContent;
            const icon = row.querySelector('.community-icon').textContent.trim();
            const status = row.querySelector('.status-tag').textContent.toLowerCase();

            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editIcon').value = icon;
            document.getElementById('editStatus').value = status;
            document.getElementById('editCommunityId').value = communityId;
            openModal('editCommunityModal');
        }

        function saveEditCommunity() {
            const communityId = document.getElementById('editCommunityId').value;
            const name = document.getElementById('editName').value.trim();
            const description = document.getElementById('editDescription').value.trim();
            const icon = document.getElementById('editIcon').value.trim() || '📚';
            const status = document.getElementById('editStatus').value;

            if (!name || !description) {
                showToast('Name and description are required', false);
                return;
            }
            if (icon && !validateEmoji(icon)) {
                showToast('Invalid emoji icon', false);
                return;
            }

            fetch('community_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_community&community_id=${communityId}&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}&icon=${encodeURIComponent(icon)}&status=${encodeURIComponent(status)}`
                })
                .then(response => response.text().then(text => ({
                    response,
                    text
                })))
                .then(({
                    response,
                    text
                }) => {
                    console.log('Raw response:', text); // keeps full error message for debugging
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    if (data.success) {
                        closeModal('editCommunityModal');
                        showToast(data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('Network error: ' + error.message, false);
                });
        }


        function deleteCommunity(communityId, communityName) {
            if (!confirm(`Are you sure you want to delete "${communityName}"? This action cannot be undone.`)) {
                return;
            }
            fetch('community_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete_community&community_id=${communityId}`
                })
                .then(response => response.text().then(text => ({
                    response,
                    text
                })))
                .then(({
                    response,
                    text
                }) => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text);
                    }
                })
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        const row = document.querySelector(`tr[data-community-id="${communityId}"]`);
                        row.remove();
                        const countEl = document.querySelector('.community-count');
                        const currentCount = parseInt(countEl.textContent);
                        countEl.textContent = `${currentCount - 1} communities`;
                        if (currentCount === 1) {
                            document.querySelector('.communities-table').outerHTML = `
                            <div class="empty-state">
                                <i class="fa-solid fa-users"></i>
                                <h3>No Communities Yet</h3>
                                <p>Create your first community to get started!</p>
                            </div>
                        `;
                        }
                    } else {
                        showToast(data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showToast('Network error: ' + error.message, false);
                });
        }
    </script>
</body>

</html>