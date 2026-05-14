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

<div class="p-container m-5">
<form action="contact.php" method="post">
    <div class="p-header">
        <h1>CONTACT ME!</h1>
    </div>  
    <div class="p-content">
        <div class="form-group">
            <label for="name">Name</label>
            <input class="form-control" type="text" name="name" id="name" placeholder="Name" value="<?= htmlspecialchars($name) ?>">
        </div> 
         <div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" type="email" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea class="form-control" name="message" placeholder="I want to report an issiue..." id="message"><?= htmlspecialchars($message) ?></textarea>

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
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-secondary">Send</button>
        </div>

    </div> 
</form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
