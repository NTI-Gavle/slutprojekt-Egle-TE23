<?php
include '../private/dbconnection.php';
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
$pageTitle = "Reset Password"; 
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <div class="p-container m-5">
        <form action="login.php" method="post" class="p-form">
            <div class="p-header">
                <h1>PASSWORD RESET!</h1>
            </div>
            <div class="p-content">
                <div class="form-group">
                    <p>Password updated. You can now log in.</p>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary login-button">&ltLogin&gt</button>
                </div>
        
    </div>
    </form>
    </div>
</body>

</html>