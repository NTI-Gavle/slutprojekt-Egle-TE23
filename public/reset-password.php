<?php
//Start reset password, send a mail via reset password logic
include '../private/dbconnection.php';
session_start();

$pageTitle = "Reset Password"; 
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <div class="p-container m-5">
    <form action="../private/reset-password-logic.php" method="post" class="p-form">
    <div class="p-header">
        <h1>RESET PASSWORD</h1>
    </div>    
    <div class="p-content">
            <div class="form-group">
                <label for="username">Account email</label>
                <input type="email" class="form-control" name="email" required placeholder="Your email">
            </div>
            <div class="form-group">
            <button type="submit" class="btn btn-secondary login-button">&ltSend reset link&gt</button>
            </div>
            <?php
            if (isset($_SESSION['reset_msg'])) {
                echo "<p>{$_SESSION['reset_msg']}</p>";
                unset($_SESSION['reset_msg']);
            }
            ?>
    </div>
    </div>
    </form>
    </div>
</body>

</html>