<?php
/**
 * delete-account.php
 * Permanently deletes the currently logged-in user's account and all associated data.
 */

session_start();
require_once 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

//CSRF check
if (empty($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("Invalid CSRF token.");
}

$userId   = (int)$_SESSION['user_id'];
$password = $_POST['confirm_password'] ?? '';

$stmt = $dbconn->prepare("SELECT Password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

//check password
if (!$row || !password_verify($password, $row['Password'])) 
{
    $_SESSION['delete_account_error'] = "Incorrect password. Your account was not deleted.";
    header("Location: ../public/settings.php?tab=account");
    exit;
}

try{
    $dbconn->beginTransaction();

    //delete media
    $mediaStmt = $dbconn->prepare(
        "SELECT media.FileName FROM media
         JOIN posts ON media.PostId = posts.id
         WHERE posts.UserId = ?"
    );
    $mediaStmt->execute([$userId]);
    foreach ($mediaStmt->fetchAll(PDO::FETCH_COLUMN) as $file) {
        $path = __DIR__ . '/../uploads/media/' . $file;
        if (file_exists($path)) @unlink($path);
    }

    //delete pfp and banner
    $pfpStmt = $dbconn->prepare("SELECT ProfilePicture, Banner FROM userprofiles WHERE UserId = ?");
    $pfpStmt->execute([$userId]);
    $profileRow = $pfpStmt->fetch(PDO::FETCH_ASSOC);
    if ($profileRow) {
        $defaults = ['default.png', 'temp-pfp.png', 'temp-banner.png', 'default_banner.jpg', ''];
        foreach (['ProfilePicture' => 'pfp', 'Banner' => 'banner'] as $col => $folder) {
            $file = $profileRow[$col] ?? '';
            if ($file && !in_array($file, $defaults)) {
                $path = __DIR__ . "/../uploads/$folder/$file";
                if (file_exists($path)) @unlink($path);
            }
        }
    }

    //delete everything off db
    $queries = [
        "DELETE FROM postscore WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
        "DELETE FROM starmarks WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
        "DELETE FROM comments WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
        "DELETE FROM media WHERE PostId IN (SELECT id FROM posts WHERE UserId = ?)",
        "DELETE FROM postscore WHERE UserId = ?",
        "DELETE FROM starmarks WHERE UserId = ?",
        "DELETE FROM comments WHERE UserId = ?",
        "DELETE FROM searchterms WHERE UserId = ?",
        "DELETE FROM followingrelationships WHERE UserId = ? OR FollowedUserId = ?",
        "DELETE FROM messages WHERE SenderId = ? OR ReceiverId = ?",
        "DELETE FROM conversations WHERE UserId = ? OR ContactUserId = ?",
        "DELETE FROM posts WHERE UserId = ?",
        "DELETE FROM userprofiles WHERE UserId = ?",
        "DELETE FROM users WHERE id = ?",
    ];

    foreach ($queries as $sql) {
        $count = substr_count($sql, '?');
        $dbconn->prepare($sql)->execute(array_fill(0, $count, $userId));
    }
    $dbconn->commit();

} catch (Exception $e) {
    $dbconn->rollBack();
    $_SESSION['delete_account_error'] = "Something went wrong. Please try again.";
    header("Location: ../public/settings.php?tab=account");
    exit;
}

session_unset();
session_destroy();
header("Location: ../public/login.php");
exit;