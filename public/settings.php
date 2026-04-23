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
<div class="settings-container">
    <nav class="settings-nav settings-field">
        <h1>Settings</h1>
        <ul class="settings-list">
            <li><button class="btn btn-secondary">&ltgeneral&gt</button></li>
            <li><button class="btn btn-secondary">&ltaccount&gt</button></li>
            <li><button class="btn btn-secondary">&ltabout us&gt</button></li>
            <li><button class="btn btn-secondary">&ltlogout&gt</button></li>
        </ul>
    </nav>
    <div class="settings-display settings-field">
        <h1>title</h1>
        <div>
            insert settings here <br>
            or here
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';