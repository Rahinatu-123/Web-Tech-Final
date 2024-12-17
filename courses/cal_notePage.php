<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'rahinatu.lawal'; 
$password = 'mohammed2'; 
$db_name = 'webtech_fall2024_rahinatu_lawal';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Ensure user is logged in (you'll need to implement login logic)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$userId) {
    // Redirect to login or handle unauthorized access
    header('Location: ../login.php');
    exit;
}

// Get entity type ID for notes
try {
    // First try to get the entity type
    $entityTypeStmt = $pdo->prepare("SELECT entityTypeId FROM entityTypes WHERE entityTypeName = 'note'");
    $entityTypeStmt->execute();
    $entityType = $entityTypeStmt->fetch(PDO::FETCH_ASSOC);
    
    // If it doesn't exist, create it
    if (!$entityType) {
        $insertStmt = $pdo->prepare("INSERT INTO entitytypes (entityTypeName) VALUES ('note')");
        $insertStmt->execute();
        $noteEntityTypeId = $pdo->lastInsertId();
        error_log("Created new entity type with ID: " . $noteEntityTypeId);
    } else {
        $noteEntityTypeId = $entityType['entityTypeId'];
        error_log("Found existing entity type with ID: " . $noteEntityTypeId);
    }

    if (!$noteEntityTypeId) {
        throw new Exception("Failed to get or create note entity type");
    }
} catch(PDOException $e) {
    error_log("Error with entity type: " . $e->getMessage());
    die("Error with entity type: " . $e->getMessage());
}

