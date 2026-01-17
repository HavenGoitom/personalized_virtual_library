const USERNAME_RE = /^[A-Za-z](?!.*__)[A-Za-z0-9_]{2,19}$/; 
const PASSWORD_RE = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/; 


function el(html) {
  const d = document.createElement('div');
  d.innerHTML = html.trim();
  return d.firstChild;
}

function uid(p = 'id') {
  return p + '_' + Math.random().toString(36).slice(2, 9);
}

function toast(msg, ms = 1600) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._h);
  t._h = setTimeout(() => t.classList.remove('show'), ms);
}


const KEYS = {
  USERS: 'wl_users_fixed',
  BOOKS: 'wl_books_fixed',
  SHELVES: 'wl_shelves_fixed',
  SESSION: 'wl_session_fixed',
  PROFILES: 'wl_profiles_fixed'
};

function load(k, def) {
  try {
    const s = localStorage.getItem(k);
    return s ? JSON.parse(s) : def;
  } catch (e) {
    return def;
  }
}

function save(k, v) {
  localStorage.setItem(k, JSON.stringify(v));
}

function loadProfiles() {
  return load(KEYS.PROFILES, {});
}

function saveProfiles(p) {
  save(KEYS.PROFILES, p);
}

function getProfile(username) {
  const p = loadProfiles();
  return p[username] || { displayName: username, bio: '', avatarData: null };
}

function setProfile(username, profile) {
  const p = loadProfiles();
  p[username] = profile;
  saveProfiles(p);
}


function ensure() {
  if (!load(KEYS.USERS, null)) save(KEYS.USERS, [{ username: 'admin', password: 'Admin@123' }]);
  
  if (!load(KEYS.BOOKS, null)) {
    const seed = [
      
      { id: uid('b'), title: 'Pride & Prejudice', author: 'Jane Austen', description: 'Classic novel.', url: 'https://www.gutenberg.org/cache/epub/1342/pg1342-images.html', coverData: 'https://covers.openlibrary.org/b/id/8231996-L.jpg', owner: 'admin', createdAt: Date.now(), category: 'Fiction' },
      { id: uid('b'), title: 'Sherlock Holmes', author: 'A. Conan Doyle', description: 'Detective tales.', url: 'https://www.gutenberg.org/ebooks/1661', coverData: 'https://covers.openlibrary.org/b/id/8228691-L.jpg', owner: 'admin', createdAt: Date.now() - 1000 * 60, category: 'Fiction' },
      { id: uid('b'), title: 'Frankenstein', author: 'Mary Shelley', description: 'Gothic novel.', url: 'https://www.gutenberg.org/ebooks/84', coverData: 'https://covers.openlibrary.org/b/id/8225261-L.jpg', owner: 'admin', createdAt: Date.now() - 2000 * 60, category: 'Fiction' },
      
      { id: uid('b'), title: 'Moby Dick', author: 'Herman Melville', description: 'Sea adventure.', url: 'https://www.gutenberg.org/ebooks/2701', coverData: 'https://covers.openlibrary.org/b/id/8100921-L.jpg', owner: 'admin', createdAt: Date.now() - 3000 * 60, category: 'Non-Fiction' },
      { id: uid('b'), title: 'Dracula', author: 'Bram Stoker', description: 'Horror classic.', url: 'https://www.gutenberg.org/ebooks/345', coverData: 'https://covers.openlibrary.org/b/id/8081536-L.jpg', owner: 'admin', createdAt: Date.now() - 4000 * 60, category: 'Non-Fiction' },
      { id: uid('b'), title: 'The Odyssey', author: 'Homer', description: 'Epic poem.', url: 'https://www.gutenberg.org/ebooks/1727', coverData: 'https://covers.openlibrary.org/b/id/8157891-L.jpg', owner: 'admin', createdAt: Date.now() - 5000 * 60, category: 'Non-Fiction' },
    ];
    save(KEYS.BOOKS, seed);
  }
  
  if (!load(KEYS.SHELVES, null)) save(KEYS.SHELVES, {});
  
  if (!load(KEYS.PROFILES, null)) {
    const defaults = {};
    const users = load(KEYS.USERS, []);
    users.forEach(u => {
      defaults[u.username] = { displayName: u.username, bio: '', avatarData: null };
    });
    save(KEYS.PROFILES, defaults);
  }
}

