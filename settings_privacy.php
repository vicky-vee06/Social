<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}include('./inc/config.php');

if (!isset($_SESSION['email'])) {
    die("User not logged in. Please log in again.");
}

$email = $_SESSION['email'];

// Fetch user by email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found. Please log in again.");
}

// Generate initials from email instead of fullname
$initials = strtoupper(substr($user['email'], 0, 1));

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - Privacy</title>
<link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
<style>
:root {
    --primary-purple: #7a3edb;
    --light-purple: #f3effe;
    --text-dark: #333;
    --text-light: #666;
    --border-light: #eee;
    --bg-light: #f9f9f9;
    --bg-white: #fff;
    --font-family: Arial, sans-serif;
}
body {
    font-family: var(--font-family);
    margin: 0;
    padding: 0;
    background-color: var(--bg-light);
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
}
.container {
    width: 100%;
    max-width: 1400px;
    display: flex;
    flex-direction: column;
    background-color: var(--bg-white);
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    min-height: 100vh;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    background-color: var(--bg-white);
    border-bottom: 1px solid var(--border-light);
    font-size: 24px;
    font-weight: bold;
    color: var(--text-dark);
}
.main-content {
    display: flex;
    flex: 1;
}
.sidebar {
    width: 300px;
    padding: 24px 16px;
    background: linear-gradient(180deg, rgba(122,62,219,0.04), rgba(122,62,219,0.01));
    border-right: 1px solid rgba(0,0,0,0.04);
    flex-shrink: 0;
    box-sizing: border-box;
    border-radius: 12px;
    margin: 18px;
    margin-top: 48px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 8px 28px rgba(46,19,83,0.04);
    position: sticky;
    top: 20px;
}
.settings-header {
    display: flex;
    align-items: center;
    padding: 10px 14px 24px;
    border-bottom: 1px solid var(--border-light);
    font-size: 18px;
    font-weight: bold;
    color: var(--text-dark);
    margin-bottom: 8px;
}
.profile-pic-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-purple);
    margin: 0 10px 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: bold;
}
.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    color: var(--text-dark);
    text-decoration: none;
    font-size: 15px;
    transition: background 0.16s ease, color 0.12s;
    border-radius: 10px;
    font-weight: 700;
}
.nav-link:hover {
    background: rgba(122,62,219,0.06);
    color: var(--primary-purple);
    transform: translateX(4px);
}
.nav-link.active {
    background: linear-gradient(90deg, rgba(122,62,219,0.06), rgba(122,62,219,0.02));
    color: var(--primary-purple);
    border-left: 4px solid var(--primary-purple);
    padding-left: 12px;
    box-shadow: 0 6px 18px rgba(122,62,219,0.06) inset;
}
.nav-icon {
    margin-right: 6px;
    font-size: 18px;
    color: var(--primary-purple);
    width: 22px;
    display: inline-flex;
    justify-content: center;
}
.settings-area {
    flex: 1;
    padding: 40px;
    background-color: var(--bg-light);
}
.settings-card {
    background-color: var(--bg-white);
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    max-width: 800px;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 20px;
    font-weight: bold;
    color: var(--text-dark);
    margin-bottom: 20px;
}
.card-header .info-icon {
    font-size: 20px;
    color: var(--text-light);
    cursor: pointer;
}
.privacy-section {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-light);
}
.privacy-section-title {
    font-size: 18px;
    font-weight: bold;
    color: var(--text-dark);
    margin-bottom: 15px;
}
.privacy-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-top: 1px solid var(--border-light);
}
.privacy-item-info {
    flex: 1;
}
.privacy-item-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
}
.privacy-item-description {
    font-size: 14px;
    color: var(--text-light);
    margin-top: 3px;
}
.privacy-action {
    display: flex;
    align-items: center;
    min-width: 100px;
}
.select-privacy {
    padding: 5px 10px;
    border: 1px solid var(--border-light);
    border-radius: 5px;
    font-size: 14px;
    color: var(--text-dark);
    background-color: var(--bg-white);
    cursor: pointer;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http://www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22666666%22%20d%3D%22M287%2069.4l-145%20145c-2.4%202.4-5.5%203.6-8.7%203.6s-6.3-1.2-8.7-3.6L5.4%2069.4C.6%2064.6%200%2057.4%204.8%2052.6c4.8-4.8%2012-4.8%2016.8%200l132%20132%20132-132c4.8-4.8%2012-4.8%2016.8%200%204.8%204.8%204.8%2012%200%2016.8z%22/%3E%3C/svg%3E');
    background-repeat: no-repeat;
    background-position: right 8px center;
    background-size: 10px;
    padding-right: 25px;
    min-width: 100px;
}
.switch {
    position: relative;
    display: inline-block;
    width: 45px;
    height: 25px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--border-light);
    transition: .4s;
    border-radius: 25px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 17px;
    width: 17px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider { background-color: var(--primary-purple); }
