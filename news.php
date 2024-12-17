<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection using mysqli
$host = 'localhost';
$user = 'rahinatu.lawal'; 
$password = 'mohammed2'; 
$db_name = 'webtech_fall2024_rahinatu_lawal';

// Create connection
$conn = new mysqli($host, $user, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to safely fetch news items
function fetchNewsItems($connection) {
    $query = "
    SELECT n.*, 
               u.firstName as author_name,
               n.fileAttachment as image,  -- Include the fileAttachment as image
               (SELECT COUNT(*) FROM likes WHERE entityId = n.newsId AND entityTypeId = 1) as likes_count,
               (SELECT COUNT(*) FROM comments WHERE entityId = n.newsId AND entityTypeId = 1) as comments_count,
               l.likeId,
               l.userId as liked_by_user_id,
               l.entityTypeId as like_entity_type_id,
               l.created_at as like_created_at
        FROM news n
        JOIN users u ON n.userId = u.userId
        LEFT JOIN likes l ON l.entityId = n.newsId AND l.entityTypeId = 1
        ORDER BY n.created_at DESC

    ";
    
    $result = $connection->query($query);
    
    if (!$result) {
        error_log("Database error: " . $connection->error);
        return [];
    }
    
    $news_items = [];
    while ($row = $result->fetch_assoc()) {
        $news_items[] = $row;
    }
    
    return $news_items;
}

// Handle POST requests for likes and comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first']);
        exit;
    }

    header('Content-Type: application/json');
    
    try {
        if ($_POST['action'] === 'like') {
            $newsId = filter_input(INPUT_POST, 'newsId', FILTER_VALIDATE_INT);
            
            // Check if already liked
            $checkStmt = $conn->prepare("SELECT * FROM likes WHERE entityId = ? AND userId = ? AND entityTypeId = 1");
            $checkStmt->bind_param("ii", $newsId, $_SESSION['user_id']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Unlike
                $stmt = $conn->prepare("DELETE FROM likes WHERE entityId = ? AND userId = ? AND entityTypeId = 1");
                $stmt->bind_param("ii", $newsId, $_SESSION['user_id']);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'action' => 'unliked']);
            } else {
                // Like
                $stmt = $conn->prepare("INSERT INTO likes (entityId, userId, entityTypeId) VALUES (?, ?, 1)");
                $stmt->bind_param("ii", $newsId, $_SESSION['user_id']);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'action' => 'liked']);
            }
            exit;
        }
        
        if ($_POST['action'] === 'comment') {
            $newsId = filter_input(INPUT_POST, 'newsId', FILTER_VALIDATE_INT);
            $commentText = trim($_POST['commentText']);
            
            if (empty($commentText)) {
                throw new Exception("Comment cannot be empty");
            }
            
            $stmt = $conn->prepare("
                INSERT INTO comments (entityId, userId, commentText, created_at, entityTypeId) 
                VALUES (?, ?, ?, NOW(), 1)
            ");
            $stmt->bind_param("iis", $newsId, $_SESSION['user_id'], $commentText);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'comment' => [
                    'commentId' => $conn->insert_id,
                    'commentText' => $commentText,
                    'firstName' => $_SESSION['firstName'],
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
            exit;
        }
        
        if ($_POST['action'] === 'fetch_comments') {
            $newsId = filter_input(INPUT_POST, 'newsId', FILTER_VALIDATE_INT);
            
            $stmt = $conn->prepare("
                SELECT c.*, u.firstName 
                FROM comments c
                JOIN users u ON c.userId = u.userId
                WHERE c.entityId = ? AND c.entityTypeId = 1
                ORDER BY c.created_at DESC
            ");
            $stmt->bind_param("i", $newsId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comments = [];
            
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'comments' => $comments]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Fetch news items
$news_items = fetchNewsItems($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - StudyNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
            line-height: 1.6;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: #FF6B6B;
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            margin: 0;
            font-size: 3em;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-header p {
            margin: 10px 0 0;
            font-size: 1.2em;
            color: rgba(255, 255, 255, 0.9);
        }

        .news-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .news-card {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .news-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #FF6B6B;
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .news-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f3f5;
        }

        .author-name {
            font-size: 1em;
            color: #34495E;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .author-name i {
            color: #34495E;
        }

        .timestamp {
            color: #6c757d;
            font-size: 0.9em;
        }

        .news-title {
            font-size: 2em;
            font-weight: 700;
            color: #34495E;
            margin: 20px 0;
            line-height: 1.3;
            letter-spacing: -0.5px;
        }

        .news-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .news-content {
            margin: 25px 0;
            line-height: 2;
            color: #34495e;
            font-size: 1.1em;
            white-space: pre-wrap;
            text-align: justify;
        }

        .news-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #f1f3f5;
        }

        .news-actions-left, .news-actions-right {
            display: flex;
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border: none;
            background: none;
            cursor: pointer;
            color: #34495E;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background-color: #f1f3f5;
        }

        .action-btn i {
            font-size: 1.2em;
        }

        .liked {
            color: #FF6B6B;
        }

        .comments-section {
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .comment {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .commenter-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .comment-form {
            margin-top: 20px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
        }

        .comment-form textarea:focus {
            outline: none;
            border-color: #2575fc;
        }

        .comment-form button {
            padding: 12px 20px;
            background-color: #2575fc;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .comment-form button:hover {
            background-color: #34495E;
        }

        .no-news {
            text-align: center;
            padding: 60px;
            color: #6c757d;
            font-size: 1.2em;
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <div class="page-header">
        <h1>News Hub</h1>
        <p>Stay informed, stay connected</p>
    </div>

    <div class="news-container">
        <?php if (empty($news_items)): ?>
            <div class="no-news">
                <i class="fas fa-newspaper"></i> 
                No news articles available at the moment.
                Check back later for updates!
            </div>
        <?php else: ?>
            
            <?php foreach ($news_items as $news): ?>

                <div class="news-card" data-news-id="<?php echo htmlspecialchars($news['newsId']); ?>">
                    <div class="news-header">
                        <span class="author-name">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($news['author_name'] ?? 'Anonymous'); ?>
                        </span>
                        <span class="timestamp">
                            <i class="fas fa-clock"></i>
                            <?php echo date('F j, Y · g:i A', strtotime($news['created_at'])); ?>
                        </span>
                    </div>
                    
                    <h2 class="news-title">
                        <?php echo htmlspecialchars($news['newsTitle'] ?? 'Untitled News'); ?>
                    </h2>
                    
                    <?php if (!empty($news['image'])): ?>
                        <img class="news-image" 
                             src="<?php echo nl2br(htmlspecialchars($news['image'])); ?>" 
                             <?php echo $news['image']; ?>
                             alt="News Image for <?php echo htmlspecialchars($news['newsTitle']); ?>">
                    <?php endif; ?>
                    
                    <div class="news-content">
                        <?php echo nl2br(htmlspecialchars($news['newsContent'] ?? 'No content available')); ?>
                    </div>
                    
                    <div class="news-actions">
                        <div class="news-actions-left">
                            
                            <button class="action-btn like-btn"  onclick="toggleLike(<?php echo $news['newsId']; ?>)">
                                <i class="fas fa-heart"></i>
                                <span class="action-label">Likes:</span>
                                <span class="likes-count"><?php echo $news['likes_count']; ?></span>
                            </button>

                            <button class="action-btn comment-btn" onclick="toggleComments(<?php echo $news['newsId']; ?>)">
                                <i class="fas fa-comment"></i>
                                <span class="action-label">Comments:</span>
                                <span class="comments-count"><?php echo $news['comments_count']; ?></span>
                            </button>
                    </div>
                    
                    <div id="comments-section-<?php echo $news['newsId']; ?>" class="comments-section" style="display: none;">
                        <div id="comments-<?php echo $news['newsId']; ?>" class="comments-container"></div>
                        <div class="comment-form">
                            <textarea id="comment-text-<?php echo $news['newsId']; ?>" placeholder="Write a comment..."></textarea>
                            <button onclick="submitComment(<?php echo $news['newsId']; ?>)">Submit Comment</button>
                        </div>
                    </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Utility function to check login status
function checkLoginStatus(action) {
    if (!document.body.classList.contains('logged-in')) {
        alert(`Please log in to ${action}`);
        return false;
    }
    return true;
}

// Function to toggle like on a news item
function toggleLike(newsId) {
    if (!checkLoginStatus('like this news')) return;

    const likeBtn = document.querySelector(`[data-news-id="${newsId}"] .like-btn`);
    const likesCount = likeBtn.querySelector('.likes-count');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like&newsId=${newsId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const currentCount = parseInt(likesCount.textContent);
            if (data.action === 'liked') {
                likeBtn.classList.add('liked');
                likesCount.textContent = currentCount + 1;
            } else {
                likeBtn.classList.remove('liked');
                likesCount.textContent = currentCount - 1;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your like.');
    });
}

// Function to toggle comments section
function toggleComments(newsId) {
    const commentsSection = document.getElementById(`comments-section-${newsId}`);
    if (!commentsSection) return;

    const isVisible = commentsSection.style.display === 'block';
    
    if (!isVisible) {
        fetchComments(newsId);
        commentsSection.style.display = 'block';
    } else {
        commentsSection.style.display = 'none';
    }
}

// Function to fetch comments for a specific news item
function fetchComments(newsId) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=fetch_comments&newsId=${newsId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const commentsDisplay = document.getElementById(`comments-${newsId}`);
            commentsDisplay.innerHTML = '';
            
            if (data.comments.length === 0) {
                commentsDisplay.innerHTML = `
                    <div class="no-comments">
                        <i class="fas fa-comment-slash"></i> 
                        No comments yet. Be the first to comment!
                    </div>
                `;
                return;
            }

            // Render comments with enhanced styling
            data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.innerHTML = `
                    <div class="comment-header">
                        <span class="commenter-name">
                            <i class="fas fa-user-circle"></i> ${comment.firstName}
                        </span>
                        <span class="timestamp">
                            <i class="fas fa-clock"></i> ${new Date(comment.created_at).toLocaleString()}
                        </span>
                    </div>
                    <div class="comment-text">${comment.commentText}</div>
                `;
                commentsDisplay.appendChild(commentDiv);
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while fetching comments.');
    });
}

// Function to submit a new comment
function submitComment(newsId) {
    if (!checkLoginStatus('comment')) return;

    const commentTextarea = document.getElementById(`comment-text-${newsId}`);
    const commentText = commentTextarea.value.trim();
    
    if (!commentText) {
        alert('Please enter a comment before submitting.');
        return;
    }

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=comment&newsId=${newsId}&commentText=${encodeURIComponent(commentText)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const commentsDisplay = document.getElementById(`comments-${newsId}`);
            
            // Remove 'No comments' message if present
            const noCommentsDiv = commentsDisplay.querySelector('.no-comments');
            if (noCommentsDiv) {
                noCommentsDiv.remove();
            }

            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.innerHTML = `
                <div class="comment-header">
                    <span class="commenter-name">
                        <i class="fas fa-user-circle"></i> ${data.comment.firstName}
                    </span>
                    <span class="timestamp">
                        <i class="fas fa-clock"></i> ${new Date(data.comment.created_at).toLocaleString()}
                    </span>
                </div>
                <div class="comment-text">${data.comment.commentText}</div>
            `;
            
            // Insert new comment at the top
            commentsDisplay.insertBefore(commentDiv, commentsDisplay.firstChild);
            
            // Clear the textarea
            commentTextarea.value = '';
            
            // Update comment count
            const commentBtn = document.querySelector(`[data-news-id="${newsId}"] .comment-btn .comments-count`);
            const currentCount = parseInt(commentBtn.textContent);
            commentBtn.textContent = currentCount + 1;

            // Ensure comments section is visible
            const commentsSection = document.getElementById(`comments-section-${newsId}`);
            commentsSection.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your comment.');
    });
}
    </script>
</body>
</html>
