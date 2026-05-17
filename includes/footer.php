<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> THE COOLEST WEBSITE! All rights reserved.</p>
    <p>
        <a href="contact.php" class="link-p">Contact me!</a> |
        <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="link-p">Login</a>
        <?php else:?>
        <a href="logout.php" class="link-p" onclick="return confirm('Are you sure you want to log out?')">Logout</a>
        <?php endif;?>
        | <a href="GDPR.php" class="link-p">Privacy</a> 
    </p>
</footer>
</body>
</html>