input:checked + .slider:before { transform: translateX(20px); }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div><i class="fas fa-cog" style="margin-right:8px;color:var(--primary-purple)"></i> settings</div>
        <div class="header-icons"></div>
    </div>

    <div class="main-content">
        <div class="sidebar">
            <div class="settings-header">
                <div class="profile-pic-small"><?= $initials ?></div>
                settings
            </div>

            <a href="../auth/settings_profile.php" class="nav-link">
                <i class="fas fa-user nav-icon" aria-hidden="true"></i>Profile Information
            </a>
            <a href="../auth/settings_account.php" class="nav-link">
                <i class="fas fa-key nav-icon" aria-hidden="true"></i>Account
            </a>
            <a href="../auth/settings_privacy.php" class="nav-link active">
                <i class="fas fa-lock nav-icon" aria-hidden="true"></i>Privacy
            </a>
            <a href="../auth/settings_not.php" class="nav-link">
                <i class="fas fa-bell nav-icon" aria-hidden="true"></i>Notifications
            </a>
        </div>

        <div class="settings-area">
            <div class="settings-card">
                <div class="card-header">
                    Privacy
                    <i class="fas fa-info-circle info-icon"></i>
                </div>

                <div class="privacy-section">
                    <div class="privacy-section-title">Profile Visibility</div>
                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Who can see your profile?</div>
                            <div class="privacy-item-description">Controls who can view your name, photo, and bio.</div>
                        </div>
                        <div class="privacy-action">
                            <select class="select-privacy" id="profileVisibility">
                                <option value="friends">Friends</option>
                                <option value="friends_of_friends">Friends of Friends</option>
                                <option value="everyone">Everyone</option>
                            </select>
                        </div>
                    </div>

                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Allow profile to be searched by search engines</div>
                            <div class="privacy-item-description">e.g., Google, Bing</div>
                        </div>
                        <div class="privacy-action">
                            <label class="switch">
                                <input type="checkbox" checked id="searchEngineToggle" onchange="toggleSetting('searchEngineToggle')">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="privacy-section">
                    <div class="privacy-section-title">Communication & Messaging</div>
                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Who can send you messages?</div>
                            <div class="privacy-item-description">Filter messages from unknown users.</div>
                        </div>
                        <div class="privacy-action">
                            <select class="select-privacy" id="messageFilter">
                                <option value="friends">Friends</option>
                                <option value="everyone">Everyone</option>
                            </select>
                        </div>
                    </div>

                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Who can see your online status?</div>
                            <div class="privacy-item-description">Show or hide your 'Active now' status.</div>
                        </div>
                        <div class="privacy-action">
                            <label class="switch">
                                <input type="checkbox" checked id="onlineStatusToggle" onchange="toggleSetting('onlineStatusToggle')">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="privacy-section" style="border-bottom: none; margin-bottom: 0;">
                    <div class="privacy-section-title">Post & Content</div>
                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Default post audience</div>
                            <div class="privacy-item-description">Setting for all new posts and uploads.</div>
                        </div>
                        <div class="privacy-action">
                            <select class="select-privacy" id="defaultPostAudience">
                                <option value="friends">Friends</option>
                                <option value="private">Only me</option>
                                <option value="public">Public</option>
                            </select>
                        </div>
                    </div>

                    <div class="privacy-item">
                        <div class="privacy-item-info">
                            <div class="privacy-item-title">Restrict past posts</div>
                            <div class="privacy-item-description">Apply new privacy settings to all previous posts.</div>
                        </div>
                        <div class="privacy-action">
                            <div class="action-link" onclick="alert('Past posts restricted to Friends.')">
                                Apply <span style="margin-left: 5px;">→</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function toggleSetting(id) {
    const checkbox = document.getElementById(id);
    console.log(id + ' toggled. New state: ' + checkbox.checked);
    // Here you can add PHP AJAX call to save setting
}
</script>
</body>
</html>
