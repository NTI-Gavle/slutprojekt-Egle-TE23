<?php
//Administration panel, only accessible to users with IsAdmin = 1.
// Allows admins to manage users (ban, unban, promote, delete) and moderate content (delete posts and comments).

$pageTitle = "Admin";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');
require_once('../includes/functions.php');

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header("Location: index.php");
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    //delete post
    if ($action === 'delete_post') {
        $id = (int)($_POST['post_id'] ?? 0);
        if ($id) {
            $dbconn->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
            $msg = "Post #$id deleted.";
        }
    }
    //delete comment
    elseif ($action === 'delete_comment') {
        $id = (int)($_POST['comment_id'] ?? 0);
        if ($id) {
            $dbconn->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
            $msg = "Comment #$id deleted.";
        }
    }
    //add ban
    elseif ($action === 'ban_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id) {
            $dbconn->prepare("UPDATE users SET IsBanned = 1 WHERE id = ?")->execute([$id]);
            $msg = "User #$id banned.";
        }
    }
    //remove ban
    elseif ($action === 'unban_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id) {
            $dbconn->prepare("UPDATE users SET IsBanned = 0 WHERE id = ?")->execute([$id]);
            $msg = "User #$id unbanned.";
        }
    }
    //add admin
    elseif ($action === 'make_admin') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id) {
            $dbconn->prepare("UPDATE users SET IsAdmin = 1 WHERE id = ?")->execute([$id]);
            $msg = "User #$id is now an admin.";
        }
    }
    //remove admin
    elseif ($action === 'remove_admin') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id && $id !== (int)$_SESSION['user_id']) {
            $dbconn->prepare("UPDATE users SET IsAdmin = 0 WHERE id = ?")->execute([$id]);
            $msg = "Admin removed from user #$id.";
        }
    }

    //delete all user data when deleteing user
    elseif ($action === 'delete_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id && $id !== (int)$_SESSION['user_id']) {
            try {
                $dbconn->beginTransaction();

                //media
                $mediaStmt = $dbconn->prepare(
                    "SELECT media.FileName FROM media
                     JOIN posts ON media.PostId = posts.id
                     WHERE posts.UserId = ?"
                );
                $mediaStmt->execute([$id]);
                foreach ($mediaStmt->fetchAll(PDO::FETCH_COLUMN) as $file) {
                    $path = __DIR__ . '/../uploads/media/' . $file;
                    if (file_exists($path)) @unlink($path);
                }

                //pfp and banner
                $pfpStmt = $dbconn->prepare("SELECT ProfilePicture, Banner FROM userprofiles WHERE UserId = ?");
                $pfpStmt->execute([$id]);
                $profileRow = $pfpStmt->fetch(PDO::FETCH_ASSOC);
                if ($profileRow) {
                    foreach (['ProfilePicture' => 'pfp', 'Banner' => 'banner'] as $col => $folder) {
                        $file = $profileRow[$col] ?? '';
                        if ($file && !in_array($file, ['default.png', 'temp-pfp.png', 'temp-banner.png', 'default_banner.jpg'])) {
                            $path = __DIR__ . "/../uploads/$folder/$file";
                            if (file_exists($path)) @unlink($path);
                        }
                    }
                }

                //all db rows
                $tables = [
                    "DELETE FROM postscore WHERE UserId = ?",
                    "DELETE FROM starmarks WHERE UserId = ?",
                    "DELETE FROM searchterms WHERE UserId = ?",
                    "DELETE FROM followingrelationships WHERE UserId = ? OR FollowedUserId = ?",
                    "DELETE FROM comments WHERE UserId = ?",
                    "DELETE FROM media WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
                    "DELETE FROM postscore WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
                    "DELETE FROM starmarks WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
                    "DELETE FROM comments WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
                    "DELETE FROM posts WHERE UserId = ?",
                    "DELETE FROM messages WHERE SenderId = ? OR ReceiverId = ?",
                    "DELETE FROM conversations WHERE UserId = ? OR ContactUserId = ?",
                    "DELETE FROM userprofiles WHERE UserId = ?",
                    "DELETE FROM users WHERE id = ?",
                ];
                foreach ($tables as $sql) {
                    $paramCount = substr_count($sql, '?');
                    $params = array_fill(0, $paramCount, $id);
                    $dbconn->prepare($sql)->execute($params);
                }

                $dbconn->commit();
                $msg = "User #$id and all their data have been permanently deleted.";

            } catch (Exception $e) {
                $dbconn->rollBack();
                $msg = "Error deleting user: " . htmlspecialchars($e->getMessage());
            }
        } elseif ($id === (int)$_SESSION['user_id']) {
            $msg = "You cannot delete your own account from the admin panel.";
        }
    }
}

//get data
$tab = $_GET['tab'] ?? 'users';
$users = [];
$posts = [];
$comments = [];

