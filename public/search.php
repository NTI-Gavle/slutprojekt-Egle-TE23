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
        LEFT JOIN postscore ON posts.id = postscore.PostId WHERE posts.Text LIKE ?
        GROUP BY posts.id, userprofiles.Nickname, userprofiles.ProfilePicture
        ORDER BY posts.CreatedAt DESC LIMIT 30");
    $stmt->execute(['%'.$query.'%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //user search
    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture, up.Description
        FROM users u JOIN userprofiles up ON u.id = up.UserId
        WHERE u.Username LIKE ? OR up.Nickname LIKE ? LIMIT 10");
    $stmt->execute(['%'.$query.'%','%'.$query.'%']);
    $userResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//recent
$recentSearches = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $dbconn->prepare("SELECT id, SearchTerm FROM searchterms WHERE UserId = ? AND Type = 'user' ORDER BY id DESC LIMIT 5");
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

        <form action="search.php" method="GET" class="search-form">
            <div id="search-wrapper" style="width:100%;margin-bottom:15px; max-width: 5000px;">
                <div id="search-bar">
                    <i class="fa-solid fa-magnifying-glass" style="opacity:0.7"></i>
                    <input type="text" name="q" id="search-field" value="<?= htmlspecialchars($query) ?>"
                        placeholder="search..." autocomplete="off">
                </div>
            </div>
        </form>

        <div id="search-dropdown" style="position:static; display:block;">
            <div id="search-dropdown-inner">
                <?php if ($query === ''): ?>
                <!--recent-->
                <?php if (!empty($recentSearches)): ?>
                <div class="search-dd-section">Recent</div>

                <?php foreach ($recentSearches as $rs): ?>
                <div class="search-dd-row">
                    <i class="fa-solid fa-clock-rotate-left search-dd-icon"></i>

                    <a class="no-underline" href="search.php?q=<?= urlencode($rs['SearchTerm']) ?>">
                        <?= htmlspecialchars($rs['SearchTerm']) ?>
                    </a>

                    <button class="search-dd-del" onclick="location.href='search.php?delete_search=<?= $rs['id'] ?>'">
                        ✕
                    </button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <!--popular -->
                <?php if (!empty($popularSearches)): ?>
                <div class="search-dd-section">Trending</div>
                
                <?php foreach ($popularSearches as $ps): ?>
                <div class="search-dd-row">
                    <i class="fa-solid fa-sun search-dd-icon" style="color:var(--accent-color)"></i>

                    <a class="no-underline" href="search.php?q=<?= urlencode($ps['SearchTerm']) ?>">
                        <?= htmlspecialchars($ps['SearchTerm']) ?>
                    </a>

                    <small style="opacity:0.5;margin-left:auto">
                        <?= $ps['cnt'] ?>
                    </small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <?php else: ?>
                <!--users-->
                <?php if (!empty($userResults)): ?>
                <div class="search-dd-section">People</div>
                <?php foreach ($userResults as $u): ?>
                <a href="profile.php?id=<?= $u['id'] ?>" class="search-dd-row no-underline">
                    <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture']) ?>" class="search-dd-pfp">
                    <div>
                        <strong><?= htmlspecialchars($u['Nickname']) ?></strong>
                        <small>@<?= htmlspecialchars($u['Username']) ?></small>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
                <!--post-->
                <?php if (!empty($results)): ?>
                <div class="search-dd-section">Posts</div>

                <?php foreach ($results as $post): ?>
                <a href="post.php?id=<?= $post['id'] ?>" class="search-dd-row no-underline">
                    <i class="fa-solid fa-magnifying-glass search-dd-icon"></i>
                    <span>
                        <?= htmlspecialchars(mb_strimwidth($post['Text'], 0, 70, '...')) ?>
                    </span>
                </a>
                
                <?php endforeach; ?>

                <?php else: ?>
                <div class="search-dd-empty">
                    No results found for "<?= htmlspecialchars($query) ?>"
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php
//delete
if (isset($_GET['delete_search']) && isset($_SESSION['user_id'])) {
    $delId = (int)$_GET['delete_search'];
    $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE id = ? AND UserId = ?");
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