ensure();


function currentUser() {
  return load(KEYS.SESSION, null);
}

function setCurrentUser(u) {
  if (u) save(KEYS.SESSION, u);
  else localStorage.removeItem(KEYS.SESSION);
  refreshUserDisplay();
}

function refreshUserDisplay() {
  const u = currentUser();
  const label = document.getElementById('userLabel');
  const av = document.getElementById('userAv');
  
  if (!u) {
    label.textContent = 'Not signed in';
    av.style.backgroundImage = '';
    av.textContent = '';
    return;
  }
  
  const profile = getProfile(u);
  label.textContent = profile.displayName ? 'Signed in: ' + profile.displayName : 'Signed in: ' + u;
  
  if (profile.avatarData) {
    av.style.backgroundImage = `url(${profile.avatarData})`;
    av.textContent = '';
  } else {
    av.style.backgroundImage = '';
    av.textContent = (profile.displayName || u).charAt(0).toUpperCase();
  }
}

function allBooks() {
  return load(KEYS.BOOKS, []).slice();
}

function saveBooks(list) {
  save(KEYS.BOOKS, list);
}

function allShelves() {
  return load(KEYS.SHELVES, {});
}

function saveShelves(obj) {
  save(KEYS.SHELVES, obj);
}

function userShelf(u) {
  const s = allShelves();
  return s[u] || [];
}

const main = document.getElementById('mainContent');
const modalRoot = document.getElementById('modalRoot');
const navlinks = document.querySelectorAll('.navlink');

function setActive(route) {
  navlinks.forEach(a => a.classList.toggle('active', a.dataset.route === route));
}

function route(route, opts = {}) {
  setActive(route);
  
  if (route === 'home') renderLanding();
  else if (route === 'books') renderBooks();
  else if (route === 'shelf') renderShelf();
  else if (route === 'profile') renderProfile();
  else if (route === 'login') renderLogin();
  else if (route === 'signup') renderSignup();
  else if (route === 'reader' && opts.book) renderReader(opts.book);
}

document.querySelectorAll('[data-route]').forEach(b => b.addEventListener('click', e => {
  e.preventDefault();
  route(b.dataset.route);
}));

document.getElementById('addBtn').addEventListener('click', () => {
  if (!currentUser()) {
    toast('Sign in to add books');
    route('login');
    return;
  }
  openAddModal();
});

function bookCard(book) {
  const div = document.createElement('div');
  div.className = 'book';
  
  const cover = document.createElement('div');
  cover.className = 'cover';
  if (book.coverData) cover.style.backgroundImage = `url(${book.coverData})`;
  else cover.style.backgroundImage = `linear-gradient(180deg,#fff,#eee)`;
  
  const label = document.createElement('div');
  label.className = 'spine-label';
  label.textContent = book.title;
  
  const cat = document.createElement('div');
  cat.className = 'cat-badge';
  cat.textContent = book.category || 'Unknown';
  
  const tag = document.createElement('div');
  tag.className = 'tag';
  if (book.owner === currentUser()) {
    tag.textContent = 'You';
    tag.classList.add('you');
  }
  
  const opts = document.createElement('div');
  opts.className = 'opts';
  
  const addBtn = document.createElement('button');
  addBtn.className = 'icon';
  addBtn.title = 'Add to My Shelf';
  addBtn.textContent = '＋';
  addBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    addToShelf(book);
  });
  
  opts.appendChild(addBtn);
  
  if (book.owner === currentUser()) {
    const edit = document.createElement('button');
    edit.className = 'icon';
    edit.textContent = '✎';
    
    const del = document.createElement('button');
    del.className = 'icon';
    del.textContent = '🗑';
    
    edit.addEventListener('click', (ev) => {
      ev.stopPropagation();
      openEditGlobalModal(book);
    });
    
    del.addEventListener('click', (ev) => {
      ev.stopPropagation();
      if (confirm('Delete this book?')) deleteGlobal(book.id);
    });
    
    opts.appendChild(edit);
    opts.appendChild(del);
  }
  
  div.appendChild(cover);
  div.appendChild(label);
  div.appendChild(cat);
  div.appendChild(tag);
  div.appendChild(opts);
  
  div.addEventListener('click', () => route('reader', { book }));
  
  return div;
}

