/**
 * Camino del Dharma (theme FSE) - eventos calendar grid tooltips.
 * Ported verbatim from static/assets/js/calendar.js (WU-07); the
 * add-to-calendar dialog half of that file lives in calendar-dialog.js
 * (WU-08A).
 */
/**
 * Month-grid tooltips (eventos). Hover and :focus-visible are CSS-only.
 * Finger (pointer: coarse): first tap reveals the name; the visual hint
 * “Toca de nuevo para ver el evento.” appears under the grid (aria-hidden).
 * Keyboard (event.detail === 0) follows the link on the first activation.
 */
(function () {
  var grid = document.querySelector('.eventos-calendar-grid');
  if (!grid) return;

  var tooltipLinks = grid.querySelectorAll('a[data-tooltip]');
  if (!tooltipLinks.length) return;

  var revealOnTapQuery = window.matchMedia('(pointer: coarse), (hover: none) and (max-width: 767px)');

  function clearVisibleTooltips(exceptLink) {
    tooltipLinks.forEach(function (link) {
      if (link !== exceptLink) link.classList.remove('is-tooltip-visible');
    });
  }

  grid.addEventListener('click', function (event) {
    if (!revealOnTapQuery.matches) return;
    if (event.detail === 0) return;

    var link = event.target.closest('a[data-tooltip]');
    if (!link || !grid.contains(link)) return;

    if (link.classList.contains('is-tooltip-visible')) return;

    event.preventDefault();
    clearVisibleTooltips(link);
    link.classList.add('is-tooltip-visible');
  });

  document.addEventListener('click', function (event) {
    if (event.target.closest('.eventos-calendar-grid a[data-tooltip]')) return;
    clearVisibleTooltips();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') clearVisibleTooltips();
  });
})();
