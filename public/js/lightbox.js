let lbImages = [], lbIndex = 0;

function initLightbox() {
    document.querySelectorAll('.lightbox-trigger').forEach(img => {
        img.addEventListener('click', (e) => {
            e.stopPropagation();
            lbImages = JSON.parse(img.dataset.images || '[]');
            lbIndex  = parseInt(img.dataset.index) || 0;
            openLightbox();
        });
    });
}
function openLightbox() {
    const lb  = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    if (!lb || !img || !lbImages.length) return;
    img.src = '../uploads/media/' + lbImages[lbIndex];
    lb.style.display = 'flex';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    if (lb) lb.style.display = 'none';
}
function lightboxStep(dir) {
    lbIndex = (lbIndex + dir + lbImages.length) % lbImages.length;
    openLightbox();
}
document.addEventListener('keydown', e => 
{
    const lb = document.getElementById('lightbox');
    if (!lb || lb.style.display !== 'flex') return;
    if (e.key === 'ArrowRight') lightboxStep(1);
    if (e.key === 'ArrowLeft') lightboxStep(-1);
    if (e.key === 'Escape') closeLightbox();
});

document.addEventListener('DOMContentLoaded', initLightbox);