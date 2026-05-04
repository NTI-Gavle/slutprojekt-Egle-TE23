<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['message'];

    $sql = "INSERT INTO messages (ConversationId, SenderId, ReceiverId, Text, TimeSent) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([$conversationId, $_SESSION['user_id'],$otherUserId,$text]);
    
    header("Location: ?conversation=" . $conversationId);
    exit;
}
?>