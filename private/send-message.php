<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['ajax'])) { echo json_encode(['ok'=>false]); exit; }
    header("Location: ../public/login.php");
    exit;
}

$senderId = $_SESSION['user_id'];
$conversationId = (int)($_POST['conversation_id'] ?? 0);
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$text = trim($_POST['text'] ?? '');

if ($conversationId && $receiverId && $text !== '' && mb_strlen($text) <= 500) {
    $stmt = $dbconn->prepare("SELECT id FROM conversations WHERE id = ? AND (UserId = ? OR ContactUserId = ?)");
    $stmt->execute([$conversationId, $senderId, $senderId]);
    if ($stmt->fetch()) {
        $stmt = $dbconn->prepare("INSERT INTO messages (ConversationId, SenderId, ReceiverId, Text, TimeSent) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$conversationId, $senderId, $receiverId, $text]);
    }
}

if (isset($_GET['ajax'])) {
    echo json_encode(['ok' => true]);
    exit;
}

header("Location: ../public/(chat.php?conversation=" . $conversationId);
exit;