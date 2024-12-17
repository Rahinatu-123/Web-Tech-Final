<?php
session_start();
include './db/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle Delete Operation
if (isset($_POST['delete_news'])) {
    $newsId = $_POST['news_id'];
    $delete_query = "DELETE FROM news WHERE newsId = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $newsId);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "News deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting news!";
    }
    header('Location: manaNews.php');
    exit;
}

// Fetch all news from database
$query = "SELECT n.*, CONCAT(u.firstName, ' ', u.lastName) as author_name 
          FROM news n 
          JOIN users u ON n.userId = u.userId 
          ORDER BY n.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            padding-left: 0 !important;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: var(--white);
            border-bottom: var(--border);
            padding: 0 2rem;
        }

        .container {
            margin-top: 8rem;
            padding: 0 2rem;
        }

        .news-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        }

        .news-table th,
        .news-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--light-bg);
        }

        .news-table th {
            background: var(--main-color);
            color: var(--white);
            font-size: 1.8rem;
        }

        .news-table td {
            font-size: 1.6rem;
        }

        .news-table tr:hover {
            background: var(--light-bg);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .action-btn {
            padding: .8rem 1.5rem;
            border-radius: .5rem;
            font-size: 1.4rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background: var(--main-color);
            color: var(--white);
        }

        .delete-btn {
            background: var(--red);
            color: var(--white);
        }

        .news-content {
            max-width: 500px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="admin_dashboard.php" class="logo">StudyNest</a>
            <div class="icons">
                <a href="admin_dashboard.php" class="fas fa-home"></a>
            </div>
        </section>
    </header>

    <div class="container">
        <h1 class="heading">Manage News</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <table class="news-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Views</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($news = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($news['newsTitle']); ?></td>
                            <td class="news-content"><?php echo strip_tags($news['newsContent']); ?></td>
                            <td><?php echo htmlspecialchars($news['author_name']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($news['created_at'])); ?></td>
                            <td><?php echo $news['views']; ?></td>
                            <td class="action-buttons">
                                <a href="editNews.php?id=<?php echo $news['newsId']; ?>" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="news_id" value="<?php echo $news['newsId']; ?>">
                                    <button type="submit" name="delete_news" class="action-btn delete-btn" 
                                            onclick="return confirm('Are you sure you want to delete this news?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No news articles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>