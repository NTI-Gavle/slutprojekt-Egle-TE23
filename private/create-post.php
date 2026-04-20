<?php
session_start();
include('dbconnection.php');

if(!$_SESSION["user_id"]){
    header("Location: ../public/login.php");
}

if (isset($_POST["create-post-text"])) {
    $text = $_POST["create-post-text"];
}
else{
    header("Location: ../public/index.php");
}

$now =  $now = date('Y-m-d H:i:s');;

$sql = "INSERT INTO posts (UserId,Text,CreatedAt) VALUES (?,?,?)";
$stmt = $dbconn->prepare($sql);
$data = array($_SESSION["user_id"], $text, $now);
$stmt->execute($data);

header("Location: ../public/index.php");