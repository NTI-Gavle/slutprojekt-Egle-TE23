<?php if (isset($_SESSION["user_id"])) {
    $stmt = $dbconn->prepare("SELECT * FROM userprofiles WHERE UserId = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);}
?>
    <div id="create-post-popout" style="display:none">
    <div class="p-container create-post-container">
        <div class="p-header">
            <div> 
                <button onclick="CloseCreatePost()" class="btn btn-icon">✕</button>
            <span class="post-username"><?= htmlspecialchars($profile["Nickname"]) ?></span>
            </div>
        </div>
        <div class="p-content">
            <form action="../private/create-post.php" method="post">
                <div class="form-group">
                    <textarea maxlength="500" name="create-post-text" id="create-post-text"
                        class="form-control" placeholder="tell the world something!"></textarea>
                </div>
                <div class="post-button-container">
                    <div>
                        <button type="button" class="btn btn-icon">Image</button>
                        <button type="button" class="btn btn-icon">Emoji</button>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-secondary" value="Post">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>