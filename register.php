<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './db/db_connect.php';

$error = "";

// Query to fetch majors
$majorQuery = "SELECT * FROM majors";
$stmtMajors = $conn->prepare($majorQuery);
$stmtMajors->execute();
$majorResult = $stmtMajors->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize input data
    $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
    $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));
    $confirmPassword = htmlspecialchars(trim($_POST['confirmPassword'] ?? ''));
    $majorId = htmlspecialchars(trim($_POST['major'] ?? ''));
    $yearGroup = htmlspecialchars(trim($_POST['yearGroup'] ?? ''));

    // Default role for new users (e.g., "Student")
    $roleId = 1; // Assuming "Student" has a roleId of 1

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword) || empty($majorId) || empty($yearGroup)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $emailQuery = "SELECT email FROM users WHERE email = ?";
        $stmtEmail = $conn->prepare($emailQuery);
        $stmtEmail->bind_param('s', $email);
        $stmtEmail->execute();
        $emailResult = $stmtEmail->get_result();

        if ($emailResult->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert the new user into the database
            $insertQuery = "INSERT INTO users (firstName, lastName, email, password, roleId, majorId, yearGroup, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmtInsert = $conn->prepare($insertQuery);

            if (!$stmtInsert) {
                die("Preparation failed: " . $conn->error);
            }

            // Bind parameters
            $stmtInsert->bind_param(
                "ssssiis",
                $firstName,
                $lastName,
                $email,
                $hashedPassword,
                $roleId,
                $majorId,
                $yearGroup
            );

            // Execute the statement
            if ($stmtInsert->execute()) {
                // Set a success session variable
                $_SESSION['success'] = "Registration successful! You can now log in.";

                // Redirect to login page
                header('Location: login.php');
                exit();
            } else {
                $error = "Failed to register user. Please try again.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <!-- font awesome cdn link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- custom css file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <section class="flex">
        <a href="index.html" class="logo">StudyNest</a>
    </section>
</header>

<section class="form-container">

    <form action="" method="post">
        <h3>Register Now</h3>

        <!-- First Name -->
        <p>First Name <span>*</span></p>
        <input type="text" name="firstName" placeholder="Enter your first name" required maxlength="50" class="box" value="<?= htmlspecialchars($firstName ?? '') ?>">

        <!-- Last Name -->
        <p>Last Name <span>*</span></p>
        <input type="text" name="lastName" placeholder="Enter your last name" required maxlength="50" class="box" value="<?= htmlspecialchars($lastName ?? '') ?>">

        <!-- Email -->
        <p>Email <span>*</span></p>
        <input type="email" name="email" placeholder="Enter your email" required maxlength="50" class="box" value="<?= htmlspecialchars($email ?? '') ?>">

        <!-- Password -->
        <p>Password <span>*</span></p>
        <input type="password" name="password" placeholder="Enter your password" required maxlength="20" class="box">

        <!-- Confirm Password -->
        <p>Confirm Password <span>*</span></p>
        <input type="password" name="confirmPassword" placeholder="Confirm your password" required maxlength="20" class="box">

        <!-- Year Group -->
        <p>Year Group <span>*</span></p>
        <select name="yearGroup" required class="box">
            <option value="" disabled selected>Select Year Group</option>
            <option value="2025" <?= (isset($yearGroup) && $yearGroup == "2025") ? "selected" : "" ?>>2025</option>
            <option value="2026" <?= (isset($yearGroup) && $yearGroup == "2026") ? "selected" : "" ?>>2026</option>
            <option value="2027" <?= (isset($yearGroup) && $yearGroup == "2027") ? "selected" : "" ?>>2027</option>
            <option value="2028" <?= (isset($yearGroup) && $yearGroup == "2028") ? "selected" : "" ?>>2028</option>
        </select>

        <!-- Major -->
        <p>Major <span>*</span></p>
        <select name="major" required class="box">
            <option value="" disabled selected>Select Major</option>
            <?php while ($row = $majorResult->fetch_assoc()) : ?>
                <option value="<?= htmlspecialchars($row['majorId']) ?>" <?= (isset($majorId) && $majorId == $row['majorId']) ? "selected" : "" ?>>
                    <?= htmlspecialchars($row['majorName']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="submit" value="Register" class="btn">
        
        <?php if (!empty($error)) : ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
    </form>

</section>

<script src="js/script.js"></script>
</body>
</html>
