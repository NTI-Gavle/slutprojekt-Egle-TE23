<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['message'];
    $conversationId = $_POST['conversationId'];
    $otherUserId= $_POST['otherUserId'];

    $sql = "INSERT INTO messages (ConversationId, SenderId, ReceiverId, Text, TimeSent) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([$conversationId, $_SESSION['user_id'],$otherUserId,$text]);
    
    header("Location: ?conversation=" . $conversationId);
    exit;
}
?>