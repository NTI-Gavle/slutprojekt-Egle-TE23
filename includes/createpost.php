<script src="../public/js/createpost.js" defer></script>

<div id="create-post-popout" style="display:none" class="modal-overlay">
    <div class="p-container create-post-container">
        <div class="p-header">
            <button type="button" onclick="CloseCreatePost()" class="btn btn-icon">✕</button>
            <span class="post-username"><?= htmlspecialchars($profile["Nickname"] ?? '') ?></span>
            <div style="display:flex;gap:6px;margin-left:auto">
                <button type="button" class="btn btn-primary btn-sm" onclick="saveDraft()">save draft</button>
                <button type="button" class="btn btn-icon" onclick="discardDraft()" title="Discard draft"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        <div class="p-content">
            <form action="../private/create-post.php" method="post" enctype="multipart/form-data">
                <div class="form-group" style="position:relative">
                    <textarea name="create-post-text" id="create-post-text" class="form-control"
                        placeholder="tell the world something!" maxlength="500" rows="3"
                        style="resize:none;padding-right:50px"></textarea>
                    <small style="opacity:0.5;float:right" id="post-char-counter">0/500</small>
                </div>
                <!--media-->
                <div id="post-media-preview" class="post-media-preview"></div>
                <!--emoji-->
                <div style="position:relative">
                    <div id="emoji-picker" style="display:none" class="emoji-picker-grid"></div>
                </div>

                <div class="post-button-container" style="align-items:center">
                    <div style="display:flex;gap:8px;align-items:center">
                        <label class="btn btn-icon" title="Add images (max 4)" style="cursor:pointer;margin:0"> <i class="fa-solid fa-image"></i>
                            <input type="file" id="post-media-input" name="media[]" accept="image/*" multiple style="display:none" onchange="limitFiles(this,4)">
                        </label>
                        <button type="button" id="emoji-btn" class="btn btn-icon" title="Emoji"><i class="fa-regular fa-face-grin"></i></button>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-secondary" value="Post">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>