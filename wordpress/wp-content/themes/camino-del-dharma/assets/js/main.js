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
  var switcher = document.querySelector('.lang-switcher');
  if (!switcher) return;

  var buttons = switcher.querySelectorAll('.lang-btn');
  var current = 'es';

  // No persistence while the site is Spanish-only: storing a preference that
  // cannot change writes to every visitor's browser for no purpose. When the
  // English version ships, WordPress will handle language selection with its
  // own mechanism (see ADR 0019 on keeping client-side storage to a minimum).
  function setActive(lang) {
    current = lang;
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
