<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');
error_reporting(E_ALL);

session_start();
include '../inc/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function getDynamicStats($conn)
{
    $stats = ['students' => 0, 'faculty' => 0, 'departments' => 0, 'campuses' => 1];

    $res = $conn->query("SELECT COUNT(*) AS total FROM users");
    if ($res) $stats['students'] = (int)$res->fetch_assoc()['total'];

    $res = $conn->query("SELECT COUNT(*) AS total FROM users WHERE faculty IS NOT NULL AND faculty <> ''");
    if ($res) $stats['faculty'] = (int)$res->fetch_assoc()['total'];

    $res = $conn->query("SELECT COUNT(DISTINCT department) AS total FROM users WHERE department IS NOT NULL AND department <> ''");
    if ($res) $stats['departments'] = (int)$res->fetch_assoc()['total'];

    return $stats;
}

try {
    switch ($action) {
        case 'get_institution_details':
            $stmt = $conn->prepare("
                SELECT name, location, about_text, mission, vision, email, phone, website
                FROM institution_details WHERE id = 1
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (!$row) throw new Exception('Institution not found');

            $stats = getDynamicStats($conn);
            $row = array_merge($row, $stats);

            echo json_encode(['success' => true, 'data' => $row]);
            break;

        case 'update_institution_details':
            $about_text = $_POST['about_text'] ?? null;
            $mission = $_POST['mission'] ?? null;
            $vision = $_POST['vision'] ?? null;

            $stmt = $conn->prepare("
                UPDATE institution_details
                SET about_text = COALESCE(?, about_text),
                    mission = COALESCE(?, mission),
                    vision = COALESCE(?, vision)
                WHERE id = 1
            ");
            $stmt->bind_param("sss", $about_text, $mission, $vision);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Institution details updated successfully']);
            break;

        case 'update_contact_info':
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $website = $_POST['website'] ?? '';
            $location = $_POST['location'] ?? '';

            if (empty($phone) || empty($email) || empty($location)) {
                throw new Exception('Phone, Email and Location are required');
            }

            $stmt = $conn->prepare("
                UPDATE institution_details
                SET phone = ?, email = ?, website = ?, location = ?
                WHERE id = 1
            ");
            $stmt->bind_param("ssss", $phone, $email, $website, $location);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Contact info updated successfully']);
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
