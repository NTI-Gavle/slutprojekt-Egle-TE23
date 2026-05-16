<?php
session_start();
include('dbconnection.php');

if (!$_SESSION["user_id"]) {
    header("Location: ../public/login.php");
    exit;
}

$text = trim($_POST["create-post-text"] ?? '');
if ($text === '' && empty($_FILES['media']['name'][0])) {
    header("Location: ../public/index.php");
    exit;
}
$now = date('Y-m-d H:i:s');
$sql = "INSERT INTO posts (UserId, Text, CreatedAt) VALUES (?, ?, ?)";
$stmt = $dbconn->prepare($sql);
$stmt->execute([$_SESSION["user_id"], $text, $now]);
$postId = $dbconn->lastInsertId();

//media
if (!empty($_FILES['media']['name'][0])) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $uploadDir = '../uploads/media/';
    $count = 0;
    foreach ($_FILES['media']['tmp_name'] as $i => $tmp) {
        if ($count >= 4) break;
        if ($_FILES['media']['error'][$i] !== 0) continue;
        $mime = mime_content_type($tmp);
        if (!in_array($mime, $allowed)) continue;
        if ($_FILES['media']['size'][$i] > 10 * 1024 * 1024) continue; //10mb max
        $ext = pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $ext;
        if (move_uploaded_file($tmp, $uploadDir . $fileName)) {
            $stmt = $dbconn->prepare("INSERT INTO media (PostId, FileName, MediaType) VALUES (?, ?, 'image')");
            $stmt->execute([$postId, $fileName]);
            $count++;
        }
    }
}
header("Location: ../public/index.php");
exit;