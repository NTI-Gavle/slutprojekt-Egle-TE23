<?php
session_start();
include 'dbconnection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
$postId = (int)($_POST['post_id'] ?? 0);
$text = trim($_POST['comment-text'] ?? '');

if ($postId && $text !== '' && mb_strlen($text) <= 300) {
    $stmt = $dbconn->prepare("INSERT INTO comments (PostId, UserId, Text, CreatedAt) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$postId, $_SESSION['user_id'], $text]);
}

//takes back to previus page
$ref = $_SERVER['HTTP_REFERER'] ?? '../public/index.php';
header("Location:".$ref);
exit;
