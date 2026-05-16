<?php
$pageTitle = "Post";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');

$postId = (int)($_GET['id'] ?? 0);
if (!$postId) { header("Location: index.php"); exit; }

$stmt = $dbconn->prepare("SELECT posts.*,
    ANY_VALUE(users.Username) AS Username,
    ANY_VALUE(userprofiles.Nickname) AS Nickname,
    ANY_VALUE(userprofiles.ProfilePicture) AS ProfilePicture,
    COALESCE(SUM(ps.Value = 1), 0) AS Likes,
    COALESCE(SUM(ps.Value = -1), 0) AS Dislikes
    FROM posts
    JOIN users ON posts.UserId = users.id
    JOIN userprofiles ON posts.UserId = userprofiles.UserId
    LEFT JOIN postscore ps ON posts.id = ps.PostId
    WHERE posts.id = ?
    GROUP BY posts.id");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) { header("Location: index.php"); exit; }

//media
$stmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ? ORDER BY id");
$stmt->execute([$postId]);
$mediaFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);

//comments
$stmt = $dbconn->prepare("SELECT comments.*, userprofiles.Nickname, userprofiles.ProfilePicture
    FROM comments JOIN userprofiles ON comments.UserId = userprofiles.UserId
    WHERE comments.PostId = ?
    ORDER BY comments.CreatedAt ASC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

//view
if ($postId) {
    $dbconn->prepare("UPDATE posts SET ViewCount = ViewCount + 1 WHERE id = ?")->execute([$postId]);
}
?>
<script src="js/feed.js" defer></script>

<div class="feed-container">
    <?php require_once __DIR__ . '/../includes/feednav.php'; ?>

    <div class="feed">
        <div class="post-container" data-post-id="<?= $post['id'] ?>">
            <a href="profile.php?id=<?= $post['UserId'] ?>" class="post-header no-underline">
                <img src="../uploads/pfp/<?= htmlspecialchars($post['ProfilePicture']) ?>" class="post-profile-pic">
                <div>
                    <span class="post-username"><?= htmlspecialchars($post['Nickname']) ?></span>
                    <small style="display:block;opacity:0.6">@<?= htmlspecialchars($post['Username']) ?></small>
                </div>
                <span class="post-views">👁 <?= number_format($post['ViewCount'] ?? 0) ?></span>
            </a>
            <div class="post-content">
                <p style="font-size:1.1em"><?= htmlspecialchars($post['Text']) ?></p>
                <?php if (!empty($mediaFiles)): ?>
                <div class="post-img-container">
                    <?php foreach ($mediaFiles as $i => $file): ?>
                    <img src="../uploads/media/<?= htmlspecialchars($file) ?>" class="post-media-img lightbox-trigger"
                        data-index="<?= $i ?>" data-images='<?= htmlspecialchars(json_encode($mediaFiles)) ?>'
                        alt="post image">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <small style="opacity:0.5"><?= date('M j, Y g:i a', strtotime($post['CreatedAt'])) ?></small>
            </div>
            <div class="post-button-containesr">
                <?php if (isset($_SESSION['user_id'])): ?>
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

        <!-- Comment form -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="post-container" style="padding:0">
            <form action="../private/create-comment.php" method="post"
                style="padding:15px;display:flex;flex-direction:column;gap:10px">
                <input type="hidden" name="post_id" value="<?= $postId ?>">
                <textarea name="comment-text" class="form-control" placeholder="Add a comment..." maxlength="300"
                    rows="2" style="resize:none"></textarea>
                <div style="display:flex;justify-content:flex-end">
                    <button type="submit" class="btn btn-secondary btn-sm">&ltpost comment&gt</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Comments list -->
        <div class="post-feed" style="margin-top:0">
            <?php if (empty($comments)): ?>
            <p style="text-align:center;opacity:0.6;padding:20px">No comments yet. Be the first!</p>
            <?php endif; ?>
            <?php foreach ($comments as $c): ?>
            <div class="post-container comment-post">
                <div class="post-header" style="padding:8px 12px">
                    <img src="../uploads/pfp/<?= htmlspecialchars($c['ProfilePicture']) ?>" class="post-profile-pic"
                        style="width:36px;height:36px">
                    <a href="profile.php?id=<?= $c['UserId'] ?>" class="post-username no-underline"
                        style="font-size:1em"><?= htmlspecialchars($c['Nickname']) ?></a>
                    <small
                        style="margin-left:auto;opacity:0.5"><?= date('M j, g:i a', strtotime($c['CreatedAt'])) ?></small>
                </div>
                <div class="post-content" style="padding:10px 20px">
                    <p><?= htmlspecialchars($c['Text']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<!-- Lightbox for images -->
<div id="lightbox" style="display:none" class="modal-overlay" onclick="if(event.target===this)closeLightbox()">
    <div class="lightbox-box">
        <button class="lightbox-btn lightbox-prev" onclick="lightboxStep(-1)">&#8249;</button>
        <img id="lightbox-img" src="" alt="full size"
            style="max-width:90vw;max-height:85vh;border-radius:10px;object-fit:contain">
        <button class="lightbox-btn lightbox-next" onclick="lightboxStep(1)">&#8250;</button>
        <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    </div>
</div>

<script>
let lbImages = [],
    lbIndex = 0;
document.querySelectorAll('.lightbox-trigger').forEach(img => {
    img.addEventListener('click', () => {
        lbImages = JSON.parse(img.dataset.images);
        lbIndex = parseInt(img.dataset.index);
        openLightbox();
    });
});

function openLightbox() {
    document.getElementById('lightbox-img').src = '../uploads/media/' + lbImages[lbIndex];
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}

function lightboxStep(dir) {
    lbIndex = (lbIndex + dir + lbImages.length) % lbImages.length;
    openLightbox();
}
document.addEventListener('keydown', e => {
    if (document.getElementById('lightbox').style.display === 'flex') {
        if (e.key === 'ArrowRight') lightboxStep(1);
        if (e.key === 'ArrowLeft') lightboxStep(-1);
        if (e.key === 'Escape') closeLightbox();
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>