<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';

if(!isset($_SESSION["user_id"])){
     header("Location: ../public/login.php");
}

$conversationId = $_GET['conversation'] ?? null;
$messages = [];

if ($conversationId) 
{
    $sql = "SELECT * FROM messages WHERE ConversationId = ? ORDER BY TimeSent ASC";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([$conversationId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //get other users username and profile by conversation id
}
?>

<div class="contacts-container">
    <form class="search" method="POST" action="chat.php">
        <input type="text" name="" id="" placeholder="search...">
    </form>
    <div>
        <button>Chats</button>
        <button>Requests</button>
    </div>
    
<?php
    $sql = "SELECT DISTINCT ConversationId FROM messages WHERE SenderId = ? OR ReceiverId = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <?php foreach($conversations as $conv): ?>
        <a href="?conversation=<?= $conv['ConversationId'] ?>">
            Conversation <?= $conv['ConversationId'] ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="chat-container">
<?php if (!$conversationId): ?>
    <div class="no-chat">
        <p>Select a conversation to start chatting</p>
    </div>
<?php else: ?>
    <div class="post-container">
        <div class="post-header">
            <img src="<?= htmlspecialchars($profile["ProfilePicture"]) ?>" class="post-profile-pic">
            <span class="post-username"><?= htmlspecialchars($profile["Nickname"]) ?></span>
        </div>

        <div class="post-content">
            <?php foreach($messages as $msg): ?>
                <div class="message <?= $msg['SenderId'] == $_SESSION['user_id'] ? 'sent' : 'received' ?>">
                    <p><?= htmlspecialchars($msg['Text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <form action="../private/send-message.php" action="Post">
            <input type="text" value="message">
            <input type="submit" value="send">
        </form>
    </div>


<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/sitenav.php'; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';