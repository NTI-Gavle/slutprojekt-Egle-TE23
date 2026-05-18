<?php
//login page

session_start();

$errorMessage = "";
if (isset($_SESSION["loginError"])) {
    $errorMessage = $_SESSION["loginError"];
    unset($_SESSION["loginError"]);
}

$pageTitle = "Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="p-container m-5">
    <form action="../private/loginlogic.php" method="post">
        <div class="p-header">
            <h1>LOGIN</h1>
        </div>    
        <div class="p-content">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
            
            <?php
            if ($errorMessage != "") 
                {echo "<p id='errormsg'>" . $errorMessage . "</p>";}
            ?>
             <div class="form-group">
            <button type="submit" class="btn btn-secondary login-button">&ltLogin&gt</button>
            </div>
            <div>
                <a href="reset-password.php" class="link-p">forgot password</a> 
                <br>
                <a href="signup.php" class="link-p">signup instead</a>
            </div>
        </div>
    </form>
</div>
</body>

<?php require_once __DIR__ . '/../includes/footer.php';