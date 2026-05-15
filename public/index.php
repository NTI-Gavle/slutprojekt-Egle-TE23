<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';

include('../private/dbconnection.php');

$feed = $_GET['feed'] ?? 'discover';
unset($stmt);
switch ($feed) {
    case 'new':
        $sql = "SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId
        GROUP BY posts.Id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY posts.CreatedAt DESC
        LIMIT 50";
        break;

    case 'top':
        $sql = "SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId
        GROUP BY posts.Id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY (Score / (TIMESTAMPDIFF(HOUR, posts.CreatedAt, NOW()) + 2)) DESC
        LIMIT 50";
        break;

    case 'following':    
        if (!isset($_SESSION["user_id"])) {
            header("Location: login.php");
            exit;
        }
        $sql = "SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId
        WHERE posts.UserId IN ( SELECT FollowedUserId FROM followingrelationships WHERE UserId = ?)
        GROUP BY posts.Id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY posts.CreatedAt DESC
        LIMIT 50";
        $stmt = $dbconn->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        break;

    default: //discover
        $sql = "SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId
        GROUP BY posts.Id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY RAND()
        LIMIT 50";
        break;
}
if(!isset($stmt)){
    $stmt = $dbconn->prepare($sql);
    $stmt->execute();
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>
<script src="js/feed.js" defer></script>

<?php if(isset($_SESSION["user_id"])):?>
<div id="create-post-popout">
    <div class="p-container create-post-container">
        <div class="p-header">
            <button onclick="CloseCreatePost()" class="btn btn-icon">X</button>
            <span class="post-username"><?=htmlspecialchars($profile["Nickname"])?></span>
        </div>
        <div class="p-content">
            <form action="../private/create-post.php" method="post">
                <div class="form-group">
                    <textarea type="text" maxlength="500" name="create-post-text" id="create-post-text"
                        class="form-control" placeholder="tell the world something!"></textarea>
                </div>
                <div class="post-button-container">
                    <div>
                        <button class="btn btn-icon">Image</button>
                        <button class="btn btn-icon">Emoji</button>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-secondary" value="Post">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>
    <div class="feed">
        <h1>&lt<?= htmlspecialchars($feed) ?>&gt</h1>
        <?php if (empty($posts)): ?>
        <p style="text-align:center">Nothing here yet...</p>
        <?php endif; ?>

        <div class="post-feed">
            <?php foreach($posts as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id']?>">
                <a href="profile.php?id=<?= htmlspecialchars($post["UserId"]) ?>" class="post-header no-underline">
                    <img src=<?php echo("../uploads/pfp/".htmlspecialchars($post["ProfilePicture"]))?>
                        class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post["Nickname"]) ?></span>
                </a>

                <div class="post-content">
                    <p><?= htmlspecialchars($post["Text"])?></p>
                </div>

                <div class="post-button-container">
                    <?php if(isset($_SESSION['user_id'])):?>
                    <div>
                        <button class="btn btn-icon like-btn">Like(<?= $post['Likes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon dislike-btn">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon comment-btn">Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn">Star</button>
                        <button class="btn btn-icon share-btn">Share</button>
                    </div>
                    <?php else: ?>
                    <div>
                        <a href="login.php" class="btn btn-icon like-btn">Like(<?= $post['Likes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon dislike-btn">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon comment-btn">Comment</a>
                    </div>
                    <div>
                        <a href="login.php" class="btn btn-icon starmark-btn">Star</a>
                        <button class="btn btn-icon share-btn">Share</button>
                    </div>
                    <?php endif;?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php';