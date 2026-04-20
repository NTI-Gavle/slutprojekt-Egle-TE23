<?php
$pageTitle = "Profile"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';

include('../private/dbconnection.php');

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

$sql = "SELECT * FROM posts WHERE UserId = ? ORDER BY CreatedAt DESC LIMIT 50";;
$stmt = $dbconn->prepare($sql);
$data = array($_SESSION["user_id"]);
$stmt->execute($data);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="feed-container">

<?php require_once __DIR__ . '/../includes/feednav.php'; ?>

<div class="feed">
    <div class="post-container">
        <div class="post-header">
                <img src=<?php echo(htmlspecialchars($profile["ProfilePicture"]))?> alt="profile picture" class="post-profile-pic">
                <span class="post-username"><?php echo(htmlspecialchars($res["Username"]))?></span>
        </div>
        <div class="profile-container">
            <div class="profile-background-container">
                <img src=<?php echo(htmlspecialchars($profile["ProfilePicture"]))?> alt="profile-background" class="profile-background">
            </div>
            <img src=<?php echo(htmlspecialchars($profile["ProfilePicture"]))?> alt="profile picture" class="profile-pic">
            <div class="profile-name">
                <div>
                    <p><?php echo(htmlspecialchars($profile["Nickname"]))?></p>
                    <p>@<?php  echo(htmlspecialchars($res["Username"]))?></p>
                </div>
                <button class="btn btn-secondary btn-sm">&ltedit&gt</button>   
            </div>
            <div class="profile-description">
                <p><?php echo(htmlspecialchars($profile["Description"]))?></p>
            </div>
            <div class="profile-stats">

            </div>
            <div class="profile-buttons">

            </div>
        </div>
    </div>
    <div class="post-feed">
<?php foreach($posts as $post): ?>
    <div class="post-container">
        <div class="post-header">
            <img src="<?= htmlspecialchars($profile["ProfilePicture"]) ?>" class="post-profile-pic">
            <span class="post-username"><?= htmlspecialchars($profile["Nickname"]) ?></span>
        </div>

        <div class="post-content">
            <p><?= htmlspecialchars($post["Text"]) ?></p>
        </div>

        <div class="post-button-container">
            <div>
                <button class="btn btn-icon">Like</button>
                <button class="btn btn-icon">Dislike</button>
                <button class="btn btn-icon">Comment</button>  
            </div>
            <div>
                <button class="btn btn-icon">Star</button>
                <button class="btn btn-icon">Send</button>
            </div>  
        </div>
    </div>
<?php endforeach; ?>
</div>
</div>

<?php require_once __DIR__ . '/../includes/sitenav.php'; ?>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>