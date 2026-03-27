<?php
session_start();
include('dbconnection.php');

if (isset($_POST["username"])) {
    $user = $_POST["username"];
    if (strlen($user) < 3) {
        $_SESSION["signupError"] = "Username must be at least 3 characters long";
        header("Location: ../public/signup.php");
        exit();
    }
    if (strlen($user) > 20) {
        $_SESSION["signupError"] = "Username cant be longer than 20 characters";
        header("Location: ../public/signup.php");
        exit();
    }
}
if (isset($_POST["email"])) {
    $email = $_POST["email"];
}
if (isset($_POST["password"])) {
    $pass = $_POST["password"];
    if (strlen($pass) < 8) {
        $_SESSION["signupError"] = "Password must be at least 8 characters long";
        header("Location: ../public/signup.php");
        exit();
    }
    if (strlen($pass) > 50) {
        $_SESSION["signupError"] = "Password cant be longer than 50 characters";
        header("Location: ../public/signup.php");
        exit();
    }
}
if (isset($_POST["passwordConfirm"])) {
    $passConfirm = $_POST["passwordConfirm"];
}

if (!isset($pass) || !isset($user) || !isset($passConfirm) || !isset($email)) {
    header("Location: ../public/signup.php");
}

$sql = "SELECT * FROM users WHERE email =?";
$stmt = $dbconn->prepare($sql);
$data = array($email);
$stmt->execute($data);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($res)) {
    $_SESSION["signupError"] = "Email is already in use";
    header("Location: ../public/signup.php");
    die();
}

$sql = "SELECT * FROM users WHERE username =?";
$stmt = $dbconn->prepare($sql);
$data = array($user);
$stmt->execute($data);

$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($res)) {
    $_SESSION["signupError"] = "User already exists";
    header("Location: ../public/signup.php");
    die();
}
if ($pass != $passConfirm) {
    $_SESSION["signupError"] = "Passwords do not match";
    header("Location: ../public/signup.php");
    die();
}
$now =  $now = date('Y-m-d H:i:s');;
$sql = "INSERT INTO users (Email,Username,Password,UserSince) VALUES (?,?,?,?)";
$stmt = $dbconn->prepare($sql);
$data = array($email, $user, password_hash($pass, PASSWORD_DEFAULT), $now);
$stmt->execute($data);

$_SESSION["signupError"] = "User resistration succes!";

header("Location: ../public/signup.php");

?>