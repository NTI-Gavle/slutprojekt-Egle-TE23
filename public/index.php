<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';

include('../private/dbconnection.php');

if(isset($_SESSION["user_id"])){
    $sql = "SELECT * FROM users WHERE Id =?";
    $stmt = $dbconn->prepare($sql);
    $data = array($_SESSION["user_id"]);
    $stmt->execute($data);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM userprofiles WHERE UserId =?";
    $stmt = $dbconn->prepare($sql);
    $data = array($_SESSION["user_id"]);
    $stmt->execute($data);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

$sql = "SELECT * FROM posts ORDER BY CreatedAt DESC LIMIT 50";
$stmt = $dbconn->prepare($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div id="create-post-popout">
    <div class="p-container create-post-container">
            <div class="p-header">
                <button onclick="CloseCreatePost()" class="btn btn-icon">X</button>
                <span class="post-username"><?php if($profile["Nickname"]) echo(htmlspecialchars($profile["Nickname"]))?></span>
            </div>
            <div class="p-content">
                <form action="../private/create-post.php" method="post">
                    <div class="form-group">
                        <textarea type="text" maxlength="500" name="create-post-text" id="create-post-text" class="form-control" placeholder="tell the world something!"></textarea>
                    </div>
                    <div class="post-button-container">
                <div>
                    <btn class="btn btn-icon">Image</btn>
                    <btn class="btn btn-icon">Emoji</btn> 
                </div>
                <div>
                    <input type="submit" class="btn btn-secondary" value="Post">
                </div>  
            </div>
                </form>
            </div>
        </div>
</div>

<div class="feed-container">
<?php require_once __DIR__ . '/../includes/feednav.php'; ?>
<div class="feed">
    <h1>&ltdiscorver&gt</h1>

     <div class="post-feed">
        <?php foreach($posts as $post): 
            
            $sql = "SELECT * FROM userprofiles WHERE UserId =?";
            $stmt = $dbconn->prepare($sql);
            $data = array($post["UserId"]);
            $stmt->execute($data);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="post-container">
                <div class="post-header">
                    <img src="<?= htmlspecialchars($user["ProfilePicture"]) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($user["Nickname"]) ?></span>
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

<?php
require_once __DIR__ . '/../includes/footer.php';