<?php
$pageTitle = "Profile";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$myId = $_SESSION["user_id"];
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : $myId;
$isOwnProfile = ($viewId === $myId);

//viewer
$stmt = $dbconn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewId]);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$res) { header("Location: index.php"); exit; }
$stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
$stmt->execute([$viewId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
//follow count
$stmt = $dbconn->prepare("SELECT COUNT(*) FROM followingrelationships WHERE UserId = ?");
$stmt->execute([$viewId]);
$followingCount = $stmt->fetchColumn();

$stmt = $dbconn->prepare("SELECT COUNT(*) FROM followingrelationships WHERE FollowedUserId = ?");
$stmt->execute([$viewId]);
$followerCount = $stmt->fetchColumn();
//viewer follow status
$isFollowing = false;
if (!$isOwnProfile) {
    $stmt = $dbconn->prepare("SELECT 1 FROM followingrelationships WHERE UserId = ? AND FollowedUserId = ?");
    $stmt->execute([$myId, $viewId]);
    $isFollowing = (bool)$stmt->fetchColumn();
}
//follow unfollow
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isOwnProfile) {
    if (isset($_POST['follow'])) {
        $stmt = $dbconn->prepare("INSERT IGNORE INTO followingrelationships (UserId, FollowedUserId) VALUES (?, ?)");
        $stmt->execute([$myId, $viewId]);
    } 
    elseif (isset($_POST['unfollow'])) {
        $stmt = $dbconn->prepare("DELETE FROM followingrelationships WHERE UserId = ? AND FollowedUserId = ?");
        $stmt->execute([$myId, $viewId]);
    }
    header("Location: profile.php?id=$viewId");
    exit;
}
$tab = $_GET['tab'] ?? 'posts';
//posts
$posts = [];
if ($tab === 'posts') {
    $stmt = $dbconn->prepare(" SELECT posts.*,
        COALESCE(SUM(ps.Value=1),0) as Likes,
        COALESCE(SUM(ps.Value=-1),0) as Dislikes
        FROM posts LEFT JOIN postscore ps ON posts.id = ps.PostId
        WHERE posts.UserId = ?
        GROUP BY posts.id ORDER BY posts.CreatedAt DESC LIMIT 50 ");
    $stmt->execute([$viewId]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//media
$mediaPosts = [];
if ($tab === 'media') {
    $stmt = $dbconn->prepare("  SELECT posts.*, GROUP_CONCAT(media.FileName ORDER BY media.id) as MediaFiles
        FROM posts JOIN media ON media.PostId = posts.id WHERE posts.UserId = ?
        GROUP BY posts.id ORDER BY posts.CreatedAt DESC LIMIT 50 ");
    $stmt->execute([$viewId]);
    $mediaPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//starmerks
$starredPosts = [];
if ($tab === 'stars') {
    $stmt = $dbconn->prepare("SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(ps.Value=1),0) as Likes, 
        COALESCE(SUM(ps.Value=-1),0) as Dislikes
        FROM starmarks JOIN posts ON starmarks.PostId = posts.id
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ps ON posts.id = ps.PostId WHERE starmarks.UserId = ?
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture 
        ORDER BY starmarks.id DESC LIMIT 50 ");
    $stmt->execute([$viewId]);
    $starredPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//comments
$userComments = [];
if ($tab === 'comments') {
    $stmt = $dbconn->prepare("SELECT comments.*, posts.Text as PostText, posts.id as PostId,
        up2.Nickname as PostAuthorNick, up2.ProfilePicture as PostAuthorPic
        FROM comments JOIN posts ON comments.PostId = posts.id
        JOIN userprofiles up2 ON posts.UserId = up2.UserId  WHERE comments.UserId = ?
        ORDER BY comments.CreatedAt DESC LIMIT 50 ");
    $stmt->execute([$viewId]);
    $userComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//follows
$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture FROM followingrelationships fr JOIN users u ON fr.FollowedUserId = u.id JOIN userprofiles up ON u.id = up.UserId WHERE fr.UserId = ?");
$stmt->execute([$viewId]);
$followingList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture FROM followingrelationships fr JOIN users u ON fr.UserId = u.id JOIN userprofiles up ON u.id = up.UserId WHERE fr.FollowedUserId = ?");
$stmt->execute([$viewId]);
$followerList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<script src="js/feed.js" defer></script>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>

    <div class="feed">
        <!--profile-->
        <div class="post-container profile-card">
            <div class="profile-background-container">
                <img src="../uploads/banner/<?= htmlspecialchars($profile['Banner'] ?? 'default_banner.jpg') ?>"
                    alt="banner" class="profile-background">
            </div>
            <div class="profile-card-body">
                <img src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture'] ?? 'default.png') ?>" alt="pfp"
                    class="profile-pic-large">
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
                        <button class="btn-stat"
                            onclick="document.getElementById('followers-modal').style.display='flex'">
                            <strong><?= $followerCount ?></strong> followers
                        </button>
                        <button class="btn-stat"
                            onclick="document.getElementById('following-modal').style.display='flex'">
                            <strong><?= $followingCount ?></strong> following
                        </button>
                    </div>
                </div>
            </div>
            <!--tabs-->
            <div class="profile-tabs">
                <a href="?id=<?= $viewId ?>&tab=posts" class="profile-tab <?= $tab==='posts'? 'active':'' ?>">&ltposts&gt</a>
                <a href="?id=<?= $viewId ?>&tab=media" class="profile-tab <?= $tab==='media'? 'active':'' ?>">&ltmedia&gt</a>
                <a href="?id=<?= $viewId ?>&tab=stars" class="profile-tab <?= $tab==='stars'? 'active':'' ?>">&ltstars&gt</a>
                <a href="?id=<?= $viewId ?>&tab=comments" class="profile-tab <?=$tab==='comments'? 'active':'' ?>">&ltcomments&gt</a>
            </div>
        </div>


        <?php if ($tab === 'posts'): ?>
        <div class="post-feed">
            <?php if (empty($posts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No posts yet.</p>
            <?php endif; ?>
            <?php foreach ($posts as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <div class="p-header">
                    <img src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture']) ?>"
                        class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($profile['Nickname']) ?></span>
                    <span class="post-views"><i class="fa-solid fa-eye"></i>
                        <?= number_format($post['ViewCount'] ?? 0) ?></span>
                </div>
                <div class="p-content">
                    <p><?= htmlspecialchars($post['Text']) ?></p>
                </div>
                <div class="post-button-container">
                    <div>
                        <button class="btn btn-icon like-btn"><i class="fa-solid fa-thumbs-up"></i>
                            <?= $post['Likes'] ?></button>
                        <button class="btn btn-icon dislike-btn"><i class="fa-solid fa-thumbs-down"></i>
                            <?= $post['Dislikes'] ?></button>
                        <button class="btn btn-icon comment-btn"><i class="fa-solid fa-comment"></i> Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn"><i class="fa-solid fa-star"></i> Star</button>
                        <button class="btn btn-icon share-btn"><i class="fa-solid fa-paper-plane"></i> Send</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($tab === 'media'): ?>
        <div class="media-grid">
            <?php if (empty($mediaPosts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No media yet.</p>
            <?php endif; ?>
            <?php foreach ($mediaPosts as $mp):
            $files = explode(',', $mp['MediaFiles']);
        ?>
            <?php foreach ($files as $file): ?>
            <a href="post.php?id=<?= $mp['id'] ?>">
                <img src="../uploads/media/<?= htmlspecialchars($file) ?>" class="media-thumb">
            </a>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

        <?php elseif ($tab === 'stars'): ?>
        <div class="post-feed">
            <?php if (empty($starredPosts)): ?><p style="text-align:center;opacity:0.6;padding:20px">No starred posts.
            </p><?php endif; ?>
            <?php foreach ($starredPosts as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <a href="profile.php?id=<?= $post['UserId'] ?>" class="post-header no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($post['ProfilePicture']) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post['Nickname']) ?></span>
                </a>
                <div class="post-content">
                    <p><?= htmlspecialchars($post['Text']) ?></p>
                </div>
                <div class="post-button-container">
                    <div>
                        <button class="btn btn-icon like-btn">Like (<?= $post['Likes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon dislike-btn">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</button>
                        <button class="btn btn-icon comment-btn">Comment</button>
                    </div>
                    <div>
                        <button class="btn btn-icon starmark-btn">Star</button>
                        <button class="btn btn-icon share-btn">Send</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php elseif ($tab === 'comments'): ?>
        <div class="post-feed">
            <?php if (empty($userComments)): ?><p style="text-align:center;opacity:0.6;padding:20px">No comments yet.
            </p><?php endif; ?>
            <?php foreach ($userComments as $c): ?>
            <div class="post-container">
                <a href="post.php?id=<?= $c['PostId'] ?>" class="post-header no-underline" style="opacity:0.75">
                    <img src="../uploads/pfp/<?= htmlspecialchars($c['PostAuthorPic']) ?>" class="post-profile-pic"
                        style="width:32px;height:32px">
                    <span style="font-size:0.85em">Replying to <?= htmlspecialchars($c['PostAuthorNick']) ?>:
                        <em><?= htmlspecialchars(mb_strimwidth($c['PostText'],0,60,'...')) ?></em></span>
                </a>
                <div class="post-content">
                    <div style="display:flex;gap:10px;align-items:flex-start">
                        <img src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture']) ?>"
                            class="post-profile-pic" style="width:32px;height:32px">
                        <p><?= htmlspecialchars($c['Text']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<!--followers-->
<div id="followers-modal" style="display:none" class="modal-overlay"
    onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header">
            <span>Followers</span>
            <button class="btn btn-icon"
                onclick="document.getElementById('followers-modal').style.display='none'">✕</button>
        </div>
        <div class="p-content">
            <?php foreach ($followerList as $u): ?>
            <a href="profile.php?id=<?= $u['id'] ?>" class="no-underline">
                <div class="search-user-row">
                    <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="post-profile-pic">
                    <div>
                        <strong><?= htmlspecialchars($u['Nickname']) ?></strong><br><small>@<?= htmlspecialchars($u['Username']) ?></small>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($followerList)): ?><p style="opacity:0.6">No followers yet.</p><?php endif; ?>
        </div>
    </div>
</div>

<!--following-->
<div id="following-modal" style="display:none" class="modal-overlay"
    onclick="if(event.target===this)this.style.display='none'">
    <div class="p-container modal-box">
        <div class="p-header">
            <span>Following</span>
            <button class="btn btn-icon"
                onclick="document.getElementById('following-modal').style.display='none'">✕</button>
        </div>
        <div class="p-content">
            <?php foreach ($followingList as $u): ?>
            <a href="profile.php?id=<?= $u['id'] ?>" class="no-underline">
                <div class="search-user-row">
                    <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="post-profile-pic">
                    <div>
                        <strong><?= htmlspecialchars($u['Nickname']) ?></strong><br><small>@<?= htmlspecialchars($u['Username']) ?></small>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($followingList)): ?><p style="opacity:0.6">Not following anyone yet.</p><?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>