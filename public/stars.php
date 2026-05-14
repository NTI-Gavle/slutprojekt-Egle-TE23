<?php
$pageTitle = "Bookmarks";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$sql = "SELECT 
    posts.*,
    userprofiles.Nickname,
    userprofiles.ProfilePicture,
    COALESCE(scores.Score, 0) as Score,
    COALESCE(scores.Likes, 0) as Likes,
    COALESCE(scores.Dislikes, 0) as Dislikes
FROM starmarks
JOIN posts ON starmarks.PostId = posts.Id
JOIN userprofiles ON posts.UserId = userprofiles.UserId
LEFT JOIN (
    SELECT PostId,
    SUM(Value) as Score,
    SUM(Value = 1) as Likes,
    SUM(Value = -1) as Dislikes
    FROM postscore
    GROUP BY PostId
) as scores 
ON posts.Id = scores.PostId
WHERE starmarks.UserId = ?
ORDER BY starmarks.Id DESC";

$stmt = $dbconn->prepare($sql);
$stmt->execute([$userId]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>

    <div class="feed">
        <h1>&ltstarmarks&gt</h1>

        <?php if (empty($posts)): ?>
        <p style="text-align:center">Nothing here yet... Go star some posts!</p>
        <?php endif; ?>

        <div class="post-feed">
            <?php foreach($posts as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <a href="profile.php?id=<?= $post["UserId"] ?>" class="post-header no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($post["ProfilePicture"]) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post["Nickname"]) ?></span>
                </a>

                <div class="post-content">
                    <p><?= htmlspecialchars($post["Text"]) ?></p>
                </div>

                <div class="post-button-container">
                    <div>
                        <button class="btn btn-icon like-btn">Like (<?= $post['Likes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon dislike-btn">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon comment-btn">Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn active">Star</button>
                        <button class="btn btn-icon share-btn">Share</button>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>