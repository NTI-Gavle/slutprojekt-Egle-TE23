<?php
$pageTitle = "Profile";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');
require_once('../includes/functions.php');

if (!isset($_SESSION["user_id"])) { header("Location: login.php"); exit; }

$myId   = $_SESSION["user_id"];
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : $myId;
$isOwnProfile = ($viewId === $myId);
$loggedIn = true;

$stmt = $dbconn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewId]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$res) { header("Location: index.php"); exit; }

$stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
$stmt->execute([$viewId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $dbconn->prepare("SELECT COUNT(*) FROM followingrelationships WHERE UserId = ?");
$stmt->execute([$viewId]);
$followingCount = $stmt->fetchColumn();

$stmt = $dbconn->prepare("SELECT COUNT(*) FROM followingrelationships WHERE FollowedUserId = ?");
$stmt->execute([$viewId]);
$followerCount = $stmt->fetchColumn();

$isFollowing = false;
if (!$isOwnProfile) {
    $stmt = $dbconn->prepare("SELECT 1 FROM followingrelationships WHERE UserId = ? AND FollowedUserId = ?");
    $stmt->execute([$myId, $viewId]);
    $isFollowing = (bool)$stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isOwnProfile) {
    if (isset($_POST['follow'])) {
        $dbconn->prepare("INSERT IGNORE INTO followingrelationships (UserId, FollowedUserId) VALUES (?,?)")->execute([$myId, $viewId]);
    } elseif (isset($_POST['unfollow'])) {
        $dbconn->prepare("DELETE FROM followingrelationships WHERE UserId = ? AND FollowedUserId = ?")->execute([$myId, $viewId]);
    }
    header("Location: profile.php?id=$viewId"); exit;
}

$tab = $_GET['tab'] ?? 'posts';
$posts = $mediaPosts = $starredPosts = $userComments = [];

if ($tab === 'posts') {
    $stmt = $dbconn->prepare("SELECT posts.*, users.Username,
        COALESCE(SUM(ps.Value=1),0) as Likes, COALESCE(SUM(ps.Value=-1),0) as Dislikes
        FROM posts JOIN users ON posts.UserId = users.id
        LEFT JOIN postscore ps ON posts.id = ps.PostId
        WHERE posts.UserId = ?
        GROUP BY posts.id ORDER BY posts.CreatedAt DESC LIMIT 50");
    $stmt->execute([$viewId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($posts as &$p) {
        $p['Nickname'] = $profile['Nickname'];
        $p['ProfilePicture'] = $profile['ProfilePicture'];
    } unset($p);
}
if ($tab === 'media') {
    $stmt = $dbconn->prepare("SELECT posts.*, GROUP_CONCAT(media.FileName ORDER BY media.id) as MediaFiles
        FROM posts JOIN media ON media.PostId = posts.id WHERE posts.UserId = ?
        GROUP BY posts.id ORDER BY posts.CreatedAt DESC LIMIT 50");
    $stmt->execute([$viewId]);
    $mediaPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($tab === 'stars') {
    $stmt = $dbconn->prepare("SELECT posts.*, users.Username,
        userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(ps.Value=1),0) as Likes, COALESCE(SUM(ps.Value=-1),0) as Dislikes
        FROM starmarks JOIN posts ON starmarks.PostId = posts.id
        JOIN users ON posts.UserId = users.id
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ps ON posts.id = ps.PostId
        WHERE starmarks.UserId = ?
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY starmarks.id DESC LIMIT 50");
    $stmt->execute([$viewId]);
    $starredPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
if ($tab === 'comments') {
    $stmt = $dbconn->prepare("SELECT comments.*, users.Username,
        posts.Text as PostText, posts.id as PostId,
        up2.Nickname as PostAuthorNick, up2.ProfilePicture as PostAuthorPic
        FROM comments
        JOIN users ON comments.UserId = users.id
        JOIN posts ON comments.PostId = posts.id
        JOIN userprofiles up2 ON posts.UserId = up2.UserId
        WHERE comments.UserId = ?
        ORDER BY comments.CreatedAt DESC LIMIT 50");
    $stmt->execute([$viewId]);
    $userComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture FROM followingrelationships fr JOIN users u ON fr.FollowedUserId = u.id JOIN userprofiles up ON u.id = up.UserId WHERE fr.UserId = ?");
$stmt->execute([$viewId]); $followingList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture FROM followingrelationships fr JOIN users u ON fr.UserId = u.id JOIN userprofiles up ON u.id = up.UserId WHERE fr.FollowedUserId = ?");
$stmt->execute([$viewId]); $followerList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<script src="js/feed.js" defer></script>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>
    <div class="feed">
        <!--profile-->
        <div class="post-card profile-card">
            <div class="profile-background-container">
                <img src="../uploads/banner/<?= htmlspecialchars($profile['Banner'] ?? 'default_banner.jpg') ?>" alt="banner" class="profile-background">
            </div>
            <div class="profile-card-body">
                <img src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture'] ?? 'default.png') ?>" alt="pfp" class="profile-pic-large">
                <div class="profile-info">
                    <div class="profile-name-row">
                        <div>
                            <h2 style="margin:0"><?= htmlspecialchars($profile['Nickname'] ?? '') ?></h2>
                            <span style="opacity:0.6">@<?= htmlspecialchars($res['Username']) ?></span>
                        </div>
                        <?php if ($isOwnProfile): ?>
                        <a class="btn btn-secondary btn-sm" href="settings.php">&ltedit profile&gt</a>
                        <?php else: ?>
                        <form method="POST">
                            <button name="<?= $isFollowing ? 'unfollow' : 'follow' ?>" class="btn btn-secondary btn-sm">
                                &lt<?= $isFollowing ? 'unfollow' : 'follow' ?>&gt
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($profile['Description'])): ?>
                    <p class="profile-bio"><?= htmlspecialchars($profile['Description']) ?></p>
                    <?php endif; ?>
                    <div class="profile-stats-row">
                        <button class="btn-stat" onclick="document.getElementById('followers-modal').style.display='flex'">
                            <strong><?= $followerCount ?></strong> followers
                        </button>
                        <button class="btn-stat" onclick="document.getElementById('following-modal').style.display='flex'">
                            <strong><?= $followingCount ?></strong> following
                        </button>
                    </div>
                </div>
            </div>
            <div class="profile-tabs">
                <a href="?id=<?= $viewId ?>&tab=posts" class="profile-tab <?= $tab==='posts'?'active':'' ?>">&ltposts&gt</a>
                <a href="?id=<?= $viewId ?>&tab=media" class="profile-tab <?= $tab==='media'?'active':'' ?>">&ltmedia&gt</a>
                <a href="?id=<?= $viewId ?>&tab=stars" class="profile-tab <?= $tab==='stars'?'active':'' ?>">&ltstars&gt</a>
                <a href="?id=<?= $viewId ?>&tab=comments" class="profile-tab <?= $tab==='comments'?'active':'' ?>">&ltcomments&gt</a>
            </div>
        </div>

        <?php if ($tab === 'posts'): ?>
        <div class="post-feed">
            <?php if (empty($posts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No posts yet.</p><?php endif; ?>
            <?php foreach ($posts as $post):
                $mstmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
                $mstmt->execute([$post['id']]);
                $mf = $mstmt->fetchAll(PDO::FETCH_COLUMN);
                renderPostCard($post, $mf, null, 0, $loggedIn);
            endforeach; ?>
        </div>

        <?php elseif ($tab === 'media'): ?>
        <div class="media-grid">
            <?php if (empty($mediaPosts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No media yet.</p><?php endif; ?>
            <?php foreach ($mediaPosts as $mp):
                foreach (explode(',', $mp['MediaFiles']) as $file): ?>
            <a href="post.php?id=<?= $mp['id'] ?>">
                <img src="../uploads/media/<?= htmlspecialchars($file) ?>" class="media-thumb">
            </a>
            <?php endforeach; endforeach; ?>
        </div>

        <?php elseif ($tab === 'stars'): ?>
        <div class="post-feed">
            <?php if (empty($starredPosts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No starred posts.</p><?php endif; ?>
            <?php foreach ($starredPosts as $post):
                $mstmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
                $mstmt->execute([$post['id']]);
                $mf = $mstmt->fetchAll(PDO::FETCH_COLUMN);
                renderPostCard($post, $mf, null, 0, $loggedIn);
            endforeach; ?>
        </div>

        <?php elseif ($tab === 'comments'): ?>
        <div class="post-card" style="overflow:hidden">
            <?php if (empty($userComments)): ?><p style="text-align:center;opacity:0.6;padding:20px">No comments yet.</p><?php endif; ?>
            <div class="comment-thread">
                <?php foreach ($userComments as $c): ?>
                <div class="comment-thread-item">
                    <div class="comment-thread-avatar">
                        <img src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture']) ?>" alt="">
                    </div>
                    <div class="comment-thread-body">
                        <div class="comment-thread-meta">
                            <strong><?= htmlspecialchars($profile['Nickname']) ?></strong>
                            <small>@<?= htmlspecialchars($res['Username']) ?></small>
                            <small style="margin-left:auto"><?= date('M j, g:i a', strtotime($c['CreatedAt'])) ?></small>
                        </div>
                        <p class="comment-thread-text"><?= htmlspecialchars($c['Text']) ?></p>
                        <a href="post.php?id=<?= $c['PostId'] ?>" class="no-underline" style="font-size:0.78em; opacity:0.55; display:flex; gap:6px; align-items:center;margin-top:4px">
                            <img src="../uploads/pfp/<?= htmlspecialchars($c['PostAuthorPic']) ?>" style="width:18px;height:18px;border-radius:50%;object-fit:cover">
                            Replying to <?= htmlspecialchars($c['PostAuthorNick']) ?>: <em><?= htmlspecialchars(mb_strimwidth($c['PostText'], 0, 55, '…')) ?></em>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<!--followers-->
<div id="followers-modal" style="display:none" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header"><span>Followers</span><button class="btn btn-icon" onclick="document.getElementById('followers-modal').style.display='none'">✕</button></div>
        <div class="p-content">
            <?php foreach ($followerList as $u): ?>
            <a href="profile.php?id=<?= $u['id'] ?>" class="link-p">
                <div class="search-user-row">
                    <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="post-profile-pic">
                    <div><strong><?= htmlspecialchars($u['Nickname']) ?></strong><br><small>@<?= htmlspecialchars($u['Username']) ?></small></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($followerList)): ?><p style="opacity:0.6">No followers yet.</p><?php endif; ?>
        </div>
    </div>
</div>

<!--following-->
<div id="following-modal" style="display:none" class="modal-overlay" onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header"><span>Following</span><button class="btn btn-icon" onclick="document.getElementById('following-modal').style.display='none'">✕</button></div>
        <div class="p-content">
            <?php foreach ($followingList as $u): ?>
            <a href="profile.php?id=<?= $u['id'] ?>" class="link-p">
                <div class="search-user-row">
                    <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="post-profile-pic">
                    <div><strong><?= htmlspecialchars($u['Nickname']) ?></strong><br><small>@<?= htmlspecialchars($u['Username']) ?></small></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($followingList)): ?><p style="opacity:0.6">Not following anyone yet.</p><?php endif; ?>
        </div>
    </div>
</div>


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
<?php 
$profile = null;
$following = [];
if ($loggedIn) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
        FROM followingrelationships fr
        JOIN users u ON fr.FollowedUserId = u.id
        JOIN userprofiles up ON u.id = up.UserId
        WHERE fr.UserId = ? LIMIT 30");
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


<?php require_once __DIR__ . '/../includes/footer.php'; ?>