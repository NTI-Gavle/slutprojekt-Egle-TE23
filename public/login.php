<?php
session_start();

$errorMessage = "";
if (isset($_SESSION["loginError"])) {
    $errorMessage = $_SESSION["loginError"];
    unset($_SESSION["loginError"]);
}

$pageTitle = "Login";
require_once __DIR__ . '/../includes/header.php';
?>
    <div id="login-container">
    <form action="../private/loginlogic.php" method="post" class="login-form">
    <div class="post-header">
        <h1>LOGIN</h1>
    </div>    
    <div class="login-content">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
        </div>
        <?php
        if ($errorMessage != "") {
            echo "<p id='errormsg'>" . $errorMessage . "</p>";
        }
        ?>
        <button type="submit" class="btn btn-secondary login-button">Login</button>
        <div><a href="reset-password.php" class="login-link">forgot password</a> <br><a href="signup.php" class="login-link">signup instead</a></div>
    </div>
    </form>
    </div>
</body>

</html>