<nav class="site-nav">
    <div class="time">16:00</div>
    <hr class="site-nav-bar-hr">
    <div class="site-nav-bar-vr">|</div>
    <div class="site-links">
        <a class="btn btn-icon" href="chat.php">C</a>
        <a class="btn btn-icon" href="profile.php">P</a>
        <?php if(isset($_SESSION['user_id'])):?> 
        <button onclick="OpenCreatePost()" class="btn btn-icon">+</button>
        <?php else:?>
        <a href="login.php"  class="btn btn-icon">+</a>
        <?php endif;?>
        <a class="btn btn-icon" href="stars.php">S</a>
        <a class="btn btn-icon" href="settings.php">St</a>
        <a class="btn btn-icon" href="contact.php">Cn</a>
    </div>
    </ul>
</nav>