<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}
// Use email from session
$email = isset($_SESSION['email']) ? $_SESSION['email'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - Notifications</title>
<link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
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
        --green-toggle: #4caf50;
    }

    body { font-family: var(--font-family); margin: 0; padding: 0; background-color: var(--bg-light); display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
    .container { width: 100%; max-width: 1400px; display: flex; flex-direction: column; background-color: var(--bg-white); box-shadow: 0 0 20px rgba(0,0,0,0.05); min-height: 100vh; }
    .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background-color: var(--bg-white); border-bottom: 1px solid var(--border-light); font-size: 24px; font-weight: bold; color: var(--text-dark); }
    .main-content { display: flex; flex: 1; }
    .sidebar { width: 350px; padding: 28px; background: linear-gradient(180deg, rgba(122,62,219,0.04), rgba(122,62,219,0.01)); border-right: 1px solid rgba(0,0,0,0.04); flex-shrink: 0; box-sizing: border-box; border-radius: 12px; margin: 18px; margin-top: 48px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 8px 28px rgba(46,19,83,0.04); position: sticky; top: 20px; }
    .settings-header { display: flex; align-items: center; padding: 0 20px 20px; border-bottom: 1px solid var(--border-light); font-size: 18px; font-weight: bold; color: var(--text-dark); }
    .profile-pic-small { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-purple); margin: 0 10px 0 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: bold; }
    .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 14px; color: var(--text-dark); text-decoration: none; font-size: 15px; transition: background 0.16s ease, color 0.12s; border-radius: 10px; font-weight: 700; }
    .nav-link:hover { background: rgba(122,62,219,0.06); color: var(--primary-purple); transform: translateX(4px); }
    .nav-link.active { background: linear-gradient(90deg, rgba(122,62,219,0.06), rgba(122,62,219,0.02)); color: var(--primary-purple); border-left: 4px solid var(--primary-purple); padding-left: 12px; box-shadow: 0 6px 18px rgba(122,62,219,0.06) inset; }
    .nav-icon { margin-right: 6px; font-size: 18px; color: var(--primary-purple); width: 22px; display: inline-flex; justify-content: center; }
    .settings-area { flex: 1; padding: 40px; background-color: var(--bg-light); }
    .settings-card { background-color: var(--bg-white); border-radius: 10px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); max-width: 800px; }
    .card-header { display: flex; justify-content: space-between; align-items: center; font-size: 20px; font-weight: bold; color: var(--text-dark); margin-bottom: 20px; }
    .card-header .info-icon { font-size: 20px; color: var(--text-light); cursor: pointer; }

    .notification-group { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border-light); }
    .notification-group-title { font-size: 18px; font-weight: bold; color: var(--primary-purple); margin-bottom: 10px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-purple); display: inline-block; }
    .notification-group-description { font-size: 14px; color: var(--text-light); margin-bottom: 20px; }
    .notification-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-top: 1px solid var(--border-light); }
    .notification-item:last-of-type { border-bottom: 1px solid var(--border-light); }
    .notification-item-info { flex: 1; }
    .notification-item-title { font-size: 16px; font-weight: 600; color: var(--text-dark); }
    .notification-item-subtitle { font-size: 14px; color: var(--text-light); margin-top: 3px; }
    .switch { position: relative; display: inline-block; width: 45px; height: 25px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-light); transition: .4s; border-radius: 25px; }
    .slider:before { position: absolute; content: ""; height: 17px; width: 17px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--primary-purple); }
    input:focus + .slider { box-shadow: 0 0 1px var(--primary-purple); }
    input:checked + .slider:before { transform: translateX(20px); }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div><i class="fas fa-cog" style="margin-right:8px;color:var(--primary-purple)"></i> settings</div>
    </div>

    <div class="main-content">
        <div class="sidebar">
            <div class="settings-header">
                <div class="profile-pic-small"><?php echo strtoupper(substr($email,0,2)); ?></div>
                settings
            </div>

            <a href="../auth/settings_profile.php" class="nav-link">
                <i class="fas fa-user nav-icon" aria-hidden="true"></i>Profile Information
            </a>
            <a href="../auth/settings_account.php" class="nav-link">
                <i class="fas fa-key nav-icon" aria-hidden="true"></i>Account
            </a>
            <a href="../auth/settings_privacy.php" class="nav-link">
                <i class="fas fa-lock nav-icon" aria-hidden="true"></i>Privacy
            </a>
            <a href="../auth/settings_not.php" class="nav-link active">
                <i class="fas fa-bell nav-icon" aria-hidden="true"></i>Notifications
            </a>
        </div>

        <div class="settings-area">
            <div class="settings-card">
                <div class="card-header">
                    Notifications
                    <i class="fas fa-info-circle info-icon" aria-hidden="true"></i>
                </div>

                <div class="notification-group">
                    <div class="notification-group-title">Push Notifications</div>
                    <div class="notification-group-description">Receive alerts directly to your device.</div>

                    <div class="notification-item">
                        <div class="notification-item-info">
                            <div class="notification-item-title">New Comments on your posts</div>
                            <div class="notification-item-subtitle">Receive a notification when someone comments.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="pushComments">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="notification-item">
                        <div class="notification-item-info">
                            <div class="notification-item-title">Mentions and Tags</div>
                            <div class="notification-item-subtitle">Get notified when you are @mentioned or tagged.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="pushMentions">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="notification-item">
                        <div class="notification-item-info">
                            <div class="notification-item-title">Friend Requests</div>
                            <div class="notification-item-subtitle">Alerts for incoming friend invitations.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="pushRequests">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="notification-item" style="border-bottom: none;">
                        <div class="notification-item-info">
                            <div class="notification-item-title">New Study Group Activities</div>
                            <div class="notification-item-subtitle">Updates on discussions in groups you join.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="pushGroups">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="notification-group" style="border-bottom: none;">
                    <div class="notification-group-title">Email Notifications</div>
                    <div class="notification-group-description">Receive these updates via your registered email.</div>

                    <div class="notification-item">
                        <div class="notification-item-info">
                            <div class="notification-item-title">Weekly Digest</div>
                            <div class="notification-item-subtitle">Summary of community activities and news.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="emailDigest">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="notification-item">
                        <div class="notification-item-info">
                            <div class="notification-item-title">Security & Account Alerts</div>
                            <div class="notification-item-subtitle">Important security information (e.g., password change).</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked id="emailSecurity">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="notification-item" style="border-bottom: none;">
                        <div class="notification-item-info">
                            <div class="notification-item-title">Marketing & Promotions</div>
                            <div class="notification-item-subtitle">Opt-in for non-essential promotional emails.</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="emailMarketing">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log(this.id + ' notification setting changed to: ' + this.checked);
            // Here you could send an AJAX request to save the change
        });
    });
</script>
</body>
</html>
