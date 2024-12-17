<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include './db/db_connect.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT userId, password, roleId, firstName, lastName FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['userId'];
                $_SESSION['roleId'] = $user['roleId'];
                $_SESSION['firstName'] = $user['firstName'];
                $_SESSION['lastName'] = $user['lastName'];
                $_SESSION['email'] = $email;

                switch ($user['roleId']) {
                    case 1:
                        header('Location: dashboard.php');
                        exit();
                    case 2:
                        header('Location: admin_dashboard.php');
                        exit();
                    default:
                        $error = "Invalid role assignment. Please contact support.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <!-- Font Awesome CDN Link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Inline CSS for styling -->
   <style>
       /* Ensure header spans full width */
       header.header {
           width: 100%;
           background-color: White; /* Dark background for contrast */
           color: white; /* Text color */
           padding: 10px 20px; /* Spacing for aesthetics */
           position: fixed; /* Keep header fixed at top */
           top: 0;
           left: 0;
           z-index: 1000; /* Ensure header is above all other elements */
           display: flex;
           align-items: center;
           justify-content: space-between; /* Space out elements in header */
           box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Add shadow for depth */
       }

       /* Style for logo */
       header.header .logo {
           font-size: 1.5rem;
           font-weight: bold;
           color: #34495E;
           text-decoration: none;
       }

       /* Ensure header content wraps properly */
       header.header .flex {
           display: flex;
           align-items: center;
           justify-content: space-between;
           width: 100%;
       }

       /* Style for form container */
       .form-container {
           margin-top: 100px; /* Offset for fixed header */
           padding: 20px;
           max-width: 400px;
           margin: 100px auto;
           border: 1px solid #ddd;
           border-radius: 5px;
           background-color: #f9f9f9;
       }

       /* Style for input boxes and buttons */
       .form-container .box {
           width: 100%;
           padding: 10px;
           margin: 10px 0;
           border: 1px solid #ddd;
           border-radius: 5px;
       }

       .form-container .btn {
           width: 100%;
           padding: 10px;
           border: none;
           border-radius: 5px;
           background-color: #FF6B6B;
           color: white;
           cursor: pointer;
           font-size: 16px;
       }

       .form-container .btn:hover {
           background-color: #34495E;
       }

       /* Error message styling */
       .form-container .error {
           color: red;
           font-size: 14px;
       }
   </style>
</head>
<body>

<header class="header">
   <section class="flex">
      <a href="index.html" class="logo">StudyNest</a>
   </section>
</header>

<section class="form-container">
   <form action="" method="post">
      <h3>Login Now</h3>
      <p>Your Email <span>*</span></p>
      <input type="email" name="email" placeholder="Enter your email" required maxlength="50" class="box">
      <p>Your Password <span>*</span></p>
      <input type="password" name="password" placeholder="Enter your password" required maxlength="20" class="box">
      
      <!-- Forgot Password Link -->
      <p><a href="forgetPassword.php" class="forgot-password" style="color: black;">Forgot Password?</a></p>

      <input type="submit" value="Login" name="submit" class="btn">

      <!-- Display error message if any -->
      <?php if (!empty($error)): ?>
         <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
   </form>
</section>

<script src="js/script.js"></script>

</body>
</html>
