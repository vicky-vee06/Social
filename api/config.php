<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "socialthreads";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die(json_encode(['success' => false, 'msg' => 'DB connection failed']));
}
?>
