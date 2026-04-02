<?php
include '../private/dbconnection.php';
session_start();

$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<body>
    <div id="login-container">
    <form action="../private/reset-password-logic.php" method="post" class="login-form">
    <div class="post-header">
        <h1>RESET PASSWORD</h1>
    </div>    
    <div class="login-content">
            <div class="form-group">
                <label for="username">Account email</label>
                <input type="email" class="form-control" name="email" required placeholder="Your email">
            </div>

            <button type="submit" class="btn btn-secondary login-button">&ltSend reset link&gt</button>
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