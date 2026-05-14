<?php
session_start();
require_once 'dbconnection.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$postId = $_POST['post_id'];
$value = (int)$_POST['value'];

$stmt = $dbconn->prepare("SELECT * FROM postscore WHERE UserId=? AND PostId=?");
$stmt->execute([$userId, $postId]);
$existing = $stmt->fetch();

if ($existing) {
    if ($existing['Value'] == $value) {
        $dbconn->prepare("DELETE FROM postscore WHERE UserId=? AND PostId=?")
               ->execute([$userId, $postId]);
    } 
    else {
        $dbconn->prepare("UPDATE postscore SET Value=? WHERE UserId=? AND PostId=?")
               ->execute([$value, $userId, $postId]);
    }
} 
else {
    $dbconn->prepare("INSERT INTO postscore (UserId, PostId, Value) VALUES (?, ?, ?)")
           ->execute([$userId, $postId, $value]);
}

$stmt = $dbconn->prepare("SELECT SUM(Value = 1) as likes, SUM(Value = -1) as dislikes FROM postscore WHERE PostId = ?");
$stmt->execute([$postId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);