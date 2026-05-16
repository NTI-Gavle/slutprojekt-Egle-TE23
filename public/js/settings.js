function ShowSettings(id) {
    document.querySelectorAll('.settings-section').forEach(el => {
        el.style.display = 'none';
    });
    document.getElementById(id).style.display = 'flex';

    history.replaceState(null, '', `settings.php?tab=${id}`);
}

//cookie stuff, i want a cookie!
function setCookie(name, value, days = 365) {
    const d = new Date();
    d.setTime(d.getTime() + days * 86400000);
    document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
}

//darkmode
const darkmodeToggle = document.getElementById('darkmode-toggle');
if (darkmodeToggle) {
    if (darkmodeToggle.checked) document.documentElement.classList.add('darkmode');
    darkmodeToggle.addEventListener('change', () => {
        const on = darkmodeToggle.checked;
        document.documentElement.classList.toggle('darkmode', on);
        document.body.classList.toggle('darkmode', on);
        setCookie('darkmode', on);
    });
}
//bg animation
const animBgToggle = document.getElementById('animated-bg-toggle');
if (animBgToggle) {
    const canvas = document.getElementById('starfield');
    animBgToggle.addEventListener('change', () => {
        const on = animBgToggle.checked;
        setCookie('animated-bg', on);
        if (canvas) {
            if (on) {
                window.location.reload();
                
            } else {
                canvas.style.display = 'none';
            }
        }
    });
}

//
function previewImage(input, previewId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById(previewId).src = e.target.result;
    reader.readAsDataURL(input.files[0]);
}

function validatePasswords() {
    const np = document.getElementById('new-pass').value;
    const cp = document.getElementById('confirm-pass').value;
    const msg = document.getElementById('pass-mismatch');
    if (np && np !== cp) {
        msg.style.display = 'block';
        return false;
    }
    msg.style.display = 'none';
    return true;
}