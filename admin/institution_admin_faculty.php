<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution - Faculty</title>

    <link rel="stylesheet" href="../css/faculty-directory.css">
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        /* Button Styles */
        .btn-view,
        .btn-message {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-weight: 500;
        }

        .btn-view {
            background-color: #793DDC;
            color: white;
        }

        .btn-message {
            background-color: #f5f5f5;
            color: #333333;
            border: 1px solid #EEEEEE;
        }

        .btn-view:hover {
            background-color: #6a00d6;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-message:hover {
            background-color: #eeeeee;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-color: #793DDC;
            color: #793DDC;
        }

        .btn-view:active,
        .btn-message:active {
            transform: translateY(0);
            box-shadow: none;
        }

        :root {
            --primary-color: #793DDC;
            --primary-dark: #6a00d6;
            --background-color: #F8F7FF;
            --card-background: #FFFFFF;
            --text-color: #333333;
            --light-text: #666666;
            --border-color: #EEEEEE;
            --font-regular: 500;
            --font-medium: 600;
            --font-bold: 700;
            --header-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            --hover-bg: rgba(121, 61, 220, 0.08);
            --transition-normal: 0.3s ease;
            --font-primary: 'Segoe UI', system-ui, -apple-system, sans-serif;
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: var(--font-primary);
            margin: 0;
            padding: 20px;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
        }

        .container {
            width: 95%;
            max-width: 1600px;
            display: flex;
            background-color: var(--card-background);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            min-height: 100vh;
            margin: 0 auto;
        }

        /* Reusable Sidebar & Header Styles */
        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 20px;
            background-color: var(--card-background);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-title {
            font-size: 24px;
            font-weight: var(--font-bold);
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            border: none;
            background: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-normal);
            color: var(--text-color);
        }

        .header-icon:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .header-icon i {
            font-size: 20px;
        }

        .sidebar {
            width: 280px;
            padding: 25px;
            border-right: 1px solid var(--border-color);
            flex-shrink: 0;
            background-color: var(--card-background);
            height: 100vh;
            position: sticky;
            top: 0;
            left: 0;
            overflow-y: auto;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.05);
        }

        .institution-info {
            text-align: left;
            margin-bottom: 30px;
            padding: 15px;
            background: linear-gradient(to bottom, var(--hover-bg), transparent);
            border-radius: 12px;
        }

        .institution-info h2 {
            font-size: 17px;
            color: var(--text-color);
            margin: 0 0 8px 0;
            font-weight: var(--font-bold);
            letter-spacing: -0.3px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            margin-bottom: 8px;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 10px;
            font-weight: var(--font-regular);
            transition: all 0.3s ease;
            letter-spacing: 0.2px;
        }

        .nav-link:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            font-weight: var(--font-bold);
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.12);
        }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 0 40px 40px 40px;
            min-width: 0;
        }

        .dashboard-tabs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 30px;
            padding-top: 10px;
        }

        .tab-navigation {
            display: flex;
            gap: 25px;
        }

        .tab-button {
            padding: 10px 0;
            cursor: pointer;
            font-weight: 600;
            color: var(--light-text);
            border-bottom: 3px solid transparent;
            text-decoration: none;
            transition: var(--transition-normal);
        }

        .tab-button:hover {
            color: var(--primary-color);
            border-bottom-color: rgba(121, 61, 220, 0.3);
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        /* Faculty List Actions */
        .faculty-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0 30px 0;
            padding: 0 5px;
        }

        .search-bar {
            padding: 8px 15px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            width: 360px;
            transition: var(--transition-normal);
        }

        .search-bar:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(121, 61, 220, 0.1);
        }

        .search-bar i {
            color: var(--light-text);
            font-size: 14px;
        }

        .search-bar input {
            border: none;
            outline: none;
            margin-left: 10px;
            flex-grow: 1;
        }

        .add-faculty-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: var(--font-medium);
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-faculty-btn i {
            font-size: 16px;
        }

        .add-faculty-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(121, 61, 220, 0.3);
        }

        /* Faculty Table */
        .faculty-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: var(--card-background);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin: 0 auto;
        }

        .faculty-table tr:hover {
            background-color: var(--hover-bg);
        }

        .faculty-table tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .faculty-table tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .faculty-table th,
        .faculty-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .faculty-table th {
            background-color: #f8f9fc;
            color: var(--text-color);
            font-weight: var(--font-medium);
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
            padding: 16px 15px;
        }

        .faculty-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 5px;
            border-radius: 8px;
            transition: var(--transition-normal);
        }

        .faculty-profile:hover {
            background-color: var(--hover-bg);
        }

        .faculty-table td {
            font-weight: var(--font-regular);
            color: var(--text-color);
        }

        .profile-pic-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-normal);
        }

        .profile-pic-small i {
            font-size: 18px;
            color: var(--primary-color);
            opacity: 0.7;
        }

        tr:hover .profile-pic-small i {
            opacity: 1;
            transform: scale(1.1);
        }

        .faculty-name strong {
            display: block;
            color: var(--text-color);
            font-size: 14px;
        }

        .faculty-name span {
            color: var(--light-text);
            font-size: 12px;
        }

        .action-button {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            background: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-color);
            font-weight: var(--font-medium);
            transition: var(--transition-normal);
            margin-right: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-button i {
            font-size: 12px;
        }

        .action-button:hover {
            background-color: var(--hover-bg);
            color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(121, 61, 220, 0.1);
        }

        .status-tag {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-verified {
            background-color: #e8f5e9;
            color: #4caf50;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ff9800;
        }

        .status-tag i {
            font-size: 11px;
        }

        .header-section {
            padding: 0.75rem;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .header-inline {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-section h1 {
            font-size: 1.125rem;
            margin: 0;
            color: var(--text-color);
            font-weight: 600;
            white-space: nowrap;
        }

        .search-box {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 200px;
            max-width: 300px;
            background-color: #f5f5f5;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            font-size: 0.875rem;
            width: 100%;
            margin-left: 0.5rem;
        }

        .search-box i {
            color: #666;
            font-size: 0.875rem;
        }

        .add-faculty-btn-compact {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border: none;
            border-radius: 4px;
            background-color: var(--primary-color);
            color: white;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .add-faculty-btn-compact:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .add-faculty-btn-compact i {
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .header-inline {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .search-box {
                max-width: none;
                order: 3;
                width: 100%;
            }
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal {
            background: var(--card-background);
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: var(--font-bold);
            color: var(--text-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--light-text);
            padding: 5px;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: var(--font-medium);
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: var(--font-primary);
            font-size: 14px;
            transition: var(--transition-normal);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(121, 61, 220, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .modal-footer .btn-view,
        .modal-footer .btn-message {
            padding: 0.625rem 1.25rem;
            font-size: 0.9375rem;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            background: var(--card-background);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i {
            font-size: 20px;
        }

        .toast.success {
            border-left: 4px solid #4caf50;
        }

        .toast.success i {
            color: #4caf50;
        }
    </style>
    <!-- Modal Styles -->
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="institution-info">
                <h2>University of Lagos (UNILAG)</h2>
                <p style="font-size: 12px; color: #4CAF50; font-weight: bold;">✔️ verified institution</p>
                <p style="font-size: 12px; color: var(--light-text);">Lagos State, Nigeria</p>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <nav>
                <a href="institution_admindashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="institution_adminabout.php" class="nav-link"><i class="fa-solid fa-info-circle"></i> About</a>
                <a href="institution_admincommunity.php" class="nav-link"><i class="fa-solid fa-users"></i> Communities</a>
                <a href="institution_admin_faculty.php" class="nav-link active"><i class="fa-solid fa-building"></i> Faculty</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <div class="header-title">Faculty Management</div>

            </div>

            <div class="dashboard-tabs">
                <div class="tab-navigation">
                    <a href="institution_admindashboard.php" class="tab-button">Dashboard</a>
                    <a href="institution_adminabout.php" class="tab-button">About</a>
                    <a href="institution_admincommunity.php" class="tab-button">Communities</a>
                    <a href="institution_admin_faculty.php" class="tab-button active">Faculty</a>
                </div>
                <div class="header-actions">
                    <button class="header-icon"><i class="fa-regular fa-bell"></i></button>
                    <button class="header-icon"><i class="fa-solid fa-gear"></i></button>
                </div>
            </div>

            <header class="header-section">
                <div class="header-inline">
                    <h1>Faculty & Staff Directory</h1>
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="facultySearch" placeholder="Search faculty members...">
                    </div>
                    <button class="add-faculty-btn-compact" onclick="openModal('addFacultyModal')">
                        <i class="fa-solid fa-plus"></i> Add Faculty Member
                    </button>
                </div>
            </header>
            <div class="directory-table" id="directoryTable">
                <div class="table-header">
                    <div>NAME</div>
                    <div>ROLE</div>
                    <div>DEPARTMENT</div>
                    <div>CONTACT</div>
                    <div>STATUS</div>
                    <div>ACTIONS</div>
                </div>

                <div class="table-row" data-search-terms="Dr. Adekunle Alabi Senior Lecturer Computer Science">
                    <div class="table-cell table-cell-name">
                        <strong>Dr. Adekunle Alabi</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Senior Lecturer
                    </div>
                    <div class="table-cell table-cell-dept">
                        Computer Science
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> adekunle@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Computer Science Dept</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Dr. Adekunle Alabi')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Dr. Adekunle Alabi')">Message</button>
                        </div>
                    </div>
                </div>

                <div class="table-row" data-search-terms="Dr. Sarah Okonjo Associate Professor Engineering">
                    <div class="table-cell table-cell-name">
                        <strong>Dr. Sarah Okonjo</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Associate Professor
                    </div>
                    <div class="table-cell table-cell-dept">
                        Engineering
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> s.okonjo@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Engineering Block B</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Dr. Sarah Okonjo')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Dr. Sarah Okonjo')">Message</button>
                        </div>
                    </div>
                </div>

                <div class="table-row" data-search-terms="Dr. Mohammed Ibrahim Chemistry Department Research Director">
                    <div class="table-cell table-cell-name">
                        <strong>Dr. Mohammed Ibrahim</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Research Director
                    </div>
                    <div class="table-cell table-cell-dept">
                        Chemistry
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> m.ibrahim@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Science Complex</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Dr. Mohammed Ibrahim')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Dr. Mohammed Ibrahim')">Message</button>
                        </div>
                    </div>
                </div>

                <div class="table-row" data-search-terms="Dr. Chioma Nwosu Medicine Clinical Professor Medical Sciences">
                    <div class="table-cell table-cell-name">
                        <strong>Dr. Chioma Nwosu</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Clinical Professor
                    </div>
                    <div class="table-cell table-cell-dept">
                        Medical Sciences
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> c.nwosu@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Medical College</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Dr. Chioma Nwosu')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Dr. Chioma Nwosu')">Message</button>
                        </div>
                    </div>
                </div>

                <div class="table-row" data-search-terms="Prof. Uche Okoro Head of Department Faculty of Arts">
                    <div class="table-cell table-cell-name">
                        <strong>Prof. Uche Okoro</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Head of Department
                    </div>
                    <div class="table-cell table-cell-dept">
                        Faculty of Arts
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> uche.okoro@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Arts Faculty Building</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Prof. Uche Okoro')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Prof. Uche Okoro')">Message</button>
                        </div>
                    </div>
                </div>

                <div class="table-row" data-search-terms="Mrs. Funmi Davies Admin Officer Admin Staff">
                    <div class="table-cell table-cell-name">
                        <strong>Mrs. Funmi Davies</strong>
                    </div>
                    <div class="table-cell table-cell-role">
                        Admin Officer
                    </div>
                    <div class="table-cell table-cell-dept">
                        Admin Staff
                    </div>
                    <div class="table-cell table-cell-contact">
                        <div>
                            <span class="contact-item"><i class="fa-solid fa-envelope"></i> funmi.d@unilag.edu.ng</span>
                            <span class="contact-item"><i class="fa-solid fa-location-dot"></i> Admin Block</span>
                        </div>
                    </div>
                    <div class="table-cell table-cell-status">
                        <span class="status-badge"><i class="fa-solid fa-circle"></i> Active</span>
                    </div>
                    <div class="table-cell table-cell-actions">
                        <div class="action-buttons">
                            <button class="btn-view" onclick="action('View', 'Mrs. Funmi Davies')">View</button>
                            <button class="btn-message" onclick="action('Message', 'Mrs. Funmi Davies')">Message</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Faculty Modal -->
    <div class="modal-overlay" id="addFacultyModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Add New Faculty Member</div>
                <button class="modal-close" onclick="closeModal('addFacultyModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="addFacultyForm">
                    <div class="form-group">
                        <label for="facultyName">Full Name</label>
                        <input type="text" class="form-control" id="facultyName" required>
                    </div>
                    <div class="form-group">
                        <label for="facultyEmail">Email Address</label>
                        <input type="email" class="form-control" id="facultyEmail" required>
                    </div>
                    <div class="form-group">
                        <label for="facultyDepartment">Department</label>
                        <input type="text" class="form-control" id="facultyDepartment" required>
                    </div>
                    <div class="form-group">
                        <label for="facultyRole">Role</label>
                        <select class="form-control" id="facultyRole" required>
                            <option value="">Select Role</option>
                            <option value="Lecturer">Lecturer</option>
                            <option value="Senior Lecturer">Senior Lecturer</option>
                            <option value="Head of Department">Head of Department</option>
                            <option value="Professor">Professor</option>
                            <option value="Admin Officer">Admin Officer</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="action-button" onclick="closeModal('addFacultyModal')">Cancel</button>
                <button class="add-faculty-btn" onclick="submitAddFaculty()">Add Faculty</button>
            </div>
        </div>
    </div>

    <!-- View Profile Modal -->
    <div class="modal-overlay" id="viewProfileModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Faculty Profile</div>
                <button class="modal-close" onclick="closeModal('viewProfileModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body" id="profileModalContent">
                <!-- Content will be dynamically inserted -->
            </div>
            <div class="modal-footer">
                <button class="action-button" onclick="closeModal('viewProfileModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal-overlay" id="editRoleModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Edit Faculty Role</div>
                <button class="modal-close" onclick="closeModal('editRoleModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editRoleForm">
                    <div class="form-group">
                        <label for="editFacultyRole">Select New Role</label>
                        <select class="form-control" id="editFacultyRole" required>
                            <option value="">Select Role</option>
                            <option value="lecturer">Lecturer</option>
                            <option value="senior_lecturer">Senior Lecturer</option>
                            <option value="hod">Head of Department</option>
                            <option value="professor">Professor</option>
                            <option value="admin">Admin Staff</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="action-button" onclick="closeModal('editRoleModal')">Cancel</button>
                <button class="add-faculty-btn" onclick="saveRoleChange()">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        // Action Handler Function
        function action(type, facultyName) {
            const facultyRow = document.querySelector(`[data-search-terms*="${facultyName}"]`);
            const facultyData = {
                name: facultyName,
                email: facultyRow.querySelector('.contact-item:first-child').textContent.trim(),
                role: facultyRow.querySelector('.table-cell-role').textContent.trim(),
                department: facultyRow.querySelector('.table-cell-dept').textContent.trim(),
                location: facultyRow.querySelector('.contact-item:last-child').textContent.trim(),
                status: facultyRow.querySelector('.status-badge').textContent.trim()
            };

            switch (type) {
                case 'View':
                    viewProfile(facultyData);
                    break;
                case 'Message':
                    // Create and show messaging modal
                    const messageModal = document.createElement('div');
                    messageModal.className = 'modal-overlay';
                    messageModal.id = 'messageModal';
                    messageModal.innerHTML = `
                        <div class="modal">
                            <div class="modal-header">
                                <div class="modal-title">Message ${facultyData.name}</div>
                                <button class="modal-close" onclick="closeModal('messageModal')">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="messageSubject">Subject</label>
                                    <input type="text" class="form-control" id="messageSubject" placeholder="Enter message subject">
                                </div>
                                <div class="form-group">
                                    <label for="messageContent">Message</label>
                                    <textarea class="form-control" id="messageContent" rows="4" placeholder="Type your message here..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn-message" onclick="closeModal('messageModal')">Cancel</button>
                                <button class="btn-view" onclick="sendMessage('${facultyData.email}')">Send Message</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(messageModal);
                    openModal('messageModal');
                    break;
            }
        }

        // Send Message Function
        function sendMessage(email) {
            const subject = document.getElementById('messageSubject').value;
            const content = document.getElementById('messageContent').value;

            if (!subject || !content) {
                showToast('Please fill in both subject and message');
                return;
            }

            // Here you would typically send the message to your backend
            showToast('Message sent successfully!');
            closeModal('messageModal');

            // Remove the temporary modal from DOM after closing
            setTimeout(() => {
                const messageModal = document.getElementById('messageModal');
                if (messageModal) {
                    messageModal.remove();
                }
            }, 300);
        }

        // Modal Management
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'toast success';
            toast.innerHTML = `
                <i class="fa-solid fa-check-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Add Faculty Functions
        document.querySelector('.add-faculty-btn').addEventListener('click', function() {
            openModal('addFacultyModal');
        });

        function submitAddFaculty() {
            const form = document.getElementById('addFacultyForm');
            if (form.checkValidity()) {
                const name = document.getElementById('facultyName').value;
                const email = document.getElementById('facultyEmail').value;
                const department = document.getElementById('facultyDepartment').value;
                const role = document.getElementById('facultyRole').value;

                // Generate random background color for profile pic
                const colors = ['#f0e6ff', '#c8e6c9', '#ffcdd2'];
                const randomColor = colors[Math.floor(Math.random() * colors.length)];

                const newRow = `
                    <tr>
                        <td>
                            <div class="faculty-profile">
                                <div class="profile-pic-small" style="background-color: ${randomColor};">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="faculty-name">
                                    <strong>${name}</strong>
                                    <span>${email}</span>
                                </div>
                            </div>
                        </td>
                        <td>${department}</td>
                        <td>${role}</td>
                        <td><span class="status-tag status-pending"><i class="fa-solid fa-clock"></i> Pending</span></td>
                        <td>
                            <button class="action-button"><i class="fa-solid fa-paper-plane"></i> Send Invite</button>
                            <button class="action-button"><i class="fa-solid fa-user-xmark"></i> Remove</button>
                        </td>
                    </tr>
                `;

                document.querySelector('.faculty-table tbody').insertAdjacentHTML('beforeend', newRow);

                // Add event listeners to new row
                const newTr = document.querySelector('.faculty-table tbody tr:last-child');
                setupRowEventListeners(newTr);

                closeModal('addFacultyModal');
                form.reset();
                showToast('Faculty member added successfully!');
            } else {
                form.reportValidity();
            }
        }

        function setupRowEventListeners(row) {
            const facultyData = {
                element: row,
                name: row.querySelector('.faculty-name strong').textContent,
                email: row.querySelector('.faculty-name span').textContent,
                department: row.querySelector('td:nth-child(2)').textContent,
                role: row.querySelector('td:nth-child(3)').textContent,
                status: row.querySelector('.status-tag').textContent.trim(),
                bgColor: row.querySelector('.profile-pic-small').getAttribute('style')
            };

            row.querySelector('.faculty-profile').addEventListener('click', () => viewProfile(facultyData));

            row.querySelectorAll('.action-button').forEach(button => {
                button.addEventListener('click', () => {
                    const action = button.textContent.trim();
                    switch (action) {
                        case 'Send Invite':
                            sendInvite(facultyData);
                            break;
                        case 'Remove':
                            removeFaculty(facultyData);
                            break;
                    }
                });
            });
        }

        // Store current faculty member being edited
        let currentFaculty = null;

        // View Profile Handler
        function viewProfile(facultyData) {
            const content = document.getElementById('profileModalContent');
            content.innerHTML = `
                <div class="faculty-profile" style="margin-bottom: 20px;">
                    <div class="profile-pic-small" style="width: 60px; height: 60px; ${facultyData.bgColor}">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 18px;">${facultyData.name}</h3>
                        <p style="margin: 5px 0; color: var(--light-text);">${facultyData.email}</p>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Department:</strong> ${facultyData.department}
                </div>
                <div style="margin-bottom: 15px;">
                    <strong>Role:</strong> ${facultyData.role}
                </div>
                <div>
                    <strong>Status:</strong> 
                    <span class="status-tag ${facultyData.status === 'Verified' ? 'status-verified' : 'status-pending'}">
                        ${facultyData.status}
                    </span>
                </div>
            `;
            openModal('viewProfileModal');
        }

        // Edit Role Handler
        function editRole(faculty) {
            currentFaculty = faculty;
            document.getElementById('editFacultyRole').value = faculty.role.toLowerCase().replace(' ', '_');
            openModal('editRoleModal');
        }

        // Save Role Change
        function saveRoleChange() {
            if (!currentFaculty) return;

            const newRole = document.getElementById('editFacultyRole').value;
            if (!newRole) {
                alert('Please select a role');
                return;
            }

            // Update the role in the table
            const row = currentFaculty.element.closest('tr');
            const roleCell = row.querySelector('td:nth-child(3)');
            roleCell.textContent = newRole.replace('_', ' ').split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');

            closeModal('editRoleModal');
            showToast(`Role updated for ${currentFaculty.name}`);
            currentFaculty = null;
        }

        // Send Invite Handler
        function sendInvite(faculty) {
            // Simulate sending invite
            setTimeout(() => {
                showToast(`Invite sent to ${faculty.name}`);
            }, 500);
        }

        // Remove Faculty Handler
        function removeFaculty(faculty) {
            if (confirm(`Are you sure you want to remove ${faculty.name}?`)) {
                const row = faculty.element.closest('tr');
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    row.remove();
                    showToast(`${faculty.name} has been removed`);
                }, 300);
            }
        }

        // Add click handlers to all buttons
        document.querySelectorAll('.faculty-table tbody tr').forEach(row => {
            const facultyData = {
                element: row,
                name: row.querySelector('.faculty-name strong').textContent,
                email: row.querySelector('.faculty-name span').textContent,
                department: row.querySelector('td:nth-child(2)').textContent,
                role: row.querySelector('td:nth-child(3)').textContent,
                status: row.querySelector('.status-tag').textContent.trim(),
                bgColor: row.querySelector('.profile-pic-small').getAttribute('style')
            };

            // Add click handlers for profile name and picture
            row.querySelector('.faculty-profile').addEventListener('click', () => viewProfile(facultyData));

            // Add click handlers for action buttons
            const buttons = row.querySelectorAll('.action-button');
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    const action = button.textContent.trim();
                    switch (action) {
                        case 'View Profile':
                            viewProfile(facultyData);
                            break;
                        case 'Edit Role':
                            editRole(facultyData);
                            break;
                        case 'Send Invite':
                            sendInvite(facultyData);
                            break;
                        case 'Remove':
                            removeFaculty(facultyData);
                            break;
                    }
                });
            });
        });

        // Make faculty profiles clickable
        document.querySelectorAll('.faculty-profile').forEach(profile => {
            profile.style.cursor = 'pointer';
        });
    </script>

    <script>
        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        const facultyRows = document.querySelectorAll('.faculty-table tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            facultyRows.forEach(row => {
                const name = row.querySelector('.faculty-name strong').textContent.toLowerCase();
                const email = row.querySelector('.faculty-name span').textContent.toLowerCase();
                const department = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const role = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

                const matches = name.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    department.includes(searchTerm) ||
                    role.includes(searchTerm);

                row.style.display = matches ? '' : 'none';
            });
        });

        // Action buttons functionality
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim();
                const facultyName = this.closest('tr').querySelector('.faculty-name strong').textContent;

                switch (action) {
                    case 'View Profile':
                        showNotification(`Viewing profile of ${facultyName}`);
                        // Add your view profile logic here
                        break;
                    case 'Edit Role':
                        showNotification(`Editing role for ${facultyName}`);
                        // Add your edit role logic here
                        break;
                    case 'Send Invite':
                        showNotification(`Sending invite to ${facultyName}`);
                        // Add your send invite logic here
                        break;
                    case 'Remove':
                        if (confirm(`Are you sure you want to remove ${facultyName}?`)) {
                            showNotification(`Removing ${facultyName} from faculty list`);
                            // Add your remove logic here
                        }
                        break;
                }
            });
        });

        // Add Faculty button functionality
        document.querySelector('.add-faculty-btn').addEventListener('click', function() {
            showNotification('Opening Add Faculty form...');
            // Add your add faculty logic here
        });

        // Notification system
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <i class="fa-solid fa-info-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);

            // Add styles dynamically
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.backgroundColor = 'var(--primary-color)';
            notification.style.color = 'white';
            notification.style.padding = '12px 24px';
            notification.style.borderRadius = '8px';
            notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            notification.style.display = 'flex';
            notification.style.alignItems = 'center';
            notification.style.gap = '8px';
            notification.style.zIndex = '1000';
            notification.style.transform = 'translateY(100px)';
            notification.style.opacity = '0';
            notification.style.transition = 'all 0.3s ease';

            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateY(0)';
                notification.style.opacity = '1';
            }, 10);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateY(100px)';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Header icons functionality
        document.querySelectorAll('.header-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const isNotification = this.querySelector('.fa-bell');
                const isSettings = this.querySelector('.fa-gear');

                if (isNotification) {
                    showNotification('Opening notifications panel...');
                    // Add your notifications logic here
                }
                if (isSettings) {
                    showNotification('Opening settings panel...');
                    // Add your settings logic here
                }
            });
        });
    </script>
</body>

</html>