// Fetch topics and notes (similar to previous code)
try {
    $topicStmt = $pdo->prepare("SELECT topicId, topicName FROM topics WHERE courseId = 1");
    $topicStmt->execute();
    $topics = $topicStmt->fetchAll(PDO::FETCH_ASSOC);

    $noteStmt = $pdo->prepare("
        SELECT n.noteId, n.topicId, n.noteText, n.image_path, t.topicName, n.created_at 
        FROM notes n 
        JOIN topics t ON n.topicId = t.topicId 
        WHERE t.courseId = 1
    ");
    $noteStmt->execute();
    $notes = $noteStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_POST['action'] === 'submit_comment') {
            // Validate input
            $noteId = filter_input(INPUT_POST, 'noteId', FILTER_VALIDATE_INT);
            $commentText = trim($_POST['commentText']);

            if (!$noteId || empty($commentText)) {
                throw new Exception("Invalid input");
            }

            // Insert comment
            $commentStmt = $pdo->prepare("
                INSERT INTO comments (
                    userId, 
                    parentId, 
                    entityTypeId, 
                    entityId, 
                    commentText, 
                    isRead, 
                    created_at
                ) VALUES (
                    :userId, 
                    NULL, 
                    :entityTypeId, 
                    :entityId, 
                    :commentText, 
                    0, 
                    NOW()
                )
            ");

            $commentStmt->execute([
                ':userId' => $userId,
                ':entityTypeId' => $noteEntityTypeId,
                ':entityId' => $noteId,
                ':commentText' => $commentText
            ]);

            // Respond with success
            echo json_encode([
                'status' => 'success', 
                'commentId' => $pdo->lastInsertId(),
                'userId' => $userId
            ]);
            exit;
        }

        if ($_POST['action'] === 'get_comments') {
            $noteId = $_POST['noteId'];
            
            // Debug: Print values
            error_log("Note ID: " . $noteId);
            error_log("Entity Type ID: " . $noteEntityTypeId);
            
            try {
                if (!$noteId) {
                    throw new Exception("Invalid note ID");
                }
                
                error_log("Fetching comments for note ID: " . $noteId . " and entity type ID: " . $noteEntityTypeId);
                
                // Get comments for this note
                $commentStmt = $pdo->prepare("
                    SELECT c.commentId, c.commentText, c.created_at, u.firstName 
                    FROM comments c
                    JOIN users u ON c.userId = u.userId
                    WHERE c.entityTypeId = ? 
                    AND c.entityId = ?
                    ORDER BY c.created_at DESC
                ");
                
                $commentStmt->execute([$noteEntityTypeId, $noteId]);
                $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Found " . count($comments) . " comments");
                
                echo json_encode([
                    'status' => 'success',
                    'comments' => $comments
                ]);
                exit;
            } catch (Exception $e) {
                error_log("Error in get_comments: " . $e->getMessage());
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Error fetching comments: ' . $e->getMessage()
                ]);
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Error in get_comments: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error fetching comments: ' . $e->getMessage()
        ]);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculus Notes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        window.jsPDF = window.jspdf.jsPDF;
    </script>
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
            position: relative;
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
        .notes-container {
            display: flex;
            flex-direction: column; /* Stack notes vertically */
            gap: 20px;
        }
        .note-card {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            box-sizing: border-box;
            transition: transform 0.3s ease-in-out;
        }
        .note-card:hover {
            transform: translateY(-5px);
        }
        .note-header {
            font-size: 20px;
            font-weight: bold;
            color: #34395E;
            margin-bottom: 15px;
        }

        .download-btn {
            background-color: #FF6B6B;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
            padding: 8px 15px;
            font-size: inherit;
            font-family: inherit;
        }

        .download-btn:hover {
            background-color: #ff4f4f;
        }

        .view-comments-btn {
            background-color: #34495E;
        }

        .view-comments-btn:hover {
            background-color: #2c3e50;
        }

        .note-header {
            margin-bottom: 15px;
        }

        .note-content {
            margin-bottom: 15px;
        }
        .comments-section {
            margin-top: 15px;
            display: none;
            background-color: #34495E;
            padding: 10px;
            border-radius: 5px;
        }
        .comment-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
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

        .comments-display .comment {
            background-color: #e9e9e9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .comments-section {
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .comments-display {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
        }

        .comment {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .comment .username {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .comment .timestamp {
            font-size: 0.8em;
            color: #666;
            margin-left: 10px;
        }

        .comment-input {
            display: flex;
            gap: 10px;
        }

        .comment-textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 60px;
        }

        .submit-comment {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-comment:hover {
            background-color: #45a049;
        }

        /* Header styles */
        .questions-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #34495E;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .questions-btn:hover {
            background-color: #2c3e50;
        }

        /* Note actions styles */
        .note-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            margin-top: 15px;
        }

        .note-actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            transition: background-color 0.3s;
        }
    </style>
</head>
<body>
    <header class="calculus-header">
        <h1>Calculus Notes</h1>
        <button class="questions-btn" onclick="window.location.href='calculus_question.php';">Questions</button>
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

        <div class="notes-container" id="notesContainer">
            <?php foreach ($notes as $note): ?>
                <div class="note-card" data-topic-id="<?php echo $note['topicId']; ?>">
                    <div class="note-header">
                        <h3><?php echo htmlspecialchars($note['topicName']); ?></h3>
                        <span class="timestamp"><?php echo date('M j, Y g:i A', strtotime($note['created_at'])); ?></span>
                    </div>


                    <div class="note-content">
                        <?php echo $note['noteText']; ?>
                        <?php if (!empty($note['image_path'])): ?>
                        <img class="note-image" 
                             src="<?php echo nl2br(htmlspecialchars($note['image_path'])); ?>" 
                             <?php echo $note['image_path']; ?>
                             alt="News Image for <?php echo htmlspecialchars($note['topicName']); ?>">
                        <?php endif; ?>
                    
                        <div class="note-actions">
                            <button class="download-btn" data-topic-name="<?php echo $note['topicName']; ?>" data-note-id="<?php echo $note['noteId']; ?>">Download</button>
                            <button class="view-comments-btn" onclick="toggleComments(this)" data-note-id="<?php echo $note['noteId']; ?>">
                                <i class="fas fa-comments"></i> View Comments
                            </button>
                        </div>
                        <div class="comments-section" style="display: none;">
                            <div class="comments-display"></div>
                            <div class="comment-input">
                                <textarea placeholder="Write a comment..." class="comment-textarea"></textarea>
                                <button class="submit-comment" data-note-id="<?php echo $note['noteId']; ?>">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Function to toggle comments visibility
        function toggleComments(button) {
            const noteCard = button.closest('.note-card');
            const commentsSection = noteCard.querySelector('.comments-section');
            const noteId = button.getAttribute('data-note-id');
            
            if (commentsSection.style.display === 'none') {
                // Hide all other comment sections first
                document.querySelectorAll('.comments-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show this comment section and fetch comments
                commentsSection.style.display = 'block';
                fetchComments(noteId, noteCard);
                button.innerHTML = '<i class="fas fa-comments"></i> Hide Comments';
            } else {
                commentsSection.style.display = 'none';
                button.innerHTML = '<i class="fas fa-comments"></i> View Comments';
            }
        }

        // Function to fetch comments
        function fetchComments(noteId, noteCard) {
            const commentsDisplay = noteCard.querySelector('.comments-display');
            commentsDisplay.innerHTML = '<p>Loading comments...</p>';

            const formData = new FormData();
            formData.append('action', 'get_comments');
            formData.append('noteId', noteId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw server response:', text); // Debug log
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        if (data.comments && data.comments.length > 0) {
                            commentsDisplay.innerHTML = '';
                            data.comments.forEach(comment => {
                                const commentDiv = document.createElement('div');
                                commentDiv.className = 'comment';
                                
                                const date = new Date(comment.created_at);
                                const formattedDate = date.toLocaleString();
                                
                                commentDiv.innerHTML = `
                                    <div class="username">${comment.firstName}</div>
                                    <div class="comment-text">
                                        ${comment.commentText}
                                        <span class="timestamp">${formattedDate}</span>
                                    </div>
                                `;
                                commentsDisplay.appendChild(commentDiv);
                            });
                        } else {
                            commentsDisplay.innerHTML = '<p>No comments yet.</p>';
                        }
                    } else {
                        throw new Error(data.message || 'Unknown error');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Raw response text:', text);
                    throw e;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                commentsDisplay.innerHTML = `<p>Error: ${error.message}</p>`;
            });
        }

        // Topic filtering functionality
        const topicsList = document.getElementById('topicsList');
        const notesContainer = document.getElementById('notesContainer');

        topicsList.addEventListener('click', (e) => {
            if (e.target.tagName === 'LI') {
                // Update active class
                document.querySelectorAll('#topicsList li').forEach(li => li.classList.remove('active'));
                e.target.classList.add('active');

                const selectedTopicId = e.target.getAttribute('data-topic-id');
                const noteCards = document.querySelectorAll('.note-card');

                noteCards.forEach(card => {
                    if (selectedTopicId === 'all' || card.getAttribute('data-topic-id') === selectedTopicId) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });

        // Handle comment submission
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('submit-comment')) {
                const noteCard = e.target.closest('.note-card');
                const commentInput = noteCard.querySelector('.comment-textarea');
                const noteId = e.target.getAttribute('data-note-id');
                const commentText = commentInput.value.trim();
                
                if (commentText) {
                    const formData = new FormData();
                    formData.append('action', 'submit_comment');
                    formData.append('noteId', noteId);
                    formData.append('commentText', commentText);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            commentInput.value = ''; // Clear input
                            fetchComments(noteId, noteCard); // Reload comments
                        } else {
                            alert('Failed to submit comment: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting the comment');
                    });
                }
            }
        });

        // Download functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('download-btn') || e.target.closest('.download-btn')) {
                const button = e.target.classList.contains('download-btn') ? e.target : e.target.closest('.download-btn');
                const noteId = button.getAttribute('data-note-id');
                const topicName = button.getAttribute('data-topic-name');
                const noteCard = button.closest('.note-card');
                const noteContent = noteCard.querySelector('.note-content').innerText;
                
                const doc = new jsPDF();
                
                // Add title
                doc.setFontSize(16);
                doc.text(`Notes: ${topicName}`, 20, 20);
                
                // Add content with word wrap
                doc.setFontSize(12);
                const splitText = doc.splitTextToSize(noteContent, 170);
                doc.text(splitText, 20, 30);
                
                // Save the PDF
                doc.save(`${topicName}_Note_${noteId}.pdf`);
            }
        });
    </script>
</body>
</html>
