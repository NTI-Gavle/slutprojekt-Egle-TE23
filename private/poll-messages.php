<?php
session_start();
require_once '../private/dbconnection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['messages' => []]); exit; }

$userId = $_SESSION['user_id'];
$conversationId = (int)($_GET['conversation'] ?? 0);
$afterId = (int)($_GET['after'] ?? 0);

if (!$conversationId) { echo json_encode(['messages' => []]); exit; }

//verify user
$stmt = $dbconn->prepare("SELECT id FROM conversations WHERE id = ? AND (UserId = ? OR ContactUserId = ?)");
$stmt->execute([$conversationId, $userId, $userId]);
if (!$stmt->fetch()) { echo json_encode(['messages' => []]); exit; }

$stmt = $dbconn->prepare("SELECT id, SenderId, Text, DATE_FORMAT(TimeSent, '%H:%i') AS TimeSent
    FROM messages WHERE ConversationId = ? AND id > ?
    ORDER BY id ASC LIMIT 50");
$stmt->execute([$conversationId, $afterId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$row) {
   $row['isMine'] = ((int)$row['SenderId'] === (int)$userId);
}

echo json_encode(['messages' => $rows]);