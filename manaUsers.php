<?php
session_start();
include './db/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleId'] != 2) {
    header('Location: login.php');
    exit;
}

// Handle Delete Action
if (isset($_POST['delete'])) {
    $deleteId = $_POST['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE userId = ?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
}

// Fetch all users
$stmt = $conn->prepare("SELECT userId, firstName, lastName, email, roleId, majorId, yearGroup, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            padding-left: 0 !important; /* Override the default padding */
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
            margin-top: 8rem; /* Add margin to prevent content from going under header */
            padding: 0 2rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: var(--white);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        }

        .users-table th,
        .users-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--light-bg);
        }

        .users-table th {
            background: var(--main-color);
            color: var(--white);
            font-size: 1.8rem;
        }

        .users-table td {
            font-size: 1.6rem;
        }

        .users-table tr:hover {
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
            margin: 2rem;
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

        /* Add styles for the side-bar */
        .side-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 30rem;
            background-color: var(--white);
            border-right: var(--border);
            z-index: 1200;
            transition: .2s linear;
            overflow-y: auto;
            padding-top: 8rem; /* Space for header */
        }

        .side-bar.active {
            left: -31rem;
        }

        /* Adjust main content when sidebar is present */
        .side-bar ~ .container {
            padding-left: 32rem;
            transition: .2s linear;
        }

        .side-bar.active ~ .container {
            padding-left: 2rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="admin_dashboard.php" class="logo">StudyNest</a>
            <h1 style="font-size: 2.5rem; color: var(--black);">Users Management</h1>
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
        <h1 style="font-size: 3rem; margin: 2rem; color: var(--black);">Manage Users</h1>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Major</th>
                    <th>Year Group</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['userId']); ?></td>
                        <td><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo $user['roleId'] == 2 ? 'Admin' : 'Student'; ?></td>
                        <td><?php echo htmlspecialchars($user['majorId']); ?></td>
                        <td><?php echo htmlspecialchars($user['yearGroup']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td class="action-buttons">
                            <a href="editUser.php?id=<?php echo $user['userId']; ?>" class="action-btn edit-btn">Edit</a>
                            <form method="POST" style="display: inline-flex;">
                                <button type="submit" name="delete" value="<?php echo $user['userId']; ?>" 
                                        class="action-btn delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this user?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Fade out success/error messages after 3 seconds
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