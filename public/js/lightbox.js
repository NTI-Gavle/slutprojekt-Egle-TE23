let lbImages=[], lbIndex=0;
document.querySelectorAll('.lightbox-trigger').forEach(img=>{
    img.addEventListener('click',()=>{
        lbImages=JSON.parse(img.dataset.images);
        lbIndex=parseInt(img.dataset.index);
        openLightbox();
    });
});
function openLightbox(){ document.getElementById('lightbox-img').src='../uploads/media/'+lbImages[lbIndex]; document.getElementById('lightbox').style.display='flex'; }
function closeLightbox(){ document.getElementById('lightbox').style.display='none'; }
function lightboxStep(dir){ lbIndex=(lbIndex+dir+lbImages.length)%lbImages.length; openLightbox(); }
document.addEventListener('keydown',e=>{ if(document.getElementById('lightbox').style.display==='flex'){ if(e.key==='ArrowRight')lightboxStep(1); if(e.key==='ArrowLeft')lightboxStep(-1); if(e.key==='Escape')closeLightbox(); } });