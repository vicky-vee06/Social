<?php
// Connection to database
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "user_system";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['profile_pic']) || empty($_SESSION['profile_pic'])) {
    $_SESSION['profile_pic'] = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
}

// Fetch student info
$student_id = 1;
$sql = "SELECT * FROM users WHERE id = $student_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "Student not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - About</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        /* Your existing CSS here */
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

        /* --- Header & Sidebar (Reusable Style Block) --- */
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

        .header-actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--light-text);
            padding: 8px;
            border-radius: 8px;
        }

        .header-actions button:hover {
            background: rgba(0,0,0,0.03);
            color: var(--primary-color);
        }

        .sidebar {
            width: 250px;
            padding: 20px;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .profile-summary {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #b39ddb; /* Placeholder */
            margin: 0 auto 10px;
            border: 2px solid var(--primary-color);
        }

        .profile-summary h2 {
            font-size: 18px;
            margin: 0;
            color: var(--text-color);
        }

        .edit-profile {
            font-size: 12px;
            color: var(--primary-color);
            text-decoration: none;
            display: block;
            margin-top: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 8px;
            color: var(--light-text);
            text-decoration: none;
            border-radius: 8px;
            gap: 8px;
            font-weight: 600;
        }
        .nav-link i {
            color: var(--primary-color);
            width: 18px;
            text-align: center;
        }
        .nav-link.active {
            background: rgba(121,61,220,0.06);
            color: var(--primary-color);
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            padding: 0 30px 30px 30px;
        }

        .profile-header-strip {
            height: 120px;
            background-color: var(--primary-color);
            border-radius: 0 0 15px 15px;
            margin: 0 -30px 20px -30px;
        }

        .profile-tabs {
            display: flex;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
            padding-top: 10px;
            align-items: center;
        }

        /* style tabs as links without underline */
        .profile-tabs a.tab-button {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 700;
            color: var(--light-text);
            border-bottom: 3px solid transparent;
            text-decoration: none; /* remove underline */
            border-radius: 6px 6px 0 0;
            transition: color 0.18s ease, border-bottom-color 0.18s ease, background-color 0.12s ease;
        }

        .profile-tabs a.tab-button:hover {
            color: var(--primary-color);
            background-color: rgba(121,61,220,0.04);
        }

        .profile-tabs a.tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background-color: rgba(121,61,220,0.06);
        }

        /* --- About Content Specific Styles --- */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-card {
            background-color: var(--card-background);
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(26,18,44,0.04);
            grid-column: span 1; /* default: one column */
            transition: box-shadow 0.15s ease, transform 0.12s ease;
        }
        .info-card.full { grid-column: span 2 } /* utility class for full-width cards */
        
        .info-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(26,18,44,0.06) }
        
        .info-card h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            color: var(--primary-color);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 14px;
            font-weight: 700;
        }
        .info-card h3 i { color: var(--primary-color); font-size: 16px }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #F5F5F5;
            align-items: center;
        }
        
        .info-item:last-of-type {
            border-bottom: none;
        }

        .info-item strong {
            color: var(--text-color);
            font-weight: 600;
            width: 140px;
            flex-shrink: 0;
            font-size: 14px;
        }
        
        .info-item span {
            color: var(--light-text);
        }

        /* Styling for the academic info section to look distinct */
        .academic-info .info-item strong {
            width: 150px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h1 style="font-size: 24px; color: var(--text-color);">Profile</h1>
            <div class="profile-summary">
                <div class="profile-pic"></div>
                <h2><?php echo htmlspecialchars($student['first_name']); ?></h2>
                <p style="font-size: 14px; color: var(--light-text); margin-top: 5px;">50 friends</p>
                <a href="#" class="edit-profile">edit profile ✏️</a>
            </div>

            <nav>
                <a href="./home_feed.php" class="nav-link"><i class="fas fa-home"></i>Home</a>
                <a href="./student_aboutprofile.php" class="nav-link active"><i class="fas fa-user"></i>Profile</a>
                <a href="#" class="nav-link"><i class="fas fa-search"></i>Explore</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <div style="font-size: 24px; font-weight: bold;"></div>
                <div class="header-actions">
                    <button title="Notifications"><i class="fas fa-bell"></i></button>
                    <button title="Settings"><i class="fas fa-cog"></i></button>
                    <button title="More"><i class="fas fa-ellipsis-h"></i></button>
                </div>
            </div>

            <div class="profile-header-strip"></div>

            <div class="profile-tabs">
                <a class="tab-button" href="profile_timeline.php">Timeline</a>
                <a class="tab-button active" href="student_aboutprofile.php" aria-current="page">About</a>
                <a class="tab-button" href="student_profileinstitution.php">Institutions</a>
                <a class="tab-button" href="student_profilecommunity.php">Communities</a>
                <a class="tab-button" href="student_profilefriends.php">Friends</a>
            </div>

            <div class="about-content">
                
                <div class="info-card contact-info">
                    <h3><i class="fas fa-address-book"></i>Contact Information</h3>
                    <div class="info-item">
                        <strong>Phone Number</strong>
                        <span><?php echo htmlspecialchars($student['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Address</strong>
                        <span><?php echo htmlspecialchars($student['address']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email</strong>
                        <span><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                </div>

                <div class="info-card academic-info">
                    <h3><i class="fas fa-graduation-cap"></i>Academic Information</h3>
                    <div class="info-item">
                        <strong>Faculty</strong>
                        <span><?php echo htmlspecialchars($student['faculty']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Institution</strong>
                        <span><?php echo htmlspecialchars($student['institution']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Department</strong>
                        <span><?php echo htmlspecialchars($student['department']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Matric No.</strong>
                        <span><?php echo htmlspecialchars($student['matric_no']); ?></span>
                    </div>
                </div>
                
                <div class="info-card short-bio full">
                    <h3><i class="fas fa-user"></i>Bio / Interests</h3>
                    <div class="info-item">
                        <strong>Gender</strong>
                        <span><?php echo htmlspecialchars($student['gender']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Birthday</strong>
                        <span><?php echo htmlspecialchars($student['birthday']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Interests</strong>
                        <span><?php echo htmlspecialchars($student['interests']); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
