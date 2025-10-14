<?php
session_start();
$_SESSION['id'] = $user['id'];
$_SESSION['user'] = $user['fullname'];
$_SESSION['email'] = $user['email']; // optional
header("Location: home.php");
// exit;

include('./config.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header('Location: ../login.php?error=All fields are required');
        exit;
    }

    // Check if the user exists
    $checkquery = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $checkquery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            header('Location: ../index.php?success=Successfully logged in');
            exit;
        } else {
            header('Location: ../login.php?error=Invalid password');
            exit;
        }
    } else {
        header('Location: ../login.php?error=User not found');
        exit;
    }
}
?>
