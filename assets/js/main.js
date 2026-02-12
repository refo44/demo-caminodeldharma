/**
 * Camino del Dharma — Minimal JS
 * Only mobile nav toggle. Refs: 05-arquitectura-informacion-navegacion, 19-accesibilidad-estandares
 */
(function () {
  var toggle = document.getElementById('nav-toggle');
  var menu = document.getElementById('nav-menu');
  if (!toggle || !menu) return;

  toggle.setAttribute('aria-expanded', 'false');
  toggle.addEventListener('click', function () {
    var isOpen = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  function closeMenuOnDesktop() {
    if (window.matchMedia('(min-width: 768px)').matches) {
      menu.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
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
    document.documentElement.lang = lang;
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
