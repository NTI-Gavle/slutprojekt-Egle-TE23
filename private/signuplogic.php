<?php
session_start();
include('dbconnection.php');

if (isset($_POST["username"])) {
    $user = $_POST["username"];
    if (strlen($user) < 3) {
        $_SESSION["signupError"] = "Username must be at least 3 characters long";
        header("Location: signup.php");
        exit();
    }
    if (strlen($user) > 20) {
        $_SESSION["signupError"] = "Username cant be longer than 20 characters";
        header("Location: signup.php");
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
        header("Location: signup.php");
        exit();
    }
    if (strlen($pass) > 50) {
        $_SESSION["signupError"] = "Password cant be longer than 50 characters";
        header("Location: signup.php");
        exit();
    }
}
if (isset($_POST["passwordConfirm"])) {
    $passConfirm = $_POST["passwordConfirm"];
}

if (!isset($pass) || !isset($user) || !isset($passConfirm) || !isset($email)) {
    header("Location: signup.php");
}

$sql = "SELECT * FROM users WHERE email =?";
$stmt = $dbconn->prepare($sql);
$data = array($email);
$stmt->execute($data);
$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($res)) {
    $_SESSION["signupError"] = "Email is already in use";
    header("Location: signup.php");
    die();
}

$sql = "SELECT * FROM users WHERE username =?";
$stmt = $dbconn->prepare($sql);
// parameters in array, if empty we could skip the $data-variable
$data = array($user);
$stmt->execute($data);

$res = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($res)) {
    $_SESSION["signupError"] = "User already exists";
    header("Location: signup.php");
    die();
}
if ($pass != $passConfirm) {
    $_SESSION["signupError"] = "Passwords do not match";
    header("Location: signup.php");
    die();
}
$sql = "INSERT INTO users (email,username,password) VALUES (?,?,?)";
$stmt = $dbconn->prepare($sql);
$data = array($email, $user, password_hash($pass, PASSWORD_DEFAULT));
$stmt->execute($data);

$_SESSION["signupError"] = "User resistration succes!";

header("Location: signup.php");

?>