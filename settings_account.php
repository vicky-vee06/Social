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

// Helper for initials
$initials = strtoupper(substr($user['email'], 0, 1));

// Handle updates
$successMessage = $errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Email
    if (isset($_POST['update_email'])) {
        $newEmail = trim($_POST['email']);
        if ($newEmail && $newEmail !== $user['email']) {
            $stmt = $conn->prepare("UPDATE users SET email=? WHERE email=?");
            $stmt->bind_param("ss", $newEmail, $email);
            if ($stmt->execute()) {
                $_SESSION['email'] = $newEmail;
                $user['email'] = $newEmail;
                $successMessage = "Email updated successfully!";
            } else {
                $errorMessage = "Failed to update email.";
            }
        }
    }

    // Update Phone
    if (isset($_POST['update_phone'])) {
        $newPhone = trim($_POST['phone']);
        if ($newPhone && $newPhone !== $user['phone']) {
            $stmt = $conn->prepare("UPDATE users SET phone=? WHERE email=?");
            $stmt->bind_param("ss", $newPhone, $email);
            if ($stmt->execute()) {
                $user['phone'] = $newPhone;
                $successMessage = "Phone number updated successfully!";
            } else {
                $errorMessage = "Failed to update phone.";
            }
        }
    }

    // Update Password
    if (isset($_POST['update_password'])) {
        $newPass = trim($_POST['new_password']);
        $confirmPass = trim($_POST['confirm_password']);
        if ($newPass && $newPass === $confirmPass) {
            $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->bind_param("ss", $hashedPass, $email);
            if ($stmt->execute()) {
                $successMessage = "Password updated successfully!";
            } else {
                $errorMessage = "Failed to update password.";
            }
        } else {
            $errorMessage = "Passwords do not match!";
        }
    }

    // Delete Account
    if (isset($_POST['delete_account'])) {
        $stmt = $conn->prepare("DELETE FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            session_destroy();
            header("Location: sign.php");
            exit;
        } else {
            $errorMessage = "Failed to delete account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - Account</title>
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
body { font-family: var(--font-family); margin:0; padding:0; background: var(--bg-light); display:flex; justify-content:center; }
.container { width:100%; max-width:1400px; display:flex; flex-direction:column; background: var(--bg-white); min-height:100vh; }
.header { display:flex; justify-content:space-between; align-items:center; padding:15px 30px; font-size:24px; font-weight:bold; color:var(--text-dark); border-bottom:1px solid var(--border-light); background: var(--bg-white); }
.main-content { display:flex; flex:1; }
.sidebar { width:300px; padding:24px 16px; background: linear-gradient(180deg, rgba(122,62,219,0.04), rgba(122,62,219,0.01)); border-right:1px solid rgba(0,0,0,0.04); flex-shrink:0; border-radius:12px; margin:18px; margin-top:48px; display:flex; flex-direction:column; gap:8px; box-shadow:0 8px 28px rgba(46,19,83,0.04); position:sticky; top:20px; }
.profile-pic-small { width:40px; height:40px; border-radius:50%; background-color:var(--primary-purple); display:flex; align-items:center; justify-content:center; color:white; font-size:14px; font-weight:bold; margin-bottom:10px; }
.nav-link { display:flex; align-items:center; gap:12px; padding:14px; color:var(--text-dark); text-decoration:none; font-size:15px; border-radius:10px; font-weight:700; margin:4px 0; }
.nav-link:hover { background: rgba(122,62,219,0.06); color: var(--primary-purple); transform: translateX(4px); }
.nav-link.active { background: linear-gradient(90deg, rgba(122,62,219,0.06), rgba(122,62,219,0.02)); color: var(--primary-purple); border-left:4px solid var(--primary-purple); padding-left:12px; box-shadow:0 6px 18px rgba(122,62,219,0.06) inset; }
.settings-area { flex:1; padding:40px; background: var(--bg-light); }
.settings-card { background: var(--bg-white); border-radius:10px; padding:30px; box-shadow:0 4px 10px rgba(0,0,0,0.05); max-width:800px; }
.card-header { display:flex; justify-content:space-between; align-items:center; font-size:20px; font-weight:bold; color: var(--text-dark); margin-bottom:20px; }
.account-section { margin-bottom:30px; }
.account-item { padding:20px 0; border-bottom:1px solid var(--border-light); }
.account-item-title { font-size:18px; font-weight:600; color: var(--text-dark); margin-bottom:5px; }
.account-item-detail { display:flex; justify-content:space-between; align-items:center; }
.account-item-value { font-size:16px; color:var(--text-light); }
.action-link, .action-button { cursor:pointer; }
.action-button { background: var(--primary-purple); color:white; padding:8px 15px; border:none; border-radius:5px; font-weight:600; transition:0.2s; }
.action-button:hover { background:#6a35c5; }
.modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; z-index:1000; }
.modal-overlay.active { display:flex; }
.modal-content { background: var(--bg-white); border-radius:10px; padding:30px; width:90%; max-width:450px; box-shadow:0 5px 15px rgba(0,0,0,0.3); text-align:center; }
.modal-header { display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-light); padding-bottom:15px; margin-bottom:20px; }
.modal-title { font-size:20px; font-weight:bold; color: var(--primary-purple); }
.modal-close { font-size:24px; color: var(--primary-purple); cursor:pointer; font-weight:bold; }
.modal-input { width:100%; padding:10px; border:1px solid var(--border-light); border-radius:5px; font-size:16px; margin-top:10px; }
.modal-button-group { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }
.success-msg { color:green; font-weight:600; margin-bottom:10px; }
.error-msg { color:red; font-weight:600; margin-bottom:10px; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div><i class="fas fa-cog" style="margin-right:8px;color:var(--primary-purple)"></i>Settings</div>
        <div class="header-icons"></div>
    </div>
    <div class="main-content">
        <div class="sidebar">
            <div class="profile-pic-small"><?= $initials ?></div>
            <a href="settings_profile.php" class="nav-link">Profile Information</a>
            <a href="settings_account.php" class="nav-link active">Account</a>
            <a href="settings_privacy.php" class="nav-link">Privacy</a>
            <a href="settings_not.php" class="nav-link">Notifications</a>
        </div>
        <div class="settings-area">
            <div class="settings-card">
                <div class="card-header">Account <i class="fas fa-info-circle" style="color:var(--text-light)"></i></div>

                <?php if($successMessage) echo "<div class='success-msg'>$successMessage</div>"; ?>
                <?php if($errorMessage) echo "<div class='error-msg'>$errorMessage</div>"; ?>

                <div class="account-section">
                    <!-- Update Email -->
                    <div class="account-item">
                        <div class="account-item-title">Email</div>
                        <div class="account-item-detail">
                            <div class="account-item-value"><?= htmlspecialchars($user['email']) ?></div>
                            <form method="post" style="margin:0;">
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                <button type="submit" name="update_email" class="action-button">Update Email</button>
                            </form>
                        </div>
                    </div>
                    <!-- Update Phone -->
                    <div class="account-item">
                        <div class="account-item-title">Phone</div>
                        <div class="account-item-detail">
                            <div class="account-item-value"><?= htmlspecialchars($user['phone']) ?></div>
                            <form method="post" style="margin:0;">
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                                <button type="submit" name="update_phone" class="action-button">Update Phone</button>
                            </form>
                        </div>
                    </div>
                    <!-- Update Password -->
                    <div class="account-item">
                        <div class="account-item-title">Password</div>
                        <div class="account-item-detail">
                            <form method="post" style="margin:0;">
                                <input type="password" name="new_password" placeholder="New Password" required>
                                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                                <button type="submit" name="update_password" class="action-button">Change Password</button>
                            </form>
                        </div>
                    </div>
                    <!-- Delete Account -->
                    <div class="account-item" style="border-bottom:none;">
                        <div class="account-item-title">Deactivate Account</div>
                        <form method="post" style="margin:0;">
                            <button type="submit" name="delete_account" class="action-button" style="background:#d32f2f;">Delete Account</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
