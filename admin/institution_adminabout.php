<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');
error_reporting(E_ALL);

session_start();


include '../inc/config.php';

// Fetch institution details
$stmt = $conn->prepare("SELECT name, location, about_text, mission, vision, email, phone, website, students, faculty, departments, campuses FROM institution_details WHERE id = 1");
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
    'website' => 'http://www.unilag.edu.ng',
    'students' => 12000,
    'faculty' => 150,
    'departments' => 42,
    'campuses' => 5
];
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
        <div class="header-left">
            <nav class="header-nav">
                <a href="institution_admindashboard.php">Dashboard</a>
                <a href="institution_adminabout.php" class="active-nav">About</a>
                <a href="institution_admincommunity.php">Communities</a>
                <a href="institution_admin_faculty.php">Faculty</a>
            </nav>
        </div>
        <div class="header-right">
            <i class="fa-regular fa-bell"></i>
            <i class="fa-solid fa-gear"></i>
        </div>
    </header>

    <div class="page-container">
        <aside class="sidebar">
            <div class="uni-info">
                <div class="uni-profile-img">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="uni-details">
                    <strong><?php echo htmlspecialchars($institution['name']); ?></strong>
                    <p><i class="fa-solid fa-circle-check verified-icon"></i> Verified Institution</p>
                    <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($institution['location']); ?></p>
                </div>
            </div>
            <nav class="side-nav">
                <a href="institution_admindashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="institution_adminabout.php" class="nav-link active-sidebar"><i class="fa-solid fa-info-circle"></i> About</a>
                <a href="institution_admincommunity.php" class="nav-link"><i class="fa-solid fa-users"></i> Communities</a>
                <a href="institution_admin_faculty.php" class="nav-link"><i class="fa-solid fa-building"></i> Faculty</a>
            </nav>
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
                        <p><?php echo htmlspecialchars($institution['about_text']); ?></p>
                        <button onclick="openModal('editAboutModal')">Edit About</button>
                    </div>

                    <div class="content-block">
                        <h2><i class="fa-solid fa-bullseye"></i> Mission & Vision</h2>
                        <div class="mission-vision-item">
                            <strong>Mission:</strong>
                            <p><?php echo htmlspecialchars($institution['mission']); ?></p>
                        </div>
                        <div class="mission-vision-item">
                            <strong>Vision:</strong>
                            <p><?php echo htmlspecialchars($institution['vision']); ?></p>
                        </div>
                        <button onclick="openModal('editMissionVisionModal')">Edit Mission & Vision</button>
                    </div>

                   
                </div>

                <div class="right-column">
                    <div class="contact-card">
                        <h3><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i>
                            <span><?php echo htmlspecialchars($institution['phone']); ?></span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:<?php echo htmlspecialchars($institution['email']); ?>"><?php echo htmlspecialchars($institution['email']); ?></a>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-globe"></i>
                            <a href="<?php echo htmlspecialchars($institution['website']); ?>" target="_blank"><?php echo htmlspecialchars($institution['website']); ?></a>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span><?php echo htmlspecialchars($institution['location']); ?></span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <h3><i class="fa-solid fa-chart-simple"></i> Institution Stats</h3>
                        <div class="stats-inner-grid">
                            <div class="stat-line">
                                <span>Students</span>
                                <strong><?php echo number_format($institution['students']) . '+'; ?></strong>
                            </div>
                            <div class="stat-line">
                                <span>Faculty</span>
                                <strong><?php echo number_format($institution['faculty']) . '+'; ?></strong>
                            </div>
                            <div class="stat-line">
                                <span>Departments</span>
                                <strong><?php echo number_format($institution['departments']); ?></strong>
                            </div>
                            <div class="stat-line">
                                <span>Campuses</span>
                                <strong><?php echo number_format($institution['campuses']); ?></strong>
                            </div>
                        </div>
                        <button onclick="openModal('editStatsModal')">Edit Stats</button>
                    </div>
                </div>
            </div>

            <!-- Modals -->
            <div id="editAboutModal" class="modal">
                <div class="modal-content">
                    <h2>Edit About</h2>
                    <textarea id="aboutText" rows="4"><?php echo htmlspecialchars($institution['about_text']); ?></textarea>
                    <button onclick="updateAbout()">Update</button>
                    <button class="close" onclick="closeModal('editAboutModal')">Cancel</button>
                </div>
            </div>

            <div id="editMissionVisionModal" class="modal">
                <div class="modal-content">
                    <h2>Edit Mission & Vision</h2>
                    <label>Mission:</label>
                    <textarea id="missionText" rows="3"><?php echo htmlspecialchars($institution['mission']); ?></textarea>
                    <label>Vision:</label>
                    <textarea id="visionText" rows="3"><?php echo htmlspecialchars($institution['vision']); ?></textarea>
                    <button onclick="updateMissionVision()">Update</button>
                    <button class="close" onclick="closeModal('editMissionVisionModal')">Cancel</button>
                </div>
            </div>

            <div id="editStatsModal" class="modal">
                <div class="modal-content">
                    <h2>Edit Stats</h2>
                    <label>Students:</label>
                    <input type="number" id="students" value="<?php echo htmlspecialchars($institution['students']); ?>" min="0">
                    <label>Faculty:</label>
                    <input type="number" id="faculty" value="<?php echo htmlspecialchars($institution['faculty']); ?>" min="0">
                    <label>Departments:</label>
                    <input type="number" id="departments" value="<?php echo htmlspecialchars($institution['departments']); ?>" min="0">
                    <label>Campuses:</label>
                    <input type="number" id="campuses" value="<?php echo htmlspecialchars($institution['campuses']); ?>" min="0">
                    <button onclick="updateStats()">Update</button>
                    <button class="close" onclick="closeModal('editStatsModal')">Cancel</button>
                </div>
            </div>

            <div id="contactModal" class="modal">
                <div class="modal-content">
                    <h2>Send Message</h2>
                    <input type="text" id="contactSubject" placeholder="Subject">
                    <textarea id="contactMessage" rows="4" placeholder="Your message"></textarea>
                    <button onclick="sendContactMessage()">Send</button>
                    <button class="close" onclick="closeModal('contactModal')">Cancel</button>
                </div>
            </div>

            <div id="toast" class="toast"></div>
        </main>
    </div>

    <script>
        // --- Global functions for modals and toast ---
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + (isSuccess ? 'success' : 'error');
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        function postAction(bodyObj, callback) {
            const formBody = Object.keys(bodyObj)
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(bodyObj[key]))
                .join('&');

            fetch('about_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formBody
                })
                .then(response => response.text())
                .then(raw => {
                    let data;
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + raw);
                    }
                    callback(data);
                })
                .catch(err => {
                    showToast('Network error: ' + err.message, false);
                });
        }

        function updateAbout() {
            const aboutText = document.getElementById('aboutText').value;
            if (!aboutText) return showToast('About text required', false);
            postAction({
                action: 'update_institution_details',
                about_text: aboutText,
                mission: '',
                vision: ''
            }, data => {
                if (data.success) {
                    document.querySelector('.content-block p').textContent = aboutText;
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
                about_text: '',
                mission,
                vision
            }, data => {
                if (data.success) {
                    document.querySelector('.mission-vision-item:nth-child(1) p').textContent = mission;
                    document.querySelector('.mission-vision-item:nth-child(2) p').textContent = vision;
                    closeModal('editMissionVisionModal');
                    showToast(data.message);
                } else showToast(data.message, false);
            });
        }

        function updateStats() {
            const students = document.getElementById('students').value;
            const faculty = document.getElementById('faculty').value;
            const departments = document.getElementById('departments').value;
            const campuses = document.getElementById('campuses').value;
            if (students < 0 || faculty < 0 || departments < 0 || campuses < 0) return showToast('Stats cannot be negative', false);
            postAction({
                action: 'update_stats',
                students,
                faculty,
                departments,
                campuses
            }, data => {
                if (data.success) {
                    document.querySelector('.stat-line:nth-child(1) strong').textContent = Number(students).toLocaleString() + '+';
                    document.querySelector('.stat-line:nth-child(2) strong').textContent = Number(faculty).toLocaleString() + '+';
                    document.querySelector('.stat-line:nth-child(3) strong').textContent = Number(departments).toLocaleString();
                    document.querySelector('.stat-line:nth-child(4) strong').textContent = Number(campuses).toLocaleString();
                    closeModal('editStatsModal');
                    showToast(data.message);
                } else showToast(data.message, false);
            });
        }

        function sendContactMessage() {
            const subject = document.getElementById('contactSubject').value;
            const message = document.getElementById('contactMessage').value;
            if (!subject || !message) return showToast('Subject and message required', false);
            postAction({
                action: 'send_contact_message',
                subject,
                message
            }, data => {
                if (data.success) {
                    closeModal('contactModal');
                    showToast(data.message);
                } else showToast(data.message, false);
            });
        }

        // --- Navigation active state ---
        document.addEventListener('DOMContentLoaded', () => {
            const navLinks = document.querySelectorAll('.nav-link');
            const headerNavLinks = document.querySelectorAll('.header-nav a');
            const handleNavClick = event => {
                navLinks.forEach(link => link.classList.remove('active-sidebar'));
                headerNavLinks.forEach(link => link.classList.remove('active-nav'));
                const targetLink = event.currentTarget;
                if (targetLink.classList.contains('nav-link')) {
                    targetLink.classList.add('active-sidebar');
                    const linkText = targetLink.textContent.trim();
                    headerNavLinks.forEach(hLink => {
                        if (hLink.textContent.trim() === linkText) hLink.classList.add('active-nav');
                    });
                } else {
                    targetLink.classList.add('active-nav');
                    const linkText = targetLink.textContent.trim();
                    navLinks.forEach(sLink => {
                        if (sLink.textContent.trim().includes(linkText)) sLink.classList.add('active-sidebar');
                    });
                }
            };
            navLinks.forEach(l => l.addEventListener('click', handleNavClick));
            headerNavLinks.forEach(l => l.addEventListener('click', handleNavClick));
        });
    </script>

</body>

</html>