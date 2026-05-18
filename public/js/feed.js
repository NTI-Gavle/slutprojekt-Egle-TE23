//for post actions, manages ajax för like dislike and star

document.addEventListener("click", async (e) => {
    const post = e.target.closest(".post-card, .post-container");
    if (!post) return;
    const postId = post.dataset.postId;
    if (!postId) return;

    const likeBtn    = e.target.closest(".like-btn");
    const dislikeBtn = e.target.closest(".dislike-btn");
    const starBtn    = e.target.closest(".starmark-btn");
    const commentBtn = e.target.closest(".comment-btn, .post-comment-count-bar, .post-preview-comment, .post-comment-count");
    const shareBtn   = e.target.closest(".share-btn");

    //like dislike
    if (likeBtn || dislikeBtn) {
        const value = likeBtn ? 1 : -1;
        const res = await fetch("../private/score.php", {
            method: "POST",
            body: new URLSearchParams({ post_id: postId, value })
        });
        const data = await res.json();
        const lb = post.querySelector(".like-btn");
        const db = post.querySelector(".dislike-btn");
        if (lb) lb.innerHTML = `<i class="fa-solid fa-thumbs-up"></i> ${data.likes ?? 0}`;
        if (db) db.innerHTML = `<i class="fa-solid fa-thumbs-down"></i> ${data.dislikes ?? 0}`;
        return;
    }

    //star
    if (starBtn) {
        const res = await fetch("../private/starmark.php", {
            method: "POST",
            body: new URLSearchParams({ post_id: postId })
        });
        const data = await res.json();
        starBtn.classList.toggle("active", data.starred);
        starBtn.innerHTML = data.starred
            ? "<i class='fa-solid fa-star'></i>"
            : "<i class='fa-regular fa-star'></i>";
        return;
    }

    //comment
    if (commentBtn) {
        const popout = document.getElementById("comment-popout");
        if (!popout) return;
        document.getElementById("comment-post-id").value = postId;
        const postText = post.querySelector(".post-body p, .post-content p")?.textContent ?? "";
        document.getElementById("comment-post-preview").textContent = postText;

        const commentsList = document.getElementById("comments-list");
        commentsList.innerHTML = "<p style='opacity:0.6;text-align:center;padding:10px'>Loading…</p>";
        popout.style.display = "flex";

        const res = await fetch("../private/get-comments.php?post_id=" + postId);
        const data = await res.json();
        if (!data.length) {
            commentsList.innerHTML = "<p style='opacity:0.6;text-align:center;padding:10px'>No comments yet.</p>";
        } 
        else {
            commentsList.innerHTML = data.map((c, i) => `
                <div class="comment-thread-item">
                    <div class="comment-thread-avatar">
                        <img src="../uploads/pfp/${escHtml(c.ProfilePicture)}" alt="">
                        ${i < data.length - 1 ? '<div class="comment-thread-line"></div>' : ''}
                    </div>
                    <div class="comment-thread-body">
                        <div class="comment-thread-meta">
                            <strong>${escHtml(c.Nickname)}</strong>
                            <small style="opacity:0.5;font-size:0.78em">${formatTime(c.CreatedAt)}</small>
                        </div>
                        <p class="comment-thread-text">${escHtml(c.Text)}</p>
                    </div>
                </div>
            `).join('');
        }
        return;
    }

    //share
    if (shareBtn) {
        const sendPopout = document.getElementById("send-popout");
        if (!sendPopout) return;
        sendPopout.dataset.postId = postId;
        sendPopout.style.display = "flex";
        bindSendPopout(postId);
        return;
    }
});
 
function bindSendPopout(postId) {
    const copyBtn = document.getElementById("copy-link-btn");
    if (copyBtn) {
        const fresh = copyBtn.cloneNode(true);
        copyBtn.parentNode.replaceChild(fresh, copyBtn);
        fresh.addEventListener("click", () => {
            const url = window.location.origin + "/SLUTPROJEKT%20WEB/slutprojekt-Egle-TE23/public/post.php?id=" + postId;
            if (navigator.clipboard) 
            {
                navigator.clipboard.writeText(url).then(() => showCopyConfirm());
            } 
            else {
                const ta = document.createElement("textarea");
                ta.value = url;
                ta.style.position = "fixed";
                ta.style.opacity = "0";
                document.body.appendChild(ta);
                ta.select();
                document.execCommand("copy");
                document.body.removeChild(ta);
                showCopyConfirm();
            }
        });
    }
 
    document.querySelectorAll(".send-to-user-btn").forEach(btn => {
        const fresh = btn.cloneNode(true);
        btn.parentNode.replaceChild(fresh, btn);
        fresh.addEventListener("click", async () => {
            const receiverId = fresh.dataset.userId;
            fresh.textContent = "Sending...";
            fresh.disabled = true;
            try {
                const res = await fetch("../private/send-post-message.php", {
                    method: "POST",
                    body: new URLSearchParams({ post_id: postId, receiver_id: receiverId })
                });
                const data = await res.json();
                fresh.textContent = data.ok ? "< Sent! >" : "< Error >";
                if (!data.ok) fresh.disabled = false;
            } catch {
                fresh.textContent = "< Error >";
                fresh.disabled = false;
            }
        });
    });
}
 
function showCopyConfirm() {
    const confirm = document.getElementById("copy-confirm");
    if (!confirm) return;
    confirm.style.display = "inline";
    setTimeout(() => confirm.style.display = "none", 2000);
}
 

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
    document.querySelectorAll(".post-card[data-post-id], .post-container[data-post-id]").forEach(p => observer.observe(p));
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(ts.replace(' ', 'T'));
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}