function addToShelf(book) {
  const u = currentUser();
  if (!u) {
    toast('Sign in to add to shelf');
    route('login');
    return;
  }
  
  const shelves = allShelves();
  shelves[u] = shelves[u] || [];
  
  const exists = shelves[u].some(s => s.origId === book.id);
  if (exists) {
    toast('Already in your shelf');
    return;
  }
  
  const copy = Object.assign({}, book, { id: uid('s'), origId: book.id, owner: u, createdAt: Date.now() });
  shelves[u].unshift(copy);
  saveShelves(shelves);
  toast('Added to My Shelf');
}

function deleteGlobal(id) {
  let books = allBooks();
  books = books.filter(b => b.id !== id);
  saveBooks(books);
  toast('Deleted');
  route('books');
}

function applyGlobalEdit(bookId, changes) {
  const books = allBooks();
  const i = books.findIndex(b => b.id === bookId);
  if (i === -1) {
    toast('Not found');
    return;
  }
  books[i] = Object.assign({}, books[i], changes);
  saveBooks(books);
  toast('Updated');
  route('books');
}

function applyShelfEdit(shelfId, changes) {
  const u = currentUser();
  if (!u) return;
  
  const shelves = allShelves();
  shelves[u] = (shelves[u] || []).map(b => b.id === shelfId ? Object.assign({}, b, changes) : b);
  saveShelves(shelves);
  toast('Shelf updated');
  route('shelf');
}

function openAddModal() {
  const modal = el(`<div class="modal-backdrop"><div class="modal panel">
    <h3 style="margin:0 0 10px 0">Add Book</h3>
    <div style="display:grid;gap:8px">
      <label>Title</label><input id="m_title" type="text" />
      <label>Author</label><input id="m_author" type="text" />
      <label>Category</label>
      <select id="m_category">
        <option value="Fiction">Fiction</option>
        <option value="Non-Fiction">Non-Fiction</option>
      </select>
      <label>URL</label><input id="m_url" type="url" placeholder="https://..." />
      <label>Cover image</label><input id="m_cover" type="file" accept="image/*" />
      <label>Description</label><textarea id="m_desc"></textarea>
      <div style="display:flex;justify-content:flex-end;gap:8px"><button class="btn ghost" id="m_cancel">Cancel</button><button class="btn" id="m_save">Save</button></div>
    </div>
  </div></div>`);
  
  modalRoot.innerHTML = '';
  modalRoot.classList.remove('hidden');
  modalRoot.appendChild(modal);
  
  modal.querySelector('#m_cancel').addEventListener('click', closeModal);
  
  modal.querySelector('#m_save').addEventListener('click', () => {
    const t = modal.querySelector('#m_title').value.trim();
    const a = modal.querySelector('#m_author').value.trim();
    const u = modal.querySelector('#m_url').value.trim();
    const d = modal.querySelector('#m_desc').value.trim();
    const f = modal.querySelector('#m_cover').files[0];
    const cat = modal.querySelector('#m_category').value;
    
    if (!t || !u) {
      toast('Title and URL required');
      return;
    }
    
    if (!f) {
      toast('Cover image required');
      return;
    }
    
    const r = new FileReader();
    r.onload = (ev) => {
      const cover = ev.target.result;
      const book = { id: uid('b'), title: t, author: a, description: d, url: u, coverData: cover, owner: currentUser(), createdAt: Date.now(), category: cat };
      const bs = allBooks();
      bs.unshift(book);
      saveBooks(bs);
      
      const shelves = allShelves();
      shelves[currentUser()] = shelves[currentUser()] || [];
      const copy = Object.assign({}, book, { id: uid('s'), origId: book.id });
      shelves[currentUser()].unshift(copy);
      saveShelves(shelves);
      
      toast('Added');
      closeModal();
      route('books');
    };
    r.readAsDataURL(f);
  });
}

