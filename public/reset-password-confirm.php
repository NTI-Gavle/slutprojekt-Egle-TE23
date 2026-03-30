<?php
include '../private/dbconnection.php';
session_start();

$errorMessage = "";
if (isset($_SESSION["resetError"])) {
    $errorMessage = $_SESSION["resetError"];
    unset($_SESSION["resetError"]);
}
$token = $_GET['token'] ?? '';

$now = date("Y-m-d H:i:s", time());
$stmt = $dbconn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > ?");
$stmt->execute([$token, $now]);
$user = $stmt->fetch();

if (!$user["id"]) {
    $_SESSION["loginerror"] = "Invalid or expired reset link.";
    header("Location: ../public/login.php");
    exit;
}
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <div id="login-container">
    <form action="../private/new-password-logic.php" method="post" class="login-form">
    <div class="post-header">
        <h1>RESET PASSWORD</h1>
    </div>    
    <div class="login-content">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <p class="">Reset password for: <?= $user["email"] ?></p>
            <input type="password" id="password" class="form-control mt-3" name="password" required placeholder="New password" maxlength="50">

            <small class="char-counter" data-for="password"></small>
            <input type="password" id="passwordConfirm"class="form-control mt-3" name="confirm" required placeholder="Confirm password" maxlength="50">

            <small class="char-counter" data-for="passwordConfirm"></small>
            <?php
            if ($errorMessage != "") {
                echo "<p id='errormsg-purple'>" . $errorMessage . "</p>";
            }
            ?>
            <button type="submit" class="btn btn-secondary login-button mt-3">&ltReset password&gt</button>
    </div>
    </div>
    </form>
    </div>
</body>

</html>