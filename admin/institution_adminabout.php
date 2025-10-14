<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University of Lagos - About</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS Reset and Variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #7f00ff; /* Purple for key elements */
            --primary-dark: #6a00d6;
            --light-bg: #ffffff;
            --main-bg: #f5f5f7; /* Off-white page background */
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
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--main-bg);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6 {
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
            position: relative;
        }

        /* Top Header (Navigation Bar) */
        .header {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 40px);
            max-width: 1400px;
            height: var(--header-height);
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 0 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 40px;
            padding-left: 270px;
            flex: 1;
        }

        .uni-brand {
            display: flex;
            align-items: center;
            min-width: 220px;
            position: relative;
            padding-right: 20px;
        }

        .uni-brand::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 24px;
            width: 1px;
            background-color: var(--border-color);
        }

        .uni-brand strong {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 600;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .header-nav {
            display: flex;
            gap: 5px;
            align-items: center;
            height: 100%;
        }

        .header-nav a {
            text-decoration: none;
            color: var(--text-dark);
            padding: 8px 16px;
            border-radius: 8px;
            transition: var(--transition-normal);
            font-weight: 500;
            position: relative;
            display: flex;
            align-items: center;
            height: 38px;
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
            bottom: -15px;
            left: 15px;
            right: 15px;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* Left Sidebar (Institution Info and Navigation) */
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

        .side-nav {
            padding: 0 5px;
        }

        .uni-profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--hover-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            overflow: hidden;
            border: 3px solid var(--primary-color);
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .uni-profile-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(127, 0, 255, 0.2);
        }

        .uni-profile-img i {
            font-size: 35px;
            color: var(--primary-color);
        }

        .uni-info {
            display: flex;
            flex-direction: column;
            align-items: center;
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
            margin-bottom: 10px;
            overflow: hidden;
            border: 2px solid var(--primary-color);
        }

        .uni-profile-img i {
            font-size: 30px;
            color: var(--text-light);
        }

        .uni-details p {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 2px;
        }
        
        .uni-details strong {
            display: block;
            margin-bottom: 5px;
        }

        .verified-icon {
            color: green;
            margin-right: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
            padding: 14px 18px;
            margin: 4px 0;
            border-radius: 12px;
            transition: var(--transition-normal);
            font-weight: 500;
            position: relative;
            overflow: hidden;
            font-size: 15px;
            letter-spacing: 0.2px;
        }

        .nav-link i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-normal);
        }

        .nav-link:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateX(5px);
            box-shadow: 0 4px 10px rgba(127, 0, 255, 0.08);
        }

        .nav-link:hover i {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .nav-link.active-sidebar {
            background-color: rgba(127, 0, 255, 0.1);
            color: var(--primary-color);
            font-weight: var(--font-bold);
            box-shadow: 0 4px 12px rgba(127, 0, 255, 0.12);
        }

        .nav-link.active-sidebar i {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .nav-link i {
            margin-right: 10px;
        }

        .nav-link:hover, .nav-link.active-sidebar {
            background-color: #f0e6ff; /* Very light purple */
            color: var(--primary-color);
        }
        
        .nav-section-title {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            margin: 25px 15px 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding-left: 12px;
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            padding: 80px 40px 40px 40px; /* Space for the fixed header */
            overflow-y: auto;
            max-height: 90vh; /* Max height for scrolling content */
        }

        .welcome-header {
            background-color: #f7f3ff; /* Lightest purple background */
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
            letter-spacing: -0.5px;
        }

        .welcome-header p {
            font-size: 16px;
            color: var(--text-dark);
            opacity: 0.9;
        }

        /* About Grid Layout */
        .about-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .left-column {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .right-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Content Blocks */
        .content-block {
            background-color: var(--light-bg);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            transition: var(--transition-normal);
        }

        .content-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .content-block h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 18px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            letter-spacing: -0.3px;
        }

        .content-block h2 i {
            color: var(--primary-color);
            margin-right: 10px;
            font-size: 24px;
        }

        .content-block p {
            font-size: 15px;
            line-height: 1.7;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .mission-vision-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .mission-vision-item strong {
            display: block;
            font-size: 16px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        /* Right Column Cards (Institution Stats and Contact) */
        .contact-card, .stats-card {
            background-color: var(--light-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .contact-card h3, .stats-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .contact-card h3 i, .stats-card h3 i {
            margin-right: 8px;
            font-size: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .contact-item i {
            color: var(--primary-color);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .contact-item span {
            color: #444;
        }
        
        .contact-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }

        /* Stats Grid */
        .stats-inner-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .stat-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #f0f0f0;
        }

        .stat-line:last-child {
            border-bottom: none;
        }

        .stat-line span {
            font-size: 14px;
            color: var(--text-light);
        }

        .stat-line strong {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 1440px) {
            .page-container,
            .header {
                max-width: 1300px;
            }
        }

        @media (max-width: 1200px) {
            .page-container,
            .header {
                max-width: 1100px;
            }
            .header-left {
                padding-left: 20px;
            }
            .sidebar {
                width: 280px;
            }
            .main-content {
                padding: 80px 30px 30px;
            }
        }

        @media (max-width: 992px) {
            .about-grid {
                grid-template-columns: 1fr;
            }
            .header-nav a {
                padding: 8px 15px;
            }
            .content-block {
                padding: 25px;
            }
        }

        @media (max-width: 768px) {
            .page-container,
            .header {
                margin: 10px;
                width: calc(100% - 20px);
            }
            .header-left {
                gap: 15px;
            }
            .uni-brand strong {
                display: none;
            }
            .header-nav {
                gap: 2px;
            }
            .sidebar {
                width: 250px;
            }
        }

        @media (max-width: 576px) {
            .page-container,
            .header {
                margin: 5px;
                width: calc(100% - 10px);
            }
            .main-content {
                padding: 80px 20px 20px;
            }
            .content-block {
                padding: 20px;
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
                    <strong style="font-size: 16px;">University of Lagos (UNILAG)</strong>
                    <p><i class="fa-solid fa-circle-check verified-icon"></i> Verified Institution</p>
                    <p><i class="fa-solid fa-location-dot"></i> Lagos State, Nigeria</p>
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
                <h1>Welcome, University of Lagos!</h1>
                <p>Verified Academic Institution. Lagos Nigeria</p>
            </div>

            <div class="about-grid">
                <div class="left-column">
                    <div class="content-block">
                        <h2><i class="fa-solid fa-book"></i> About Us</h2>
                        <p>Founded in 1995, University of Tech is a leading institution dedicated to excellence in education, research, and innovation. We pride ourselves on fostering a collaborative learning environment that prepares students for the challenges of the modern world.</p>
                        <p>Our mission is to advance knowledge through teaching, research, and service, while cultivating a diverse and inclusive community of scholars committed to making a positive impact on society.</p>
                    </div>

                    <div class="content-block">
                        <h2><i class="fa-solid fa-bullseye"></i> Mission & Vision</h2>
                        <div class="mission-vision-list">
                            <div class="mission-vision-item">
                                <strong>Mission:</strong>
                                <p>To provide transformative educational experiences that inspire innovation, critical thinking, and lifelong learning.</p>
                            </div>
                            <div class="mission-vision-item">
                                <strong>Vision:</strong>
                                <p>To provide transformative educational experiences that inspire innovation, critical thinking, and lifelong learning.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-block">
                        <h2><i class="fa-solid fa-map-location-dot"></i> Location & Contact</h2>
                        <div class="contact-item">
                            <i class="fa-solid fa-location-dot"></i> 
                            <span>**Address:** Akoka, Yaba, Lagos State, Nigeria.</span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-envelope"></i> 
                            <span>**Email:** <a href="mailto:info@universitytech.edu">info@universitytech.edu</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i> 
                            <span>**Phone:** +234 803 456 7890</span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-globe"></i> 
                            <span>**Website:** <a href="http://www.unilag.edu">www.unilag.edu</a></span>
                        </div>
                    </div>
                </div>

                <div class="right-column">
                    <div class="contact-card">
                        <h3><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                        <div class="contact-item">
                            <i class="fa-solid fa-phone"></i> 
                            <span>+234 803 456 7890</span>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-envelope"></i> 
                            <a href="mailto:info@universitytech.edu">info@universitytech.edu</a>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-globe"></i> 
                            <a href="http://www.unilag.edu">www.unilag.edu</a>
                        </div>
                        <div class="contact-item">
                            <i class="fa-solid fa-location-dot"></i> 
                            <span>Akoka, Yaba, Lagos State, Nigeria.</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <h3><i class="fa-solid fa-chart-simple"></i> Institution Stats</h3>
                        <div class="stats-inner-grid">
                            <div class="stat-line">
                                <span>Students</span>
                                <strong>12,000+</strong>
                            </div>
                            <div class="stat-line">
                                <span>Faculty</span>
                                <strong>150+</strong>
                            </div>
                            <div class="stat-line">
                                <span>Department</span>
                                <strong>42</strong>
                            </div>
                            <div class="stat-line">
                                <span>Campuses</span>
                                <strong>5</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('About page loaded.');

            // Navigation state management
            const navLinks = document.querySelectorAll('.nav-link');
            const headerNavLinks = document.querySelectorAll('.header-nav a');
            
            // Function to handle link clicks
            const handleNavClick = (event) => {
                // Don't prevent default - allow normal navigation
                const targetLink = event.currentTarget;

                // Clear active states in both side and header navs
                navLinks.forEach(link => link.classList.remove('active-sidebar'));
                headerNavLinks.forEach(link => link.classList.remove('active-nav'));

                // Set active state on the clicked link
                if (targetLink.classList.contains('nav-link')) {
                    targetLink.classList.add('active-sidebar');
                    // Find and set active state on corresponding header link
                    const linkText = targetLink.textContent.trim();
                    headerNavLinks.forEach(hLink => {
                        if (hLink.textContent.trim() === linkText) {
                            hLink.classList.add('active-nav');
                        }
                    });
                } else if (targetLink.parentElement.classList.contains('header-nav')) {
                    targetLink.classList.add('active-nav');
                    // Find and set active state on corresponding sidebar link
                    const linkText = targetLink.textContent.trim();
                    navLinks.forEach(sLink => {
                        if (sLink.textContent.trim().includes(linkText)) {
                            sLink.classList.add('active-sidebar');
                        }
                    });
                }
            };

            // Attach click listeners
            navLinks.forEach(link => link.addEventListener('click', handleNavClick));
            headerNavLinks.forEach(link => link.addEventListener('click', handleNavClick));

            // Content block hover effects
            const contentBlocks = document.querySelectorAll('.content-block');
            contentBlocks.forEach(block => {
                block.addEventListener('mouseover', () => {
                    block.style.transform = 'translateY(-3px)';
                    block.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
                });
                block.addEventListener('mouseout', () => {
                    block.style.transform = 'translateY(0)';
                    block.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.06)';
                });
            });

            // Header icons hover effects
            const headerIcons = document.querySelectorAll('.header-right i');
            headerIcons.forEach(icon => {
                icon.addEventListener('mouseover', () => {
                    icon.style.transform = 'translateY(-2px)';
                });
                icon.addEventListener('mouseout', () => {
                    icon.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>