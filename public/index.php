<?php
$pageTitle = "Home";
require_once __DIR__ . '/../includes/header.php';
include('../private/dbconnection.php');
require_once('../includes/functions.php');

$feed = $_GET['feed'] ?? 'discover';
$loggedIn = isset($_SESSION['user_id']);

switch ($feed) {
    case 'new':
        $sql  = "SELECT posts.*, users.Username,
            userprofiles.Nickname, userprofiles.ProfilePicture,
            COALESCE(SUM(postscore.Value=1),0)  AS Likes,
            COALESCE(SUM(postscore.Value=-1),0) AS Dislikes
            FROM posts
            JOIN users        ON posts.UserId = users.id
            JOIN userprofiles ON posts.UserId = userprofiles.UserId
            LEFT JOIN postscore ON posts.id = postscore.PostId
            GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
            ORDER BY posts.CreatedAt DESC LIMIT 50";
        $stmt = $dbconn->prepare($sql); $stmt->execute();
        break;
    case 'top':
        $sql  = "SELECT posts.*, users.Username,
            userprofiles.Nickname, userprofiles.ProfilePicture,
            COALESCE(SUM(postscore.Value=1),0)  AS Likes,
            COALESCE(SUM(postscore.Value=-1),0) AS Dislikes,
            COALESCE(SUM(postscore.Value),0)    AS Score
            FROM posts
            JOIN users        ON posts.UserId = users.id
            JOIN userprofiles ON posts.UserId = userprofiles.UserId
            LEFT JOIN postscore ON posts.id = postscore.PostId
            GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
            ORDER BY (Score / (TIMESTAMPDIFF(HOUR, posts.CreatedAt, NOW()) + 2)) DESC LIMIT 50";
        $stmt = $dbconn->prepare($sql); $stmt->execute();
        break;
    case 'following':
        if (!$loggedIn) { header("Location: login.php"); exit; }
        $sql  = "SELECT posts.*, users.Username,
            userprofiles.Nickname, userprofiles.ProfilePicture,
            COALESCE(SUM(postscore.Value=1),0)  AS Likes,
            COALESCE(SUM(postscore.Value=-1),0) AS Dislikes
            FROM posts
            JOIN users        ON posts.UserId = users.id
            JOIN userprofiles ON posts.UserId = userprofiles.UserId
            LEFT JOIN postscore ON posts.id = postscore.PostId
            WHERE posts.UserId IN (SELECT FollowedUserId FROM followingrelationships WHERE UserId = ?)
            GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
            ORDER BY posts.CreatedAt DESC LIMIT 50";
        $stmt = $dbconn->prepare($sql); $stmt->execute([$_SESSION['user_id']]);
        break;
    default: // discover
        $sql  = "SELECT posts.*, users.Username,
            userprofiles.Nickname, userprofiles.ProfilePicture,
            COALESCE(SUM(postscore.Value=1),0)  AS Likes,
            COALESCE(SUM(postscore.Value=-1),0) AS Dislikes
            FROM posts
            JOIN users        ON posts.UserId = users.id
            JOIN userprofiles ON posts.UserId = userprofiles.UserId
            LEFT JOIN postscore ON posts.id = postscore.PostId
            GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
            ORDER BY RAND() LIMIT 50";
        $stmt = $dbconn->prepare($sql); $stmt->execute();
        break;
}
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$profile   = null;
$following = [];
if ($loggedIn) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
        FROM followingrelationships fr
        JOIN users u        ON fr.FollowedUserId = u.id
        JOIN userprofiles up ON u.id = up.UserId
        WHERE fr.UserId = ? LIMIT 30");
    $stmt->execute([$_SESSION['user_id']]);
    $following = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//stared
$starredIds = [];
if ($loggedIn && !empty($posts)) {
    $postIds      = array_column($posts, 'id');
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));
    $stmt         = $dbconn->prepare("SELECT PostId FROM starmarks WHERE UserId = ? AND PostId IN ($placeholders)");
    $stmt->execute(array_merge([$_SESSION['user_id']], $postIds));
    $starredIds   = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

//top comment
$topComments    = [];
$commentCounts  = [];
if (!empty($posts)) 
    {
    $postIds      = array_column($posts, 'id');
    $placeholders = implode(',', array_fill(0, count($postIds), '?'));

    $stmt = $dbconn->prepare("SELECT c.PostId, COUNT(*) AS cnt FROM comments c WHERE c.PostId IN ($placeholders) GROUP BY c.PostId");
    $stmt->execute($postIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $commentCounts[$row['PostId']] = $row['cnt'];
    }

    $stmt = $dbconn->prepare("SELECT c.*, up.Nickname, up.ProfilePicture,
        ROW_NUMBER() OVER (PARTITION BY c.PostId ORDER BY c.CreatedAt ASC) AS rn
        FROM comments c JOIN userprofiles up ON c.UserId = up.UserId
        WHERE c.PostId IN ($placeholders)");
    $stmt->execute($postIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ($row['rn'] == 1) $topComments[$row['PostId']] = $row;
    }
}
?>
<script src="js/feed.js" defer></script>

<?php if ($loggedIn): ?>
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>

<!--comment popout-->
<div id="comment-popout" style="display:none" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header">
            <button onclick="document.getElementById('comment-popout').style.display='none'" class="btn btn-icon">✕</button>
            <span class="post-username">Comments</span>
        </div>
        <div class="p-content">
            <div id="comment-post-preview" class="comment-preview"></div>
            <div id="comments-list" class="comment-thread" style="margin:10px 0;max-height:300px;overflow-y:auto"></div>
            <form id="comment-form" action="../private/create-comment.php" method="post">
                <input type="hidden" name="post_id" id="comment-post-id">
                <div class="form-group">
                    <textarea maxlength="300" name="comment-text" class="form-control" placeholder="Write a comment…" rows="2" style="resize:none"></textarea>
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
                    <button class="btn btn-secondary btn-sm send-to-user-btn" data-user-id="<?= $f['id'] ?>">&ltsend&gt</button>
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
        <p style="text-align:center;opacity:0.6;padding:20px">Nothing here yet…</p>
        <?php endif; ?>
        <div class="post-feed">
            <?php foreach ($posts as $post):
                $pid    = $post['id'];
                $topC   = $topComments[$pid]   ?? null;
                $cCount = $commentCounts[$pid]  ?? 0;
                $mstmt  = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
                $mstmt->execute([$pid]);
                $mediaFiles = $mstmt->fetchAll(PDO::FETCH_COLUMN);
                renderPostCard($post, $mediaFiles, $topC, $cCount, $loggedIn, $starredIds);
            endforeach; ?>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>