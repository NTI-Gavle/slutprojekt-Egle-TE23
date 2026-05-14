<?php
include '../private/dbconnection.php';

if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<!--place dark mode class here to scrollbar also has dark mode-->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'My Home Project' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/app.js" defer></script>
    <script src="js/stars.js" defer></script>
</head>

<body>
    <header class="site-header" id="header">
        <div class="header-container">
            <h1 class="site-title">
                <a href="index.php">LO-GO</a>
            </h1>
            <div id="search-bar">
                <form action="search.php" method="post">
                    <input type="text" name="search" id="search-field" placeholder="search...">
                </form>
                <button class="btn btn-icon">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <?php if(isset($_SESSION['user_id'])):
            {    
                $sql = "SELECT * FROM userprofiles WHERE UserId =?";
                $stmt = $dbconn->prepare($sql);
                $data = array($_SESSION["user_id"]);
                $stmt->execute($data);
                $profile = $stmt->fetch(PDO::FETCH_ASSOC);
                echo ('<a href="profile.php"><img class="post-profile-pic" src="../uploads/pfp/'.htmlspecialchars($profile["ProfilePicture"]).' " alt="your profile picture"></a>');
            }
            ?>
            <?php else: ?>
            <a href="login.php"><img src="../public/Images/placeholder_1.png" class="post-profile-pic" alt="login image">login</a>
            <?php endif; ?>
            <div id="canvasColorsSource"></div>
        </div>
    </header>
    <canvas id="starfield"></canvas>
    <main class="main-content">