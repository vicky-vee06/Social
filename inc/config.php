<?php
$host = "localhost";
$user = "root"; // your MySQL username (default = root)
$pass = ""; // your MySQL password (default = empty)
$db   = "user_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: {$conn->connect_error}");
}
?>
