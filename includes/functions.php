<?php
function renderPostCard(array $post, array $mediaFiles = [], ?array $topComment = null, int $commentCount = 0, bool $loggedIn = false): void {
    $pid      = (int)$post['id'];
    $likes    = (int)($post['Likes'] ?? 0);
    $dislikes = (int)($post['Dislikes'] ?? 0);
    $views    = number_format($post['ViewCount'] ?? 0);
    $date     = date('M j, Y · g:i a', strtotime($post['CreatedAt']));
    $mediaCount = count($mediaFiles);
?>
<div class="post-card" data-post-id="<?= $pid ?>">
    <a href="post.php?id=<?= (int)$post['id'] ?>" class="post-header">

        <img href="profile.php?id=<?= (int)$post['UserId'] ?>"
            src="../uploads/pfp/<?= htmlspecialchars($post['ProfilePicture'] ?? 'default.png') ?>"
            class="post-profile-pic" alt="">
        <div>
            <div class="post-username"><?= htmlspecialchars($post['Nickname'] ?? '') ?></div>
            <div class="post-handle">@<?= htmlspecialchars($post['Username'] ?? '') ?></div>
        </div>
        <span class="post-views" style="margin-left:auto"><i class="fa-solid fa-eye"></i> <?= $views ?></span>
    </a>

    <?php if ($post['Text'] !== ''): ?>
    <div class="post-body">
        <p><?= htmlspecialchars($post['Text']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($mediaCount > 0): ?>
    <div class="post-img-grid count-<?= min($mediaCount, 4) ?>">
        <?php foreach (array_slice($mediaFiles, 0, 4) as $i => $file): ?>
        <img src="../uploads/media/<?= htmlspecialchars($file) ?>" class="lightbox-trigger" data-index="<?= $i ?>"
            data-images='<?= htmlspecialchars(json_encode($mediaFiles)) ?>' alt="post image">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="post-meta"><?= $date ?></div>

    <?php if ($topComment): ?>
    <div class="post-preview-comment comment-btn">
        <img src="../uploads/pfp/<?= htmlspecialchars($topComment['ProfilePicture']) ?>" alt="">
        <p><strong><?= htmlspecialchars($topComment['Nickname']) ?></strong>
            <?= htmlspecialchars(mb_strimwidth($topComment['Text'], 0, 90, '…')) ?></p>
    </div>
    <?php endif; ?>
    <?php if ($commentCount > 1): ?>
    <div class="post-comment-count-bar comment-btn">View all <?= $commentCount ?> comments</div>
    <?php endif; ?>

    <div class="post-actions">
        <div class="action-group">
            <?php if ($loggedIn): ?>
            <button class="action-btn like-btn"><i class="fa-solid fa-thumbs-up"></i> <?= $likes ?></button>
            <button class="action-btn dislike-btn"><i class="fa-solid fa-thumbs-down"></i> <?= $dislikes ?></button>
            <button class="action-btn comment-btn"><i class="fa-solid fa-comment"></i> Comment</button>
            <?php else: ?>
            <a href="login.php" class="action-btn"><i class="fa-solid fa-thumbs-up"></i> <?= $likes ?></a>
            <a href="login.php" class="action-btn"><i class="fa-solid fa-thumbs-down"></i> <?= $dislikes ?></a>
            <a href="login.php" class="action-btn"><i class="fa-solid fa-comment"></i> Comment</a>
            <?php endif; ?>
        </div>
        <div class="action-group">
            <?php if ($loggedIn): ?>
            <button class="action-btn starmark-btn"><i class="fa-solid fa-star"></i></button>
            <button class="action-btn share-btn"><i class="fa-solid fa-paper-plane"></i></button>
            <?php else: ?>
            <a href="login.php" class="action-btn"><i class="fa-solid fa-star"></i></a>
            <button class="action-btn share-btn"><i class="fa-solid fa-paper-plane"></i></button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
}