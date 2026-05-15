<?php
include '../private/dbconnection.php';
session_start();
header('Content-Type: application/json');

$postId = (int)($_GET['post_id'] ?? 0);
if (!$postId) { echo json_encode([]); exit; }

$stmt = $dbconn->prepare(" SELECT comments.*, userprofiles.Nickname, userprofiles.ProfilePicture
    FROM comments JOIN userprofiles ON comments.UserId = userprofiles.UserId
    WHERE comments.PostId = ? ORDER BY comments.CreatedAt ASC LIMIT 100");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($comments);
