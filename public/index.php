<?php
$pageTitle = "Home";
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
        $sql = "SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId
        WHERE posts.UserId IN (SELECT FollowedUserId FROM followingrelationships WHERE UserId = ?)
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
if (!isset($stmt)) {
    $stmt = $dbconn->prepare($sql);
    $stmt->execute();
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION["user_id"])) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    //get follows for send func
    $stmt = $dbconn->prepare("SELECT u.Id, u.Username, up.Nickname, up.ProfilePicture
        FROM followingrelationships fr
        JOIN users u ON fr.FollowedUserId = u.Id
        JOIN userprofiles up ON u.Id = up.UserId
        WHERE fr.UserId = ?
        LIMIT 30");
    $stmt->execute([$_SESSION["user_id"]]);
    $following = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<script src="js/feed.js" defer></script>

<?php if (isset($_SESSION["user_id"])): ?>
    
<!--create post popout-->
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>

<!--comment popout-->
<div id="comment-popout" style="display:none" class="modal-overlay">
    <div class="p-container  modal-box">
        <div class="p-header">
            <button onclick="document.getElementById('comment-popout').style.display='none'" class="btn btn-icon">✕</button>
            <span class="post-username">Add a comment</span>
        </div>
        <div class="p-content">
            <div id="comment-post-preview" class="comment-preview"></div>
            <div id="comments-list" class="comments-list"></div>
            <form id="comment-form" action="../private/create-comment.php" method="post">
                <input type="hidden" name="post_id" id="comment-post-id">
                <div class="form-group">
                    <textarea maxlength="300" name="comment-text" id="comment-text"
                        class="form-control" placeholder="Write a comment..."></textarea>
                </div>
                <div class="post-button-container">
                    <div></div>
                    <input type="submit" class="btn btn-secondary" value="Comment">
                </div>
            </form>
        </div>
    </div>
</div>
<!--send post popout-->
<div id="send-popout" style="display:none" class="modal-overlay">
    <div class="p-container modal-box">
        <div class="p-header">
            <button onclick="document.getElementById('send-popout').style.display='none'" class="btn btn-icon">✕</button>
            <span class="post-username">Send post</span>
        </div>
        <div class="p-content">
            <div class="send-options">
                <button class="btn btn-secondary" id="copy-link-btn">&ltcopy link&gt</button>
                <span id="copy-confirm" style="display:none;color:green">Copied!</span>
            </div>
            <p style="text-align:center;opacity:0.7">— or send to —</p>
            <?php if (!empty($following)): ?>
            <div class="follow-send-list">
                <?php foreach ($following as $f): ?>
                <div class="follow-item">
                    <img src="../uploads/pfp/<?= htmlspecialchars($f['ProfilePicture']) ?>" class="post-profile-pic">
                    <span><?= htmlspecialchars($f['Nickname']) ?></span>
                    <button class="btn btn-secondary btn-sm send-to-user-btn"
                        data-user-id="<?= $f['Id'] ?>">&ltsend&gt</button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align:center;opacity:0.6">Follow people to send them posts!</p>
            <?php endif; ?>
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
            <?php foreach ($posts as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <a href="profile.php?id=<?= htmlspecialchars($post["UserId"]) ?>" class="post-header no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($post["ProfilePicture"]) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post["Nickname"]) ?></span>
                    <span class="post-views"><?= number_format($post['ViewCount'] ?? 0) ?> views</span>
                </a>

                <div class="post-content">
                    <p><?= htmlspecialchars($post["Text"]) ?></p>
                </div>

                <div class="post-button-container">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div>
                        <button class="btn btn-icon like-btn">Like (<?= $post['Likes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon dislike-btn">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon comment-btn">Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn">Star</button>
                        <button class="btn btn-icon share-btn">Send</button>
                    </div>
                    <?php else: ?>
                    <div>
                        <a href="login.php" class="btn btn-icon">Like (<?= $post['Likes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon">Comment</a>
                    </div>
                    <div>
                        <a href="login.php" class="btn btn-icon">Star</a>
                        <button class="btn btn-icon share-btn">Send</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>