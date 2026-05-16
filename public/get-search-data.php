<?php
session_start();
require_once '../private/dbconnection.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type === 'recent') {
    $recent = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $dbconn->prepare("SELECT id, SearchTerm FROM searchterms WHERE UserId = ? 
        AND Type = 'user' ORDER BY id DESC LIMIT 5");
        $stmt->execute([$_SESSION['user_id']]);
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $dbconn->prepare("SELECT SearchTerm, COUNT(*) as cnt FROM searchterms WHERE Type = 'user' 
    GROUP BY SearchTerm ORDER BY cnt DESC LIMIT 5");
    $stmt->execute();
    $popular = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['recent' => $recent, 'popular' => $popular]);
    exit;
}

if ($type === 'suggest') {
    $q = trim($_GET['q'] ?? '');
    if (!$q) { echo json_encode([]); exit; }
    //add search to recent
    if (isset($_SESSION['user_id']) && strlen($q) >= 2) {
        $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE UserId = ? AND SearchTerm = ? AND Type = 'user'");
        $stmt->execute([$_SESSION['user_id'], $q]);
        $stmt = $dbconn->prepare("INSERT INTO searchterms (UserId, SearchTerm, Type) VALUES (?, ?, 'user')");
        $stmt->execute([$_SESSION['user_id'], $q]);
    }
    $stmt = $dbconn->prepare("SELECT u.id, u.Username, up.Nickname, up.ProfilePicture FROM users u JOIN userprofiles up ON u.id = up.UserId WHERE u.Username LIKE ? OR up.Nickname LIKE ? LIMIT 4");
    $stmt->execute(["%$q%", "%$q%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $dbconn->prepare("SELECT DISTINCT SearchTerm FROM searchterms WHERE SearchTerm LIKE ? AND Type = 'user' ORDER BY id DESC LIMIT 4");
    $stmt->execute(["%$q%"]);
    $terms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['users' => $users, 'terms' => $terms]);
    exit;
}
if ($type === 'delete' && isset($_SESSION['user_id'])) {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $dbconn->prepare("DELETE FROM searchterms WHERE id = ? AND UserId = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    echo json_encode(['ok' => true]);
    exit;
}
echo json_encode([]);