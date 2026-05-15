<?php
$pageTitle = "Search";
require_once __DIR__ . '/../includes/header.php';
include('../private/dbconnection.php');

$query = trim($_POST['search'] ?? $_GET['q'] ?? '');
$results = [];
$userResults = [];

if ($query !== '') {
    //user search terms
    if (isset($_SESSION['user_id'])) {
        $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE UserId = ? AND SearchTerm = ? AND Type = 'user'");
        $stmt->execute([$_SESSION['user_id'], $query]);
        $stmt = $dbconn->prepare("INSERT INTO searchterms (UserId, SearchTerm, Type) VALUES (?, ?, 'user')");
        $stmt->execute([$_SESSION['user_id'], $query]);
    }
    //post searchs
    $stmt = $dbconn->prepare("SELECT posts.*, userprofiles.Nickname, userprofiles.ProfilePicture,
        COALESCE(SUM(postscore.Value), 0) as Score,
        COALESCE(SUM(Value = 1), 0) as Likes,
        COALESCE(SUM(Value = -1), 0) as Dislikes
        FROM posts JOIN userprofiles ON posts.UserId = userprofiles.UserId
        LEFT JOIN postscore ON posts.Id = postscore.PostId WHERE posts.Text LIKE ?
        GROUP BY posts.Id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY posts.CreatedAt DESC LIMIT 30");
    $stmt->execute(['%'.$query.'%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //user search
    $stmt = $dbconn->prepare("SELECT u.Id, u.Username, up.Nickname, up.ProfilePicture, up.Description
        FROM users u JOIN userprofiles up ON u.Id = up.UserId
        WHERE u.Username LIKE ? OR up.Nickname LIKE ? LIMIT 10");
    $stmt->execute(['%'.$query.'%','%'.$query.'%']);
    $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//recent
$recentSearches = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $dbconn->prepare("SELECT Id, SearchTerm FROM searchterms WHERE UserId = ? AND Type = 'user' ORDER BY Id DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $recentSearches = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//other users searches
$stmt = $dbconn->prepare("SELECT SearchTerm, COUNT(*) as cnt
    FROM searchterms WHERE Type = 'user' GROUP BY SearchTerm
    ORDER BY cnt DESC LIMIT 8");
$stmt->execute();
$popularSearches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>

    <div class="feed">
        <h1>&ltsearch&gt</h1>
        <form action="search.php" method="POST" class="search-form">
            <div id="search-bar" style="width:100%;margin-bottom:15px">
                <input type="text" name="search" id="search-field" value="<?= htmlspecialchars($query) ?>" placeholder="search posts, people..." autofocus>
                <button type="submit" class="btn btn-icon"><i class="fa fa-search"></i></button>
            </div>
        </form>
        <?php if ($query === ''): ?>
        <!--recent-->
        <?php if (!empty($recentSearches)): ?>
        <div class="p-container" style="margin-bottom:20px">
            <div class="p-header">
                <span>&ltrecent&gt</span>
                <a href="search.php?clear_recent=1" class="btn btn-icon" style="margin-left:auto">clear all</a>
            </div>
            <div class="p-content">
                <?php foreach ($recentSearches as $rs): ?>
                <div class="search-term-row">
                    <a href="search.php?q=<?= urlencode($rs['SearchTerm']) ?>" class="link-p">
                        &lt&gt <?= htmlspecialchars($rs['SearchTerm']) ?>
                    </a>
                    <a href="search.php?delete_search=<?= $rs['Id'] ?>" class="btn btn-icon" title="Remove">✕</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <!--popular -->
        <?php if (!empty($popularSearches)): ?>
        <div class="p-container">
            <div class="p-header"><span>&lttrending searches&gt</span></div>
            <div class="p-content">
                <?php foreach ($popularSearches as $ps): ?>
                <div class="search-term-row">
                    <a href="search.php?q=<?= urlencode($ps['SearchTerm']) ?>" class="link-p">
                        &lt&gt <?= htmlspecialchars($ps['SearchTerm']) ?>
                        <small style="opacity:0.6">(<?= $ps['cnt'] ?>)</small>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <!--users-->
        <?php if (!empty($userResults)): ?>
        <div class="p-container" style="margin-bottom:20px">
            <div class="p-header"><span>&ltpeople&gt</span></div>
            <div class="p-content">
                <?php foreach ($userResults as $u): ?>
                <a href="profile.php?id=<?= $u['Id'] ?>" class="no-underline">
                    <div class="search-user-row">
                        <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="post-profile-pic">
                        <div>
                            <strong><?= htmlspecialchars($u['Nickname']) ?></strong>
                            <small>@<?= htmlspecialchars($u['Username']) ?></small>
                            <p style="opacity:0.7"><?= htmlspecialchars(mb_strimwidth($u['Description'] ?? '', 0, 60, '...')) ?></p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <!--post-->
        <?php if (empty($results)): ?>
            <p style="text-align:center">No posts found for "<?= htmlspecialchars($query) ?>"</p>
        <?php else: ?>
        <div class="post-feed">
            <?php foreach ($results as $post): ?>
            <div class="post-container" data-post-id="<?= $post['id'] ?>">
                <a href="profile.php?id=<?= htmlspecialchars($post["UserId"]) ?>" class="post-header no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($post["ProfilePicture"]) ?>" class="post-profile-pic">
                    <span class="post-username"><?= htmlspecialchars($post["Nickname"]) ?></span>
                </a>
                <div class="post-content">
                    <p><?= htmlspecialchars($post["Text"]) ?></p>
                </div>
                <div class="post-button-container">
                    <div>
                        <span class="btn btn-icon">Like (<?= $post['Likes'] ?? 0 ?>)</span>
                        <span class="btn btn-icon">Dislike (<?= $post['Dislikes'] ?? 0 ?>)</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php
//delete
if (isset($_GET['delete_search']) && isset($_SESSION['user_id'])) {
    $delId = (int)$_GET['delete_search'];
    $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE Id = ? AND UserId = ?");
    $stmt->execute([$delId, $_SESSION['user_id']]);
    header("Location: search.php");
    exit;
}
//clear
if (isset($_GET['clear_recent']) && isset($_SESSION['user_id'])) {
    $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE UserId = ? AND Type = 'user'");
    $stmt->execute([$_SESSION['user_id']]);
    header("Location: search.php");
    exit;
}

require_once __DIR__ . '/../includes/footer.php';
?>
