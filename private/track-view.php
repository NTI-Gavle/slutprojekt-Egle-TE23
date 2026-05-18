<?php
session_start();
include 'dbconnection.php';

$postId = (int)($_POST['post_id'] ?? 0);
if ($postId) {
    $stmt = $dbconn->prepare("UPDATE posts SET ViewCount = ViewCount + 1 WHERE Id = ?");
    $stmt->execute([$postId]);
}
http_response_code(204);//succes
