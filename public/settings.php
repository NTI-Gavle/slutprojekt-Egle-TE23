<?php
$pageTitle = "Settings";
require_once __DIR__ . '/../includes/header.php';
require_once('../private/dbconnection.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$tab = $_GET['tab'] ?? 'account';
$allowed = ['account','general','about'];
if (!in_array($tab, $allowed)) {
    $tab = 'account';
}


$stmt = $dbconn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
//errs
$settingsErrors = $_SESSION['settings_errors'] ?? [];
unset($_SESSION['settings_errors']);
 
//defult cookies
$animatedBgOn = !isset($_COOKIE['animated-bg']) || $_COOKIE['animated-bg'] === 'true';
$darkmodeOn = !empty($_COOKIE['darkmode']) && $_COOKIE['darkmode'] === 'false';
?>

<script defer src="js/settings.js"></script>

<div class="settings-container">
    <nav class="settings-nav settings-field">
        <h2 style="margin-bottom:16px">Settings</h2>
        <ul class="settings-list">
            <li><button onclick="location.href='settings.php?tab=account'" class="btn btn-secondary btn-pill">&ltaccount&gt</button></li>
            <li><button onclick="location.href='settings.php?tab=general'" class="btn btn-secondary btn-pill">&ltgeneral&gt</button></li>
            <li><button onclick="location.href='settings.php?tab=about'" class="btn btn-secondary btn-pill">&ltabout&gt</button></li>
            <li style="margin-top:20px">
                <a href="logout.php" class="btn btn-primary btn-pill" onclick="return confirm('Are you sure you want to log out?')">&ltlogout&gt</a>
            </li>
        </ul>
    </nav>

    <div class="settings-display settings-field">
        <!--account-->
        <div id="account" class="settings-section" style="<?= $tab === 'account' ? 'display:flex' : 'display:none' ?>">
            <h2>Account</h2>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✓ Changes saved!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Something went wrong — please try again.</div>
            <?php endif; ?>

            <form method="POST" action="../private/settings_update.php" enctype="multipart/form-data" class="settings-form">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                <!--pfp-->
                <div class="settings-image-row">
                    <div class="settings-image-group">
                        <label class="settings-label">Profile picture</label>
                        <div class="settings-image-preview-wrap">
                            <img id="pfp-preview" src="../uploads/pfp/<?= htmlspecialchars($profile['ProfilePicture'] ?? '') ?>" alt="pfp" class="settings-pfp-preview">
                            <label class="settings-image-overlay" title="Change photo" style="border-radius:100px;">
                                <i class="fa-solid fa-camera"></i>
                                <input type="file" name="profile_picture" accept="image/*" style="display:none;" onchange="previewImage(this,'pfp-preview')">
                            </label>
                        </div>
                    </div>
                    <div class="settings-image-group" style="flex:1">
                        <label class="settings-label">Banner</label>
                        <div class="settings-banner-preview-wrap">
                            <img id="banner-preview" src="../uploads/banner/<?= htmlspecialchars($profile['Banner'] ?? '') ?>" alt="banner" class="settings-banner-preview">
                            <label class="settings-image-overlay" title="Change banner" style="border-radius:10px;">
                                <i class="fa-solid fa-camera"></i>
                                <input type="file" name="profile_banner" accept="image/*" style="display:none" onchange="previewImage(this,'banner-preview')">
                            </label>
                        </div>
                    </div>
                </div>
                <!--general info-->
                <div class="settings-section-title">Profile Info</div>
                <label class="settings-label">Nickname
                    <input type="text" name="nickname" class="form-control" maxlength="30"
                        value="<?= htmlspecialchars($profile['Nickname'] ?? '') ?>">
                </label>
                <label class="settings-label">Bio
                    <textarea name="description" class="form-control" maxlength="200" rows="3"
                        style="resize:vertical"><?= htmlspecialchars($profile['Description'] ?? '') ?></textarea>
                </label>
                <label class="settings-label">Birthday
                    <input type="date" name="birthdate" class="form-control"
                        value="<?= htmlspecialchars($profile['BirthDate'] ?? '') ?>">
                </label>

                <!--username and email-->
                <div class="settings-section-title" style="margin-top:16px">Account</div>
                <label class="settings-label">Username
                    <input type="text" name="username" class="form-control" maxlength="20"
                        value="<?= htmlspecialchars($user['Username'] ?? '') ?>">
                </label>
                <label class="settings-label">Email
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($user['Email'] ?? '') ?>">
                </label>

                <!--change password-->
                <div class="settings-section-title" style="margin-top:16px">Change Password</div>
                <p style="font-size:0.85em;opacity:0.65">Leave blank to keep your current password.</p>
                <label class="settings-label">New password
                    <input type="password" name="password" id="new-pass" class="form-control" placeholder="New password" maxlength="50">
                </label>
                <label class="settings-label">Confirm new password
                    <input type="password" name="password_confirm" id="confirm-pass" class="form-control" placeholder="Confirm new password" maxlength="50">
                </label>
                <p id="pass-mismatch" style="color:red;display:none;font-size:0.85em">Passwords do not match.</p>

                <button type="submit" class="btn btn-secondary" style="margin-top:16px" onclick="return validatePasswords()">
                    Save changes
                </button>
            </form>
        </div>

        <!--general-->
        <div id="general" class="settings-section" style="<?= $tab === 'general' ? 'display:flex' : 'display:none' ?>">
            <h2>General</h2>
            <div class="settings-toggle-row">
                <div>
                    <strong>Dark mode</strong>
                    <p style="opacity:0.6;font-size:0.85em;margin:0">Switch between the day and night theme!</p>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="darkmode-toggle"
                        <?= (!empty($_COOKIE['darkmode']) && $_COOKIE['darkmode'] === 'true') ? 'checked' : '' ?>>
                </div>
            </div>
            <div class="settings-toggle-row">
                <div>
                    <strong>Animated star background</strong>
                    <p style="opacity:0.6;font-size:0.85em;margin:0">Show the background aniation.</p>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="animated-bg-toggle"
                        <?= (!empty($_COOKIE['animated-bg']) && $_COOKIE['animated-bg'] === 'true') ? 'checked' : '' ?>>
                </div>
            </div>
        </div>
        <!--about-->
      <div id="about" class="settings-section" style="<?= $tab === 'about' ? 'display:flex' : 'display:none' ?>">
            <h2>About LO-GO</h2>
            <p>I made this awesome super cool website! 
            <br>If you want to contact me there's a button bellow, press it!</p>
            <p style="opacity:0.6; font-size:0.85em">Version 1.75.1 &bull; Made by Me</p>
            <a href="contact.php" class="btn btn-secondary btn-sm" style="margin-top:10px">&ltcontact&gt</a>
        </div>

    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>