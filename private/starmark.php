<?php
session_start();
require_once 'dbconnection.php';

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];

$stmt = $dbconn->prepare("SELECT * FROM starmarks WHERE UserId=? AND PostId=?");
$stmt->execute([$userId, $postId]);

if ($stmt->fetch()) {
    $stmt = $dbconn->prepare("DELETE FROM starmarks WHERE UserId=? AND PostId=?");
    $stmt->execute([$userId, $postId]);
} else {
    $stmt = $dbconn->prepare("INSERT INTO starmarks (UserId, PostId) VALUES (?, ?)");
    $stmt->execute([$userId, $postId]);
}