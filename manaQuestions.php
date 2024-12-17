<?php
session_start();
include './db/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleId'] != 2) {
    header('Location: login.php');
    exit;
}

// Handle question deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $questionId = $_POST['delete'];
    
    // Delete the question
    $stmt = $conn->prepare("DELETE FROM questions WHERE questionId = ?");
    $stmt->bind_param("i", $questionId);
    
    if ($stmt->execute()) {
        $success = "Question deleted successfully!";
    } else {
        $error = "Error deleting question: " . $conn->error;
    }
}

// Debug database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all questions
$query = "SELECT * FROM questions";
echo "Executing query: " . $query . "<br>";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo "Number of rows: " . $result->num_rows . "<br>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Admin Dashboard</title>
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

        .header .flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            padding: 1.5rem 0;
        }

        .header .flex h1 {
            margin: 0;
            white-space: nowrap;
        }

        .container {
            margin-top: 8rem;
            padding: 2rem;
        }

        .questions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        }

        .questions-table th,
        .questions-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--light-bg);
        }

        .questions-table th {
            background: var(--main-color);
            color: var(--white);
            font-size: 1.8rem;
        }

        .questions-table td {
            font-size: 1.6rem;
        }

        .questions-table tr:hover {
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
            background: var(--orange);
            color: var(--white);
        }

        .delete-btn {
            background: var(--red);
            color: var(--white);
            border: none;
        }

        .message {
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: .5rem;
            text-align: center;
            font-size: 1.8rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .question-content {
            max-width: 40rem;
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
            <h1 style="font-size: 2.5rem; color: var(--black);">Manage Questions</h1>
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="search-btn" class="fas fa-search"></div>
                <div id="toggle-btn" class="fas fa-sun"></div>
            </div>
            <div class="profile">
                <a href="admin_dashboard.php" class="btn">Dashboard</a>
                <div class="flex-btn">
                    <a href="logout.php" class="option-btn">logout</a>
                </div>
            </div>
        </section>
    </header>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <table class="questions-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Posted By</th>
                    <th>Topic</th>
                    <th>Posted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && $result->num_rows > 0):
                    while ($question = $result->fetch_assoc()): 
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($question['questionId']); ?></td>
                        <td class="question-content"><?php echo strip_tags($question['questionText']); ?></td>
                        <td><?php echo htmlspecialchars($question['userId']); ?></td>
                        <td><?php echo htmlspecialchars($question['topicId']); ?></td>
                        <td><?php echo htmlspecialchars($question['created_at']); ?></td>
                        <td class="action-buttons">
                            <a href="editQuestion.php?id=<?php echo $question['questionId']; ?>" class="action-btn edit-btn">Edit</a>
                            <form method="POST" style="display: inline-flex;">
                                <button type="submit" name="delete" value="<?php echo $question['questionId']; ?>" 
                                        class="action-btn delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this question?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No questions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Fade out messages after 3 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(() => msg.remove(), 500);
            });
        }, 3000);
    </script>
</body>
</html>