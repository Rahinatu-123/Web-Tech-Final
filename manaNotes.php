<?php
session_start();
include './db/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleId'] != 2) {
    header('Location: login.php');
    exit;
}

// Handle note deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $noteId = $_POST['delete'];
    
    // Delete the note
    $stmt = $conn->prepare("DELETE FROM notes WHERE noteId = ?");
    $stmt->bind_param("i", $noteId);
    
    if ($stmt->execute()) {
        $success = "Note deleted successfully!";
    } else {
        $error = "Error deleting note: " . $conn->error;
    }
}

// Debug database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all notes with user information
$query = "SELECT n.*, u.firstName, u.lastName, t.topicName 
          FROM notes n 
          LEFT JOIN users u ON n.userId = u.userId
          LEFT JOIN topics t ON n.topicId = t.topicId
          ORDER BY n.created_at DESC";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes - Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #2196F3;
            --accent-color: #FF9800;
            --danger-color: #f44336;
            --success-color: #4CAF50;
            --text-color: #333;
            --text-light: #666;
            --bg-color: #f5f5f5;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
        }

        body {
            padding-left: 0 !important;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .container {
            margin-top: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .heading {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .notes-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2rem;
            background: var(--card-bg);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .notes-table th,
        .notes-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .notes-table th {
            background: var(--primary-color);
            color: white;
            font-size: 1.6rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notes-table td {
            font-size: 1.4rem;
            vertical-align: top;
            color: var(--text-color);
        }

        .notes-table tr:hover {
            background: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .notes-table tr:last-child td {
            border-bottom: none;
        }

        .action-buttons {
            display: flex;
            gap: 0.8rem;
        }

        .action-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 1.4rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .edit-btn {
            background: var(--secondary-color);
            color: white;
        }

        .edit-btn:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }

        .delete-btn {
            background: var(--danger-color);
            color: white;
        }

        .delete-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .view-btn {
            background: var(--accent-color);
            color: white;
        }

        .view-btn:hover {
            background: #F57C00;
            transform: translateY(-2px);
        }

        .message {
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            font-size: 1.6rem;
            text-align: center;
            animation: slideIn 0.5s ease;
        }

        .success {
            background: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid var(--success-color);
        }

        .error {
            background: #FFEBEE;
            color: #C62828;
            border-left: 4px solid var(--danger-color);
        }

        .note-content {
            max-width: 40rem;
            max-height: 8rem;
            overflow: hidden;
            position: relative;
            line-height: 1.6;
            color: var(--text-color);
        }

        .note-content:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: linear-gradient(transparent, var(--card-bg));
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            position: relative;
            background: var(--card-bg);
            margin: 5% auto;
            padding: 2rem;
            width: 80%;
            max-width: 800px;
            border-radius: 1rem;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.3s ease;
        }

        .close-btn {
            position: absolute;
            right: 2rem;
            top: 2rem;
            font-size: 2.5rem;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: var(--danger-color);
        }

        .note-image {
            max-width: 100px;
            height: auto;
            cursor: pointer;
            border-radius: 0.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .note-image:hover {
            transform: scale(1.05);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .topic-badge {
            background: var(--secondary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            font-size: 1.2rem;
            display: inline-block;
        }

        .date-cell {
            color: var(--text-light);
            font-size: 1.3rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="admin_dashboard.php" class="logo">StudyNest</a>
            <h1 class="heading">Manage Notes</h1>
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
            <div class="message success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <table class="notes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Topic</th>
                    <th>Content</th>
                    <th>Posted By</th>
                    <th>Posted Date</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && $result->num_rows > 0):
                    while ($note = $result->fetch_assoc()): 
                        $initial = strtoupper(substr($note['firstName'], 0, 1));
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($note['noteId']); ?></td>
                        <td><span class="topic-badge"><?php echo htmlspecialchars($note['topicName']); ?></span></td>
                        <td>
                            <div class="note-content">
                                <?php echo $note['noteText']; ?>
                            </div>
                            <button class="action-btn view-btn" onclick="showNoteModal(<?php echo htmlspecialchars(json_encode($note['noteText'])); ?>)">
                                <i class="fas fa-eye"></i> View Full Note
                            </button>
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar"><?php echo $initial; ?></div>
                                <?php echo htmlspecialchars($note['firstName'] . ' ' . $note['lastName']); ?>
                            </div>
                        </td>
                        <td class="date-cell"><?php echo date('F j, Y', strtotime($note['created_at'])); ?></td>
                        <td>
                            <?php if ($note['image_path']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($note['image_path']); ?>" 
                                     alt="Note Image" 
                                     class="note-image"
                                     onclick="showImageModal('uploads/<?php echo htmlspecialchars($note['image_path']); ?>')">
                            <?php else: ?>
                                <span style="color: var(--text-light);">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="editNote.php?id=<?php echo $note['noteId']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" style="display: inline-flex;">
                                <button type="submit" name="delete" value="<?php echo $note['noteId']; ?>" 
                                        class="action-btn delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this note?');">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-light);">
                            No notes found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for viewing full note -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeNoteModal()">&times;</span>
            <div id="modalNoteContent"></div>
        </div>
    </div>

    <!-- Modal for viewing full image -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" style="width: 100%; height: auto; border-radius: 0.5rem;">
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
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

        // Note Modal Functions
        function showNoteModal(content) {
            const modal = document.getElementById('noteModal');
            const modalContent = document.getElementById('modalNoteContent');
            modalContent.innerHTML = content;
            modal.style.display = 'block';
        }

        function closeNoteModal() {
            const modal = document.getElementById('noteModal');
            modal.style.display = 'none';
        }

        // Image Modal Functions
        function showImageModal(imagePath) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imagePath;
            modal.style.display = 'block';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const noteModal = document.getElementById('noteModal');
            const imageModal = document.getElementById('imageModal');
            if (event.target == noteModal) {
                closeNoteModal();
            }
            if (event.target == imageModal) {
                closeImageModal();
            }
        }
    </script>
</body>
</html>