function openEditGlobalModal(book) {
  const modal = el(`<div class="modal-backdrop"><div class="modal panel">
    <h3 style="margin:0 0 10px 0">Edit Book</h3>
    <div style="display:grid;gap:8px">
      <label>Title</label><input id="e_title" type="text" value="${escapeHtml(book.title)}" />
      <label>Author</label><input id="e_author" type="text" value="${escapeHtml(book.author || '')}" />
      <label>Category</label>
      <select id="e_category">
        <option value="Fiction"${book.category === 'Fiction' ? ' selected' : ''}>Fiction</option>
        <option value="Non-Fiction"${book.category === 'Non-Fiction' ? ' selected' : ''}>Non-Fiction</option>
      </select>
      <label>URL</label><input id="e_url" type="url" value="${escapeHtml(book.url || '')}" />
      <label>Cover (optional)</label><input id="e_cover" type="file" accept="image/*" />
      <label>Description</label><textarea id="e_desc">${escapeHtml(book.description || '')}</textarea>
      <div style="display:flex;justify-content:flex-end;gap:8px"><button class="btn ghost" id="e_cancel">Cancel</button><button class="btn" id="e_save">Save</button></div>
    </div>
  </div></div>`);
  
  modalRoot.innerHTML = '';
  modalRoot.classList.remove('hidden');
  modalRoot.appendChild(modal);
  
  modal.querySelector('#e_cancel').addEventListener('click', closeModal);
  
  modal.querySelector('#e_save').addEventListener('click', () => {
    const t = modal.querySelector('#e_title').value.trim();
    const a = modal.querySelector('#e_author').value.trim();
    const cat = modal.querySelector('#e_category').value;
    const u = modal.querySelector('#e_url').value.trim();
    const d = modal.querySelector('#e_desc').value.trim();
    const f = modal.querySelector('#e_cover').files[0];
    
    if (!t || !u) {
      toast('Title and URL required');
      return;
    }
    
    if (f) {
      const r = new FileReader();
      r.onload = (ev) => {
        applyGlobalEdit(book.id, { title: t, author: a, url: u, description: d, coverData: ev.target.result, category: cat });
        closeModal();
      };
      r.readAsDataURL(f);
    } else {
      applyGlobalEdit(book.id, { title: t, author: a, url: u, description: d, category: cat });
      closeModal();
    }
  });
}

function openEditShelfModal(book) {
  const modal = el(`<div class="modal-backdrop"><div class="modal panel">
    <h3 style="margin:0 0 10px 0">Edit Shelf Copy</h3>
    <div style="display:grid;gap:8px">
      <label>Title</label><input id="s_title" type="text" value="${escapeHtml(book.title)}" />
      <label>Author</label><input id="s_author" type="text" value="${escapeHtml(book.author || '')}" />
      <label>Category</label>
      <select id="s_category">
        <option value="Fiction"${book.category === 'Fiction' ? ' selected' : ''}>Fiction</option>
        <option value="Non-Fiction"${book.category === 'Non-Fiction' ? ' selected' : ''}>Non-Fiction</option>
      </select>
      <label>Cover (optional)</label><input id="s_cover" type="file" accept="image/*" />
      <label>Description</label><textarea id="s_desc">${escapeHtml(book.description || '')}</textarea>
      <div style="display:flex;justify-content:flex-end;gap:8px"><button class="btn ghost" id="s_cancel">Cancel</button><button class="btn" id="s_save">Save</button></div>
    </div>
  </div></div>`);
  
  modalRoot.innerHTML = '';
  modalRoot.classList.remove('hidden');
  modalRoot.appendChild(modal);
  
  modal.querySelector('#s_cancel').addEventListener('click', closeModal);
  
  modal.querySelector('#s_save').addEventListener('click', () => {
    const t = modal.querySelector('#s_title').value.trim();
    const a = modal.querySelector('#s_author').value.trim();
    const cat = modal.querySelector('#s_category').value;
    const d = modal.querySelector('#s_desc').value.trim();
    const f = modal.querySelector('#s_cover').files[0];
    
    if (f) {
      const r = new FileReader();
      r.onload = (ev) => {
        applyShelfEdit(book.id, { title: t, author: a, description: d, coverData: ev.target.result, category: cat });
        closeModal();
      };
      r.readAsDataURL(f);
    } else {
      applyShelfEdit(book.id, { title: t, author: a, description: d, category: cat });
      closeModal();
    }
  });
}

function closeModal() {
  modalRoot.innerHTML = '';
  modalRoot.classList.add('hidden');
}

function escapeHtml(s) {
  return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
}




