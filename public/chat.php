<?php
//Chat page where a logged in user can chat with other users.
//user can add and delete conversations
//messages are loaded with ajax

$pageTitle = "Chat";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
//user profile
$userId = $_SESSION["user_id"];
$conversationId = $_GET['conversation'] ?? null;
$messages = [];
$otherUser = null;

$stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
$stmt->execute([$userId]);
$myProfile = $stmt->fetch(PDO::FETCH_ASSOC);

//new conversation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_chat_user_id'])) {
    $targetId = (int)$_POST['new_chat_user_id'];

    $stmt = $dbconn->prepare("SELECT id FROM conversations 
    WHERE (UserId = ? AND ContactUserId = ?) 
    OR (UserId = ? AND ContactUserId = ?) 
    LIMIT 1 ");

    $stmt->execute([$userId, $targetId, $targetId, $userId]);
    $existing = $stmt->fetch();
    if ($existing) header("Location: chat.php?conversation=" . $existing['id']);
    else {
        $stmt = $dbconn->prepare("INSERT INTO conversations (UserId, ContactUserId) VALUES (?, ?)");
        $stmt->execute([$userId, $targetId]);
        $newId = $dbconn->lastInsertId();
        header("Location: chat.php?conversation=" . $newId);
    }
    exit;
}

//delete conversation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_conversation'])) {
    $delId = (int)$_POST['delete_conversation'];

    $stmt = $dbconn->prepare("DELETE FROM conversations WHERE id = ? AND (UserId = ? OR ContactUserId = ?)");
    $stmt->execute([$delId, $userId, $userId]);
    header("Location: chat.php");
    exit;
}

