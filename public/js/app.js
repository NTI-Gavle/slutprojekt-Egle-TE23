window.onscroll = function () { scrollFunction(); };
function scrollFunction() {
    const header = document.querySelector("header");
    if (!header) return;
    if ((this.oldScroll || 0) > this.scrollY) {
        header.style.transform = "translateY(0px)";
    } else {
        header.style.transform = "translateY(-120px)";
    }
    this.oldScroll = this.scrollY;
}

//create post
function CloseCreatePost() {
    document.getElementById("create-post-popout").style.display = "none";
}
function OpenCreatePost() {
    document.getElementById("create-post-popout").style.display = "flex";
    const draft = localStorage.getItem('post_draft');
    const ta    = document.getElementById('create-post-text');
    if (draft && ta && !ta.value) {
        ta.value = draft;
        ta.dispatchEvent(new Event('input'));
    }
}

//logout
function confirmLogout(e) {
    if (!confirm('Are you sure you want to log out?')) e.preventDefault();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href*="logout.php"]').forEach(a => {
        a.addEventListener('click', confirmLogout);
    });

    //resize text
    const textArea = document.getElementById('create-post-text');
    if (textArea) {
        textArea.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = Math.min(this.scrollHeight, 200) + "px";
            this.style.overflowY = this.scrollHeight > 200 ? "scroll" : "hidden";
        });
    }

    //char counter
    document.querySelectorAll('input[maxlength], textarea[maxlength]').forEach(el => {
        const counter = document.querySelector(`.char-counter[data-for="${el.id}"]`);
        if (!counter) return;
        const max    = el.getAttribute('maxlength');
        const update = () => { counter.textContent = `${el.value.length}/${max}`; };
        el.addEventListener('input', update);
        update();
    });

    //media
    const mediaInput   = document.getElementById('post-media-input');
    const mediaPreview = document.getElementById('post-media-preview');
    if (mediaInput && mediaPreview) {
        mediaInput.addEventListener('change', () => {
            mediaPreview.innerHTML = '';
            Array.from(mediaInput.files).slice(0, 4).forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    const wrap = document.createElement('div');
                    wrap.className = 'media-preview-item';
                    wrap.style.cssText = 'position:relative;display:inline-block';
                    wrap.innerHTML = `
                        <img src="${e.target.result}" class="media-preview-thumb" alt="preview">
                        <button type="button" class="media-remove-btn" data-index="${i}"
                            style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;font-size:0.7em">✕</button>`;
                    mediaPreview.appendChild(wrap);
                };
                reader.readAsDataURL(file);
            });
        });
        mediaPreview.addEventListener('click', e => {
            if (e.target.classList.contains('media-remove-btn')) {
                e.target.closest('.media-preview-item').remove();
            }
        });
    }

    //text or media
    const postForm = document.querySelector('#create-post-popout form');
    if (postForm) {
        postForm.addEventListener('submit', e => {
            const text  = document.getElementById('create-post-text')?.value.trim() ?? '';
            const files = document.getElementById('post-media-input')?.files ?? [];
            if (text === '' && files.length === 0) {
                e.preventDefault();
                alert('Please add some text or at least one image.');
                return;
            }
            localStorage.removeItem('post_draft');
        });
    }

    //emojis
    const emojiBtn    = document.getElementById('emoji-btn');
    const emojiPicker = document.getElementById('emoji-picker');
    const postText    = document.getElementById('create-post-text');
    if (emojiBtn && emojiPicker && postText) {
        const emojis = [
            "(˶>⩊<˶)", "(˶˃ ᵕ ˂˶)", "(｡•́‿•̀｡)", "( ˶ˆᗜˆ˵ )", "(≧◡≦)",
            "(✿◠‿◠)", "(˵ •̀ ᴗ - ˵ ) ✧", "(ᵕ—ᴗ—)",  "(,,>﹏<,,)",
            "(〃´𓎟`〃)", "ꉂ(˵˃ ᗜ ˂˵)", "(ﾉ≧ڡ≦)", "(╯°□°）╯︵ ┻━┻", "(☞ﾟヮﾟ)☞",
            "(╥﹏╥)", "(っ- ‸ - ς)", "(｡•́︿•̀｡)", "(ಥ﹏ಥ)", "( T﹏T )",
            "(¬_¬)", "(ಠ_ಠ)", "(>_<)", "(≖_≖ )", "(╬ಠ益ಠ)"
        ];
        emojiPicker.innerHTML = emojis.map(em =>
            `<span class="emoji-option" onclick="insertEmoji('${em.replace(/'/g, "\\'")}')">${em}</span>`
        ).join('');
        emojiBtn.addEventListener('click', e => {
            e.stopPropagation();
            emojiPicker.style.display = emojiPicker.style.display === 'grid' ? 'none' : 'grid';
        });
        document.addEventListener('click', () => emojiPicker.style.display = 'none');
    }
});

function insertEmoji(emoji) {
    const ta = document.getElementById('create-post-text');
    if (!ta) return;
    const pos   = ta.selectionStart;
    ta.value    = ta.value.slice(0, pos) + emoji + ta.value.slice(ta.selectionEnd);
    ta.selectionStart = ta.selectionEnd = pos + emoji.length;
    ta.focus();
    document.getElementById('emoji-picker').style.display = 'none';
}

//draft
function saveDraft() {
    const text = document.getElementById('create-post-text')?.value ?? '';
    if (text.trim()) { localStorage.setItem('post_draft', text); alert('Draft saved!'); }
}
function loadDraft() {
    const draft = localStorage.getItem('post_draft');
    const ta    = document.getElementById('create-post-text');
    if (draft && ta) { ta.value = draft; ta.dispatchEvent(new Event('input')); }
}
function discardDraft() {
    if (confirm('Discard draft?')) {
        localStorage.removeItem('post_draft');
        const ta = document.getElementById('create-post-text');
        if (ta) ta.value = '';
    }
}