<?php
$pageTitle = "Settings"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';

include('../private/dbconnection.php');

if(isset($_SESSION["Username"])){
    header("Location: ../public/login.php");
}

$sql = "SELECT * FROM users WHERE Username =?";
$stmt = $dbconn->prepare($sql);
$data = array($_SESSION["username"]);
$stmt->execute($data);
$res = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM userprofiles WHERE UserId =?";
$stmt = $dbconn->prepare($sql);
$data = array($_SESSION["user_id"]);
$stmt->execute($data);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<script defer src="js/settings.js"></script>

<div class="settings-container">
    <nav class="settings-nav settings-field">
        <h1>Settings</h1>
        <ul class="settings-list">
            <li><button onclick="ShowSettings('settings-general')" class="btn btn-secondary">&ltgeneral&gt</button></li>
            <li><button onclick="ShowSettings('settings-account')"class="btn btn-secondary">&ltaccount&gt</button></li>
            <li><button onclick="ShowSettings('settings-about')" class="btn btn-secondary">&ltabout us&gt</button></li>
            <li><a href="logout.php" class="btn btn-secondary">&ltlogout&gt</a></li>
        </ul>
    </nav>
    <div class="settings-display settings-field">
        <div id="settings-general">
            <h1 >General</h1>
            <div>
                <div>Theme</div>
                <div>Time zone</div>
                <div>Clouds</div>
                <div>Animated background</div>
                <div>Notifications</div>
                <div>Mentions</div>
            </div>
        </div>    
        <div id="settings-account">
            <h1>Account</h1>
            <div>
                <div>Profile picture</div>
                <div>Profile banner</div>
                <div>Username</div>
                <div>Nickname</div>
                <div>Description</div>
                <div>Email</div>
                <div>Password</div>
                <div>Birthday</div>
            </div>
        </div>
        <div id="settings-about">
            <h1>About us</h1>
            <div>
                <p>Hiiiii my name is me! i make this website and uhhh ye!</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';