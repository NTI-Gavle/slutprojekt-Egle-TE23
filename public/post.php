<?php
// Displays a single post in full detail

$pageTitle = "Post";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');
require_once('../includes/functions.php');

$loggedIn = isset($_SESSION['user_id']);
$postId   = (int)($_GET['id'] ?? 0);

if (!$postId) {
    header("Location: index.php");
    exit;
}

//post data
$stmt = $dbconn->prepare( "SELECT posts.*, users.Username,
    ANY_VALUE(userprofiles.Nickname) AS Nickname,
    ANY_VALUE(userprofiles.ProfilePicture) AS ProfilePicture,
    COALESCE(SUM(ps.Value=1), 0) AS Likes,
    COALESCE(SUM(ps.Value=-1), 0)  AS Dislikes
    FROM posts JOIN users ON posts.UserId = users.id
    JOIN userprofiles ON posts.UserId = userprofiles.UserId
    LEFT JOIN postscore ps ON posts.id = ps.PostId
    WHERE posts.id = ? GROUP BY posts.id");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: index.php");
    exit;
}

//media
$stmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
$stmt->execute([$postId]);
$mediaFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);

//comments
$stmt = $dbconn->prepare(
    "SELECT comments.*, users.Username, userprofiles.Nickname, userprofiles.ProfilePicture
     FROM comments JOIN users ON comments.UserId = users.id
     JOIN userprofiles ON comments.UserId = userprofiles.UserId
     WHERE comments.PostId = ? ORDER BY comments.CreatedAt ASC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

//+1 view
$dbconn->prepare("UPDATE posts SET ViewCount = ViewCount + 1 WHERE id = ?")->execute([$postId]);

//user provile
$myProfile = null;
if ($loggedIn) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $myProfile = $stmt->fetch(PDO::FETCH_ASSOC);
}

//owner and admin can delete
$canDelete = $loggedIn && (
    (int)$post['UserId'] === (int)$_SESSION['user_id']
    || !empty($_SESSION['is_admin'])
);
?>
<script src="js/feed.js" defer></script>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>
    <div class="feed">
        <!--post-->
        <?php renderPostCard($post, $mediaFiles, null, 0, $loggedIn); ?>

        <!--delete button-->
        <?php if ($canDelete): ?>
        <div style="display:flex;justify-content:flex-end;margin-bottom:10px">
            <form action="../private/delete-post.php" method="POST"
                  onsubmit="return confirm('Delete this post? This cannot be undone.')">
                <input type="hidden" name="post_id" value="<?= $postId ?>">
                <button type="submit" class="btn btn-sm"
                        style="background:#e74c3c;color:#fff;border-radius:20px;border:none;cursor:pointer">
                    <i class="fa-solid fa-trash"></i> Delete post
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!--new comment-->
        <?php if ($loggedIn && $myProfile): ?>
        <div class="comment-compose">
            <img src="../uploads/pfp/<?= htmlspecialchars($myProfile['ProfilePicture'] ?? 'default.png') ?>" alt="">
            <div class="comment-compose-inner">
                <form action="../private/create-comment.php" method="post">
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <textarea name="comment-text" placeholder="Add a comment…" maxlength="300" rows="2"
                        class="form-control"
                        style="border:none;background:none;color:var(--post-text-color);resize:none;outline:none;width:100%;font-family:monospace">
                    </textarea>
                    <div class="comment-compose-footer">
                        <button type="submit" class="btn btn-secondary btn-sm">&ltpost comment&gt</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!--comments-->
        <div class="post-card" style="padding:0;overflow:hidden">
            <?php if (empty($comments)): ?>
            <p style="text-align:center;opacity:0.6;padding:20px">No comments yet. Be the first!</p>
            <?php endif; ?>

            <div class="comment-thread">
                <?php foreach ($comments as $i => $c): ?>
                <div class="comment-thread-item">
                    <div class="comment-thread-avatar">
                        <a href="profile.php?id=<?= $c['UserId'] ?>">
                            <img src="../uploads/pfp/<?= htmlspecialchars($c['ProfilePicture']) ?>" alt="">
                        </a>
                        <?php if ($i < count($comments) - 1): ?>
                        <div class="comment-thread-line"></div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-thread-body">
                        <div class="comment-thread-meta">
                            <a href="profile.php?id=<?= $c['UserId'] ?>" class="no-underline" style="color:inherit">
                                <strong><?= htmlspecialchars($c['Nickname']) ?></strong>
                            </a>
                            <small>@<?= htmlspecialchars($c['Username']) ?></small>
                            <small style="margin-left:auto"><?= date('M j, g:i a', strtotime($c['CreatedAt'])) ?></small>
                        </div>
                        <p class="comment-thread-text"><?= htmlspecialchars($c['Text']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php if ($loggedIn): ?>
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>

<!--comment popout-->
<div id="comment-popout" style="display:none" class="modal-overlay"
     onclick="if(event.target===this)this.style.display='none'">
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
                    <textarea maxlength="300" name="comment-text" class="form-control"
                              placeholder="Write a comment…" rows="2" style="resize:none"></textarea>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:6px">
                    <input type="submit" class="btn btn-secondary btn-sm" value="Post comment">
                </div>
            </form>
        </div>
    </div>
</div>

<!--send post popout-->
<?php
// following list
$profile   = null;
$following = [];
if ($loggedIn) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbconn->prepare( "SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
         FROM followingrelationships fr JOIN users u  ON fr.FollowedUserId = u.id
         JOIN userprofiles up ON u.id = up.UserId
         WHERE fr.UserId = ? LIMIT 30" );
    $stmt->execute([$_SESSION['user_id']]);
    $following = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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

<!--lightbox-->
<div id="lightbox" style="display:none" class="modal-overlay" onclick="if(event.target===this)closeLightbox()">
    <div class="lightbox-box">
        <button class="lightbox-btn lightbox-prev" onclick="lightboxStep(-1)">&#8249;</button>
        <img id="lightbox-img" src="" alt="full size" style="max-width:90vw;max-height:85vh;border-radius:10px;object-fit:contain">
        <button class="lightbox-btn lightbox-next" onclick="lightboxStep(1)">&#8250;</button>
        <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    </div>
</div>
<script defer src="js/lightbox.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>