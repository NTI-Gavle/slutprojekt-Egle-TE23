<?php
include '../private/dbconnection.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'LO-GO' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/app.js" defer></script>
    <script src="js/stars.js" defer></script>
    <script src="js/header.js" defer></script>
</head>

<body>
    <header class="site-header" id="header">
        <div class="header-container">
            <h1 class="site-title">
                <a href="index.php">LO-GO</a>
            </h1>
            <!--search bar-->
            <div id="search-wrapper">
                <div id="search-bar">
                    <i class="fa-solid fa-magnifying-glass" style="opacity:0.7"></i>
                    <input type="text" id="search-field" placeholder="search..." autocomplete="off"
                           oninput="handleSearchInput(this.value)"
                           onfocus="openSearchDropdown()"
                           onkeydown="if(event.key==='Enter'){doSearch(this.value)}">
                </div>
                <div id="search-dropdown" style="display:none">
                    <div id="search-dropdown-inner"></div>
                </div>
            </div>

            <!--login-->
            <?php if (isset($_SESSION['user_id'])): ?>
            <?php
                $sql = "SELECT * FROM userprofiles WHERE UserId = ?";
                $stmt = $dbconn->prepare($sql);
                $stmt->execute([$_SESSION["user_id"]]);
                $headerProfile = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <a href="profile.php?id=<?= $_SESSION['user_id'] ?>">
                <img class="post-profile-pic" src="../uploads/pfp/<?= htmlspecialchars($headerProfile['ProfilePicture'] ?? '') ?>" alt="your profile picture">
            </a>
            <?php else: ?>
            <a href="login.php" class="btn btn-secondary btn-sm">&ltlogin&gt</a>
            <?php endif; ?>
            <!--for canvas color-->
            <div id="canvasColorsSource"></div>
        </div>
    </header>
    <canvas id="starfield"></canvas>
    <main class="main-content">
