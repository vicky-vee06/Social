<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../inc/config.php'; // Include database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institution - Faculty</title>

    <link rel="stylesheet" href="../css/faculty-directory.css">
    <link rel="stylesheet" href="../fontawesome-free-6.7.2-web/css/all.min.css">
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

            <table class="faculty-table">
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>ROLE</th>
                        <th>DEPARTMENT</th>
                        <th>CONTACT</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="facultyTableBody">
                    <?php
                    $query = "
                        SELECT u.id, u.name, u.email, u.department, u.role
                        FROM users u
                        LEFT JOIN student_institutions si ON u.id = si.student_id
                        WHERE u.role IS NOT NULL AND (si.name = 'University of Lagos' OR u.institution = 'University of Lagos')
                    ";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        $name = htmlspecialchars($row['name']);
                        $email = htmlspecialchars($row['email']);
                        $department = htmlspecialchars($row['department'] ?? 'N/A');
                        $role = htmlspecialchars($row['role'] ?? 'N/A');
                        $colors = ['#f0e6ff', '#c8e6c9', '#ffcdd2'];
                        $randomColor = $colors[array_rand($colors)];
                    ?>
                        <tr data-email="<?php echo $email; ?>" data-search-terms="<?php echo strtolower("$name $email $department $role"); ?>">
                            <td>
                                <div class="faculty-profile">
                                    <div class="profile-pic-small" style="background-color: <?php echo $randomColor; ?>;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div class="faculty-name">
                                        <strong><?php echo $name; ?></strong>
                                        <span><?php echo $email; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $role; ?></td>
                            <td><?php echo $department; ?></td>
                            <td>
                                <div>
                                    <span class="contact-item"><i class="fa-solid fa-envelope"></i> <?php echo $email; ?></span>
                                    <span class="contact-item"><i class="fa-solid fa-location-dot"></i> <?php echo $department; ?> Dept</span>
                                </div>
                            </td>
                            <td><span class="status-tag status-pending"><i class="fa-solid fa-clock"></i> Pending</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-view" onclick="action('View', '<?php echo addslashes($name); ?>', '<?php echo addslashes($email); ?>')">View</button>
                                    <button class="btn-message" onclick="action('Message', '<?php echo addslashes($name); ?>', '<?php echo addslashes($email); ?>')">Message</button>
                                    <button class="action-button" onclick="action('Remove', '<?php echo addslashes($name); ?>', '<?php echo addslashes($email); ?>')">Remove</button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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
                        <label for="facultyUsername">Username</label>
                        <input type="text" class="form-control" id="facultyUsername" required>
                    </div>
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
            <div class="modal-body" id="profileModalContent"></div>
            <div class="modal-footer">
                <button class="action-button" onclick="closeModal('viewProfileModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Modal Management
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function showToast(message, isSuccess = true) {
            const toast = document.createElement('div');
            toast.className = `toast ${isSuccess ? 'success' : 'error'}`;
            toast.innerHTML = `
                <i class="fa-solid ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Search Functionality
        document.getElementById('facultySearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#facultyTableBody tr');
            rows.forEach(row => {
                const searchTerms = row.getAttribute('data-search-terms').toLowerCase();
                row.style.display = searchTerms.includes(searchTerm) ? '' : 'none';
            });
        });

        // Action Handler
        function action(type, facultyName, facultyEmail) {
            const row = document.querySelector(`[data-email="${facultyEmail}"]`);
            if (!row) return;

            const facultyData = {
                name: facultyName,
                email: facultyEmail,
                role: row.querySelector('td:nth-child(2)').textContent.trim(),
                department: row.querySelector('td:nth-child(3)').textContent.trim(),
                location: row.querySelector('.contact-item:last-child').textContent.trim(),
                status: row.querySelector('.status-tag').textContent.trim(),
                bgColor: row.querySelector('.profile-pic-small').getAttribute('style')
            };

            switch (type) {
                case 'View':
                    viewProfile(facultyData);
                    break;
                case 'Message':
                    openMessageModal(facultyData);
                    break;
                case 'Remove':
                    if (confirm(`Are you sure you want to remove ${facultyName}?`)) {
                        removeFaculty(facultyData);
                    }
                    break;
            }
        }

        // View Profile
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
                    <span class="status-tag status-pending">
                        ${facultyData.status}
                    </span>
                </div>
            `;
            openModal('viewProfileModal');
        }

        // Message Modal
        function openMessageModal(facultyData) {
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
        }

        // Send Message
        function sendMessage(email) {
            const subject = document.getElementById('messageSubject').value;
            const content = document.getElementById('messageContent').value;

            if (!subject || !content) {
                showToast('Please fill in both subject and message', false);
                return;
            }

            fetch('faculty_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=send_message&email=${encodeURIComponent(email)}&subject=${encodeURIComponent(subject)}&message=${encodeURIComponent(content)}`
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success);
                if (data.success) {
                    closeModal('messageModal');
                    setTimeout(() => document.getElementById('messageModal').remove(), 300);
                }
            })
            .catch(error => showToast('Network error: ' + error.message, false));
        }

        // Add Faculty
        function submitAddFaculty() {
            const form = document.getElementById('addFacultyForm');
            if (form.checkValidity()) {
                const username = document.getElementById('facultyUsername').value;
                const name = document.getElementById('facultyName').value;
                const email = document.getElementById('facultyEmail').value;
                const department = document.getElementById('facultyDepartment').value;
                const role = document.getElementById('facultyRole').value;

                fetch('faculty_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=add_faculty&username=${encodeURIComponent(username)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&department=${encodeURIComponent(department)}&role=${encodeURIComponent(role)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const colors = ['#f0e6ff', '#c8e6c9', '#ffcdd2'];
                        const randomColor = colors[Math.floor(Math.random() * colors.length)];
                        const newRow = `
                            <tr data-email="${email}" data-search-terms="${name.toLowerCase()} ${email.toLowerCase()} ${department.toLowerCase()} ${role.toLowerCase()}">
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
                                <td>${role}</td>
                                <td>${department}</td>
                                <td>
                                    <div>
                                        <span class="contact-item"><i class="fa-solid fa-envelope"></i> ${email}</span>
                                        <span class="contact-item"><i class="fa-solid fa-location-dot"></i> ${department} Dept</span>
                                    </div>
                                </td>
                                <td><span class="status-tag status-pending"><i class="fa-solid fa-clock"></i> Pending</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" onclick="action('View', '${name}', '${email}')">View</button>
                                        <button class="btn-message" onclick="action('Message', '${name}', '${email}')">Message</button>
                                        <button class="action-button" onclick="action('Remove', '${name}', '${email}')">Remove</button>
                                    </div>
                                </td>
                            </tr>`;
                        document.getElementById('facultyTableBody').insertAdjacentHTML('beforeend', newRow);
                        closeModal('addFacultyModal');
                        form.reset();
                        showToast(data.message);
                    } else {
                        showToast(data.message, false);
                    }
                })
                .catch(error => showToast('Network error: ' + error.message, false));
            } else {
                form.reportValidity();
            }
        }

        // Remove Faculty
        function removeFaculty(facultyData) {
            fetch('faculty_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove_faculty&email=${encodeURIComponent(facultyData.email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-email="${facultyData.email}"]`).remove();
                    showToast(data.message);
                } else {
                    showToast(data.message, false);
                }
            })
            .catch(error => showToast('Network error: ' + error.message, false));
        }
    </script>
</body>
</html>