//get conversation
$stmt = $dbconn->prepare("SELECT conversations.*,CASE WHEN conversations.UserId = ? THEN conversations.ContactUserId 
    ELSE conversations.UserId END AS OtherUserId
    FROM conversations WHERE conversations.UserId = ? OR conversations.ContactUserId = ?
    ORDER BY conversations.id DESC
");
$stmt->execute([$userId, $userId, $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($conversations as &$conv) 
{
    $stmt = $dbconn->prepare("SELECT u.Username, up.Nickname, up.ProfilePicture 
    FROM users u JOIN userprofiles up ON u.id = up.UserId WHERE u.id = ?");
    $stmt->execute([$conv['OtherUserId']]);
    $conv['OtherProfile'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $dbconn->prepare("SELECT Text FROM messages WHERE ConversationId = ? ORDER BY TimeSent DESC LIMIT 1");
    $stmt->execute([$conv['id']]);
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    $conv['LatestMessage'] = $latest['Text'] ?? '';
}
unset($conv);

if ($conversationId)
{
    $stmt = $dbconn->prepare("SELECT *, CASE WHEN UserId = ? THEN ContactUserId 
    ELSE UserId END AS OtherUserId FROM conversations WHERE id = ? AND (UserId = ? OR ContactUserId = ?)");
    $stmt->execute([$userId, $conversationId, $userId, $userId]);
    $convo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$convo) {
        header("Location: chat.php");
        exit;
    }
    $stmt = $dbconn->prepare("SELECT u.Username, up.Nickname, up.ProfilePicture 
    FROM users u JOIN userprofiles up ON u.id = up.UserId WHERE u.id = ?");
    $stmt->execute([$convo['OtherUserId']]);
    $otherUser = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbconn->prepare("SELECT * FROM messages WHERE ConversationId = ? ORDER BY TimeSent ASC");
    $stmt->execute([$conversationId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture
    FROM followingrelationships fr
    JOIN users u ON fr.FollowedUserId = u.id
    JOIN userprofiles up ON u.id = up.UserId
    WHERE fr.UserId = ?");
$stmt->execute([$userId]);
$following = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="js/chat.js" defer></script>
<script>document.body.dataset.userId = '<?= $userId ?>';</script>
 
<div class="chat-page-container">
 
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h2><i class="fa-solid fa-comment"></i> &ltmessages&gt</h2>
            <button class="btn btn-secondary btn-sm" onclick="document.getElementById('new-chat-modal').style.display='flex'">&ltnew&gt</button>
        </div>
 
        <?php if (empty($conversations)): ?>
            <p class="chat-empty">No conversations yet.</p>
        <?php endif; ?>
 
        <?php foreach ($conversations as $conv): ?>
        <div class="chat-list-item <?= $conversationId == $conv['id'] ? 'active' : '' ?>">
            <a href="chat.php?conversation=<?= $conv['id'] ?>" class="chat-list-link no-underline">
                <img src="../uploads/pfp/<?= htmlspecialchars($conv['OtherProfile']['ProfilePicture'] ?? 'default.png') ?>" class="post-profile-pic">
                <div class="chat-list-info">
                    <span class="post-username" style="font-size:1em"><?= htmlspecialchars($conv['OtherProfile']['Nickname'] ?? '') ?></span>
                    <span class="chat-preview"><?= htmlspecialchars(mb_strimwidth($conv['LatestMessage'], 0, 35, '...')) ?></span>
                </div>
            </a>
            <form method="POST" action="chat.php" onsubmit="return confirm('Delete this conversation?')">
                <input type="hidden" name="delete_conversation" value="<?= $conv['id'] ?>">
                <button type="submit" class="btn btn-icon chat-delete-btn" title="Delete">✕</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
 
    <div class="chat-main">
        <?php if (!$conversationId): ?>
            <div class="no-chat"><p>Select a conversation or start a new one.</p></div>
        <?php else: ?>
            <div class="chat-header post-header">
                <img src="../uploads/pfp/<?= htmlspecialchars($otherUser['ProfilePicture'] ?? 'default.png') ?>" class="post-profile-pic">
                <a href="profile.php?id=<?= $convo['OtherUserId'] ?>" class="post-username no-underline">
                    <?= htmlspecialchars($otherUser['Nickname'] ?? '') ?>
                    <small>@<?= htmlspecialchars($otherUser['Username'] ?? '') ?></small>
                </a>
            </div>
 
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages as $msg): ?>
                <div class="message-bubble <?= $msg['SenderId'] == $userId ? 'sent' : 'received' ?>" data-msg-id="<?= $msg['id'] ?>">
                    <p><?= htmlspecialchars($msg['Text']) ?></p>
                    <span class="message-time"><?= date('H:i', strtotime($msg['TimeSent'])) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                    <p style="text-align:center;">Say hello!</p>
                <?php endif; ?>
            </div>

            <form class="chat-input-form" action="../private/send-message.php" method="POST">
                <input type="hidden" name="conversation_id" value="<?= htmlspecialchars($conversationId) ?>">
                <input type="hidden" name="receiver_id" value="<?= $convo['OtherUserId'] ?>">
                <input type="text" name="text" class="form-control" placeholder="Message..." autocomplete="off" required maxlength="500">
                <button type="submit" class="btn btn-secondary">&ltsend&gt</button>
            </form>
        <?php endif; ?>
    </div>
 
    <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>
<!--new chat-->
<div id="new-chat-modal" style="display:none" class="modal-overlay">
    <div class="p-container modal-box">
        <div class="p-header">
            <span>New conversation</span>
            <button class="btn btn-icon" onclick="document.getElementById('new-chat-modal').style.display='none'">✕</button>
        </div>
        <div class="p-content">
            <input type="text" id="follow-search" class="form-control" placeholder="Search people you follow..." oninput="filterFollowing(this.value)">
            <div id="follow-list">
                <?php foreach ($following as $f): ?>
                <form method="POST" action="chat.php" class="follow-item">
                    <input type="hidden" name="new_chat_user_id" value="<?= $f['id'] ?>">
                    <img src="../uploads/pfp/<?= htmlspecialchars($f['ProfilePicture']) ?>" class="post-profile-pic">
                    <span><?= htmlspecialchars($f['Nickname']) ?> <small>@<?= htmlspecialchars($f['Username']) ?></small></span>
                    <button type="submit" class="btn btn-secondary btn-sm">&ltchat&gt</button>
                </form>
                <?php endforeach; ?>
                <?php if (empty($following)): ?>
                    <p>You're not following anyone yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(isset($_SESSION["user_id"])): ?>
<?php require_once __DIR__ . '/../includes/createpost.php'; ?>
<?php endif;?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>