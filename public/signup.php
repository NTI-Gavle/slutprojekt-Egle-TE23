<?php
session_start();

$errorMessage = "";
if (isset($_SESSION["signupError"])) {
    $errorMessage = $_SESSION["signupError"];
    unset($_SESSION["signupError"]);
}
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

    <div style="margin:50px;">
        <form action="../private/signuplogic.php" method="post" class="signup-form">
            <h1 style="color:blueviolet; width: fit-content;" class="m-auto ">SIGNUP</h1>
            <div class="form-group">
                <label for="passwordConfirm">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address"
                    maxlength="1000">
                <small class="char-counter" data-for="email"></small>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username"
                    maxlength="20">

                <small class="char-counter" data-for="username"></small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                    maxlength="50">

                <small class="char-counter" data-for="password"></small>
            </div>
            <div class="form-group">
                <label for="passwordConfirm">Confirm Password</label>
                <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm"
                    placeholder="Confirm password" maxlength="50">

                <small class="char-counter" data-for="passwordConfirm"></small>
            </div>
            <?php
            if ($errorMessage != "") {
                echo "<p id='errormsg-purple'>" . $errorMessage . "</p>";
            }
            ?>
            <button type="submit" class="btn btn-primary login-button">Signup</button>
            <div><a href="login.php" class="login-link">login instead</a></div>
        </form>
    </div>
</body>

</html>