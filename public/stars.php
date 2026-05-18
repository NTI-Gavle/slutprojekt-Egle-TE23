<?php
$pageTitle = "Bookmarks";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');
require_once('../includes/functions.php');

if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }
$userId = $_SESSION["user_id"];

$stmt = $dbconn->prepare("SELECT posts.*, users.Username,
    userprofiles.Nickname, userprofiles.ProfilePicture,
    COALESCE(SUM(ps.Value=1),0)  AS Likes,
    COALESCE(SUM(ps.Value=-1),0) AS Dislikes
    FROM starmarks
    JOIN posts        ON starmarks.PostId = posts.id
    JOIN users        ON posts.UserId = users.id
    JOIN userprofiles ON posts.UserId = userprofiles.UserId
    LEFT JOIN postscore ps ON posts.id = ps.PostId
    WHERE starmarks.UserId = ?
    GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
    ORDER BY starmarks.id DESC");
$stmt->execute([$userId]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$starredIds = array_column($posts, 'id');
?>
<script src="js/feed.js" defer></script>
<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>
    <div class="feed">
        <h1>&ltstarmarks&gt</h1>
        <?php if (empty($posts)): ?>
        <p style="text-align:center;opacity:0.6;padding:20px">Nothing here yet… go star some posts!</p>
        <?php endif; ?>
        <div class="post-feed">
            <?php foreach ($posts as $post):
                $mstmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
                $mstmt->execute([$post['id']]);
                $mf = $mstmt->fetchAll(PDO::FETCH_COLUMN);
                renderPostCard($post, $mf, null, 0, true, $starredIds);
            endforeach; ?>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php if (isset($_SESSION["user_id"])): ?>
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>

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

<?php
$following = [];
$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
    FROM followingrelationships fr
    JOIN users u        ON fr.FollowedUserId = u.id
    JOIN userprofiles up ON u.id = up.UserId
    WHERE fr.UserId = ? LIMIT 30");
$stmt->execute([$userId]);
$following = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>