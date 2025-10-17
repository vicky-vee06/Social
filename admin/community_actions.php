<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/Applications/XAMPP/xamppfiles/logs/php_errors.log');

session_start();
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
error_log('community_actions.php executed at ' . date('Y-m-d H:i:s'));
error_log('POST data: ' . print_r($_POST, true));

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$institution_id = $_SESSION['institution_id'] ?? 1;

// Validate emoji input
function validateEmoji($input) {
    $emojiRegex = '/^[\p{Emoji}\p{Emoji_Presentation}\p{Emoji_Modifier_Base}\p{Emoji_Component}]{1,4}$/u';
    return empty($input) || preg_match($emojiRegex, $input);
}

// Map frontend string status to numeric DB values
$status_map = ['active' => 1, 'inactive' => 0];

switch ($action) {
    case 'get_communities':
        $stmt = $conn->prepare("
            SELECT 
                c.id,
                c.name,
                c.description,
                c.icon,
                c.status,
                c.created_at,
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT p.id) as post_count
            FROM student_communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            WHERE c.institution_id = ?
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            error_log('Prepare failed for get_communities: ' . $conn->error);
            break;
        }
        $stmt->bind_param("i", $institution_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $communities = [];
        while ($row = $result->fetch_assoc()) {
            // Convert numeric status back to string for frontend
            $row['status'] = ($row['status'] == 1) ? 'active' : 'inactive';
            $communities[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $communities]);
        error_log('Communities fetched successfully');
        break;

    case 'create_community':
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '📚');
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Name and description are required']);
            error_log('Create community failed: Missing fields');
            break;
        }
        if (!validateEmoji($icon)) {
            echo json_encode(['success' => false, 'message' => 'Invalid emoji icon']);
            error_log('Create community failed: Invalid emoji icon');
            break;
        }
        if (!isset($status_map[$status])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            error_log('Create community failed: Invalid status: ' . $status);
            break;
        }
        $numeric_status = $status_map[$status];

        $stmt = $conn->prepare("INSERT INTO student_communities (institution_id, name, description, icon, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            error_log('Prepare failed for create_community: ' . $conn->error);
            break;
        }
        $stmt->bind_param("isssi", $institution_id, $name, $description, $icon, $numeric_status);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Community created successfully']);
            error_log('Community created successfully: ' . $name);
        } else {
            echo json_encode(['success' => false, 'message' => 'Create failed: ' . $stmt->error]);
            error_log('Create community failed: ' . $stmt->error);
        }
        break;

    case 'update_community':
        $community_id = (int)($_POST['community_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '📚');
        $status = $_POST['status'] ?? 'active';

        if ($community_id <= 0 || empty($name) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Community ID, name, and description are required']);
            error_log('Update community failed: Missing fields');
            break;
        }
        if (!validateEmoji($icon)) {
            echo json_encode(['success' => false, 'message' => 'Invalid emoji icon']);
            error_log('Update community failed: Invalid emoji icon');
            break;
        }
        if (!isset($status_map[$status])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            error_log('Update community failed: Invalid status: ' . $status);
            break;
        }
        $numeric_status = $status_map[$status];

        $stmt = $conn->prepare("UPDATE student_communities SET name = ?, description = ?, icon = ?, status = ? WHERE id = ? AND institution_id = ?");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            error_log('Prepare failed for update_community: ' . $conn->error);
            break;
        }
        $stmt->bind_param("sssiii", $name, $description, $icon, $numeric_status, $community_id, $institution_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Community updated successfully']);
            error_log('Community updated successfully: ' . $community_id);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
            error_log('Update community failed: ' . $stmt->error);
        }
        break;

    case 'delete_community':
        $community_id = (int)($_POST['community_id'] ?? 0);
        if ($community_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Community ID is required']);
            error_log('Delete community failed: Missing community_id');
            break;
        }
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM community_members WHERE community_id = ?");
            if ($stmt === false) throw new Exception('Prepare failed for community_members: ' . $conn->error);
            $stmt->bind_param("i", $community_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM posts WHERE community_id = ?");
            if ($stmt === false) throw new Exception('Prepare failed for posts: ' . $conn->error);
            $stmt->bind_param("i", $community_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM student_communities WHERE id = ? AND institution_id = ?");
            if ($stmt === false) throw new Exception('Prepare failed for student_communities: ' . $conn->error);
            $stmt->bind_param("ii", $community_id, $institution_id);
            if ($stmt->execute()) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Community deleted successfully']);
                error_log('Community deleted successfully: ' . $community_id);
            } else {
                throw new Exception('Delete failed: ' . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
            error_log('Delete community failed: ' . $e->getMessage());
        }
        break;

    case 'get_community_stats':
        $community_id = (int)($_POST['community_id'] ?? 0);
        if ($community_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Community ID is required']);
            error_log('Get community stats failed: Missing community_id');
            break;
        }
        $stmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT cm.user_id) as member_count,
                COUNT(DISTINCT p.id) as post_count,
                (SELECT COUNT(DISTINCT user_id) FROM community_members 
                 WHERE community_id = ? AND joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_members,
                (SELECT COUNT(id) FROM posts 
                 WHERE community_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_posts
            FROM student_communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            LEFT JOIN posts p ON c.id = p.community_id
            WHERE c.id = ? AND c.institution_id = ?
        ");
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            error_log('Prepare failed for get_community_stats: ' . $conn->error);
            break;
        }
        $stmt->bind_param("iiii", $community_id, $community_id, $community_id, $institution_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'data' => $row]);
            error_log('Community stats fetched successfully: ' . $community_id);
        } else {
            echo json_encode(['success' => false, 'message' => 'Community not found']);
            error_log('Community not found: ' . $community_id);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        error_log('Invalid action: ' . $action);
        break;
}

$conn->close();
?>
