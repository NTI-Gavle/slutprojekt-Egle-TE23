document.addEventListener("click", async (e) => {
    const post = e.target.closest(".post-container");
    if (!post) return;
    const postId = post.dataset.postId;

    if (e.target.classList.contains("like-btn") || e.target.classList.contains("dislike-btn")) {
        const value = e.target.classList.contains("like-btn") ? 1 : -1;
        const res = await fetch("../private/score.php", {
            method: "POST",
            body: new URLSearchParams({
                post_id: postId,
                value: value
            })
        });
        const data = await res.json();
        post.querySelector(".like-btn").textContent = `Like (${data.likes ?? 0})`;
        post.querySelector(".dislike-btn").textContent = `Dislike (${data.dislikes ?? 0})`;
    }

    if (e.target.classList.contains("starmark-btn")) {
    await fetch("../private/starmark.php", {
        method: "POST", body: new URLSearchParams({ post_id: postId })
    });
    e.target.classList.toggle("active");
    e.target.textContent = e.target.classList.contains("active")? "Stared" : "Star";
    }

    if (e.target.classList.contains("share-btn")) {
        const url = window.location.origin + "/post.php?id=" + postId;
        prompt("Copy this link:", url);
    }
});
