<?php
session_start();
include 'dbconnection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'not logged in']);
    exit;
}

$senderId   = $_SESSION['user_id'];
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$postId     = (int)($_POST['post_id'] ?? 0);

if (!$receiverId || !$postId) {
    echo json_encode(['ok' => false, 'error' => 'missing data']);
    exit;
}

//find chat, if none create
$stmt = $dbconn->prepare("SELECT Id FROM conversations 
    WHERE (UserId = ? AND ContactUserId = ?) 
    OR (UserId = ? AND ContactUserId = ?) LIMIT 1 ");
$stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
$conv = $stmt->fetch();

if ($conv) {
    $convId = $conv['Id'];
} else {
    $stmt = $dbconn->prepare("INSERT INTO conversations (UserId, ContactUserId) VALUES (?, ?)");
    $stmt->execute([$senderId, $receiverId]);
    $convId = $dbconn->lastInsertId();
}

//send message
$postUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/public/post.php?id=' . $postId;
$text = "📌 Shared a post: " . $postUrl;
$stmt = $dbconn->prepare("INSERT INTO messages (ConversationId, SenderId, ReceiverId, Text, TimeSent) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$convId, $senderId, $receiverId, $text]);

echo json_encode(['ok' => true]);
