<?php
function renderPostCard(array $post, array $mediaFiles = [], ?array $topComment = null, int $commentCount = 0, bool $loggedIn = false, array $starredIds = []): void {
    $pid = (int)$post['id'];
    $text = htmlspecialchars($post['Text'] ?? '');
    $nick = htmlspecialchars($post['Nickname'] ?? $post['Username'] ?? '');
    $user = htmlspecialchars($post['Username'] ?? '');
    $pfp = htmlspecialchars($post['ProfilePicture'] ?? 'default.png');
    $likes = (int)($post['Likes']    ?? 0);
    $dislikes = (int)($post['Dislikes'] ?? 0);
    $views = (int)($post['ViewCount'] ?? 0);
    $uid = (int)($post['UserId'] ?? 0);
    $isStarred = in_array($pid, $starredIds, true);

    $ts = '';
    if (!empty($post['CreatedAt'])) 
    {
        $ts = date('M j, g:i a', strtotime($post['CreatedAt']));
    }

    echo '<div class="post-card" data-post-id="' . $pid . '">';
    echo '<div class="post-card-header-wrap">';
    echo '<a href="profile.php?id='. $uid .'" class="post-header-profile no-underline" onclick="event.stopPropagation()">';
    echo '<img src="../uploads/pfp/'. $pfp .'" class="post-profile-pic" alt="">';
    echo '<div>';
    echo '<span class="post-username">' . $nick . '</span>';
    echo '<span class="post-handle">@' . $user .'</span>';
    echo '</div>';
    echo '</a>';
    echo '<a href="post.php?id=' . $pid. '" class="post-header-link" aria-label="View post"></a>';
    echo '</div>';
    if ($text !== '') {
        echo '<div class="post-body"><p>' . nl2br($text) . '</p></div>';
    }
    echo '<div class="post-meta">' . $ts . '<span class="post-views">' . number_format($views) . ' views</span></div>';
    if (!empty($mediaFiles)) 
    {
        $cnt = min(count($mediaFiles), 4);
        $imagesJson = htmlspecialchars(json_encode($mediaFiles), ENT_QUOTES);
        echo '<div class="post-img-grid count-' . $cnt . '">';
        foreach (array_slice($mediaFiles, 0, 4) as $i => $fn) {
            $fnSafe = htmlspecialchars($fn);
            echo '<img src="../uploads/media/' . $fnSafe . '" alt="media"'
               . ' class="post-media-img lightbox-trigger"'
               . ' data-images=\'' . $imagesJson . '\''
               . ' data-index="' . $i . '"'
               . ' loading="lazy">';
        }
        echo '</div>';
    }
    if ($topComment) {
        $cNick = htmlspecialchars($topComment['Nickname'] ?? '');
        $cPfp  = htmlspecialchars($topComment['ProfilePicture'] ?? 'default.png');
        $cText = htmlspecialchars(mb_strimwidth($topComment['Text'] ?? '', 0, 80, '…'));
        echo '<div class="post-preview-comment">';
        echo '<img src="../uploads/pfp/' . $cPfp . '" alt="">';
        echo '<p><strong>' . $cNick . ':</strong> ' . $cText . '</p>';
        echo '</div>';
    }
    if ($commentCount > 0) {
        echo '<div class="post-comment-count-bar">' . $commentCount . ' comment' . ($commentCount !== 1 ? 's' : '') . '</div>';
    }

    if ($loggedIn) {
        $starClass = $isStarred ? ' active' : '';
        $starIcon  = $isStarred ? 'fa-solid fa-star' : 'fa-regular fa-star';
        echo '<div class="post-actions">';
        echo '<div class="action-group">';
        echo '<button class="action-btn like-btn"><i class="fa-solid fa-thumbs-up"></i> ' . $likes . '</button>';
        echo '<button class="action-btn dislike-btn"><i class="fa-solid fa-thumbs-down"></i> ' . $dislikes . '</button>';
        echo '<button class="action-btn comment-btn"><i class="fa-regular fa-comment"></i></button>';
        echo '</div>';
        echo '<div class="action-group">';
        echo '<button class="action-btn share-btn"><i class="fa-solid fa-share-nodes"></i></button>';
        echo '<button class="action-btn starmark-btn' . $starClass . '"><i class="' . $starIcon . '"></i></button>';
        echo '</div>';
        echo '</div>';
    } 
    else {
        echo '<div class="post-actions">';
        echo '<div class="action-group">';
        echo '<span class="action-btn"><i class="fa-solid fa-thumbs-up"></i> ' . $likes . '</span>';
        echo '<span class="action-btn"><i class="fa-solid fa-thumbs-down"></i> ' . $dislikes . '</span>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

function isAdmin(): bool {
    return !empty($_SESSION['is_admin']);
}