function renderLanding() {
  main.innerHTML = '';
  const home = el(`<div>
    <div class="home-hero"><h1>Welcome to Woodland Library</h1><p>Your personal space to discover, save, and enjoy books.</p></div>
    <div class="home-features">
      <div class="home-feature"><h3>Explore Books</h3><p>Browse a growing collection of classic and community-shared books.</p></div>
      <div class="home-feature"><h3>Your Personal Shelf</h3><p>Save books privately and customize them your way.</p></div>
      <div class="home-feature"><h3>Upload & Share</h3><p>Add your own books with links and cover images.</p></div>
    </div>
    <div class="get-started"><button id="homeGetStarted">Get Started — Build Your Shelf</button></div>
  </div>`);
  
  main.appendChild(home);
  home.querySelector('#homeGetStarted').addEventListener('click', () => {
    if (!currentUser()) route('login');
    else route('books');
  });
}

function renderBooks() {
  const books = allBooks().sort((a, b) => b.createdAt - a.createdAt);
  main.innerHTML = '';
  
  const header = el(`<div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
    <div><h2 style="margin:0">Books</h2><div style="font-size:13px;color:#6d5a48">Public collection (organized by category)</div></div>
    <div style="display:flex;gap:8px;align-items:center">
      <input id="search" placeholder="Search title/author" style="padding:8px;border-radius:8px;border:1px solid #e6e2dd;width:260px" />
      <button class="btn ghost" id="clear">Clear</button>
    </div>
  </div>`);
  
  main.appendChild(header);
  
  const shelfWrap = document.createElement('div');
  shelfWrap.className = 'shelf-wrap';
  main.appendChild(shelfWrap);
  
  function buildSection(title, items) {
    const shelf = document.createElement('div');
    shelf.className = 'shelf';
    
    const label = document.createElement('div');
    label.className = 'shelf-label';
    label.innerHTML = `<div>${title}</div><div style="font-size:13px;opacity:0.9">${items.length} book${items.length !== 1 ? 's' : ''}</div>`;
    shelf.appendChild(label);
    
    const row = document.createElement('div');
    row.className = 'books-row';
    
    if (items.length === 0) {
      const panel = document.createElement('div');
      panel.className = 'panel';
      panel.style.margin = '12px';
      panel.textContent = 'No books in this category yet — press + to add one.';
      shelf.appendChild(panel);
    } else {
      items.forEach(book => row.appendChild(bookCard(book)));
      shelf.appendChild(row);
    }
    
    return shelf;
  }
  
  function build(filter = '') {
    shelfWrap.innerHTML = '';
    const filtered = books.filter(b => {
      if (!filter) return true;
      const q = filter.toLowerCase();
      return (b.title || '').toLowerCase().includes(q) || (b.author || '').toLowerCase().includes(q);
    });
    
    const fiction = filtered.filter(b => (b.category || '').toLowerCase() === 'fiction');
    const nonFiction = filtered.filter(b => (b.category || '').toLowerCase() === 'non-fiction' || (b.category || '').toLowerCase() === 'nonfiction');
    
    shelfWrap.appendChild(buildSection('Fiction', fiction));
    shelfWrap.appendChild(buildSection('Non-Fiction', nonFiction));
  }
  
  build('');
  
  header.querySelector('#search').addEventListener('input', e => build(e.target.value));
  header.querySelector('#clear').addEventListener('click', () => {
    header.querySelector('#search').value = '';
    build('');
  });
}

