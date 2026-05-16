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
        LEFT JOIN postscore ON posts.id = postscore.PostId
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
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
        LEFT JOIN postscore ON posts.id = postscore.PostId
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
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
        LEFT JOIN postscore ON posts.id = postscore.PostId
        WHERE posts.UserId IN (SELECT FollowedUserId FROM followingrelationships WHERE UserId = ?)
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
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
        LEFT JOIN postscore ON posts.id = postscore.PostId
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
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
    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
        FROM followingrelationships fr
        JOIN users u ON fr.FollowedUserId = u.id
        JOIN userprofiles up ON u.id = up.UserId
        WHERE fr.UserId = ?
        LIMIT 30");
    $stmt->execute([$_SESSION["user_id"]]);
    $following = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//post comments
$postIds = array_column($posts, 'id') ?: array_column($posts, 'id');
$topComments = [];
$commentCounts = [];
if (!empty($postIds)) {
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $stmt = $dbconn->prepare("SELECT c.*, up.Nickname, up.ProfilePicture,
        ROW_NUMBER() OVER (PARTITION BY c.PostId ORDER BY c.CreatedAt ASC) as rn
        FROM comments c JOIN userprofiles up ON c.UserId = up.UserId
        WHERE c.PostId IN ($placeholders)");
    $stmt->execute($postIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $commentCounts[$row['PostId']] = ($commentCounts[$row['PostId']] ?? 0) + 1;
        if ($row['rn'] == 1) $topComments[$row['PostId']] = $row;
    }
    $stmt = $dbconn->prepare("SELECT PostId, COUNT(*) as cnt FROM comments WHERE PostId IN ($placeholders) GROUP BY PostId");
    $stmt->execute($postIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $commentCounts[$row['PostId']] = $row['cnt'];
}
?>
<script src="js/feed.js" defer></script>

<?php if (isset($_SESSION["user_id"])): ?>

<!--create post popout-->
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>

<!--comment popout-->
<div id="comment-popout" style="display:none" class="modal-overlay"
    onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header">
            <button onclick="document.getElementById('comment-popout').style.display='none'"
                class="btn btn-icon">✕</button>
            <span class="post-username">Comments</span>
        </div>
        <div class="p-content">
            <div id="comment-post-preview" class="comment-preview"></div>
            <div id="comments-list" class="comments-list" style="margin:10px 0"></div>
            <form id="comment-form" action="../private/create-comment.php" method="post">
                <input type="hidden" name="post_id" id="comment-post-id">
                <div class="form-group">
                    <textarea maxlength="300" name="comment-text" class="form-control" placeholder="Write a comment..."
                        rows="2" style="resize:none"></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:6px">
                    <input type="submit" class="btn btn-secondary btn-sm" value="Post comment">
                </div>
            </form>
        </div>
    </div>
</div>
<!--send post popout-->
<div id="send-popout" style="display:none" class="modal-overlay">
    <div class="p-container modal-box">
        <div class="p-header">
            <button onclick="document.getElementById('send-popout').style.display='none'"
                class="btn btn-icon">✕</button>
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
                        data-user-id="<?= $f['id'] ?>">&ltsend&gt</button>
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
            <?php foreach ($posts as $post):
                $pid = $post['id'];
                $topC = $topComments[$pid] ?? null;
                $cCount = $commentCounts[$pid] ?? 0;
            ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <a href="profile.php?id=<?= htmlspecialchars($post["UserId"]) ?>" class="post-header no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($post["ProfilePicture"]) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post["Nickname"]) ?></span>
                    <span class="post-views"><?= number_format($post['ViewCount'] ?? 0) ?> views</span>
                </a>

                <div class="post-content">
                    <p><?= htmlspecialchars($post["Text"]) ?></p>
                </div>

                <!--comment -->
                <?php if ($topC): ?>
                <div class="post-top-comment">
                    <img src="../uploads/pfp/<?= htmlspecialchars($topC['ProfilePicture']) ?>" alt="">
                    <p><strong><?= htmlspecialchars($topC['Nickname']) ?></strong>
                        <?= htmlspecialchars(mb_strimwidth($topC['Text'], 0, 80, '...')) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($cCount > 0): ?>
                <div class="post-comment-count comment-btn" style="cursor:pointer">
                    <?= $cCount === 1 ? '1 comment' : "$cCount comments" ?> — view all
                </div>
                <?php endif; ?>

                <div class="post-button-container">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div>
                        <button class="btn btn-icon like-btn"><i class="fa-solid fa-thumbs-up"></i>
                            <?= $post['Likes'] ?></button>
                        <button class="btn btn-icon dislike-btn"><i class="fa-solid fa-thumbs-down"></i>
                            <?= $post['Dislikes'] ?></button>
                        <button class="btn btn-icon comment-btn"><i class="fa-solid fa-comment"></i> Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn"><i class="fa-solid fa-star"></i></button>
                        <button class="btn btn-icon share-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                    <?php else: ?>
                    <div>
                        <a href="login.php" class="btn btn-icon">Like (<?= $post['Likes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</a>
                        <a href="login.php" class="btn btn-icon">Comment</a>
                    </div>
                    <div>
                         <a href="login.php" class="btn btn-icon"><i class="fa-solid fa-star"></i></a>
                        <button class="btn btn-icon share-btn"><i class="fa-solid fa-paper-plane"></i> Send</button>
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