if ($tab === 'users') {
    $search = trim($_GET['s'] ?? '');
    if ($search !== '') {
        $stmt = $dbconn->prepare(
            "SELECT u.*, up.Nickname, up.ProfilePicture
             FROM users u LEFT JOIN userprofiles up ON u.id = up.UserId
             WHERE u.Username LIKE ? OR up.Nickname LIKE ?
             ORDER BY u.id DESC LIMIT 100");
        $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    } else {
        $stmt = $dbconn->query(
            "SELECT u.*, up.Nickname, up.ProfilePicture
             FROM users u LEFT JOIN userprofiles up ON u.id = up.UserId
             ORDER BY u.id DESC LIMIT 100"
        );
    }
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($tab === 'posts') {
    $search = trim($_GET['s'] ?? '');
    if ($search !== '') {
        $stmt = $dbconn->prepare(
            "SELECT p.*, u.Username, up.Nickname, up.ProfilePicture
             FROM posts p JOIN users u ON p.UserId = u.id
             JOIN userprofiles up ON p.UserId = up.UserId
             WHERE p.Text LIKE ?  ORDER BY p.id DESC LIMIT 100");
        $stmt->execute(['%' . $search . '%']);
    } 
    else {
        $stmt = $dbconn->query(
            "SELECT p.*, u.Username, up.Nickname, up.ProfilePicture
             FROM posts p JOIN users u ON p.UserId = u.id
             JOIN userprofiles up ON p.UserId = up.UserId
             ORDER BY p.id DESC LIMIT 100");
    }
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($tab === 'comments') {
    $search = trim($_GET['s'] ?? '');
    if ($search !== '') {
        $stmt = $dbconn->prepare(
            "SELECT c.*, u.Username, up.Nickname
             FROM comments c JOIN users u ON c.UserId = u.id
             JOIN userprofiles up ON c.UserId = up.UserId
             WHERE c.Text LIKE ?  ORDER BY c.id DESC LIMIT 100");
        $stmt->execute(['%' . $search . '%']);
    } 
    else {
        $stmt = $dbconn->query(
            "SELECT c.*, u.Username, up.Nickname
             FROM comments c JOIN users u ON c.UserId = u.id
             JOIN userprofiles up ON c.UserId = up.UserId
             ORDER BY c.id DESC LIMIT 100");
    }
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div style="max-width:1100px;margin:20px auto;padding:0 12px">
    <div class="p-container" style="margin-bottom:16px">
        <div class="p-header" style="justify-content:space-between;align-items:center">
            <h1 style="margin:0">&ltadmin panel&gt</h1>
            <span style="opacity:0.7;font-size:0.85em">Logged in as
                <?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
    </div>

    <?php if ($msg): ?>
    <div class="alert"
        style="background:var(--accent-color);color:#000;border-radius:12px;padding:10px 16px;margin-bottom:14px">
        <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!--tabs-->
    <div style="display:flex;gap:8px;margin-bottom:16px">
        <?php foreach (['users','posts','comments'] as $t): ?>
        <a href="admin.php?tab=<?= $t ?>"
            class="btn <?= $tab === $t ? 'btn-secondary' : 'btn-primary' ?> btn-sm btn-pill">
            &lt<?= $t ?>&gt
        </a>
        <?php endforeach; ?>
    </div>

    <!--users tab-->
    <?php if ($tab === 'users'): ?>
    <form method="GET" action="admin.php" style="display:flex;gap:8px;margin-bottom:12px">
        <input type="hidden" name="tab" value="users">
        <input type="text" name="s" class="form-control" placeholder="Search username…"
            value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" style="max-width:320px">
        <button class="btn btn-secondary btn-sm btn-pill">&ltsearch&gt</button>
        <?php if (!empty($_GET['s'])): ?>
        <a href="admin.php?tab=users" class="btn btn-primary btn-sm btn-pill">clear</a>
        <?php endif; ?>
    </form>

    <div class="p-container" style="overflow:hidden">
        <div class="p-header" style="border-radius:0px"><span>Users (<?= count($users) ?>)</span></div>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nickname</th>
                        <th>Email</th>
                        <th>Since</th>
                        <th>Admin</th>
                        <th>Banned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="<?= $u['IsBanned'] ? 'admin-row-banned' : '' ?>">
                        <td><?= $u['id'] ?></td>
                        <td>
                            <a href="profile.php?id=<?= $u['id'] ?>" class="link-p">
                                <img src="../uploads/pfp/<?= htmlspecialchars($u['ProfilePicture'] ?? 'default.png') ?>"
                                    style="width:28px;height:28px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:6px">
                                <?= htmlspecialchars($u['Username']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($u['Nickname'] ?? '') ?></td>
                        <td style="font-size:0.82em;opacity:0.7"><?= htmlspecialchars($u['Email']) ?></td>
                        <td style="font-size:0.82em;opacity:0.7"><?= date('Y-m-d', strtotime($u['UserSince'])) ?></td>
                        <td><?= $u['IsAdmin'] ? '<span style="color:var(--accent-color)"><i class="fa-solid fa-check"></i></span>' : '–' ?>
                        </td>
                        <td><?= $u['IsBanned'] ? '<span style="color:red"><i class="fa-solid fa-check"></i></span>' : '–' ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;flex-wrap:wrap">
                                <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>

                                <!--ban unban-->
                                <?php if (empty($u['IsBanned'])): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="ban_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-secondary">Ban</button>
                                </form>
                                <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="unban_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-secondary">Unban</button>
                                </form>
                                <?php endif; ?>

                                <!--add remove admin-->
                                <?php if (empty($u['IsAdmin'])): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="make_admin">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-primary btn-pill">+ Admin</button>
                                </form>
                                <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove_admin">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-pill" style="border:1px solid var(--border-color)">-
                                        Admin</button>
                                </form>
                                <?php endif; ?>

                                <!--delete user-->
                                <form method="POST"
                                    onsubmit="return confirm('Permanently delete this user and ALL their data? This cannot be undone.')">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm"
                                        style="background:#e74c3c;color:#fff;border-radius:20px;border:none;padding:4px 10px;cursor:pointer">
                                        Delete
                                    </button>
                                </form>

                                <?php else: ?>
                                <span>(you)</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!--posts tab-->
    <?php elseif ($tab === 'posts'): ?>
    <form method="GET" action="admin.php" style="display:flex;gap:8px;margin-bottom:12px">
        <input type="hidden" name="tab" value="posts">
        <input type="text" name="s" class="form-control" placeholder="Search post text…"
            value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" style="max-width:320px">
        <button class="btn btn-secondary btn-sm btn-pill">&ltsearch&gt</button>
        <?php if (!empty($_GET['s'])): ?>
        <a href="admin.php?tab=posts" class="btn btn-primary btn-sm btn-pill">clear</a>
        <?php endif; ?>
    </form>

    <div class="p-container" style="overflow:hidden">
        <div class="p-header" style="border-radius:0px"><span>Posts (<?= count($posts) ?>)</span></div>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Author</th>
                        <th>Text</th>
                        <th>Created</th>
                        <th>Views</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $p): ?>
                    <tr>
                        <td><a href="post.php?id=<?= $p['id'] ?>" class="link-p">#<?= $p['id'] ?></a></td>
                        <td>
                            <a href="profile.php?id=<?= $p['UserId'] ?>" class="link-p">
                                <?= htmlspecialchars($p['Nickname'] ?? $p['Username']) ?>
                            </a>
                        </td>
                        <td style="max-width:340px;word-break:break-word">
                            <?= htmlspecialchars(mb_strimwidth($p['Text'], 0, 120, '…')) ?>
                        </td>
                        <td style="font-size:0.82em;opacity:0.7;white-space:nowrap"><?= $p['CreatedAt'] ?></td>
                        <td><?= $p['ViewCount'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this post?')">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="tab" value="posts">
                                <button class="btn btn-sm"
                                    style="background:#e74c3c;color:#fff;border-radius:20px;border:none;padding:4px 10px;cursor:pointer">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!--comments tab-->
    <?php elseif ($tab === 'comments'): ?>
    <form method="GET" action="admin.php" style="display:flex;gap:8px;margin-bottom:12px">
        <input type="hidden" name="tab" value="comments">
        <input type="text" name="s" class="form-control" placeholder="Search comment text…"
            value="<?= htmlspecialchars($_GET['s'] ?? '') ?>" style="max-width:320px">
        <button class="btn btn-secondary btn-sm btn-pill">&ltsearch&gt</button>
        <?php if (!empty($_GET['s'])): ?>
        <a href="admin.php?tab=comments" class="btn btn-primary btn-sm btn-pill">clear</a>
        <?php endif; ?>
    </form>

    <div class="p-container" style="overflow:hidden">
        <div class="p-header" style="border-radius:0px"><span>Comments (<?= count($comments) ?>)</span></div>
        <div style="overflow-x:auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Author</th>
                        <th>Post</th>
                        <th>Text</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><a href="profile.php?id=<?= $c['UserId'] ?>" class="link-p">
                                <?= htmlspecialchars($c['Nickname'] ?? $c['Username']) ?>
                            </a></td>
                        <td><a href="post.php?id=<?= $c['PostId'] ?>" class="link-p">#<?= $c['PostId'] ?></a></td>
                        <td style="max-width:320px;word-break:break-word">
                            <?= htmlspecialchars(mb_strimwidth($c['Text'], 0, 120, '…')) ?>
                        </td>
                        <td style="font-size:0.82em;opacity:0.7;white-space:nowrap"><?= $c['CreatedAt'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this comment?')">
                                <input type="hidden" name="action" value="delete_comment">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="tab" value="comments">
                                <button class="btn btn-sm"
                                    style="background:#e74c3c;color:#fff;border-radius:20px;border:none;padding:4px 10px;cursor:pointer">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>