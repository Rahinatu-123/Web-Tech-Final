<?php
session_start();
include './db/db_connect.php';
require_once './includes/image_handler.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Debug logging
        error_log("POST data received: " . print_r($_POST, true));
        error_log("FILES data received: " . print_r($_FILES, true));
        
        // Validate inputs
        $courseName = filter_input(INPUT_POST, 'course', FILTER_SANITIZE_STRING);
        $topicName = filter_input(INPUT_POST, 'topic', FILTER_SANITIZE_STRING);
        $questionText = trim($_POST['content'] ?? '');
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            throw new Exception("User not logged in");
        }

        if (empty($courseName) || empty($topicName) || empty($questionText)) {
            throw new Exception("All fields are required");
        }

        // Start transaction
        $conn->begin_transaction();

        // Get course ID
        $stmt = $conn->prepare("SELECT courseId FROM courses WHERE courseName = ?");
        $stmt->bind_param("s", $courseName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result->num_rows) {
            throw new Exception("Invalid course selected");
        }
        
        $course = $result->fetch_assoc();
        $courseId = $course['courseId'];

        // Get or create topic
        $stmt = $conn->prepare("SELECT topicId FROM topics WHERE topicName = ? AND courseId = ?");
        $stmt->bind_param("si", $topicName, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $topic = $result->fetch_assoc();
            $topicId = $topic['topicId'];
        } else {
            $stmt = $conn->prepare("INSERT INTO topics (courseId, topicName) VALUES (?, ?)");
            $stmt->bind_param("is", $courseId, $topicName);
            $stmt->execute();
            $topicId = $stmt->insert_id;
        }

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = handleImageUpload($_FILES['image'], 'question');
            if ($imageData && $imageData['success']) {
                $imagePath = $imageData['relative_path'];
            }
        }

        // Insert question with image path
        $stmt = $conn->prepare("INSERT INTO questions (userId, topicId, questionText, image_path1, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("iiss", $userId, $topicId, $questionText, $fileAttachment);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Question added successfully'
        ]);
        exit;

    } catch (Exception $e) {
        // Rollback transaction
        if ($conn && $conn->connect_error === null) {
            $conn->rollback();
        }

        // Delete uploaded image if it exists and there was an error
        if (isset($imagePath) && !empty($imagePath)) {
            $fullPath = __DIR__ . '/' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        error_log("Error in questionAdd.php: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error adding question: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>