<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "user_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students
$students = $conn->query("SELECT id, name, avatar, followers FROM users ORDER BY followers DESC LIMIT 6");

// Fetch institutions
$institutions = $conn->query("SELECT id, name, followers FROM institutions ORDER BY followers DESC LIMIT 6");

// Fetch hot topics
$topics = $conn->query("SELECT id, topic, posts_count FROM topics ORDER BY posts_count DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Hub</title>
<link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.css">
<style>
:root {
    --primary-purple: #8A4FFB;
    --light-purple: #E7DBFF;
    --background-grey: #F0F2F5;
    --text-dark: #333;
    --text-medium: #666;
    --text-light: #999;
    --card-background: #fff;
    --border-color: #E0E0E0;
}

body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 20px;
    background-color: var(--background-grey);
    color: var(--text-dark);
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 25px;
}

/* Main content */
.main-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

/* Search bar */
.search-bar-container {
    background-color: var(--card-background);
    padding: 15px 25px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.search-bar-container input {
    flex-grow: 1;
    border: none;
    outline: none;
    font-size: 16px;
    color: var(--text-dark);
}

.search-bar-container input::placeholder {
    color: var(--text-light);
}

/* Tabs */
.nav-tabs {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.nav-tab {
    padding: 8px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 500;
    background: transparent;
    border: none;
    color: var(--text-medium);
    transition: all 0.2s;
}

.nav-tab.active {
    background-color: var(--primary-purple);
    color: #fff;
}

/* Sections */
section h2 {
    font-size: 20px;
    margin-bottom: 15px;
}

/* Featured Cards */
.featured-cards, .suggested-cards, .institution-cards {
    display: grid;
    gap: 20px;
}

.featured-cards {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.featured-card, .suggested-card, .institution-card {
    background-color: var(--card-background);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.2s ease;
}

.featured-card:hover, .suggested-card:hover, .institution-card:hover {
    transform: translateY(-5px);
}

/* Profile pictures */
.profile-pic {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

/* Buttons */
.add-friend-btn {
    margin-left: auto;
    padding: 8px 15px;
    border-radius: 20px;
    border: none;
    background-color: var(--primary-purple);
    color: #fff;
    cursor: pointer;
    transition: background 0.2s;
}

.add-friend-btn:hover {
    background-color: #6A38D6;
}

/* Right sidebar */
.right-sidebar {
    background-color: var(--primary-purple);
    border-radius: 12px;
    padding: 16px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    max-height: 400px;
    overflow-y: auto;
}

.hot-topic-item {
    background-color: rgba(255,255,255,0.15);
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: 0.2s;
}

.hot-topic-item:hover {
    background-color: rgba(255,255,255,0.25);
}

/* Animations */
.fade-in {
    animation: fadeIn 0.5s ease forwards;
    opacity: 0;
}

@keyframes fadeIn {
    to { opacity: 1; }
}
</style>
</head>
<body>

<div class="dashboard-container">
    <div class="main-content">

        <!-- Search -->
        <div class="search-bar-container">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search Students, Institutions, or Topics...">
        </div>

        <!-- Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active">Trending</button>
            <button class="nav-tab">Institutions</button>
            <button class="nav-tab">Students</button>
            <button class="nav-tab">Communities</button>
            <button class="nav-tab">Announcements</button>
        </div>

        <!-- Featured Section -->
        <section>
            <h2>Featured</h2>
            <div class="featured-cards">
                <div class="featured-card fade-in">
                    <i class="fas fa-book"></i>
                    <h3>Study Hacks</h3>
                </div>
                <div class="featured-card fade-in">
                    <i class="fas fa-school"></i>
                    <h3>Campus Updates</h3>
                </div>
                <div class="featured-card fade-in">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Student Life</h3>
                </div>
                <div class="featured-card fade-in">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Scholarships</h3>
                </div>
            </div>
        </section>

        <!-- Suggested Students -->
        <section>
            <h2>Suggested for You</h2>
            <div class="suggested-cards">
                <?php while($s = $students->fetch_assoc()): ?>
                <div class="suggested-card fade-in">
                    <img src="<?php echo !empty($s['avatar']) ? 'avatars/'.$s['avatar'] : 'img/default-avatar.png'; ?>" class="profile-pic" alt="Profile">
                    <div class="info">
                        <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                        <p><?php echo $s['followers']; ?> followers</p>
                    </div>
                    <button class="add-friend-btn" data-user="<?php echo htmlspecialchars($s['name']); ?>">Add Friend</button>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Popular Institutions -->
        <section>
            <h2>Popular Institutions</h2>
            <div class="institution-cards">
                <?php while($inst = $institutions->fetch_assoc()): ?>
                <div class="institution-card fade-in">
                    <i class="fas fa-university" style="font-size: 30px; color: var(--primary-purple);"></i>
                    <div class="info">
                        <h3><?php echo htmlspecialchars($inst['name']); ?></h3>
                        <p><?php echo $inst['followers']; ?> followers</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

    </div>

    <!-- Right Sidebar Hot Topics -->
    <div class="right-sidebar">
        <h2>Hot Topics</h2>
        <?php while($t = $topics->fetch_assoc()): ?>
        <div class="hot-topic-item fade-in">
            <h3>#<?php echo htmlspecialchars($t['topic']); ?></h3>
            <p><?php echo $t['posts_count']; ?> posts</p>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
// Add Friend Button
document.querySelectorAll('.add-friend-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const userName = btn.dataset.user;
        alert(`Friend request sent to ${userName}`);
        btn.textContent = 'Requested';
        btn.disabled = true;
        btn.style.backgroundColor = '#ccc';
        btn.style.cursor = 'not-allowed';
    });
});

// Tabs switching (example)
document.querySelectorAll('.nav-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
    });
});
</script>
</body>
</html>
