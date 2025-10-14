
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings & Privacy - ScholarThreads</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <style>
        :root {
            --primary-bg: #f0f2f5;
            --card-bg: #ffffff;
            --header-icon-size: 28px;
            --text-primary: #1c1e21;
            --text-secondary: #65676B;
            --border-color: #E4E6EB;
            --hover-bg: #F2F3F5;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --accent-color: #5E17EB;
            --accent-hover: #4912c0;
            --accent-light: rgba(94, 23, 235, 0.1);
            --transition-speed: 0.2s;
            --input-bg: #F0F2F5;
            --input-focus-bg: #E4E6EB;
            --icon-size: 20px;
            --border-radius: 16px;
            --spacing-sm: 12px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
        }

        .settings-wrapper {
            margin-left: 100px;
            padding: 25px 20px;
            background: var(--primary-bg);
            min-height: 200vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .settings-container {
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 5px var(--shadow-color), 
                       0 8px 25px -8px var(--shadow-color);
            overflow: hidden;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .settings-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px var(--shadow-color), 
                       0 12px 30px -10px var(--shadow-color);
        }

        .settings-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg) 0;
            margin-bottom: 0;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .settings-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.2px;
        }

        .search-area {
            padding: 24px 24px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .search-area::before {
            content: '\f002';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 40px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 14px;
            z-index: 1;
        }

        .search-input {
            width: 100%;
            padding: 16px 18px 16px 40px;
            background: var(--input-bg);
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 15px;
            color: var(--text-primary);
            transition: all var(--transition-speed) ease;
        }

        .search-input:focus {
            outline: none;
            background: var(--input-focus-bg);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px var(--accent-light);
        }

        .search-input:hover {
            background: var(--input-focus-bg);
        }

        .search-input::placeholder {
            color: var(--text-secondary);
            font-weight: 400;
        }

        .settings-list {
            list-style: none;
            padding: var(--spacing-lg) 0;
            margin: 0;
            background: var(--card-bg);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .setting-item {
            margin: 6px 12px;
            border-radius: 10px;
            transition: background-color var(--transition-speed) ease;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 10px;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .setting-link:hover {
            background: var(--accent-light);
            transform: translateX(6px);
        }

        .setting-link:hover .setting-item-content i,
        .setting-link:hover .setting-item-content span.text,
        .setting-link:hover .arrow {
            color: var(--accent-color);
        }

        .setting-link.active {
            background: var(--accent-light);
        }

        .setting-link.active .setting-item-content i,
        .setting-link.active span.text,
        .setting-link.active .arrow {
            color: var(--accent-color);
        }

        .setting-link:active {
            transform: translateX(6px) scale(0.98);
        }

        .setting-item-content {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .setting-item-content i {
            font-size: 20px;
            color: var(--text-secondary);
            width: 24px;
            text-align: center;
            transition: color var(--transition-speed) ease;
        }

        .setting-link:hover .setting-item-content i {
            color: var(--accent-color);
        }

        .setting-item-content span.text {
            font-size: 15px;
            font-weight: 500;
            transition: color var(--transition-speed) ease;
        }

        .setting-link:hover .setting-item-content span.text {
            color: var(--accent-color);
        }

        .arrow {
            color: var(--text-secondary);
            font-size: 14px;
            opacity: 0.7;
            transition: transform var(--transition-speed) ease;
        }

        .setting-link:hover .arrow {
            transform: translateX(4px);
            color: var(--accent-color);
            opacity: 1;
        }

        @media (max-width: 768px) {
            .settings-wrapper {
                margin-left: 0;
                padding: 16px 12px;
            }

            .settings-container {
                border-radius: 16px;
                margin: 0 8px;
            }

            .settings-header {
                padding: 24px 0;
            }

            .settings-header h1 {
                font-size: 22px;
            }

            .search-area {
                padding: 16px 20px;
            }

            .setting-link {
                padding: 14px 16px;
            }

            .setting-link:hover {
                transform: none;
                background: var(--hover-bg);
            }

            .setting-item {
                margin: 2px 8px;
            }
        }

        @media (min-width: 1200px) {
            .settings-container {
                max-width: 500px;
            }
        }
            body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
            padding: 0;
            background: var(--primary-bg);
        }
    </style>
</head>
<body>
   

    <div class="settings-wrapper">
        <div class="settings-container">
            <div class="settings-header">
                <div class="header-content">
                    <h1>Settings & Privacy</h1>
                </div>
            </div>

            

            <div class="search-area">
                <input type="text" class="search-input" placeholder="Search settings" />
            </div>

            <ul class="settings-list">
                <li class="setting-item">
                    <a href="./settings_account.php" class="setting-link">
                        <div class="setting-item-content">
                            <i class="fas fa-user"></i>
                            <span class="text">Account</span>
                        </div>
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </li>
                <li class="setting-item">
                    <a href="./settings_privacy.php" class="setting-link">
                        <div class="setting-item-content">
                            <i class="fas fa-lock"></i>
                            <span class="text">Privacy</span>
                        </div>
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </li>
                <li class="setting-item">
                    <a href="./setting_privacy.php" class="setting-link">
                        <div class="setting-item-content">
                            <i class="fas fa-bell"></i>
                            <span class="text">Notifications</span>
                        </div>
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </li>
                <li class="setting-item">
                    <a href="./setting_profileinfo.php" class="setting-link">
                        <div class="setting-item-content">
                            <i class="fas fa-pen"></i>
                            <span class="text">Profile Info</span>
                        </div>
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <script src="JS/jquery-3.6.0.min.js"></script>
    <script src="JS/bootstrap.bundle.min.js"></script>
</body>
</html>
