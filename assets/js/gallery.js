/**
 * Gallery pagination — only the current page's images are in the DOM and loaded.
 * Uses ?page=N in the URL so the current page is shareable and back/forward work.
 */
(function () {
  var IMAGES_PER_PAGE = 12;
  var grid = document.getElementById('gallery-grid');
  var paginationEl = document.getElementById('gallery-pagination');
  var dataEl = document.getElementById('gallery-data');
  if (!grid || !paginationEl || !dataEl) return;

  var images = [];
  try {
    images = JSON.parse(dataEl.textContent || '[]');
  } catch (e) {
    return;
  }

  function getPageFromUrl() {
    var params = new URLSearchParams(window.location.search);
    var p = parseInt(params.get('page'), 10);
    return isNaN(p) || p < 1 ? 1 : p;
  }

  function totalPages() {
    return Math.max(1, Math.ceil(images.length / IMAGES_PER_PAGE));
  }

  function renderGrid(page) {
    var start = (page - 1) * IMAGES_PER_PAGE;
    var slice = images.slice(start, start + IMAGES_PER_PAGE);
    grid.setAttribute('aria-busy', 'true');
    grid.innerHTML = '';
    slice.forEach(function (item) {
      var img = document.createElement('img');
      img.src = item.src;
      img.alt = item.alt || '';
      img.width = 400;
      img.height = 400;
      img.loading = 'lazy';
      grid.appendChild(img);
    });
    grid.setAttribute('aria-busy', 'false');
  }

  function getUrlForPage(page) {
    var url = new URL(window.location.href);
    if (page === 1) {
      url.searchParams.delete('page');
    } else {
      url.searchParams.set('page', String(page));
    }
    return url.toString();
  }

  function pushState(page) {
    window.history.pushState({ galleryPage: page }, '', getUrlForPage(page));
  }

  function replaceState(page) {
    window.history.replaceState({ galleryPage: page }, '', getUrlForPage(page));
  }

  function createChevronIcon(direction) {
    var path = direction === 'prev'
      ? 'M13 9a1 1 0 0 1-1-1V5.061a1 1 0 0 0-1.811-.75l-6.835 6.836a1.207 1.207 0 0 0 0 1.707l6.835 6.835a1 1 0 0 0 1.811-.75V16a1 1 0 0 1 1-1h6a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z'
      : 'M11 9a1 1 0 0 0 1-1V5.061a1 1 0 0 1 1.811-.75l6.836 6.836a1.207 1.207 0 0 1 0 1.707l-6.836 6.835a1 1 0 0 1-1.811-.75V16a1 1 0 0 0-1-1H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1z';
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', 'lucide-icon gallery-pagination-icon');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    pathEl.setAttribute('d', path);
    svg.appendChild(pathEl);
    return svg;
  }

  function renderPagination(currentPage) {
    var total = totalPages();
    var frag = document.createDocumentFragment();

    var prevLabel = 'Anterior';
    var nextLabel = 'Siguiente';
    if (currentPage > 1) {
      var prevLink = document.createElement('a');
      prevLink.href = currentPage === 2 ? '?' : '?page=' + (currentPage - 1);
      prevLink.className = 'gallery-pagination-prev';
      prevLink.setAttribute('aria-label', prevLabel);
      prevLink.appendChild(createChevronIcon('prev'));
      prevLink.addEventListener('click', function (e) {
        e.preventDefault();
        goToPage(currentPage - 1);
      });
      frag.appendChild(prevLink);
    }

    var info = document.createElement('span');
    info.className = 'gallery-pagination-info';
    info.textContent = 'Página ' + currentPage + ' de ' + total;
    frag.appendChild(info);

    if (currentPage < total) {
      var nextLink = document.createElement('a');
      nextLink.href = '?page=' + (currentPage + 1);
      nextLink.className = 'gallery-pagination-next';
      nextLink.setAttribute('aria-label', nextLabel);
      nextLink.appendChild(createChevronIcon('next'));
      nextLink.addEventListener('click', function (e) {
        e.preventDefault();
        goToPage(currentPage + 1);
      });
      frag.appendChild(nextLink);
    }

    paginationEl.innerHTML = '';
    paginationEl.appendChild(frag);

    var pageLinks = document.createElement('div');
    pageLinks.className = 'gallery-pagination-pages';
    for (var i = 1; i <= total; i++) {
      var link = document.createElement('a');
      link.href = i === 1 ? '?' : '?page=' + i;
      link.textContent = i;
      if (i === currentPage) {
        link.className = 'gallery-pagination-num is-current';
        link.setAttribute('aria-current', 'page');
      } else {
        link.className = 'gallery-pagination-num';
        (function (p) {
          link.addEventListener('click', function (e) {
            e.preventDefault();
            goToPage(p);
          });
        })(i);
      }
      pageLinks.appendChild(link);
    }
    paginationEl.appendChild(pageLinks);
  }

  function goToPage(page) {
    var total = totalPages();
    var p = Math.max(1, Math.min(page, total));
    pushState(p);
    renderGrid(p);
    renderPagination(p);
    grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function init() {
    var page = getPageFromUrl();
    replaceState(page);
    renderGrid(page);
    renderPagination(page);
  }

  window.addEventListener('popstate', function (e) {
    var page = (e.state && e.state.galleryPage) || getPageFromUrl();
    renderGrid(page);
    renderPagination(page);
  });

  init();
})();
