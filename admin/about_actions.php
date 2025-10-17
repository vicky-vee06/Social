<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');
error_reporting(E_ALL);


session_start();

// Include config
$include_result = include '../inc/config.php';
if ($include_result === false) {
    
    echo json_encode(['success' => false, 'message' => 'Failed to include config.php']);
    error_log('Failed to include config.php');
    exit;
}

if (!isset($conn) || $conn->connect_error) {
    
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Connection variable not set')]);
    error_log('Database connection failed: ' . ($conn->connect_error ?? 'Connection variable not set'));
    exit;
}

header('Content-Type: application/json');
error_log('about_actions.php executed at ' . date('Y-m-d H:i:s'));
error_log('POST data: ' . print_r($_POST, true));

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_institution_details':
            $stmt = $conn->prepare("SELECT name, location, about_text, mission, vision, email, phone, website, students, faculty, departments, campuses FROM institution_details WHERE id = 1");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            echo json_encode(['success' => !!$row, 'data' => $row ?? null, 'message' => $row ? 'Fetched successfully' : 'Institution not found']);
            break;

        case 'update_institution_details':
            $about_text = $_POST['about_text'] ?? '';
            $mission = $_POST['mission'] ?? '';
            $vision = $_POST['vision'] ?? '';

            if (!$about_text && (!$mission || !$vision)) {
                throw new Exception('All required fields missing');
            }

            $stmt = $conn->prepare("UPDATE institution_details SET about_text = ?, mission = ?, vision = ? WHERE id = 1");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->bind_param("sss", $about_text, $mission, $vision);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Institution details updated successfully']);
            } else {
                throw new Exception('Update failed: ' . $stmt->error);
            }
            break;

        case 'update_stats':
            $students = $_POST['students'] ?? 0;
            $faculty = $_POST['faculty'] ?? 0;
            $departments = $_POST['departments'] ?? 0;
            $campuses = $_POST['campuses'] ?? 0;

            if ($students < 0 || $faculty < 0 || $departments < 0 || $campuses < 0) {
                throw new Exception('Stats cannot be negative');
            }

            $stmt = $conn->prepare("UPDATE institution_details SET students = ?, faculty = ?, departments = ?, campuses = ? WHERE id = 1");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->bind_param("iiii", $students, $faculty, $departments, $campuses);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Stats updated successfully']);
            } else {
                throw new Exception('Update failed: ' . $stmt->error);
            }
            break;

        case 'send_contact_message':
            $sender_id = $_SESSION['user_id'] ?? 1;
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            if (!$subject || !$message) throw new Exception('Subject and message are required');

            $stmt = $conn->prepare("SELECT email FROM institution_details WHERE id = 1");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (!$row) throw new Exception('Institution email not found');

            $receiver_email = $row['email'];

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->bind_param("s", $receiver_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (!$row) throw new Exception('Receiver not found');

            $receiver_id = $row['id'];
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $is_read = 0;
            $stmt->bind_param("iisi", $sender_id, $receiver_id, $message, $is_read);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
            } else {
                throw new Exception('Error sending message: ' . $stmt->error);
            }
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();

?>
