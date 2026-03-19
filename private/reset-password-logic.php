<?php
session_start();
include 'dbconnection.php';
include 'sendmail.php'; 

$email = $_POST['email'] ?? '';

$stmt = $dbconn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['reset_msg'] = "A user with that email address does not exist.";
    header("Location: reset-password.php");
    exit;
}

$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", (time() + 1800)); // 30 min 
$stmt = $dbconn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
$stmt->execute([$token, $expires, $user['id']]);

$ipAddress = $_SERVER['HTTP_HOST'];
$link = "http://$ipAddress/DeluxQUIZ/reset-password-confirm.php?token=$token";

sendMail(
    $email,
    "EPIC QUIZ Password reset",
    "Lol you forgot your password. Click here to reset it:\n$link"
);

$_SESSION['reset_msg'] = "A reset link was sent to ". $email .". The link will expire in 30 minutes.";
header("Location: reset-password.php");
exit;
?>