<?php
include 'dbconnection.php';
session_start();
if (!isset($_POST['token'], $_POST['password'], $_POST['confirm'])) {
    $_SESSION['loginError'] = "Invalid request.";
    header("Location: login.php");
    exit;
}
$token = $_POST['token'];
$pass = $_POST['password'];
$conf = $_POST['confirm'];

if ($pass !== $conf) {
    $_SESSION["resetError"] = "Passwords do not match.";
    header("Location: reset-password-confirm.php?token=" . urlencode($token));
    exit;
}
if (strlen($pass) < 8) {
    $_SESSION["resetError"] = "Password must be at least 8 characters long.";
    header("Location: reset-password-confirm.php?token=" . urlencode($token));
    exit;
}
$hash = password_hash($pass, PASSWORD_DEFAULT);
$now = date("Y-m-d H:i:s", time());
$stmt = $dbconn->prepare("UPDATE users SET password = ?,reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > ?");

$stmt->execute([$hash, $token, $now]);

if ($stmt->rowCount() === 0) {
    die("Reset token expired.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php include("scripts-links.php") ?>
</head>

<body>
    <?php
    include("header.php");
    ?>
    <div style="margin:50px;">
        <form action="loginlogic.php" method="post" class="login-form">
            <h3 style="color:blueviolet; width: fit-content;" class="m-auto ">Password updated. You can now log in.</h3>
            <button type="submit" class="btn btn-primary login-button">Login</button>
    </div>
</body>

</html>