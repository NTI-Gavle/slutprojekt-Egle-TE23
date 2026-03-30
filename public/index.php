<?php
$pageTitle = "Home"; // <-- set dynamic page title
require_once __DIR__ . '/../includes/header.php';
?>

<div id="create-post-popout">
    <div class="post-container create-post-container">
            <div class="post-header">
                <button onclick="CloseCreatePost()" class="btn btn-icon">X</button>
                <span class="post-username">Username</span>
            </div>
            <div class="post-content">
                <form action="../private/create-post.php">
                    <div class="form-group"></div>
                        <input type="text" name="create-post-text" id="create-post-text" class="form-control" placeholder="tell the world something!">
                </form>
            </div>
        </div>
</div>

<div class="feed-container">
<?php require_once __DIR__ . '/../includes/feednav.php'; ?>
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
  <?php require_once __DIR__ . '/../includes/sitenav.php'; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';