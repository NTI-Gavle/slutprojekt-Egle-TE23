<?php
$pageTitle = "Profile"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>
<div class="feed-container">
<?php require_once __DIR__ . '/../includes/feednav.php'; ?>

<?php 
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

$sql = "SELECT id FROM posts WHERE UserId =?";
$stmt = $dbconn->prepare($sql);
$data = array($_SESSION["user_id"]);
$stmt->execute($data);
$posts = $stmt->fetch(PDO::FETCH_ASSOC);

?>

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
        <?php 
        foreach($posts as $post){
            echo('
                <div class="post-container">
            <div class="post-header">
                <img src="Images\placeholder_3.png" alt="profile picture" class="post-profile-pic">
                <span class="post-username">Username</span>
            </div>
         
            <div class="post-content">
                <p>post text</p>
            </div>
            <div class="post-button-container">
                <div>
                    <btn class="btn btn-icon">Like</btn>
                    <btn class="btn btn-icon">Dislike</btn>
                    <btn class="btn btn-icon">Comment</btn>  
                </div>
                <div>
                    <btn class="btn btn-icon">Star</btn>
                    <btn class="btn btn-icon">Send</btn>
                </div>  
            </div>
        </div>');};
        ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/sitenav.php'; ?>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';