// Header interactions: search suggestions, cart hover, sticky shrink, mobile offcanvas
document.addEventListener('DOMContentLoaded', function () {
  // Debounce helper
  function debounce(fn, delay) {
    let t;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // Search suggestions
  const input = document.getElementById('header-search-input');
  const suggestionsBox = document.getElementById('search-suggestions');
  let selected = -1;

  async function fetchSuggestions(q) {
    if (!q) { suggestionsBox.innerHTML = ''; suggestionsBox.classList.remove('show'); suggestionsBox.setAttribute('aria-hidden','true'); return; }
    try {
      const res = await fetch('/weblaptop/search_suggest.php?q=' + encodeURIComponent(q));
      const data = await res.json();
      renderSuggestions(data);
    } catch (e) {
      console.error(e);
    }
  }

  const debouncedFetch = debounce((e) => fetchSuggestions(e.target.value.trim()), 250);
  if (input) {
    input.addEventListener('input', debouncedFetch);
    input.addEventListener('keydown', function (e) {
      const items = suggestionsBox.querySelectorAll('.suggestion-item');
      if (!items.length) return;
      if (e.key === 'ArrowDown') { e.preventDefault(); selected = Math.min(selected + 1, items.length - 1); updateSelection(items); }
      if (e.key === 'ArrowUp') { e.preventDefault(); selected = Math.max(selected - 1, 0); updateSelection(items); }
      if (e.key === 'Enter') { e.preventDefault(); if (selected >= 0) items[selected].click(); else { this.form.submit(); } }
      if (e.key === 'Escape') { suggestionsBox.classList.remove('show'); suggestionsBox.setAttribute('aria-hidden','true'); }
    });
    document.addEventListener('click', function (ev) { if (!ev.target.closest('#header-search')) { suggestionsBox.classList.remove('show'); suggestionsBox.setAttribute('aria-hidden','true'); } });
  }

  function updateSelection(items) {
    items.forEach((it, idx) => it.classList.toggle('active', idx === selected));
    if (items[selected]) items[selected].scrollIntoView({ block: 'nearest' });
  }

  function renderSuggestions(list) {
    if (!list || !list.length) { suggestionsBox.innerHTML = '<div class="p-2 text-muted">Không có kết quả</div>'; suggestionsBox.classList.add('show'); suggestionsBox.setAttribute('aria-hidden','false'); return; }
    selected = -1;
    suggestionsBox.innerHTML = list.map(it => `
      <a href="/weblaptop/product.php?id=${it.id}" class="d-flex suggestion-item p-2 align-items-center" role="option">
        <img src="${it.image || 'https://via.placeholder.com/80x60'}" width="60" height="45" class="me-2" alt="">
        <div class="flex-fill">
          <div class="small text-muted">${it.brand || ''} <span class="mx-1">•</span> ${it.sku}</div>
          <div class="fw-semibold">${it.name}</div>
          <div class="small text-primary">${Number(it.price).toLocaleString('vi-VN')} VNĐ</div>
        </div>
      </a>
    `).join('');
    suggestionsBox.classList.add('show');
    suggestionsBox.setAttribute('aria-hidden','false');
    suggestionsBox.querySelectorAll('.suggestion-item').forEach(el => el.addEventListener('click', () => { suggestionsBox.classList.remove('show'); suggestionsBox.setAttribute('aria-hidden','true'); }));
  }

  // Cart dropdown show/hide
  const cartBtn = document.getElementById('header-cart-btn');
  const cartDropdown = document.getElementById('header-cart-dropdown');
  if (cartBtn && cartDropdown) {
    let cartTimeout;
    cartBtn.addEventListener('mouseenter', () => { clearTimeout(cartTimeout); cartDropdown.classList.add('show'); cartDropdown.setAttribute('aria-hidden','false'); cartBtn.setAttribute('aria-expanded','true'); });
    cartBtn.addEventListener('mouseleave', () => { cartTimeout = setTimeout(()=>{ cartDropdown.classList.remove('show'); cartDropdown.setAttribute('aria-hidden','true'); cartBtn.setAttribute('aria-expanded','false'); }, 250); });
    cartDropdown.addEventListener('mouseenter', () => { clearTimeout(cartTimeout); cartDropdown.classList.add('show'); cartDropdown.setAttribute('aria-hidden','false'); cartBtn.setAttribute('aria-expanded','true'); });
    cartDropdown.addEventListener('mouseleave', () => { cartDropdown.classList.remove('show'); cartDropdown.setAttribute('aria-hidden','true'); cartBtn.setAttribute('aria-expanded','false'); });
  }

  // Dropdown accessible toggles for nav
  document.querySelectorAll('.nav-dropdown > button').forEach(btn => {
    const menu = document.getElementById(btn.getAttribute('aria-controls')) || btn.nextElementSibling;
    btn.addEventListener('click', (e) => {
      const open = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', String(!open));
      if (menu) menu.style.display = open ? 'none' : 'block';
    });
    btn.addEventListener('keydown', (e) => { if (e.key === 'Escape') { btn.setAttribute('aria-expanded','false'); if (menu) menu.style.display='none'; btn.focus(); } });
  });

  // Mobile offcanvas toggling
  const mobileToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileClose = document.getElementById('mobileMenuClose');
  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => { mobileMenu.setAttribute('aria-hidden','false'); mobileToggle.setAttribute('aria-expanded','true'); document.body.style.overflow='hidden'; });
    if (mobileClose) mobileClose.addEventListener('click', () => { mobileMenu.setAttribute('aria-hidden','true'); mobileToggle.setAttribute('aria-expanded','false'); document.body.style.overflow=''; });
    // close on outside click
    mobileMenu.addEventListener('click', (e) => { if (e.target === mobileMenu) { mobileMenu.setAttribute('aria-hidden','true'); mobileToggle.setAttribute('aria-expanded','false'); document.body.style.overflow=''; } });
  }

  // Sticky shrink header on scroll
  const header = document.querySelector('.site-main-header');
  let lastScroll = 0;
  if (header) window.addEventListener('scroll', () => {
    const sc = window.scrollY;
    if (sc > 100 && sc > lastScroll) header.classList.add('shrink');
    else header.classList.remove('shrink');
    lastScroll = sc;
  });

  // Auto-dismiss flash alerts
  document.querySelectorAll('.flash-alert').forEach(function(el) {
    setTimeout(function() {
      el.classList.add('fade');
      setTimeout(() => { try { el.remove(); } catch(e){} }, 300);
    }, 5000);
  });
});