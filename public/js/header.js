
document.addEventListener('click', e => {
    if (!document.getElementById('search-wrapper').contains(e.target)) {
        document.getElementById('search-dropdown').style.display = 'none';
    }
});

let searchTimeout;
function openSearchDropdown() {
    const val = document.getElementById('search-field').value;
    handleSearchInput(val);
}

function handleSearchInput(val) {
    clearTimeout(searchTimeout);
    if (val.trim() === '') {
        loadRecentSearches();
        return;
    }
    searchTimeout = setTimeout(() => fetchSearchSuggestions(val), 250);
}

function doSearch(val) {
    if (val.trim()) {
        window.location.href = 'search.php?q=' + encodeURIComponent(val.trim());
    }
}

async function loadRecentSearches() {
    const dropdown = document.getElementById('search-dropdown');
    const inner = document.getElementById('search-dropdown-inner');
    dropdown.style.display = 'block';
    const res = await fetch('get-search-data.php?type=recent');
    const data = await res.json();
    if (!data.recent?.length && !data.popular?.length) {
        inner.innerHTML = '<p class="search-dd-empty">Start typing to search...</p>';
        return;
    }
    let html = '';
    if (data.recent?.length) {
        html += '<div class="search-dd-section">Recent</div>';
        data.recent.forEach(s => {
            html += `<div class="search-dd-row" onclick="doSearch('${escHtml(s.SearchTerm)}')">
                <i class="fa-solid fa-clock-rotate-left search-dd-icon"></i>
                <span>${escHtml(s.SearchTerm)}</span>
                <button class="search-dd-del" onclick="event.stopPropagation();deleteSearch(${s.Id},this)" title="remove">✕</button>
            </div>`;
        });
    }
    if (data.popular?.length) {
        html += '<div class="search-dd-section">Trending</div>';
        data.popular.forEach(s => {
            html += `<div class="search-dd-row" onclick="doSearch('${escHtml(s.SearchTerm)}')">
                <i class="fa-solid fa-sun" style="color:var(--accent-color)"></i></i>
                <span>${escHtml(s.SearchTerm)}</span>
                <small style="opacity:0.5;margin-left:auto">${s.cnt}</small>
            </div>`;
        });
    }
    inner.innerHTML = html;
}

async function fetchSearchSuggestions(val) {
    const dropdown = document.getElementById('search-dropdown');
    const inner = document.getElementById('search-dropdown-inner');
    dropdown.style.display = 'block';
    const res = await fetch('get-search-data.php?type=suggest&q=' + encodeURIComponent(val));
    const data = await res.json();
    let html = '';
    if (data.users?.length) {
        html += '<div class="search-dd-section">People</div>';
        data.users.forEach(u => {
            html += `<a href="profile.php?id=${u.Id}" class="search-dd-row no-underline">
                <img src="../uploads/pfp/${escHtml(u.ProfilePicture)}" class="search-dd-pfp">
                <div><strong>${escHtml(u.Nickname)}</strong> <small>@${escHtml(u.Username)}</small></div>
            </a>`;
        });
    }
    if (data.terms?.length) {
        html += '<div class="search-dd-section">Posts</div>';
        data.terms.forEach(s => {
            html += `<div class="search-dd-row" onclick="doSearch('${escHtml(s.SearchTerm)}')">
                <i class="fa-solid fa-magnifying-glass search-dd-icon"></i>
                <span>${escHtml(s.SearchTerm)}</span>
            </div>`;
        });
    }
    if (!html) html = `<div class="search-dd-row" onclick="doSearch('${escHtml(val)}')"><i class="fa-solid fa-magnifying-glass search-dd-icon"></i> Search for "<strong>${escHtml(val)}</strong>"</div>`;
    inner.innerHTML = html;
}

async function deleteSearch(id, btn) {
    await fetch('get-search-data.php?type=delete&id=' + id);
    btn.closest('.search-dd-row').remove();
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}