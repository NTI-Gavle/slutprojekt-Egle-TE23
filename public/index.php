<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<div class="feed-container">
<nav class="feed-nav">
    <ul>
        <li><a href="" class="btn btn-primary">&ltfollowing&gt</a></li>
        <li><a href="" class="btn btn-primary">&ltnew&gt</a></li>
        <li><a href="" class="btn btn-primary">&lttop&gt</a></li>
        <li><a href="" class="btn btn-primary">&ltdiscover&gt</a></li>
    </ul>
</nav>

<div class="feed">
    <h1>&ltdiscorver&gt</h1>
    <div class="post-feed">
        <div class="post-container">
            <div class="post-header">
                <img src="Images\placeholder_3.png" alt="profile picture" class="post-profile-pic">
                <span class="post-username">Username</span>
            </div>
            <div class="post-content">
                <p>post text</p>
            </div>
            <div class="post-button-container">
                <div>
                    <btn class="btn btn-icon">Like</btn>
                    <btn class="btn btn-icon">Dislike</btn>
                    <btn class="btn btn-icon">Comment</btn>  
                </div>
                <div>
                    <btn class="btn btn-icon">Star</btn>
                    <btn class="btn btn-icon">Send</btn>
                </div>  
            </div>
        </div>
        <div class="post-container">
            <div class="post-header">
                <img src="Images\placeholder_1.png" alt="profile picture" class="post-profile-pic">
                <span class="post-username">Username</span>
            </div>
            <div class="post-content">
                <p>Wow i sure am enjoying this image letly. thought id share it with everyone and yeah. Well i guess theres a lot to be said about it but i relly just cant think of the words. it really is crazy how cool it is</p>
            </div>
            <div class="post-button-container">
                <div>
                    <btn class="btn btn-icon">Like</btn>
                    <btn class="btn btn-icon">Dislike</btn>
                    <btn class="btn btn-icon">Comment</btn>  
                </div>
                <div>
                    <btn class="btn btn-icon">Star</btn>
                    <btn class="btn btn-icon">Send</btn>
                </div>  
            </div>
        </div>
        <div class="post-container">
            <div class="post-header">
                <img src="Images\placeholder_3.png" alt="profile picture" class="post-profile-pic">
                <span class="post-username">Username</span>
            </div>
            <div class="post-content">
                <p>post text</p>
                <div class="post-img-container">
                    <img src="Images\placeholder_2.jpg" alt="img" class="img-fluid">
                    <img src="Images\placeholder_1.png" alt="img" class="img-fluid">
                    <img src="Images\placeholder_1.png" alt="img" class="img-fluid">
                    <img src="Images\placeholder_1.png" alt="img" class="img-fluid">
                </div>
            </div>
            <div class="post-button-container">
                <div>
                    <btn class="btn btn-icon">Like</btn>
                    <btn class="btn btn-icon">Dislike</btn>
                    <btn class="btn btn-icon">Comment</btn>  
                </div>
                <div>
                    <btn class="btn btn-icon">Star</btn>
                    <btn class="btn btn-icon">Send</btn>
                </div>  
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