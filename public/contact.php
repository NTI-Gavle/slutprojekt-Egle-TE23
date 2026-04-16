<?php
$pageTitle = "Contact";
require_once __DIR__ . '/../includes/header.php';

// Initialize variables
$name = $email = $message = '';
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (empty($message)) {
        $errors[] = "Message cannot be empty.";
    }

    if (!$errors) {

        mail("egle20161130@gmail.com", "Contact Form Message from $name", $message, "From:$email");

        $success = true;
        $name = $email = $message = '';
    }
}
?>

<div id="login-container">
<form action="contact.php" method="post" class="login-form">
    <div class="post-header">
        <h1>CONTACT ME!</h1>
    </div>  
    <div class="login-content">
            <div class="form-group">
    <label for="name">Name:</label>
    <input class="form-control" type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>">

    <label for="email">Email:</label>
    <input class="form-control" type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>">

    <label for="message">Message:</label>
    <textarea class="form-control" name="message" id="message"><?= htmlspecialchars($message) ?></textarea>

    <?php if ($success): ?>
        <p class="success-message">Thank you! Your message has been sent.</p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul class="error-messages">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <button type="submit" class="btn btn-secondary">Send</button>
    </div> 
    </div> 
</form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
