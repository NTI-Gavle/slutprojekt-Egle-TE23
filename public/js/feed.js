document.addEventListener("click", async (e) => {
    const post = e.target.closest(".post-container");
    if (!post) return;
    const postId = post.dataset.postId;

    const likeBtn = e.target.closest(".like-btn");
    const dislikeBtn = e.target.closest(".dislike-btn");
    const starBtn = e.target.closest(".starmark-btn");

    //like dislike
if (likeBtn || dislikeBtn) {
    const btn = likeBtn || dislikeBtn;
    const value = likeBtn ? 1 : -1;
    const res = await fetch("../private/score.php", {
        method: "POST",
        body: new URLSearchParams({ post_id: postId, value })
    });

    const data = await res.json();
    post.querySelector(".like-btn").innerHTML = `<i class="fa-solid fa-thumbs-up"></i> ${data.likes ?? 0}`;
    post.querySelector(".dislike-btn").innerHTML = `<i class="fa-solid fa-thumbs-down"></i> ${data.dislikes ?? 0}`;
}

    //star
if (starBtn) {
    const res = await fetch("../private/starmark.php", {
        method: "POST",
        body: new URLSearchParams({ post_id: postId })
    });
    const data = await res.json();
    const isStarred = data.starred;
    starBtn.classList.toggle("active", isStarred);
    starBtn.innerHTML = isStarred
        ? "<i class='fa-solid fa-star'></i>"
        : "<i class='fa-regular fa-star'></i>";
}

    //comment
    if (e.target.classList.contains("comment-btn") || e.target.classList.contains("post-comment-count")) {
        const popout = document.getElementById("comment-popout");
        if (!popout) return;
        document.getElementById("comment-post-id").value = postId;

        const postText = post.querySelector(".post-content p")?.textContent ?? "";
        document.getElementById("comment-post-preview").textContent = postText;

        const commentsList = document.getElementById("comments-list");
        commentsList.innerHTML = "<p style='opacity:0.6;text-align:center'>Loading...</p>";
        popout.style.display = "flex";

        const res = await fetch("../private/get-comments.php?post_id=" + postId);
        const data = await res.json();
        if (data.length === 0) {
            commentsList.innerHTML = "<p style='opacity:0.6;text-align:center'>No comments yet.</p>";
        } else {
            commentsList.innerHTML = data.map(c => `
                <div class="comment-item">
                    <img src="../uploads/pfp/${escHtml(c.ProfilePicture)}" class="post-profile-pic" style="width:32px;height:32px;flex-shrink:0">
                    <div>
                        <strong>${escHtml(c.Nickname)}</strong>
                        <p>${escHtml(c.Text)}</p>
                    </div>
                </div>
            `).join("");
        }
    }

    //share
    if (e.target.classList.contains("share-btn")) {
        const sendPopout = document.getElementById("send-popout");
        if (!sendPopout) {
            const url = window.location.origin + "/public/post.php?id=" + postId;
            prompt("Copy this link:", url);
            return;
        }
        sendPopout.dataset.postId = postId;
        sendPopout.style.display = "flex";

        document.getElementById("copy-link-btn").onclick = () => {
            const url = window.location.origin + "/public/post.php?id=" + postId;
            navigator.clipboard.writeText(url).then(() => {
                const confirm = document.getElementById("copy-confirm");
                confirm.style.display = "inline";
                setTimeout(() => confirm.style.display = "none", 2000);
            });
        };

        document.querySelectorAll(".send-to-user-btn").forEach(btn => {
            btn.onclick = async () => {
                const receiverId = btn.dataset.userId;
                const res = await fetch("../private/send-post-message.php", {
                    method: "POST",
                    body: new URLSearchParams({ post_id: postId, receiver_id: receiverId })
                });
                const data = await res.json();
                if (data.ok) { btn.textContent = "✓ Sent!"; btn.disabled = true; }
            };
        });
    }
});

//views
if ('IntersectionObserver' in window) {
    const viewedPosts = new Set();
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const postId = entry.target.dataset.postId;
                if (!viewedPosts.has(postId)) {
                    viewedPosts.add(postId);
                    fetch("../private/track-view.php", {
                        method: "POST",
                        body: new URLSearchParams({ post_id: postId })
                    });
                }
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll(".post-container[data-post-id]").forEach(p => observer.observe(p));
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}