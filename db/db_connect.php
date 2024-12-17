<?php
$host = 'localhost';
$user = 'rahinatu.lawal'; 
$password = 'mohammed2'; 
$db_name = 'webtech_fall2024_rahinatu_lawal';

// Attempt to connect to the database
$conn = new mysqli($host, $user, $password, $db_name);

// Check connection
if ($conn -> connect_error) 
{
    echo''. $conn -> connect_error;
    die("Connection failed: " . $conn->connect_error);
}

?>