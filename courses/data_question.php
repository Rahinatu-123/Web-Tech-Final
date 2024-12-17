<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'rahinatu.lawal'; 
$password = 'mohammed2'; 
$db_name = 'webtech_fall2024_rahinatu_lawal';


try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch topics for calculus (courseId = 1)
$stmt = $conn->prepare("SELECT * FROM topics WHERE courseId = 4");
$stmt->execute();
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch questions with their answers
$stmt = $conn->prepare("
    SELECT q.*, 
       q.fileAttachment AS image_path, -- Assign fileAttachment to image_path
       t.topicName, 
       a.answerId, 
       a.answerText, 
       a.created_at AS answer_created_at,
       u.firstName AS answerer_name
FROM questions q 
LEFT JOIN topics t ON q.topicId = t.topicId 
LEFT JOIN answers a ON q.questionId = a.questionId
LEFT JOIN users u ON a.userId = u.userId
WHERE t.courseId = 1
ORDER BY q.created_at DESC, a.created_at DESC

");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group questions and their answers
$grouped_questions = [];
foreach ($questions as $row) {
    $questionId = $row['questionId'];
    if (!isset($grouped_questions[$questionId])) {
        $grouped_questions[$questionId] = [
            'questionId' => $row['questionId'],
            'questionText' => $row['questionText'],
            'topicId' => $row['topicId'],
            'topicName' => $row['topicName'],
            'image_path' => $row['fileAttachment'],
            'created_at' => $row['created_at'],
            'answers' => []
        ];
    }
    if ($row['answerId']) {
        $grouped_questions[$questionId]['answers'][] = [
            'answerId' => $row['answerId'],
            'answerText' => $row['answerText'],
            'created_at' => $row['answer_created_at'],
            'answerer_name' => $row['answerer_name']
        ];
    }
}

// Function to fetch comments
function fetchComments($conn, $questionId) {
    $stmt = $conn->prepare("
        SELECT c.*, u.firstName 
        FROM comments c 
        JOIN users u ON c.userId = u.userId 
        WHERE c.entityId = ? AND c.entityTypeId = 2
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$questionId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in to perform this action']);
        exit;
    }

    try {
        if ($_POST['action'] === 'fetch_comments') {
            $questionId = filter_input(INPUT_POST, 'questionId', FILTER_VALIDATE_INT);
            if (!$questionId) {
                throw new Exception("Invalid question ID");
            }

            $comments = fetchComments($conn, $questionId);
            echo json_encode([
                'status' => 'success',
                'comments' => $comments
            ]);
            exit;
        } elseif ($_POST['action'] === 'submit_comment') {
            $questionId = filter_input(INPUT_POST, 'questionId', FILTER_VALIDATE_INT);
            $commentText = trim($_POST['commentText']);

            if (!$questionId || empty($commentText)) {
                throw new Exception("Invalid input");
            }

            // Insert comment
            $stmt = $conn->prepare("
                INSERT INTO comments (userId, entityTypeId, entityId, commentText, created_at) 
                VALUES (?, 2, ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $questionId, $commentText]);
            $commentId = $conn->lastInsertId();

            echo json_encode([
                'status' => 'success',
                'comment' => [
                    'commentId' => $commentId,
                    'commentText' => $commentText,
                    'firstName' => $_SESSION['firstName'],
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
            exit;
        } elseif ($_POST['action'] === 'submit_answer') {
            $questionId = $_POST['questionId'];
            $answerText = $_POST['answerText'];
            $userId = $_SESSION['user_id'];

            // Insert answer with timestamp
            $stmt = $conn->prepare("INSERT INTO answers (questionId, userId, answerText, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$questionId, $userId, $answerText]);
            $answerId = $conn->lastInsertId();

            // Get user's name
            $userStmt = $conn->prepare("SELECT firstName FROM users WHERE userId = ?");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'message' => 'Answer submitted successfully',
                'answerId' => $answerId,
                'answerText' => $answerText,
                'answerer_name' => $user['firstName'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Error in comment submission: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculus Questions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .calculus-header {
            background-color: #FF6B6B;
            color: #34495E;
            text-align: center;
            padding: 20px;
        }
        .calculus-header h1 {
            color: #34495E;
            margin: 0;
            font-size: 24px;
        }
        .layout {
            display: flex;
            flex-direction: column; /* Vertical container */
            padding: 20px;
        }
        .filter-section {
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .filter-section ul {
            list-style-type: none;
            padding: 0;
        }
        .filter-section li {
            margin: 10px 0;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            background-color: #f2f2f2;
            transition: background-color 0.3s;
        }
        .filter-section li.active {
            background-color: #FF6B6B;
            color: white;
        }
        .questions-container {
            display: flex;
            flex-direction: column; /* Stack questions vertically */
            gap: 20px;
        }
        .question-container {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            box-sizing: border-box;
            transition: transform 0.3s ease-in-out;
        }
        .question-container:hover {
            transform: translateY(-5px);
        }
        .question-header {
            font-size: 20px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .question-content {
            display: flex;
            flex-direction: column;
        }
        .question-content img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 5px;
        }
        .question-actions {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }
        .question-actions .download-btn {
            background-color: #FF6B6B;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .question-actions .download-btn:hover {
            background-color: #ff5252;
        }
        .question-actions .answer-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .question-actions .answer-btn:hover {
            background-color: #45a049;
        }
        .question-actions .comment-btn {
            background-color: #34495E;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .question-actions .comment-btn:hover {
            background-color: #2c3e50;
        }
        .answer-section {
            margin-top: 15px;
            display: none;
        }
        .answer-input {
            width: 100%;
            min-height: 100px;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        .answer-upload-section {
            margin-bottom: 10px;
        }
        .answer-submit {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .answer-submit:hover {
            background-color: #003d82;
        }
        .comment-section {
            margin-top: 15px;
            display: none;
        }
        .comment-textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .submit-comment {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit-comment:hover {
            background-color: #003d82;
        }
        .comments-display {
            margin-top: 10px;
        }
        .comments-display .comment {
            background-color: #e9e9e9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .comments-display .comment .username {
            font-weight: bold;
            margin-bottom: 5px;
            color: #34495E;
        }
        .comments-display .comment .timestamp {
            font-size: 0.8em;
            color: #666;
            margin-left: 10px;
        }
        
        .answers-container {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .answers-container h3 {
            color: #34495E;
            margin-bottom: 15px;
        }
        .answer {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .answer-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }
        .answerer {
            font-weight: bold;
            color: #34495E;
        }
        .answer-text {
            white-space: pre-wrap;
            line-height: 1.5;
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <header class="calculus-header">
        <h1>Database Management Systems Questions</h1>
    </header>

    <div class="layout">
        <div class="filter-section">
            <ul id="topicsList">
                <li class="active" data-topic-id="all">All Topics</li>
                <?php foreach ($topics as $topic): ?>
                    <li data-topic-id="<?php echo $topic['topicId']; ?>">
                        <?php echo $topic['topicName']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="questions-container" id="questionsContainer">
            <?php foreach ($grouped_questions as $question): ?>
                <div class="question-container" data-question-id="<?php echo $question['questionId']; ?>" data-topic-id="<?php echo $question['topicId']; ?>">
                    <div class="question-header"><?php echo $question['topicName']; ?></div>
                    <div class="question-content">
                        <?php if ($question['image_path']): ?>
                            <img src="<?php echo "../" . htmlspecialchars($question['image_path']); ?>" alt="Question Image">
                        <?php endif; ?>
                        <p><?php echo $question['questionText']; ?></p>
                        <div class="question-actions">
                            <button class="download-btn" data-topic-name="<?php echo $question['topicName']; ?>" data-question-id="<?php echo $question['questionId']; ?>">Download</button>
                            <button class="answer-btn">Add Answer</button>
                            <button class="comment-btn" onclick="toggleComments(this)" data-question-id="<?php echo $question['questionId']; ?>">View Comments</button>
                        </div>

                        <div class="answer-section" style="display: none;">
                            <textarea class="answer-input" placeholder="Write your answer here..."></textarea>
                            <div class="answer-upload-section">
                                <label for="answer-image">Add Image (optional):</label>
                                <input type="file" id="answer-image" class="answer-image-upload" accept="image/*">
                                <img class="answer-preview" src="" style="display: none; max-width: 200px; margin-top: 10px;">
                            </div>
                            <button class="answer-submit" data-question-id="<?php echo $question['questionId']; ?>">Submit Answer</button>
                        </div>

                        <div class="answers-container">
                            <?php if (!empty($question['answers'])): ?>
                                <h3>Answers:</h3>
                                <?php foreach ($question['answers'] as $answer): ?>
                                    <div class="answer">
                                        <div class="answer-header">
                                            <span class="answerer"><?php echo htmlspecialchars($answer['answerer_name']); ?></span>
                                            <span class="timestamp"><?php echo date('M j, Y g:i A', strtotime($answer['created_at'])); ?></span>
                                        </div>
                                        <div class="answer-text"><?php echo nl2br(htmlspecialchars($answer['answerText'])); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="comment-section" style="display: none;">
                            <div class="comments-display"></div>
                            <textarea class="comment-textarea" placeholder="Write your comment here..."></textarea>
                            <button class="submit-comment" data-question-id="<?php echo $question['questionId']; ?>">Submit Comment</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const { jsPDF } = window.jspdf;
        const topicsList = document.getElementById('topicsList');
        const questionsContainer = document.getElementById('questionsContainer');

        // Topic filtering
        topicsList.addEventListener('click', (e) => {
            if (e.target.tagName === 'LI') {
                const topicId = e.target.getAttribute('data-topic-id');
                document.querySelectorAll('#topicsList li').forEach(li => li.classList.remove('active'));
                e.target.classList.add('active');

                document.querySelectorAll('.question-container').forEach(question => {
                    if (topicId === 'all' || question.getAttribute('data-topic-id') === topicId) {
                        question.style.display = 'block';
                    } else {
                        question.style.display = 'none';
                    }
                });
            }
        });

        // Questions and Comments Interaction
        questionsContainer.addEventListener('click', (e) => {
            // Comment Button Toggle
            if (e.target.classList.contains('comment-btn')) {
                const questionContainer = e.target.closest('.question-container');
                const commentSection = questionContainer.querySelector('.comment-section');
                const commentsDisplay = commentSection.querySelector('.comments-display');
                
                // Toggle visibility
                commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
                
                // If making visible, fetch comments
                if (commentSection.style.display === 'block') {
                    const noteId = questionContainer.getAttribute('data-question-id');
                    fetchComments(noteId, commentsDisplay);
                }
            }

            // Comment Submission
            if (e.target.classList.contains('submit-comment')) {
                const questionContainer = e.target.closest('.question-container');
                const commentSection = e.target.closest('.comment-section');
                const commentInput = commentSection.querySelector('.comment-textarea');
                const commentsDisplay = commentSection.querySelector('.comments-display');
                const commentText = commentInput.value.trim();

                if (commentText) {
                    const noteId = questionContainer.getAttribute('data-question-id');
                    
                    const formData = new FormData();
                    formData.append('action', 'submit_comment');
                    formData.append('noteId', noteId);
                    formData.append('commentText', commentText);

                    fetch('', {  // Using current page as endpoint
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Create and append new comment
                            const commentElement = document.createElement('div');
                            commentElement.classList.add('comment');
                            
                            const formattedDate = new Date().toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            commentElement.innerHTML = `
                                <div class="username">You</div>
                                <div class="comment-text">${commentText}</div>
                                <div class="timestamp">${formattedDate}</div>
                            `;

                            commentsDisplay.appendChild(commentElement);
                            
                            // Clear input
                            commentInput.value = '';
                        } else {
                            // Handle error
                            console.error('Comment submission failed:', data.message);
                            alert('Failed to submit comment. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting comment:', error);
                        alert('An error occurred. Please try again.');
                    });
                } else {
                    alert('Please enter a comment before submitting.');
                }
            }
        });

        function toggleComments(button) {
            const questionContainer = button.closest('.question-container');
            const commentSection = questionContainer.querySelector('.comment-section');
            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
        }

        // Download Button
        questionsContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('download-btn')) {
                const doc = new jsPDF();
                const topicName = e.target.getAttribute('data-topic-name');
                const questionId = e.target.getAttribute('data-question-id');
                doc.text(`Topic: ${topicName}`, 10, 10);
                doc.text(`Question ID: ${questionId}`, 10, 20);
                doc.save(`${topicName}_question_${questionId}.pdf`);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
    const questionsContainer = document.getElementById('questionsContainer');

    // Function to fetch and display comments for a specific note
    function fetchComments(questionId, commentsDisplay) {
        const formData = new FormData();
        formData.append('action', 'fetch_comments');
        formData.append('questionId', questionId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                commentsDisplay.innerHTML = '';
                data.comments.forEach(comment => {
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment';
                    commentDiv.innerHTML = `
                        <div class="username">${comment.firstName}</div>
                        <div class="comment-text">${comment.commentText}</div>
                        <div class="timestamp">${new Date(comment.created_at).toLocaleString()}</div>
                    `;
                    commentsDisplay.appendChild(commentDiv);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            commentsDisplay.innerHTML = '<p>Error loading comments</p>';
        });
    }

    // Function to submit comment
    function submitComment(questionId, commentText, commentsDisplay) {
        const formData = new FormData();
        formData.append('action', 'submit_comment');
        formData.append('questionId', questionId);
        formData.append('commentText', commentText);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.innerHTML = `
                    <div class="username">${data.comment.firstName}</div>
                    <div class="comment-text">${data.comment.commentText}</div>
                    <div class="timestamp">${new Date(data.comment.created_at).toLocaleString()}</div>
                `;
                
                commentsDisplay.insertBefore(commentDiv, commentsDisplay.firstChild);
            } else {
                // Handle error
                console.error('Comment submission failed:', data.message);
                alert('Failed to submit comment. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error submitting comment:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Update comment event listeners
    document.querySelector('.questions-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('comment-btn')) {
            const questionContainer = e.target.closest('.question-container');
            const commentSection = questionContainer.querySelector('.comment-section');
            const commentsDisplay = commentSection.querySelector('.comments-display');
            const questionId = questionContainer.getAttribute('data-question-id');

            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';

            if (commentSection.style.display === 'block') {
                fetchComments(questionId, commentsDisplay);
            }
        }

        if (e.target.classList.contains('submit-comment')) {
            const questionContainer = e.target.closest('.question-container');
            const commentSection = e.target.closest('.comment-section');
            const commentInput = commentSection.querySelector('.comment-textarea');
            const commentsDisplay = commentSection.querySelector('.comments-display');
            const questionId = questionContainer.getAttribute('data-question-id');
            const commentText = commentInput.value.trim();

            if (commentText) {
                submitComment(questionId, commentText, commentsDisplay);
                commentInput.value = '';
            }
        }
    });

    // Event delegation for answer interactions
    questionsContainer.addEventListener('click', (e) => {
        // Add Answer Button Toggle
        if (e.target.classList.contains('answer-btn')) {
            const questionContainer = e.target.closest('.question-container');
            const answerSection = questionContainer.querySelector('.answer-section');
            
            // Toggle visibility
            answerSection.style.display = answerSection.style.display === 'none' ? 'block' : 'none';
        }

        // Answer Submission
        if (e.target.classList.contains('answer-submit')) {
            const questionContainer = e.target.closest('.question-container');
            const answerInput = questionContainer.querySelector('.answer-input');
            const questionId = questionContainer.getAttribute('data-question-id');
            const answerText = answerInput.value.trim();

            if (answerText) {
                submitAnswer(questionId, answerText);
            } else {
                alert('Please enter an answer');
            }
        }
    });

    function submitAnswer(questionId, answerText) {
        const formData = new FormData();
        formData.append('action', 'submit_answer');
        formData.append('questionId', questionId);
        formData.append('answerText', answerText);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create new answer element
                const answersContainer = document.querySelector(`[data-question-id="${questionId}"] .answers-container`);
                const answerDiv = document.createElement('div');
                answerDiv.className = 'answer';
                answerDiv.innerHTML = `
                    <div class="answer-header">
                        <span class="answerer">${data.answerer_name}</span>
                        <span class="timestamp">${new Date(data.created_at).toLocaleString()}</span>
                    </div>
                    <div class="answer-text">${data.answerText}</div>
                `;
                
                // Add the new answer to the container
                if (answersContainer) {
                    if (!answersContainer.querySelector('h3')) {
                        answersContainer.innerHTML = '<h3>Answers:</h3>';
                    }
                    answersContainer.appendChild(answerDiv);
                }

                // Clear the input and hide the answer section
                const answerSection = document.querySelector(`[data-question-id="${questionId}"] .answer-section`);
                if (answerSection) {
                    const answerInput = answerSection.querySelector('.answer-input');
                    if (answerInput) {
                        answerInput.value = '';
                    }
                    answerSection.style.display = 'none';
                }
            } else {
                alert(data.message || 'Error submitting answer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting answer');
        });
    }

    // Image preview functionality
    questionsContainer.addEventListener('change', (e) => {
        if (e.target.classList.contains('answer-image-upload')) {
            const file = e.target.files[0];
            const preview = e.target.closest('.answer-upload-section').querySelector('.answer-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    });
});

// Function to toggle comments visibility and fetch comments
function toggleComments(button) {
    const questionCard = button.closest('.question-content');
    const commentsSection = questionCard.querySelector('.comment-section');
    const questionId = button.getAttribute('data-question-id');
    
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        fetchComments(questionId, commentsSection.querySelector('.comments-display'));
    } else {
        commentsSection.style.display = 'none';
    }
}

// Function to check login status before action
function checkLoginStatus(action) {
    if (!document.body.classList.contains('logged-in')) {
        alert('Please log in to ' + action);
        return false;
    }
    return true;
}

// Update event listeners to check login first
document.querySelector('.questions-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('answer-btn') || e.target.classList.contains('comment-btn')) {
        if (!document.body.classList.contains('logged-in')) {
            alert('Please log in to perform this action');
            e.preventDefault();
            return;
        }
    }
    // ... rest of the click handler ...
});

function submitComment(questionId) {
    if (!checkLoginStatus('submit a comment')) {
        return;
    }

    const commentTextArea = document.querySelector(`#comment-text-${questionId}`);
    const commentText = commentTextArea.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'submit_comment');
    formData.append('questionId', questionId);
    formData.append('commentText', commentText);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Comment submission response:', data); // Debug log
        if (data.status === 'success') {
            const commentsDisplay = document.querySelector(`#comments-${questionId}`);
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `
                <div class="comment-header">
                    <span class="commenter-name">${data.comment.firstName}</span>
                    <span class="timestamp">${new Date(data.comment.created_at).toLocaleString()}</span>
                </div>
                <div class="comment-text">${data.comment.commentText}</div>
            `;
            commentsDisplay.insertBefore(commentDiv, commentsDisplay.firstChild);
            commentTextArea.value = ''; // Clear the textarea
        } else {
            console.error('Comment submission failed:', data.message);
            alert(data.message || 'Failed to submit comment. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        alert('An error occurred while submitting the comment. Please try again.');
    });
}

// Function to toggle comments visibility
function toggleComments(questionId) {
    const commentsSection = document.querySelector(`#comments-section-${questionId}`);
    const isVisible = commentsSection.style.display !== 'none';
    
    if (!isVisible) {
        // Load comments when showing the section
        fetchComments(questionId);
        commentsSection.style.display = 'block';
    } else {
        commentsSection.style.display = 'none';
    }
}

// Function to fetch comments
function fetchComments(questionId) {
    const formData = new FormData();
    formData.append('action', 'fetch_comments');
    formData.append('questionId', questionId);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const commentsDisplay = document.querySelector(`#comments-${questionId}`);
            commentsDisplay.innerHTML = ''; // Clear existing comments
            
            data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.innerHTML = `
                    <div class="comment-header">
                        <span class="commenter-name">${comment.firstName}</span>
                        <span class="timestamp">${new Date(comment.created_at).toLocaleString()}</span>
                    </div>
                    <div class="comment-text">${comment.commentText}</div>
                `;
                commentsDisplay.appendChild(commentDiv);
            });
        } else {
            console.error('Failed to fetch comments:', data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching comments:', error);
    });
}
    </script>
</body>
</html>
