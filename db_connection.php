<?php
$servername = "localhost"; // Change if necessary
$username = "root"; // Your database username
$password = "2000"; // Your database password
$dbname = "emp"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
 