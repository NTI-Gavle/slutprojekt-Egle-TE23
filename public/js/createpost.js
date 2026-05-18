//For new post, limits images and manages char counter

const postTa = document.getElementById('create-post-text');
const postCounter = document.getElementById('post-char-counter');
if (postTa && postCounter) {
    postTa.addEventListener('input', () => {
        postCounter.textContent = postTa.value.length + '/500';
    });
}


function limitFiles(input, max) {
    const preview = document.getElementById('post-media-preview');
    preview.innerHTML = '';

    if (input.files.length > max) {
        alert(`Max ${max} images allowed.`);
        input.value = '';
        return;
    }

    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;display:inline-block';
            wrap.innerHTML = `<img src="${e.target.result}" class="media-preview-thumb" alt="preview">`;
            preview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}


