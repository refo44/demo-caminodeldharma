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
