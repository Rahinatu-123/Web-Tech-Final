<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleId'] != 2) {
    header('Location: login.php');
    exit;
}

// Get question ID from URL
$editId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$editId) {
    header('Location: manaQuestions.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $courseId = $_POST['courseId'];

    // Update question information
    $stmt = $conn->prepare("UPDATE questions SET title = ?, content = ?, courseId = ?, updated_at = NOW() WHERE questionId = ?");
    $stmt->bind_param("ssii", $title, $content, $courseId, $editId);
    
    if ($stmt->execute()) {
        header('Location: manaQuestions.php?success=1');
        exit;
    } else {
        $error = "Error updating question: " . $conn->error;
    }
}

// Fetch question data
$stmt = $conn->prepare("SELECT * FROM questions WHERE questionId = ?");
$stmt->bind_param("i", $editId);
$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();

if (!$question) {
    header('Location: manaQuestions.php');
    exit;
}

// Fetch all courses for the dropdown
$courses = $conn->query("SELECT courseId, courseName FROM courses ORDER BY courseName");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
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

        .edit-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            font-size: 1.6rem;
            color: var(--black);
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            border: 1px solid var(--light-bg);
            border-radius: 0.5rem;
        }

        .form-group textarea {
            height: 20rem;
            resize: vertical;
        }

        .submit-btn {
            background: var(--main-color);
            color: var(--white);
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.6rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #ff5252;
        }

        .back-btn {
            display: inline-block;
            background: var(--light-bg);
            color: var(--black);
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            font-size: 1.6rem;
            margin-right: 1rem;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="admin_dashboard.php" class="logo">StudyNest</a>
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
        <h1 style="font-size: 3rem; margin: 2rem; color: var(--black);">Edit Question</h1>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="edit-form">
            <form method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($question['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($question['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="courseId">Course</label>
                    <select id="courseId" name="courseId" required>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $course['courseId']; ?>" 
                                    <?php echo $course['courseId'] == $question['courseId'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['courseName']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="margin-top: 2rem;">
                    <a href="manaQuestions.php" class="back-btn">Back</a>
                    <button type="submit" class="submit-btn">Update Question</button>
                </div>
            </form>
        </div>
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
