<?php
//contact page for users to send emails with complaints or requests

$pageTitle = "Contact";
require_once __DIR__ . '/../includes/header.php';
include '../private/sendmail.php'; 

// Initialize variables
$name = $email = $message = '';
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name))                                          
        $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) 
        $errors[] = "A valid email is required.";
    if (empty($message))                                       
        $errors[] = "Message cannot be empty.";
    if (strlen($message) > 2000)                               
        $errors[] = "Message is too long (max 2000 characters).";

    if (!$errors) {
        $body = "
            <h2>Contact Form Message</h2>
            <p><strong>From:</strong> " . htmlspecialchars($name) . " &lt;" . htmlspecialchars($email) . "&gt;</p>
            <hr>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        $result = sendMail( 'egle20161130@gmail.com', "LO-GO Contact: " . $name,  $body, strip_tags($message));
        
        if ($result['success']) {
            $success = true;
            $name = $email = $message = '';
        } 
        else {
            $errors[] = "Failed to send message. Please try again later.";
        }
    }
}
?>

<div class="post-container m-5">

    <form action="contact.php" method="post">
        <div class="p-header">
            <h1>CONTACT ME!</h1>
        </div>
        <div class="p-content">
            <?php if ($success): ?>
            <div class="alert alert-success">Message sent! I might get back to you soon.</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div style="background:rgba(255,0,0,0.1);border-radius:10px;padding:10px">
                <?php foreach ($errors as $err): ?>
                <p style="color:red; margin:4px 0px">✕ <?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="contact.php" method="post" class="p-form">
                <div class="form-group">
                    <label class="settings-label">Name</label>
                    <input class="form-control" type="text" name="name" placeholder="Your name"
                        value="<?= htmlspecialchars($name) ?>" maxlength="100">

                </div>
                <div class="form-group">
                    <label class="settings-label">Email</label>
                        <input class="form-control" type="email" name="email" placeholder="your@email.com"
                            value="<?= htmlspecialchars($email) ?>">
                    
                </div>
                <div class="form-group">
                    <label class="settings-label">Message  </label>
                        <textarea class="form-control" name="message" placeholder="Write your message here..." rows="5"
                            maxlength="2000" style="resize:vertical"><?= htmlspecialchars($message) ?></textarea>
                  
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-secondary">&ltsend message&gt</button>
                </div>

        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';