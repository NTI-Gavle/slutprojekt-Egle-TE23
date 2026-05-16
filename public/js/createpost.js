const postTa = document.getElementById('create-post-text');
const postCounter = document.getElementById('post-char-counter');
if (postTa && postCounter) {
    postTa.addEventListener('input', () => { postCounter.textContent = postTa.value.length + '/500'; });
}

function limitFiles(input, max) {
    if (input.files.length > max) {
        alert(`Max ${max} images allowed.`);
        input.value = '';
        document.getElementById('post-media-preview').innerHTML = '';
        return;
    }
    //preview
    const preview = document.getElementById('post-media-preview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'media-preview-thumb';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}