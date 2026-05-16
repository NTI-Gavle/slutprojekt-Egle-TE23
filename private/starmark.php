<?php
session_start();
require_once 'dbconnection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$postId = (int)$_POST['post_id'];

if (!$postId) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post']);
    exit;
}

$stmt = $dbconn->prepare("SELECT id FROM starmarks WHERE UserId = ? AND PostId = ?");
$stmt->execute([$userId, $postId]);
$existing = $stmt->fetch();

if ($existing) {
    $dbconn->prepare("DELETE FROM starmarks WHERE UserId = ? AND PostId = ?")
           ->execute([$userId, $postId]);
    echo json_encode(['ok' => true, 'starred' => false]);
} else {
    $dbconn->prepare("INSERT INTO starmarks (UserId, PostId) VALUES (?, ?)")
           ->execute([$userId, $postId]);
    echo json_encode(['ok' => true, 'starred' => true]);
}