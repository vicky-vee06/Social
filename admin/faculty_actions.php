<?php
// 🧾 Show all PHP errors directly on the page (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);

include '../inc/config.php'; // Include database connection

// Set JSON header
header('Content-Type: application/json');

// Check database connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_faculty':
        $query = "
            SELECT u.id, u.username, u.name, u.email, u.department, u.role
            FROM users u
            LEFT JOIN student_institutions si ON u.id = si.student_id
            WHERE u.role IS NOT NULL AND (si.name = 'University of Lagos' OR u.institution = 'University of Lagos')
        ";
        $result = $conn->query($query);
        if ($result) {
            $faculty = [];
            while ($row = $result->fetch_assoc()) {
                $faculty[] = $row;
            }
            echo json_encode(['success' => true, 'faculty' => $faculty]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        }
        break;

    case 'add_faculty':
        $username   = trim($_POST['username'] ?? '');
        $name       = trim($_POST['name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $role       = trim($_POST['role'] ?? '');
        $institution = 'University of Lagos'; // or dynamic if needed

        // 🛡️ Validate required fields
        if (empty($username) || empty($name) || empty($email) || empty($department) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }

        // 🧭 Check if username already exists
        $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
        if (!$checkUsername) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed (username): ' . $conn->error]);
            break;
        }
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $checkUsername->store_result();
        if ($checkUsername->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already taken']);
            $checkUsername->close();
            break;
        }
        $checkUsername->close();

        // 🧭 Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$checkEmail) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed (email): ' . $conn->error]);
            break;
        }
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        if ($checkEmail->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $checkEmail->close();
            break;
        }
        $checkEmail->close();

        // 🧠 Insert new faculty member
        $password = password_hash('default123', PASSWORD_DEFAULT);
        $phone = '';
        $avatar = 'default-avatar.png';

        $stmt = $conn->prepare("INSERT INTO users (username, name, email, phone, role, department, institution, password, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed (insert): ' . $conn->error]);
            break;
        }
        $stmt->bind_param("sssssssss", $username, $name, $email, $phone, $role, $department, $institution, $password, $avatar);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // Link user to student_institutions
            $stmt2 = $conn->prepare("INSERT INTO student_institutions (student_id, name, role, is_primary, logo_color, logo_icon) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt2) {
                echo json_encode(['success' => false, 'message' => 'Prepare failed for student_institutions: ' . $conn->error]);
                break;
            }
            $is_primary = 1;
            $logo_color = '#793DDC';
            $logo_icon = 'fas fa-graduation-cap';
            $stmt2->bind_param("ississ", $user_id, $institution, $role, $is_primary, $logo_color, $logo_icon);
            if ($stmt2->execute()) {
                echo json_encode(['success' => true, 'message' => 'Faculty member added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding to student_institutions: ' . $conn->error]);
            }
            $stmt2->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to users: ' . $conn->error]);
        }
        $stmt->close();
        break;

    case 'send_message':
        $sender_id = 1; // Replace with session user
        $receiver_email = $_POST['email'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';

        if (empty($receiver_email) || empty($subject) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'All message fields are required']);
            break;
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            break;
        }
        $stmt->bind_param("s", $receiver_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $receiver_id = $row['id'];
            $stmt2 = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt2) {
                echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
                break;
            }
            $is_read = 0;
            $stmt2->bind_param("iisi", $sender_id, $receiver_id, $message, $is_read);
            if ($stmt2->execute()) {
                echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error sending message: ' . $conn->error]);
            }
            $stmt2->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Receiver not found']);
        }
        $stmt->close();
        break;

    case 'remove_faculty':
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            break;
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            break;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];

            $stmt1 = $conn->prepare("DELETE FROM student_institutions WHERE student_id = ?");
            $stmt1->bind_param("i", $user_id);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("DELETE FROM users WHERE email = ?");
            $stmt2->bind_param("s", $email);
            if ($stmt2->execute()) {
                echo json_encode(['success' => true, 'message' => 'Faculty member removed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error removing faculty member: ' . $conn->error]);
            }
            $stmt2->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Faculty member not found']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
