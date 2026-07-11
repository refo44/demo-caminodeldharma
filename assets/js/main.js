/**
 * Camino del Dharma — Minimal JS
 * Only mobile nav toggle. Refs: 05-arquitectura-informacion-navegacion, 19-accesibilidad-estandares
 */
(function () {
  var toggle = document.getElementById('nav-toggle');
  var menu = document.getElementById('nav-menus');
  if (!toggle || !menu) return;

  toggle.setAttribute('aria-expanded', 'false');

  function closeMenu() {
    menu.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', function () {
    var isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && menu.classList.contains('is-open')) {
      closeMenu();
      toggle.focus();
    }
  });

  document.addEventListener('click', function (event) {
    if (!menu.classList.contains('is-open')) return;
    if (menu.contains(event.target) || toggle.contains(event.target)) return;
    closeMenu();
  });

  function closeMenuOnDesktop() {
    if (window.matchMedia('(min-width: 1280px)').matches) {
      closeMenu();
    }
  }
  window.addEventListener('resize', closeMenuOnDesktop);
})();

/**
 * Language switcher (UI only; content stays in Spanish in this version)
 */
(function () {
  var STORAGE_KEY = 'caminodeldharma-lang';
  var switcher = document.querySelector('.lang-switcher');
  if (!switcher) return;

  var buttons = switcher.querySelectorAll('.lang-btn');
  var current = (typeof localStorage !== 'undefined' && localStorage.getItem(STORAGE_KEY)) || 'es';

  function setActive(lang) {
    current = lang;
    if (typeof localStorage !== 'undefined') localStorage.setItem(STORAGE_KEY, lang);
    // Content is Spanish-only in this version, so the real `lang` attribute
    // (set server-side per page) must never be overwritten by a stored
    // preference — doing so would mislabel Spanish text as English for
    // screen readers on every subsequent page load.
    buttons.forEach(function (btn) {
      var isCurrent = btn.getAttribute('data-lang') === lang;
      btn.setAttribute('aria-current', isCurrent ? 'true' : 'false');
    });
  }

  setActive(current);

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setActive(btn.getAttribute('data-lang'));
    });
  });
})();
