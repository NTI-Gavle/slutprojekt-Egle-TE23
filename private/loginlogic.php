<?php
session_start();

include('dbconnection.php');

if (isset($_POST["Username"])) {
    $user = $_POST["Username"];
}
if (isset($_POST["Password"])) {
    $pass = $_POST["Password"];
}

if (!(isset($pass) && isset($user))) {
    header("Location: ../public/login.php");
}
$sql = "SELECT * FROM Users WHERE Username =?";
$stmt = $dbconn->prepare($sql);

$data = array($user);
$stmt->execute($data);
$res = $stmt->fetch(PDO::FETCH_ASSOC);


if (password_verify($pass, $res["Password"])) {
    $_SESSION["user_id"] = $res["id"];
    $_SESSION["username"] = $res["Username"];
    header("Location: ../public/index.php");
} else {
    $_SESSION["loginError"] = "Wrong username or password";
    header("Location: ../public/login.php");
}
?>