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
    header("Location: ../public/login.php");
    exit;
}

$sql  = "SELECT * FROM users WHERE Username = ?";
$stmt = $dbconn->prepare($sql);
$stmt->execute([$user]);
$res  = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res || !password_verify($pass, $res["Password"])) {
    $_SESSION["loginError"] = "Wrong username or password";
    header("Location: ../public/login.php");
    exit;
}

if (!empty($res['IsBanned'])) {
    $_SESSION["loginError"] = "Your account has been banned.";
    header("Location: ../public/login.php");
    exit;
}

$_SESSION["user_id"]  = $res["id"];
$_SESSION["username"] = $res["Username"];
$_SESSION["is_admin"] = !empty($res["IsAdmin"]);

header("Location: ../public/index.php");
exit;