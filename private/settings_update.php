<?php
session_start();
require_once 'dbconnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("Invalid CSRF token.");
}

$userId = $_SESSION['user_id'];
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$description = trim($_POST['description'] ?? '');
$birthdate = $_POST['birthdate'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

$errors = [];

//username change
if (strlen($username) < 3) {
    $errors[] = "Username must be at least 3 characters.";
} elseif (strlen($username) > 20) {
    $errors[] = "Username cannot exceed 20 characters.";
} else {
    $stmt = $dbconn->prepare("SELECT id FROM users WHERE Username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetch()) $errors[] = "That username is already taken.";
}

//email change
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
} else {
    $stmt = $dbconn->prepare("SELECT id FROM users WHERE Email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) $errors[] = "That email address is already in use.";
}

//password change
$changePassword = $password !== '';
if ($changePassword) {
    if (strlen($password) < 8)  $errors[] = "New password must be at least 8 characters.";
    elseif (strlen($password) > 50) $errors[] = "New password cannot exceed 50 characters.";
    elseif ($password !== $passwordConfirm) $errors[] = "Passwords do not match.";
}

if (strlen($nickname) > 30)    $errors[] = "Nickname cannot exceed 30 characters.";
if (strlen($description) > 200) $errors[] = "Bio cannot exceed 200 characters.";

//errs
if (!empty($errors)) {
    $_SESSION['settings_errors'] = $errors;
    header("Location: ../public/settings.php?error=1");
    exit;
}

//update 
if ($changePassword) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $dbconn->prepare("UPDATE users SET Password = ? WHERE id = ?")->execute([$hash, $userId]);
}

$dbconn->prepare("UPDATE users SET Username = ?, Email = ? WHERE id = ?")
       ->execute([$username, $email, $userId]);

if ($birthdate !== '') {
    $dbconn->prepare("UPDATE userprofiles SET Nickname = ?, Description = ?, BirthDate = ? WHERE UserId = ?")
           ->execute([$nickname, $description, $birthdate, $userId]);
} else {
    $dbconn->prepare("UPDATE userprofiles SET Nickname = ?, Description = ? WHERE UserId = ?")
           ->execute([$nickname, $description, $userId]);
}

//image uploads
function uploadImage(array $file, string $folder): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array(mime_content_type($file['tmp_name']), $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], "../uploads/$folder/$name")) return null;
    return $name;
}

if (!empty($_FILES['profile_picture']['name'])) {
    $fn = uploadImage($_FILES['profile_picture'], 'pfp');
    if ($fn) $dbconn->prepare("UPDATE userprofiles SET ProfilePicture = ? WHERE UserId = ?")->execute([$fn, $userId]);
}
if (!empty($_FILES['profile_banner']['name'])) {
    $fn = uploadImage($_FILES['profile_banner'], 'banner');
    if ($fn) $dbconn->prepare("UPDATE userprofiles SET Banner = ? WHERE UserId = ?")->execute([$fn, $userId]);
}

$_SESSION['username'] = $username;

header("Location: ../public/settings.php?success=1");
exit;