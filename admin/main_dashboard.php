<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.css">
  <style>
    :root {
      --sidebar-width: 220px;
      --accent: #4f46e5;
      --accent-2: #7c5cff;
      --bg: #f6f8ff;
      --card: #ffffff;
      --muted: #6b6f90;
      --border: #eef1ff;
      --shadow: 0 8px 24px rgba(31, 37, 86, 0.06);
    }

    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
          .nav-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;color:var(--muted);font-weight:600;transition:all 160ms ease;text-decoration:none}
      background: var(--bg);
      color: #17203b
    }

    a {
      color: inherit
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh
    }

    /* Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      background: linear-gradient(180deg, #fff, #fbfcff);
      padding: 20px;
      border-right: 1px solid var(--border)
    }

    .logo {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--accent);
      padding: 4px 18px 18px
    }

    .nav-menu {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 6px 8px
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 10px;
      color: var(--muted);
      font-weight: 600;
      transition: all 160ms ease
    }

    .nav-item i {
      width: 20px;
      text-align: center;
      color: var(--accent)
    }

    .nav-item:hover {
      background: #f3f6ff;
      color: var(--accent)
    }

    .nav-item.active {
      background: linear-gradient(90deg, rgba(79, 70, 229, 0.06), rgba(124, 92, 255, 0.02));
      color: var(--accent)
    }

    .nav-separator {
      height: 1px;
      background: var(--border);
      margin: 8px 12px
    }

    /* Main */
    .main-content {
      flex: 1;
      padding: 22px 28px
    }

    .top-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px
    }

    .search-box {
      display: flex;
      align-items: center;
      background: var(--card);
      padding: 8px 12px;
      border-radius: 12px;
      box-shadow: var(--shadow);
      min-width: 320px
    }

    .search-box input {
      border: 0;
      background: transparent;
      outline: none;
      padding: 6px;
      font-size: 14px;
      color: #223
    }

    .search-icon {
      background: none;
      border: 0;
      color: var(--muted);
      cursor: pointer
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px
    }

    .user-name {
      font-weight: 600;
      color: #17203b
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      box-shadow: 0 6px 18px rgba(31, 37, 86, 0.08)
    }

    /* Stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin: 20px 0
    }

    .stat-card {
      padding: 20px;
      border-radius: 12px;
      color: #fff;
      box-shadow: var(--shadow);
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .stat-card.community {
      background: linear-gradient(90deg, #6d5dd3, #8a6bff)
    }

    .stat-card.users {
      background: linear-gradient(90deg, #3b82f6, #60a5ff)
    }

    .stat-card.posts {
      background: linear-gradient(90deg, #34d399, #6ee7b7)
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 800
    }

    .stat-label {
      font-size: 14px;
      opacity: 0.95
    }

    /* Panels */
    .activity-management-container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 24px
    }

    .panel {
      background: var(--card);
      padding: 18px;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(31, 37, 86, 0.04)
    }

    .panel h2 {
      margin: 0 0 12px 0;
      font-size: 1.125rem;
      color: var(--accent);
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border)
    }

    .activity-list {
      display: flex;
      flex-direction: column;
      gap: 12px
    }

    .activity-item {
      padding-bottom: 8px;
      border-bottom: 1px solid #f4f6ff
    }

    .activity-item p {
      margin: 0;
      color: #222
    }

    .activity-item strong {
      color: var(--accent)
    }

    .activity-date {
      display: block;
      font-size: 12px;
      color: var(--muted);
      margin-top: 6px
    }

    .tool-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 10px
    }

    .tool-list li {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #2b2f49
    }

    .tool-list .icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 6px;
      background: linear-gradient(90deg, rgba(79, 70, 229, 0.08), rgba(124, 92, 255, 0.02));
      color: var(--accent)
    }

    /* Checkbox */
    .checkbox-container {
      position: relative;
      padding-left: 34px;
      cursor: pointer
    }

    .checkbox-container input {
      position: absolute;
      opacity: 0;
      left: 0;
      top: 0;
      width: 18px;
      height: 18px
    }

    .checkmark {
      position: absolute;
      left: 0;
      top: 2px;
      width: 18px;
      height: 18px;
      border-radius: 4px;
      background: #f3f4f8;
      border: 1px solid var(--border)
    }

    .checkbox-container input:checked~.checkmark {
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      border-color: transparent
    }

    .checkmark:after {
      content: '';
      position: absolute;
      left: 5px;
      top: 2px;
      width: 5px;
      height: 9px;
      border: 2px solid #fff;
      border-left: 0;
      border-top: 0;
      transform: rotate(40deg);
      display: none
    }

    .checkbox-container input:checked~.checkmark:after {
      display: block
    }

    /* Responsive */
    @media(max-width:960px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr)
      }

      .activity-management-container {
        grid-template-columns: 1fr
      }
    }

    @media(max-width:560px) {
      .stats-grid {
        grid-template-columns: 1fr
      }

      .dashboard-container {
        flex-direction: column
      }

      .sidebar {
        width: 100%;
        display: flex;
        flex-direction: row;
        gap: 12px;
        overflow: auto;
        padding: 10px
      }
    }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="logo">Admin</div>
      <nav class="nav-menu">
        <a href="#" class="nav-item active">
          <span class="icon">📊</span> Overview
        </a>
        <a href="#" class="nav-item">
          <span class="icon">👤</span> Users
        </a>
        <a href="#" class="nav-item">
          <span class="icon">👥</span> Communities
        </a>
        <div class="nav-separator"></div>
        <a href="#" class="nav-item">
          <span class="icon">✍️</span> Post
        </a>
        <a href="#" class="nav-item">
          <span class="icon">📢</span> Announcement
        </a>
        <a href="#" class="nav-item">
          <span class="icon">📅</span> Event
        </a>
        <div class="nav-separator"></div>
        <a href="#" class="nav-item">
          <span class="icon">⚙️</span> Settings
        </a>
      </nav>
    </aside>

    <main class="main-content">
      <header class="top-header">
        <div class="search-box">
          <input type="text" placeholder="Search...">
          <button class="search-icon">🔍</button>
        </div>
        <div class="user-profile">
          <span class="user-name">Admin</span>
          <img src="https://via.placeholder.com/40x40?text=A" alt="Admin Avatar" class="avatar">
        </div>
      </header>

      <section class="stats-grid">
        <div class="stat-card community">
          <div class="stat-value">580</div>
          <div class="stat-label">Communities <span class="trend">▲</span></div>
        </div>
        <div class="stat-card users">
          <div class="stat-value">2,010</div>
          <div class="stat-label">Users</div>
        </div>
        <div class="stat-card posts">
          <div class="stat-value">3,000</div>
          <div class="stat-label">Posts</div>
        </div>
      </section>

      <section class="activity-management-container">
        <div class="panel activity-panel">
          <h2>Recent Activity</h2>
          <div class="activity-list">
            <div class="activity-item">
              <p><strong>User JohnDoe</strong> joined the community.</p>
              <span class="activity-date">June 4, 2025</span>
            </div>
            <div class="activity-item highlight">
              <p><strong>Post why AI is important</strong> was published.</p>
              <span class="activity-date">June 4, 2025</span>
            </div>
            <div class="activity-item">
              <p><strong>User JaneDoe</strong> joined the community.</p>
              <span class="activity-date">June 4, 2025</span>
            </div>
            <div class="activity-item highlight">
              <p><strong>Community Computer science</strong> was created.</p>
              <span class="activity-date">June 4, 2025</span>
            </div>
          </div>
        </div>

        <div class="panel tools-panel">
          <h2>Management Tools</h2>
          <ul class="tool-list">
            <li><span class="icon">👤</span> Users</li>
            <li>
              <label class="checkbox-container">
                Communities
                <input type="checkbox">
                <span class="checkmark"></span>
              </label>
            </li>
            <li><span class="icon">✍️</span> Post</li>
            <li><span class="icon">📢</span> Announcement</li>
            <li><span class="icon">📅</span> Events</li>
            <li><span class="icon">📋</span> Reports</li>
          </ul>
        </div>
      </section>
    </main>
  </div>

  <script>
    // No JavaScript is strictly necessary for this static layout, 
    // but you could add interactions like toggling navigation or handling checkbox state here.
  </script>
</body>

</html>