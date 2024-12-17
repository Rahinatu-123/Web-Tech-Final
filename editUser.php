<?php
session_start();
include './db/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['roleId'] != 2) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL
$editId = isset($_GET['id']) ? $_GET['id'] : null;
if (!$editId) {
    header('Location: manaUsers.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $roleId = $_POST['roleId'];
    $majorId = $_POST['majorId'];
    $yearGroup = $_POST['yearGroup'];

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, roleId = ?, majorId = ?, yearGroup = ?, updated_at = NOW() WHERE userId = ?");
    $stmt->bind_param("sssiiii", $firstName, $lastName, $email, $roleId, $majorId, $yearGroup, $editId);
    
    if ($stmt->execute()) {
        header('Location: manaUsers.php?success=1');
        exit;
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE userId = ?");
$stmt->bind_param("i", $editId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: manaUsers.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .edit-form {
            max-width: 600px;
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
        .form-group select {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            border: 1px solid var(--light-bg);
            border-radius: 0.5rem;
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
        <h1 style="font-size: 3rem; margin: 2rem; color: var(--black);">Edit User</h1>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="edit-form">
            <form method="POST">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="roleId">Role</label>
                    <select id="roleId" name="roleId" required>
                        <option value="1" <?php echo $user['roleId'] == 1 ? 'selected' : ''; ?>>Student</option>
                        <option value="2" <?php echo $user['roleId'] == 2 ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="majorId">Major ID</label>
                    <input type="number" id="majorId" name="majorId" value="<?php echo htmlspecialchars($user['majorId']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="yearGroup">Year Group</label>
                    <input type="number" id="yearGroup" name="yearGroup" value="<?php echo htmlspecialchars($user['yearGroup']); ?>" required>
                </div>

                <div style="margin-top: 2rem;">
                    <a href="manaUsers.php" class="back-btn">Back</a>
                    <button type="submit" class="submit-btn">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fade out error messages after 3 seconds
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
