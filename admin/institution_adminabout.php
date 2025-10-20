<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');
error_reporting(E_ALL);

session_start();
include '../inc/config.php';

// Fetch institution details
$stmt = $conn->prepare("
    SELECT name, location, about_text, mission, vision, email, phone, website
    FROM institution_details 
    WHERE id = 1
");
$stmt->execute();
$result = $stmt->get_result();
$institution = $result->fetch_assoc() ?: [
    'name' => 'University of Lagos (UNILAG)',
    'location' => 'Akoka, Yaba, Lagos State, Nigeria',
    'about_text' => 'Founded in 1962, University of Lagos is a leading institution...',
    'mission' => 'To provide transformative educational experiences...',
    'vision' => 'To be a global leader in education and research...',
    'email' => 'info@unilag.edu.ng',
    'phone' => '+2348034567890',
    'website' => 'http://www.unilag.edu.ng'
];

// Get live stats from users table
$students = 0;
$faculty = 0;
$departments = 0;
$campuses = 1;
$res = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($res) $students = (int)$res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE faculty IS NOT NULL AND faculty <> ''");
if ($res) $faculty = (int)$res->fetch_assoc()['total'];

$res = $conn->query("SELECT COUNT(DISTINCT department) AS total FROM users WHERE department IS NOT NULL AND department <> ''");
if ($res) $departments = (int)$res->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($institution['name']); ?> - About</title>
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            --hover-bg: #f0e6ff;
            --active-nav-bg: rgba(127, 0, 255, 0.1);
            --transition-normal: all 0.3s ease;
            --font-primary: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--main-bg);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--text-dark);
            font-weight: 600;
            line-height: 1.3;
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
            transition: var(--transition-normal);
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
            padding: 0 25px;
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
            gap: 5px;
            align-items: center;
        }

        .header-nav a {
            padding: 8px 16px;
            border-radius: 8px;
            color: var(--text-dark);
            font-weight: 500;
            position: relative;
        }

        .header-nav a:hover {
            color: var(--primary-color);
            background-color: var(--hover-bg);
        }

        .header-nav .active-nav {
            color: var(--primary-color);
        }

        .header-nav .active-nav::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 15px;
            right: 15px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .header-right i {
            font-size: 20px;
            color: var(--text-dark);
            cursor: pointer;
            transition: var(--transition-normal);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .header-right i:hover {
            color: var(--primary-color);
            background-color: var(--hover-bg);
            transform: translateY(-2px);
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
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            border: 2px solid var(--primary-color);
        }

        .uni-profile-img i {
            font-size: 30px;
            color: var(--text-light);
        }

        .uni-details strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: var(--font-medium);
        }

        .uni-details p {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 2px;
        }

        .verified-icon {
            color: green;
            margin-right: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            margin: 4px 0;
            border-radius: 12px;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition-normal);
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
        }

        .nav-link:hover,
        .nav-link.active-sidebar {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(127, 0, 255, 0.08);
        }

        .nav-link.active-sidebar {
            font-weight: var(--font-bold);
        }

        .main-content {
            flex-grow: 1;
            padding: 80px 40px 40px;
            overflow-y: auto;
            max-height: 95vh;
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
        }

        .welcome-header p {
            font-size: 16px;
            color: var(--text-dark);
            opacity: 0.9;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .left-column,
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .content-block {
            background-color: var(--light-bg);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            transition: var(--transition-normal);
        }

        .content-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .content-block h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .content-block p,
        .content-block textarea,
        .content-block input {
            font-size: 14px;
            line-height: 1.7;
            color: var(--text-dark);
        }

        .content-block textarea,
        .content-block input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-top: 10px;
            resize: vertical;
        }

        .content-block textarea:focus,
        .content-block input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .content-block button,
        .modal button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--light-bg);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        .content-block button:hover,
        .modal button:hover {
            background-color: var(--primary-dark);
        }

        .contact-card,
        .stats-card {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .contact-card h3,
        .stats-card h3 {
            font-size: 16px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .contact-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .stats-inner-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .stat-line {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .stat-line:last-child {
            border-bottom: none;
        }

        .stat-line strong {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            font-size: 20px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .modal-content textarea,
        .modal-content input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .modal-content button.close {
            background-color: #ccc;
            margin-left: 10px;
        }

        .modal-content button.close:hover {
            background-color: #aaa;
        }

        /* Toast Styles */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 6px;
            color: #fff;
            z-index: 1001;
            display: none;
        }

        .toast.success {
            background-color: green;
        }

        .toast.error {
            background-color: red;
        }

        @media (max-width: 992px) {
            .about-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }

            .main-content {
                padding: 80px 20px 20px;
            }
        }

        @media (max-width: 576px) {
            .page-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                padding: 20px;
            }

            .header {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }

            .header-nav {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <nav class="header-nav">
            <a href="institution_admindashboard.php">Dashboard</a>
            <a href="institution_adminabout.php" class="active-nav">About</a>
            <a href="institution_admincommunity.php">Communities</a>
            <a href="institution_admin_faculty.php">Faculty</a>
        </nav>
        <div class="header-right">
            <i class="fa-regular fa-bell"></i>
            <i class="fa-solid fa-gear"></i>
        </div>
    </header>

    <div class="page-container">
        <aside class="sidebar">
            <div class="uni-info">
                <div class="uni-profile-img"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="uni-details">
                    <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
                    <p><i class="fa-solid fa-circle-check" style="color:green"></i> Verified Institution</p>
                    <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($institution['location']); ?></p>
                </div>
            </div>
            <a href="institution_admindashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="institution_adminabout.php" class="nav-link active-sidebar"><i class="fa-solid fa-info-circle"></i> About</a>
            <a href="institution_admincommunity.php" class="nav-link"><i class="fa-solid fa-users"></i> Communities</a>
            <a href="institution_admin_faculty.php" class="nav-link"><i class="fa-solid fa-building"></i> Faculty</a>
        </aside>

        <main class="main-content">
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($institution['name']); ?>!</h1>
                <p>Verified Academic Institution • <?php echo htmlspecialchars($institution['location']); ?></p>
            </div>

            <div class="about-grid">
                <div class="left-column">
                    <div class="content-block">
                        <h2><i class="fa-solid fa-book"></i> About Us</h2>
                        <p id="aboutTextDisplay"><?php echo htmlspecialchars($institution['about_text']); ?></p>
                        <button onclick="openModal('editAboutModal')">Edit</button>
                    </div>

                    <div class="content-block">
                        <h2><i class="fa-solid fa-bullseye"></i> Mission & Vision</h2>
                        <div><strong>Mission:</strong>
                            <p id="missionDisplay"><?php echo htmlspecialchars($institution['mission']); ?></p>
                        </div>
                        <div><strong>Vision:</strong>
                            <p id="visionDisplay"><?php echo htmlspecialchars($institution['vision']); ?></p>
                        </div>
                        <button onclick="openModal('editMissionVisionModal')">Edit</button>
                    </div>
                </div>

                <div class="right-column">
                    <div class="contact-card">
                        <h3><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                        <div><i class="fa-solid fa-phone"></i> <span id="phoneDisplay"><?php echo htmlspecialchars($institution['phone']); ?></span></div>
                        <div><i class="fa-solid fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($institution['email']); ?>" id="emailDisplay"><?php echo htmlspecialchars($institution['email']); ?></a></div>
                        <div><i class="fa-solid fa-globe"></i> <a href="<?php echo htmlspecialchars($institution['website']); ?>" target="_blank" id="websiteDisplay"><?php echo htmlspecialchars($institution['website']); ?></a></div>
                        <div><i class="fa-solid fa-location-dot"></i> <span id="locationDisplay"><?php echo htmlspecialchars($institution['location']); ?></span></div>
                        <button onclick="openModal('editContactModal')">Edit Contact Info</button>
                    </div>

                    <div class="stats-card">
                        <h3><i class="fa-solid fa-chart-simple"></i> Institution Stats</h3>
                        <div><span>Students</span> <strong id="studentsDisplay"><?php echo number_format($students); ?>+</strong></div>
                        <div><span>Faculty</span> <strong id="facultyDisplay"><?php echo number_format($faculty); ?>+</strong></div>
                        <div><span>Departments</span> <strong id="departmentsDisplay"><?php echo number_format($departments); ?></strong></div>
                        <div><span>Campuses</span> <strong id="campusesDisplay"><?php echo number_format($campuses); ?></strong></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="editAboutModal" class="modal">
        <div class="modal-content">
            <h2>Edit About</h2>
            <textarea id="aboutText" rows="4" style="width:100%"><?php echo htmlspecialchars($institution['about_text']); ?></textarea>
            <button onclick="updateAbout()">Update</button>
            <button onclick="closeModal('editAboutModal')">Cancel</button>
        </div>
    </div>

    <div id="editMissionVisionModal" class="modal">
        <div class="modal-content">
            <h2>Edit Mission & Vision</h2>
            <label>Mission:</label>
            <textarea id="missionText" rows="3" style="width:100%"><?php echo htmlspecialchars($institution['mission']); ?></textarea>
            <label>Vision:</label>
            <textarea id="visionText" rows="3" style="width:100%"><?php echo htmlspecialchars($institution['vision']); ?></textarea>
            <button onclick="updateMissionVision()">Update</button>
            <button onclick="closeModal('editMissionVisionModal')">Cancel</button>
        </div>
    </div>

    <div id="editContactModal" class="modal">
        <div class="modal-content">
            <h2>Edit Contact Info</h2>
            <label>Phone</label><input type="text" id="contactPhone" value="<?php echo htmlspecialchars($institution['phone']); ?>">
            <label>Email</label><input type="email" id="contactEmail" value="<?php echo htmlspecialchars($institution['email']); ?>">
            <label>Website</label><input type="text" id="contactWebsite" value="<?php echo htmlspecialchars($institution['website']); ?>">
            <label>Location</label><input type="text" id="contactLocation" value="<?php echo htmlspecialchars($institution['location']); ?>">
            <button onclick="updateContactInfo()">Update</button>
            <button onclick="closeModal('editContactModal')">Cancel</button>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex'
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none'
        }

        function showToast(msg, success = true) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + (success ? 'success' : 'error');
            t.style.display = 'block';
            setTimeout(() => {
                t.style.display = 'none'
            }, 3000);
        }

        function postAction(bodyObj) {
            const formBody = Object.keys(bodyObj).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(bodyObj[k])).join('&');
            return fetch('about_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formBody
            }).then(r => r.json());
        }

        function updateAbout() {
            const aboutText = document.getElementById('aboutText').value;
            if (!aboutText) return showToast('About text required', false);
            postAction({
                action: 'update_institution_details',
                about_text: aboutText
            }).then(data => {
                if (data.success) {
                    document.getElementById('aboutTextDisplay').textContent = aboutText;
                    closeModal('editAboutModal');
                    showToast(data.message);
                } else showToast(data.message, false);
            });
        }

        function updateMissionVision() {
            const mission = document.getElementById('missionText').value;
            const vision = document.getElementById('visionText').value;
            if (!mission || !vision) return showToast('Mission and vision required', false);
            postAction({
                action: 'update_institution_details',
                mission,
                vision
            }).then(data => {
                if (data.success) {
                    document.getElementById('missionDisplay').textContent = mission;
                    document.getElementById('visionDisplay').textContent = vision;
                    closeModal('editMissionVisionModal');
                    showToast(data.message);
                } else showToast(data.message, false);
            });
        }

        function updateContactInfo() {
            const phone = document.getElementById('contactPhone').value.trim();
            const email = document.getElementById('contactEmail').value.trim();
            const website = document.getElementById('contactWebsite').value.trim();
            const location = document.getElementById('contactLocation').value.trim();
            if (!email || !phone || !location) return showToast('Phone, Email & Location required', false);
            postAction({
                action: 'update_contact_info',
                phone,
                email,
                website,
                location
            }).then(res => {
                if (res.success) {
                    document.getElementById('phoneDisplay').textContent = phone;
                    document.getElementById('emailDisplay').textContent = email;
                    document.getElementById('emailDisplay').href = 'mailto:' + email;
                    document.getElementById('websiteDisplay').textContent = website;
                    document.getElementById('websiteDisplay').href = website;
                    document.getElementById('locationDisplay').textContent = location;
                    closeModal('editContactModal');
                    showToast(res.message);
                } else showToast(res.message, false);
            });
        }
    </script>
</body>

</html>