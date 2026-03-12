<?php
include 'dbconnection.php';
session_start();

$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <div style="margin:50px;">
        <form action="reset-password-logic.php" method="POST" class="login-form">
            <h1 style="color:blueviolet; width: fit-content;" class="m-auto ">Reset password</h1>

            <div class="form-group">
                <label for="username">Account email</label>
                <input type="email" class="form-control" name="email" required placeholder="Your email">
            </div>

            <button type="submit" class="btn btn-primary login-button">Send reset link</button>

            <?php
            if (isset($_SESSION['reset_msg'])) {
                echo "<p>{$_SESSION['reset_msg']}</p>";
                unset($_SESSION['reset_msg']);
            }
            ?>
        </form>
</body>

</html>