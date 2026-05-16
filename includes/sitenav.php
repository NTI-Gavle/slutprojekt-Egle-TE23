<nav class="site-nav">
    <div class="time">16:00</div>
    <hr class="site-nav-bar-hr">
    <div class="site-nav-bar-vr">|</div>
    <div class="site-links">
        <a class="btn btn-icon" href="chat.php"><i class="fa-solid fa-comment-dots"></i></a>
        <a class="btn btn-icon" href="profile.php"><i class="fa-solid fa-user"></i></a>
        <?php if(isset($_SESSION['user_id'])):?> 
        <button onclick="OpenCreatePost()" class="btn btn-icon"><i class="fa-solid fa-plus"></i></button>
        <?php else:?>
        <a href="login.php"  class="btn btn-icon"><i class="fa-solid fa-plus"></i></a>
        <?php endif;?>
        <a class="btn btn-icon" href="stars.php"><i class="fa-solid fa-star"></i></a>
        <a class="btn btn-icon" href="settings.php"><i class="fa-solid fa-gear"></i></a>
        <a class="btn btn-icon" href="contact.php"><i class="fa-solid fa-envelope"></i></a>
    </div>
    </ul>
</nav>