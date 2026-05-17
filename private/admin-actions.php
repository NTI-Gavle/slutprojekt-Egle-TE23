<?php
session_start();
require_once 'dbconnection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$stmt = $dbconn->prepare("SELECT IsAdmin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row  = $stmt->fetch();

if (empty($row['IsAdmin'])) {
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$action = $_POST['action'] ?? '';
if ($action === 'delete_post') {
    $id = (int)($_POST['post_id'] ?? 0);
    if ($id) {
        $dbconn->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Missing id']);
    }
    exit;
}
if ($action === 'delete_comment') {
    $id = (int)($_POST['comment_id'] ?? 0);
    if ($id) {
        $dbconn->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Missing id']);
    }
    exit;
}
echo json_encode(['ok' => false, 'error' => 'Unknown action']);