function renderShelf() {
  const user = currentUser();
  main.innerHTML = '';
  
  if (!user) {
    main.appendChild(el(`<div class="panel" style="max-width:720px;margin:12px auto"><h3>My Shelf</h3><div style="font-size:13px;color:#6d5a48">Sign in to manage your private shelf.</div><div style="display:flex;justify-content:flex-end;margin-top:12px"><button class="btn" id="goLogin">Sign in</button></div></div>`));
    document.getElementById('goLogin').addEventListener('click', () => route('login'));
    return;
  }
  
  const header = el(`<div style="display:flex;align-items:center;justify-content:space-between"><div><h2 style="margin:0">My Shelf</h2><div style="font-size:13px;color:#6d5a48">Your personal copies</div></div></div>`);
  main.appendChild(header);
  
  const shelfPanel = el(`<div style="display:grid;gap:12px;margin-top:8px"></div>`);
  main.appendChild(shelfPanel);
  
  const shelf = userShelf(user);
  
  if (!shelf || shelf.length === 0) {
    shelfPanel.appendChild(el(`<div class="panel"><div class="small">Your shelf is empty. Add books from Books or upload new ones using +.</div></div>`));
    return;
  }
  
  shelf.forEach(b => {
    const card = el(`<div class="panel" style="display:flex;gap:12px;align-items:center">
      <div style="width:110px;height:150px;border-radius:8px;background-size:cover;background-position:center" data-cover></div>
      <div style="flex:1">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div><div style="font-weight:700" data-title></div><div style="font-size:13px;color:#6d5a48" data-author></div></div>
          <div style="display:flex;gap:8px"><button class="btn ghost" data-edit>Edit</button><button class="btn" data-delete>Delete</button><button class="btn ghost" data-open>Open</button></div>
        </div>
        <div style="margin-top:8px;color:var(--muted)" data-desc></div>
      </div>
    </div>`);
    
    card.querySelector('[data-cover]').style.backgroundImage = b.coverData ? `url(${b.coverData})` : '';
    card.querySelector('[data-title]').textContent = b.title;
    card.querySelector('[data-author]').textContent = b.author || '';
    card.querySelector('[data-desc]').textContent = b.description || '';
    
    card.querySelector('[data-open]').addEventListener('click', () => route('reader', { book: b }));
    
    card.querySelector('[data-delete]').addEventListener('click', () => {
      const shelves = allShelves();
      shelves[user] = (shelves[user] || []).filter(x => x.id !== b.id);
      saveShelves(shelves);
      toast('Removed');
      renderShelf();
    });
    
    card.querySelector('[data-edit]').addEventListener('click', () => openEditShelfModal(b));
    
    shelfPanel.appendChild(card);
  });
}

function renderProfile() {
  main.innerHTML = '';
  const user = currentUser();
  
  if (!user) {
    main.appendChild(el(`<div class="panel" style="max-width:720px;margin:12px auto"><h3>Profile</h3><div class="small">Not signed in.</div><div style="display:flex;justify-content:flex-end;margin-top:12px"><button class="btn" id="toLogin">Sign in</button></div></div>`));
    document.getElementById('toLogin').addEventListener('click', () => route('login'));
    return;
  }
  
  const profile = getProfile(user);
  
  const box = el(`<div class="panel" style="max-width:760px;margin:12px auto">
    <h3 style="margin:0 0 10px 0">Profile</h3>
    <div style="display:grid;grid-template-columns:140px 1fr;gap:12px;align-items:start">
      <div style="display:flex;flex-direction:column;gap:10px;align-items:center">
        <div class="avatar-wrap">
          <div class="avatar-preview" id="pf_preview">${escapeHtml((profile.displayName || user).charAt(0).toUpperCase())}</div>
          <div class="avatar-plus" id="pf_plus">＋</div>
        </div>
        <input id="pf_file" class="file-hidden" type="file" accept="image/*" />
        <button class="btn ghost" id="pf_remove">Remove</button>
      </div>
      <div>
        <label>Display name</label>
        <input id="pf_name" type="text" value="${escapeHtml(profile.displayName || user)}" />
        <label style="margin-top:8px">Bio</label>
        <textarea id="pf_bio">${escapeHtml(profile.bio || '')}</textarea>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px">
          <button class="btn ghost" id="pf_cancel">Cancel</button>
          <button class="btn" id="pf_save">Save</button>
        </div>
      </div>
    </div>
    <div style="margin-top:10px" class="small">Profile stored locally. Clearing storage removes it.</div>
  </div>`);
  
  main.appendChild(box);
  
  const preview = box.querySelector('#pf_preview');
  if (profile.avatarData) {
    preview.style.backgroundImage = `url(${profile.avatarData})`;
    preview.textContent = '';
  }
  
  const fileInput = box.querySelector('#pf_file');
  const plus = box.querySelector('#pf_plus');
  
  plus.addEventListener('click', () => fileInput.click());
  
  fileInput.addEventListener('change', e => {
    const f = e.target.files[0];
    if (!f) return;
    
    const r = new FileReader();
    r.onload = (ev) => {
      preview.style.backgroundImage = `url(${ev.target.result})`;
      preview.textContent = '';
      preview.dataset.temp = ev.target.result;
    };
    r.readAsDataURL(f);
  });
  
  box.querySelector('#pf_remove').addEventListener('click', () => {
    preview.style.backgroundImage = '';
    preview.textContent = (box.querySelector('#pf_name').value || user).charAt(0).toUpperCase();
    preview.dataset.temp = '__REMOVE__';
  });
  
  box.querySelector('#pf_cancel').addEventListener('click', () => renderProfile());
  
  box.querySelector('#pf_save').addEventListener('click', () => {
    const newName = box.querySelector('#pf_name').value.trim() || user;
    const newBio = box.querySelector('#pf_bio').value.trim();
    let avatar = profile.avatarData;
    
    if (preview.dataset.temp) {
      if (preview.dataset.temp === '__REMOVE__') avatar = null;
      else avatar = preview.dataset.temp;
    }
    
    setProfile(user, { displayName: newName, bio: newBio, avatarData: avatar });
    toast('Profile saved');
    refreshUserDisplay();
    renderProfile();
  });
  
  const logout = el(`<div style="display:flex;justify-content:flex-end;margin-top:12px"><button class="btn ghost" id="logoutBtn">Logout</button></div>`);
  main.appendChild(logout);
  
  logout.querySelector('#logoutBtn').addEventListener('click', () => {
    setCurrentUser(null);
    toast('Logged out');
    route('home');
  });
}

