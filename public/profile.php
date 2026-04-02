<?php
$pageTitle = "Profile"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>
<div class="feed-container">
<?php require_once __DIR__ . '/../includes/feednav.php'; ?>

<div class="feed">
    <div class="profile-container post-container">
        <div class="post-header">
                <img src="Images\placeholder_3.png" alt="profile picture" class="post-profile-pic">
                <span class="post-username">Username</span>
        </div>
        <div class="profile-container">
            <img src="Images\placeholder_1.png" alt="profile-background" class="profile-background">
            <img src="Images\placeholder_3.png" alt="profile picture" class="profile-pic">
            <p>Name</p>
            <p>@username</p>
            <button class="btn secondary-btn">&ltedit&gt</button>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed et dignissim odio. Nunc ullamcorper lacus ac arcu faucibus, a maximus tellus interdum. Aliquam placerat nulla pretium nulla congue, gravida suscipit sapien ultrices. Curabitur tincidunt rutrum odio, vel ullamcorper sem lacinia quis. Fusce eget leo quis velit rhoncus rhoncus sed venenatis elit. </p>
        </div>
    </div>
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

<?php require_once __DIR__ . '/../includes/sitenav.php'; ?>

</div>

<?php
require_once __DIR__ . '/../includes/footer.php';