<?php
// Include the database connection file
require_once './db/db_connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted token
    $token = trim($_POST['token']);

    // Check if token is provided
    if (empty($token)) {
        die("Token is required.");
    }

    // Prepare the query using mysqli
    $query = "SELECT u.email 
          FROM token t 
          JOIN users u ON t.userID = u.user_id 
          WHERE t.token = ?";
    if ($stmt = $conn->prepare($query)) { // $conn is the mysqli connection object from db_connect.php
        // Bind the token parameter
        $stmt->bind_param('s', $token);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Token is valid; redirect to reset password page
            header("Location: reset_password.php?email=" . urlencode($user['email']));
            exit;
        } else {
            // Token is invalid
            echo "<p style='color: red; text-align: center;'>Invalid token. Please try again.</p>";
        }

        // Close the statement
        $stmt->close();
    } else {
        die("Error preparing the query: " . $conn->error);
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            color: #2d3436;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
        }
        h2 {
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        p {
            margin-bottom: 20px;
            font-size: 1em;
            color: #636e72;
        }
        form {
            margin: 0;
        }
        input {
            padding: 12px 15px;
            margin: 10px 0;
            width: calc(100% - 30px);
            border: 1px solid #dcdde1;
            border-radius: 8px;
            font-size: 1em;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 8px rgba(106, 17, 203, 0.5);
        }
        button {
            padding: 12px 20px;
            background-color: #122331dc;
            color: white;
            font-weight: bold;
            font-size: 1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s;
        }
        button:hover {
            background-color: #34495E;
            transform: scale(1.05);
        }
        button:active {
            transform: scale(1);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset</h2>
        <p>We noticed you forgot your password. Please enter the token sent to your email below.</p>
        <form action="reset_password.php" method="POST">
            <input type="text" name="token" placeholder="Enter Token" required>
            <button type="submit">Submit Token</button>
        </form>
    </div>
</body>
</html>