function renderReader(book) {
  main.innerHTML = '';
  
  const header = el(`<div style="display:flex;align-items:center;justify-content:space-between"><div><h3 style="margin:0">${escapeHtml(book.title)}</h3><div style="font-size:13px;color:#6d5a48">${escapeHtml(book.author || '')}</div></div><div style="display:flex;gap:8px"><a class="btn ghost" id="openNew" target="_blank">Open in new tab</a><button class="btn" id="back">Back</button></div></div>`);
  main.appendChild(header);
  
  const reader = el(`<div class="reader-frame" style="margin-top:10px"><div class="reader-header"><div style="display:flex;gap:10px;align-items:center"><div style="width:56px;height:72px;border-radius:6px;background-size:cover;background-position:center" id="rcover"></div><div><div style="font-weight:700">${escapeHtml(book.title)}</div><div style="font-size:13px;color:#6d5a48">${escapeHtml(book.author || '')}</div></div></div></div><iframe class="reader-iframe" src="${book.url}" id="rframe"></iframe></div>`);
  main.appendChild(reader);
  
  document.getElementById('rcover').style.backgroundImage = book.coverData ? `url(${book.coverData})` : '';
  document.getElementById('openNew').href = book.url;
  document.getElementById('back').addEventListener('click', () => route('books'));
}

function renderLogin() {
  main.innerHTML = '';
  
  const card = el(`<div class="panel auth" style="max-width:760px;margin:12px auto">
    <h3>Login</h3>
    <div style="margin-top:10px;display:grid;gap:10px">
      <label>Username</label><input id="li_user" type="text" placeholder="e.g. alice_01" />
      <label>Password</label><input id="li_pass" type="password" placeholder="Your password" />
      <div style="display:flex;justify-content:flex-end;gap:8px"><button class="btn ghost" id="toSignup">Signup</button><button class="btn" id="doLogin">Login</button></div>
      <div style="font-size:13px;color:#6d5a48">Username: 3–20 chars, start with letter, letters/numbers/underscore allowed. Password: min 8 chars, must include upper, lower, digit & special.</div>
    </div>
  </div>`);
  
  main.appendChild(card);
  
  card.querySelector('#toSignup').addEventListener('click', () => route('signup'));
  
  card.querySelector('#doLogin').addEventListener('click', () => {
    const u = card.querySelector('#li_user').value.trim();
    const p = card.querySelector('#li_pass').value;
    
    if (!u || !p) {
      toast('Enter username & password');
      return;
    }
    
    if (!USERNAME_RE.test(u)) {
      toast('Invalid username format');
      return;
    }
    
    if (!PASSWORD_RE.test(p)) {
      toast('Password format invalid');
      return;
    }
    
    const users = load(KEYS.USERS, []);
    const userObj = users.find(x => x.username === u && x.password === p);
    if (!userObj) {
      toast('Invalid username or password');
      return;
    }
    setCurrentUser(u);
    toast('Signed in');
    route('books');
  });}