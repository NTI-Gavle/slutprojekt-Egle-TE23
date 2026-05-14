<?php
$pageTitle = "Settings";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../public/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

// CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Fetch user
$stmt = $dbconn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch profile
$stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<script defer src="js/settings.js"></script>

<div class="settings-container">

    <nav class="settings-nav settings-field">
        <h1>Settings</h1>
        <ul class="settings-list">
            <li><button onclick="ShowSettings('account')" class="btn btn-secondary btn-pill">&ltaccount&gt</button></li>
            <li><button onclick="ShowSettings('general')" class="btn btn-secondary btn-pill">&ltgeneral&gt</button></li>
            <li><button onclick="ShowSettings('about')" class="btn btn-secondary btn-pill">&ltabout&gt</button></li>
            <li><a href="logout.php" class="btn btn-secondary btn-pill">&ltlogout&gt</a></li>
        </ul>
    </nav>

    <div class="settings-display settings-field">
        <div id="account" class="settings-section" style="display:none;">
            <h1>Account</h1>

            <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Saved!</div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">Something went wrong</div>
            <?php endif; ?>

            <form method="POST" action="../private/settings_update.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <label>Profile picture
                    <input type="file" name="profile_picture" class="form-control">
                </label>
                <label>Profile banner
                    <input type="file" name="profile_banner" class="form-control">
                </label>
                <label>Username
                    <input type="text" name="username" class="form-control"
                        value="<?= htmlspecialchars($user['Username'] ?? '') ?>">
                </label>
                <label>Nickname
                    <input type="text" name="nickname" class="form-control"
                        value="<?= htmlspecialchars($profile['Nickname'] ?? '') ?>">
                </label>
                <label>Description
                    <textarea name="description"
                        class="form-control"><?= htmlspecialchars($profile['Description'] ?? '') ?></textarea>
                </label>
                <label>Email
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($user['Email'] ?? '') ?>">
                </label>
                <label>Password
                    <input type="password" name="password" class="form-control" placeholder="leave empty to keep">
                </label>
                <label>Birthday
                    <input type="date" name="birthdate" class="form-control"
                        value="<?= htmlspecialchars($profile['BirthDate'] ?? '') ?>">
                </label>
                <button type="submit" class="btn btn-primary mt-2">Save changes</button>
            </form>
        </div>

        <div id="general" class="settings-section">
            <h1>General</h1>
            <label class="form-label">Darkmode
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkmode-toggle"
                        <?= (!empty($_COOKIE['darkmode']) && $_COOKIE['darkmode'] === 'true') ? 'checked' : '' ?>>
                </div>
            </label>
            <label class="form-label">Clouds
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="cloud-toggle"
                        <?= (!empty($_COOKIE['clouds']) && $_COOKIE['clouds'] === 'true') ? 'checked' : '' ?>>
                </div>
            </label>
            <label class="form-label">Animated background
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="animated-bg-toggle"
                        <?= (!empty($_COOKIE['animated-bg']) && $_COOKIE['animated-bg'] === 'true') ? 'checked' : '' ?>>
                </div>
            </label>
        </div>


        <div id="about" class="settings-section" style="display:none;">
            <h1>About</h1>
            <p>My name is God, I made this.</p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>