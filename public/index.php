<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<div class="feed-container">
<section class="feed-nav">
    <ul>
        <li><a href="" class="btn btn-primary">&ltfollowing&gt</a></li>
        <li><a href="" class="btn btn-primary">&ltnew&gt</a></li>
        <li><a href="" class="btn btn-primary">&lttop&gt</a></li>
        <li><a href="" class="btn btn-primary">&ltdiscover&gt</a></li>
    </ul>
  

</section>
<div id="feed">
    <h1>&ltdiscorver&gt</h1>
    <div class="post-feed">
        <div class="post-container">
            <div class="post-header">
                <span>Username</span>
            </div>
            <div class="post-content">
                <p>post text</p>
                <img src="" alt="img">
            </div>
            <div class="post-button-conatiner">
                <btn class="btn btn-icon">Like</btn>
                <btn class="btn btn-icon">Dislike</btn>
                <btn class="btn btn-icon">Comment</btn>                      
                <btn class="btn btn-icon">Star</btn>
                <btn class="btn btn-icon">Send</btn>
            </div>
        </div>
    </div>
</div>

<nav class="site-nav">
    <div class="time">16:00</div>
    <hr>
    <ul>
        <li><a class="btn btn-icon" href="chat.php">C</a></li>
        <li><a class="btn btn-icon" href="profile.php">P</a></li>
        <li><button class="btn btn-icon">+</button></li>
        <li><a class="btn btn-icon" href="stars.php">S</a></li>
        <li><a class="btn btn-icon" href="settings.php">St</a></li>   
        <li><button class="btn btn-icon">...</button></li>   
    </ul>
</nav>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';