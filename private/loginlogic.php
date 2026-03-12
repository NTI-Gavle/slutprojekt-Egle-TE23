<?php
session_start();

include('dbconnection.php');

if (isset($_POST["username"])) {
    $user = $_POST["username"];
}
if (isset($_POST["password"])) {
    $pass = $_POST["password"];
}

if (!(isset($pass) && isset($user))) {
    header("Location: login.php");
}
$sql = "SELECT * FROM users WHERE username =?";
$stmt = $dbconn->prepare($sql);

$data = array($user);
$stmt->execute($data);
$res = $stmt->fetch(PDO::FETCH_ASSOC);


if (password_verify($pass, $res["password"])) {
    $_SESSION["user_id"] = $res["id"];
    $_SESSION["username"] = $res["username"];
    header("Location: main.php");
} else {
    $_SESSION["loginError"] = "Wrong username or password";
    header("Location: login.php");
}
?>