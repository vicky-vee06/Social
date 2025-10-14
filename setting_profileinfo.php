<?php
session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}include('./inc/config.php'); 

if (!isset($_SESSION['email'])) {
    header("Location: login.php"); 
    exit();
}

$email = $_SESSION['email'];

// Create users table if it doesn't exist
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    birthday DATE,
    gender ENUM('Male','Female','Other'),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birthday = $_POST['birthday'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update existing user
        $update = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, birthday=?, gender=?, address=? WHERE email=?");
        $update->bind_param("sssssss", $first_name, $last_name, $phone, $birthday, $gender, $address, $email);
        $update->execute();
        $update->close();
        $message = "Profile updated successfully!";
    } else {
        // Insert new user
        $insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, birthday, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $first_name, $last_name, $email, $phone, $birthday, $gender, $address);
        $insert->execute();
        $insert->close();
        $message = "Profile saved successfully!";
    }
    $stmt->close();
}

// Fetch user info
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, birthday, gender, address FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email_db, $phone, $birthday, $gender, $address);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Profile Info</title>
    <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        /* Include your previous styling here exactly */
        :root {
            --primary-color: #793DDC;
            --background-color: #F8F7FF;
            --card-background: #FFFFFF;
            --text-color: #333333;
            --light-text: #666666;
            --border-color: #EEEEEE;
            --danger-color: #FF5252;
            --shadow-md: 0 6px 18px rgba(26, 18, 44, 0.06);
            --shadow-lg: 0 12px 30px rgba(26, 18, 44, 0.08);
            --transition-speed: 0.18s;
        }

        body {
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            margin: 0;
            padding: 24px 0;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            color: var(--text-color);
        }

        .container {
            width: 94%;
            max-width: 1200px;
            display: flex;
            background-color: var(--card-background);
            box-shadow: var(--shadow-md);
            min-height: 80vh;
            border-radius: 14px;
            overflow: hidden;
        }

        /* Settings Header & Sidebar Styles (Modified for Settings View) */
        .header {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: transparent;
            align-items: center;
        }

        .sidebar {
            width: 280px;
            padding: 28px 22px;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            background: linear-gradient(180deg, #FFF 0%, #FBF9FF 100%);
        }
        
        .sidebar h2 {
            font-size: 22px;
            color: var(--text-color);
            margin: 0 0 22px 0;
            letter-spacing: -0.2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px; /* reduced spacing between icon and text */
            padding: 10px 12px;
            margin-bottom: 10px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 10px;
            transition: background-color var(--transition-speed), color var(--transition-speed);
            font-weight: 600;
        }

        .nav-link i { color: #BCA5F5; min-width: 20px; text-align: center }

        .nav-link.active {
            background-color: rgba(121, 61, 220, 0.09);
            color: var(--primary-color);
            font-weight: 700;
            border-left: 4px solid var(--primary-color);
            padding-left: 10px;
        }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 20px 30px 30px 30px;
        }
        
        .content-card {
            background-color: var(--card-background);
            padding: 22px;
            border-radius: 12px;
            box-shadow: none;
            margin-bottom: 18px;
            border: 1px solid var(--border-color);
            transition: box-shadow var(--transition-speed) ease, border-color var(--transition-speed) ease;
        }

        .content-card:hover { box-shadow: var(--shadow-lg); border-color: rgba(121,61,220,0.12) }

        .content-card h3 {
            font-size: 18px;
            color: var(--text-color);
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 12px;
            margin-top: 0;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px; /* slightly tighter between header icon and title */
            font-weight: 700;
        }

        .content-card h3 i { color: var(--primary-color); font-size: 18px }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* input-with-icon provides left icon inside the input */
        .input-with-icon { position: relative }
        .input-with-icon input, .input-with-icon select {
            /* shift typed text further left; ensure it still clears the icon */
            padding-left: 28px;
        }
        .input-with-icon i {
            position: absolute;
            left: 8px; /* slightly closer to the edge */
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
            font-size: 14px;
            transition: color var(--transition-speed) ease, transform var(--transition-speed) ease;
            pointer-events: none;
            z-index: 2;
        }

        .input-with-icon input:focus + i, .input-with-icon select:focus + i,
        .input-with-icon input:not(:placeholder-shown) + i { color: var(--primary-color) }

        .form-group label {
            font-size: 13px;
            color: var(--light-text);
            margin-bottom: 6px;
            font-weight: 700;
            letter-spacing: 0.1px;
        }

        .form-group input, .form-group select {
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            color: var(--text-color);
            transition: border-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: rgba(121,61,220,0.6);
            box-shadow: 0 6px 18px rgba(121,61,220,0.06);
        }
        
        .full-width { grid-column: span 2 }

        .save-button {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: transform var(--transition-speed) ease, background-color var(--transition-speed) ease;
            box-shadow: 0 6px 18px rgba(121,61,220,0.12);
        }

        .save-button:hover { transform: translateY(-2px); background-color: #662fb8 }
        .save-button i { margin-right: 8px; } /* consistent smaller gap for icon inside button */
        
        .delete-account {
            color: var(--danger-color);
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px; /* slightly smaller gap */
            margin-top: 12px;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 860px) {
            .container { flex-direction: column; width: 96% }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--border-color) }
            .form-grid { grid-template-columns: 1fr }
            .full-width { grid-column: auto }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar HTML here (keep as is) -->
        <div class="sidebar">
            <h2>Settings</h2>
            <nav>
                <a href="../auth/settings_profile.php" class="nav-link active"><i class="fas fa-id-badge"></i>Profile Information</a>
                <a href="#" class="nav-link"><i class="fas fa-university"></i>Change Institution</a>
                <a href="./settings_privacy.php" class="nav-link"><i class="fas fa-lock"></i>Security & Privacy</a>
                <a href="../auth/settings_not.php" class="nav-link"><i class="fas fa-bell"></i>Notifications</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <div class="header-actions">
                    <button title="Close" style="background:transparent;border:none;font-size:18px;cursor:pointer;color:var(--light-text)"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <h1 style="font-size: 28px; color: var(--text-color); margin: 18px 0 22px 0;">Profile Information</h1>

            <?php if (!empty($message)) echo "<div style='color:green;margin-bottom:12px;'>$message</div>"; ?>

            <form method="POST">
            <div class="content-card">
                <h3>Personal & Contact Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first-name">First Name</label>
                        <div class="input-with-icon">
                            <input type="text" name="first_name" id="first-name" value="<?= htmlspecialchars($first_name) ?>" placeholder=" ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name</label>
                        <div class="input-with-icon">
                            <input type="text" name="last_name" id="last-name" value="<?= htmlspecialchars($last_name) ?>" placeholder=" ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" value="<?= htmlspecialchars($email_db) ?>" disabled placeholder=" ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-with-icon">
                            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" placeholder=" ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Date of Birth</label>
                        <div class="input-with-icon">
                            <input type="date" name="birthday" id="birthday" value="<?= htmlspecialchars($birthday) ?>" placeholder=" ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <div class="input-with-icon">
                            <select name="gender" id="gender">
                                <option <?= $gender=='Male'?'selected':'' ?>>Male</option>
                                <option <?= $gender=='Female'?'selected':'' ?>>Female</option>
                                <option <?= $gender=='Other'?'selected':'' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <div class="input-with-icon">
                            <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>" placeholder=" ">
                        </div>
                    </div>
                </div>

                <button type="submit" class="save-button"><i class="fas fa-save"></i> Save Changes</button>
            </div>
            </form>

            <div class="content-card">
                <h3><i class="fas fa-user-cog"></i> Account Management</h3>
                <a href="#" class="delete-account"><i class="fas fa-trash-alt"></i>Delete My Account Permanently</a>
            </div>
        </div>
    </div>
</body>
</html>
