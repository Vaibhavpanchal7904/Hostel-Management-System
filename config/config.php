<?php
// Start session
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "hostelcvm";

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
