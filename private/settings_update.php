<?php
session_start();
require_once 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) { die("Invalid CSRF");}

$userId = $_SESSION['user_id'];

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$description = $_POST['description'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$password = $_POST['password'] ?? '';

if (!empty($password)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $dbconn->prepare("UPDATE users SET Password = ? WHERE id = ?");
    $stmt->execute([$passwordHash, $userId]);
}

$stmt = $dbconn->prepare("UPDATE users SET Username=?, Email=? WHERE id=?");
$stmt->execute([$username, $email,  $userId]);

if($birthdate != ''){
$stmt = $dbconn->prepare("UPDATE userprofiles SET Nickname=?, Description=?, BirthDate=? WHERE UserId=?");
$stmt->execute([$nickname, $description, $birthdate, $userId]);
}
else{
    $stmt = $dbconn->prepare("UPDATE userprofiles SET Nickname=?, Description=? WHERE UserId=?");
    $stmt->execute([$nickname, $description, $userId]);
}

function uploadImage($file, $folder) {
    if ($file['error'] !== 0) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null; 

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid() . "." . $ext;
    $path = "../uploads/$folder/" . $name;
    move_uploaded_file($file['tmp_name'], $path);
    return $name;
}
if (!empty($_FILES['profile_picture']['name'])) {
    $fileName = uploadImage($_FILES['profile_picture'], "pfp");
    if ($fileName) {
        $stmt = $dbconn->prepare("UPDATE userprofiles SET ProfilePicture=? WHERE UserId=?");
        $stmt->execute([$fileName, $userId]);
    }
}

if (!empty($_FILES['profile_banner']['name'])) {
    $fileName = uploadImage($_FILES['profile_banner'], "banner");
    if ($fileName) {
        $stmt = $dbconn->prepare("UPDATE userprofiles SET Banner=? WHERE UserId=?");
        $stmt->execute([$fileName, $userId]);
    }
}

header("Location: ../public/settings.php?success=1");
exit;