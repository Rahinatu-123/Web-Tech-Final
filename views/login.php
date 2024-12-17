<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // Include your database connection file

$error = ""; // Initialize an error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM usersflex WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Secure session handling
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role']; // Automatically use role from DB
                $_SESSION['firstName'] = $user['firstName'];
                $_SESSION['lastName'] = $user['lastName'];
                $_SESSION['email'] = $user['email'];

                header('Location: admin.php');
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid email.";
        }

        $stmt->close(); // Close statement
    }

    $conn->close(); // Close connection
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="../assets/css/login.css" />
    <title>Login</title>
  </head>
  <body>
    <header>
        <div class="header-container">
          <div class="logo">StudyNest</div>
          <nav>
            <ul class="nav-links">
              <li><a href="index.html">Home</a></li>
            </ul>
            <div class="auth-buttons">
                <button class="signup-btn"><a href="register.html">Sign-up</a></button>
            </div>
          </nav>
        </div>
      </header>
    <div class="container">
      <div class="wrapper">
        <div class="main-card">
          <div class="background"></div>
          <div class="side-card"></div>
          <div class="login-form">
            <h1 class="title">Login</h1>
            <form id="loginForm">
              <label for="email" class="label">Email:</label>
              <input
                type="email"
                id="email"
                class="input-field"
                placeholder="Enter your email"
                required
              />
              <label for="password" class="label">Password:</label>
              <input
                type="password"
                id="password"
                class="input-field"
                placeholder="Enter your password"
                required
              />
              <button type="submit" class="submit-button">Login</button>
            </form>
            <p class="forgot-password">Forgot Password?</p>
            <p class="signup-text">
              Don't have an account? <a href="register.html" class="signup-link">Sign Up</a>
            </p>
          </div>
          <div class="image-card">
            <img class="image" src="../assets/images/da.png" alt="Illustration" />
          </div>
        </div>
      </div>
    </div>
    <script src="../assets/js/login.js"></script>
  </body>
</html>
