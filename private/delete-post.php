<?php
/**
 * delete-post.php
 * Handles deletion of a post by its owner or admin.
 */

session_start();
require_once 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = (int)$_SESSION['user_id'];
$isAdmin = !empty($_SESSION['is_admin']);

if (!$postId) {
    header("Location: ../public/index.php");
    exit;
}

//get post
$stmt = $dbconn->prepare("SELECT UserId FROM posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

//owner or admin can delete
if (!$post || ((int)$post['UserId'] !== $userId && !$isAdmin)) {
    header("Location: ../public/index.php");
    exit;
}

//delete media
$mediaStmt = $dbconn->prepare("SELECT FileName FROM media WHERE PostId = ?");
$mediaStmt->execute([$postId]);
foreach ($mediaStmt->fetchAll(PDO::FETCH_COLUMN) as $file) {
    $path = __DIR__ . '/../uploads/media/' . $file;
    if (file_exists($path)) {
        @unlink($path);
    }
}

//delete everything relating to post
$dbconn->prepare("DELETE FROM media WHERE PostId = ?")->execute([$postId]);
$dbconn->prepare("DELETE FROM postscore WHERE PostId = ?")->execute([$postId]);
$dbconn->prepare("DELETE FROM starmarks WHERE PostId = ?")->execute([$postId]);
$dbconn->prepare("DELETE FROM comments WHERE PostId = ?")->execute([$postId]);
$dbconn->prepare("DELETE FROM posts WHERE id = ?")->execute([$postId]);

//users go to profile, admin go to admin page
if ($isAdmin) {
    header("Location: ../public/admin.php?tab=posts");
} else {
    header("Location: ../public/profile.php");
}
exit;