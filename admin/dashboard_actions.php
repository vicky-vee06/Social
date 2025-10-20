<?php
session_start();
include '../inc/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    // Create a community
    case 'create_community':
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? '📚');
        $institution_id = $_SESSION['institution_id'] ?? 1; // default institution

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Community name is required']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO student_communities (institution_id, name, description, icon, members_count, posts_count, created_at, status) VALUES (?, ?, ?, ?, 0, 0, NOW(), 1)");
        $stmt->bind_param("isss", $institution_id, $name, $description, $icon);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Community created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        break;

    // Post an announcement
    case 'announce':
        $content = trim($_POST['content'] ?? '');
        $community_id = $_POST['community_id'] ?? 14; // default to SOP community id
        $user_id = $_SESSION['user_id'] ?? 1;

        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Announcement content is required']);
            exit;
        }

        // Ensure community_id exists and is active
        $stmt_check = $conn->prepare("SELECT id FROM student_communities WHERE id = ? AND status = 1");
        $stmt_check->bind_param("i", $community_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows === 0) {
            $community_id = 14; // fallback to SOP
        }

        $stmt = $conn->prepare("INSERT INTO posts (community_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $community_id, $user_id, $content);

        if ($stmt->execute()) {
            // Update posts_count for the community
            $stmt_update_posts = $conn->prepare(
                "UPDATE student_communities 
                 SET posts_count = (SELECT COUNT(*) FROM posts WHERE community_id = ?) 
                 WHERE id = ?"
            );
            $stmt_update_posts->bind_param("ii", $community_id, $community_id);
            $stmt_update_posts->execute();

            echo json_encode(['success' => true, 'message' => 'Announcement posted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        break;

    // Update members count for a community
    case 'update_members_count':
        $community_id = $_POST['community_id'] ?? 0;
        if (!$community_id) {
            echo json_encode(['success' => false, 'message' => 'Community ID is required']);
            exit;
        }

        $stmt_update_members = $conn->prepare(
            "UPDATE student_communities sc 
             SET sc.members_count = (SELECT COUNT(*) FROM community_members cm WHERE cm.community_id = sc.id) 
             WHERE sc.id = ?"
        );
        $stmt_update_members->bind_param("i", $community_id);

        if ($stmt_update_members->execute()) {
            echo json_encode(['success' => true, 'message' => 'Members count updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt_update_members->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
