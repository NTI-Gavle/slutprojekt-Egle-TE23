<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
$senderId = $_SESSION['user_id'];
$conversationId = (int)($_POST['conversation_id'] ?? 0);
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$text = trim($_POST['text'] ?? '');

if ($conversationId && $receiverId && $text !== '' && mb_strlen($text) <= 500)
{

    $stmt = $dbconn->prepare("SELECT Id FROM conversations WHERE Id = ? AND (UserId = ? OR ContactUserId = ?)");
    $stmt->execute([$conversationId, $senderId, $senderId]);
    if ($stmt->fetch()) {
        $stmt = $dbconn->prepare("INSERT INTO messages (ConversationId, SenderId, ReceiverId, Text, TimeSent) 
        VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$conversationId, $senderId, $receiverId, $text]);
    }
}
header("Location: ../public/chat.php?conversation=" . $conversationId);
exit;