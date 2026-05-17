<?php
$pageTitle = "Search";
require_once __DIR__ . '/../includes/header.php';
include('../private/dbconnection.php');
require_once('../includes/functions.php');

if (isset($_GET['delete_search']) && isset($_SESSION['user_id'])) {
    $delId = (int)$_GET['delete_search'];
    $dbconn->prepare("DELETE FROM searchterms WHERE id = ? AND UserId = ?")->execute([$delId, $_SESSION['user_id']]);
    header("Location: search.php"); exit;
}

$query = trim($_POST['search'] ?? $_GET['q'] ?? '');
$results = [];
$userResults = [];
$loggedIn = isset($_SESSION['user_id']);
$starredIds = [];

if ($query !== '') 
    {
    if ($loggedIn) {
        $dbconn->prepare("DELETE FROM searchterms WHERE UserId = ? AND SearchTerm = ? AND Type = 'user'")->execute([$_SESSION['user_id'], $query]);
        $dbconn->prepare("INSERT INTO searchterms (UserId, SearchTerm, Type) VALUES (?, ?, 'user')")->execute([$_SESSION['user_id'], $query]);
    }
    $stmt = $dbconn->prepare("SELECT posts.*, users.Username,
        userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value=1),0)  AS Likes,
        COALESCE(SUM(postscore.Value=-1),0) AS Dislikes
        FROM posts JOIN users ON posts.UserId = users.id
        JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.id = postscore.PostId
        WHERE posts.Text LIKE ?
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY posts.CreatedAt DESC LIMIT 30");
    $stmt->execute(['%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($loggedIn && !empty($results)) {
        $pids = array_column($results, 'id');
        $ph   = implode(',', array_fill(0, count($pids), '?'));
        $s2   = $dbconn->prepare("SELECT PostId FROM starmarks WHERE UserId = ? AND PostId IN ($ph)");
        $s2->execute(array_merge([$_SESSION['user_id']], $pids));
        $starredIds = $s2->fetchAll(PDO::FETCH_COLUMN);
    }
    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture, up.Description
        FROM users u JOIN userprofiles up ON u.id = up.UserId
        WHERE u.Username LIKE ? OR up.Nickname LIKE ? LIMIT 6");
    $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
    $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$recentSearches = [];
if ($loggedIn) {
    $stmt = $dbconn->prepare("SELECT id, SearchTerm FROM searchterms WHERE UserId = ? AND Type = 'user' ORDER BY id DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $recentSearches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$stmt = $dbconn->prepare("SELECT SearchTerm, COUNT(*) AS cnt FROM searchterms WHERE Type = 'user' GROUP BY SearchTerm ORDER BY cnt DESC LIMIT 8");
$stmt->execute();
$popularSearches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="js/feed.js" defer></script>
<script src="js/lightbox.js" defer></script>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>

    <div class="feed">
        <h1>&ltsearch&gt</h1>

        <form action="search.php" method="GET">
            <div style="width:100%;margin-bottom:20px">
                <div id="search-bar" style="max-width:100%">
                    <i class="fa-solid fa-magnifying-glass" style="opacity:0.7"></i>
                    <input type="text" name="q" id="search-field-page"
                        value="<?= htmlspecialchars($query) ?>"
                        placeholder="search…" autocomplete="off" style="flex:1">
                    <?php if ($query !== ''): ?>
                    <a href="search.php" style="color:var(--secondary-text-color);opacity:0.7;text-decoration:none">✕</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if ($query === ''): ?>
        <div class="p-container" style="overflow:hidden">
            <div id="search-dropdown-inner" style="display:block">
            
            <?php if (!empty($recentSearches)): ?>
                <div class="search-dd-section">Recent</div>
                <?php foreach ($recentSearches as $rs): ?>
                <div class="search-dd-row">
                    <i class="fa-solid fa-clock-rotate-left search-dd-icon"></i>
                    <a class="no-underline" href="search.php?q=<?= urlencode($rs['SearchTerm']) ?>" style="flex:1;color:var(--post-text-color)">
                        <?= htmlspecialchars($rs['SearchTerm']) ?>
                    </a>
                    <a href="search.php?delete_search=<?= $rs['id'] ?>" class="search-dd-del no-underline" title="remove">✕</a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($popularSearches)): ?>
                <div class="search-dd-section">Trending</div>
                <?php foreach ($popularSearches as $ps): ?>
                <div class="search-dd-row">
                    <i class="fa-solid fa-sun search-dd-icon" style="color:var(--accent-color)"></i>
                    <a class="no-underline" href="search.php?q=<?= urlencode($ps['SearchTerm']) ?>" style="flex:1;color:var(--post-text-color)">
                        <?= htmlspecialchars($ps['SearchTerm']) ?>
                    </a>
                    <small style="opacity:0.5"><?= $ps['cnt'] ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($recentSearches) && empty($popularSearches)): ?>
                <div class="search-dd-empty">Start typing to search…</div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>

        <?php if (empty($userResults) && empty($results)): ?>
        <div class="p-container" style="padding:20px;text-align:center;opacity:0.6">
            No results found for "<?= htmlspecialchars($query) ?>"
        </div>
        <?php endif; ?>

        <?php if (!empty($userResults)): ?>
        <p class="search-results-section-title">People</p>
        <?php foreach ($userResults as $u): ?>
        <a href="profile.php?id=<?= $u['id'] ?>" class="search-user-card">
            <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" alt="">
            <div class="search-user-card-info">
                <strong><?= htmlspecialchars($u['Nickname']) ?></strong>
                <small>@<?= htmlspecialchars($u['Username']) ?></small>
                <?php if (!empty($u['Description'])): ?>
                <p class="search-user-card-bio"><?= htmlspecialchars(mb_strimwidth($u['Description'], 0, 80, '…')) ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
        <p class="search-results-section-title">Posts</p>
        <div class="post-feed" style="padding:0">
            <?php foreach ($results as $post):
                $mstmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
                $mstmt->execute([$post['id']]);
                $mf = $mstmt->fetchAll(PDO::FETCH_COLUMN);
                renderPostCard($post, $mf, null, 0, $loggedIn, $starredIds);
            endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php if ($loggedIn): ?>
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
                <textarea maxlength="300" name="comment-text" class="form-control" placeholder="Write a comment…" rows="2" style="resize:none"></textarea>
                <div style="display:flex;justify-content:flex-end;margin-top:6px">
                    <input type="submit" class="btn btn-secondary btn-sm" value="Post comment">
                </div>
            </form>
        </div>
    </div>
</div>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>