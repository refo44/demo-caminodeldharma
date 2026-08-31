/**
 * Album gallery pagination.
 * Each album owns its grid, controls, status and shareable page query parameter.
 */
(function () {
  var IMAGES_PER_PAGE = 12;
  var albumsRoot = document.getElementById('gallery-albums');
  var imagesDataElement = document.getElementById('gallery-data');
  var albumsDataElement = document.getElementById('gallery-albums-data');
  if (!albumsRoot || !imagesDataElement || !albumsDataElement) return;

  var images;
  var albumDefinitions;

  try {
    images = JSON.parse(imagesDataElement.textContent || '[]');
    albumDefinitions = JSON.parse(albumsDataElement.textContent || '[]');
  } catch (error) {
    return;
  }

  function pageParameter(albumId) {
    return albumId + '-page';
  }

  function totalPages(album) {
    return Math.max(1, Math.ceil(album.images.length / IMAGES_PER_PAGE));
  }

  function pageFromUrl(album) {
    var params = new URLSearchParams(window.location.search);
    var page = parseInt(params.get(pageParameter(album.id)), 10);
    if (isNaN(page) || page < 1) return 1;
    return Math.min(page, totalPages(album));
  }

  function urlForPage(album, page) {
    var url = new URL(window.location.href);
    var parameter = pageParameter(album.id);

    if (page === 1) {
      url.searchParams.delete(parameter);
    } else {
      url.searchParams.set(parameter, String(page));
    }

    url.hash = album.id;
    return url.toString();
  }

  function createChevronIcon(direction) {
    var path = direction === 'prev'
      ? 'M13 9a1 1 0 0 1-1-1V5.061a1 1 0 0 0-1.811-.75l-6.835 6.836a1.207 1.207 0 0 0 0 1.707l6.835 6.835a1 1 0 0 0 1.811-.75V16a1 1 0 0 1 1-1h6a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z'
      : 'M11 9a1 1 0 0 0 1-1V5.061a1 1 0 0 1 1.811-.75l6.836 6.836a1.207 1.207 0 0 1 0 1.707l-6.836 6.835a1 1 0 0 1-1.811-.75V16a1 1 0 0 0-1-1H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1z';
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    var pathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path');

    svg.setAttribute('class', 'lucide-icon gallery-pagination-icon');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    pathElement.setAttribute('d', path);
    svg.appendChild(pathElement);

    return svg;
  }

  function createAlbum(definition) {
    var albumImages = images.slice(definition.start, definition.end);
    var section = document.createElement('section');
    var heading = document.createElement('h2');
    var grid = document.createElement('div');
    var pagination = document.createElement('nav');
    var status = document.createElement('p');

    section.id = definition.id;
    section.className = 'gallery-album';
    section.setAttribute('aria-labelledby', definition.id + '-heading');

    heading.id = definition.id + '-heading';
    heading.textContent = definition.title;

    grid.className = 'gallery-grid';
    grid.setAttribute('aria-busy', 'false');
    grid.setAttribute('aria-label', 'Imágenes de ' + definition.title);

    pagination.className = 'gallery-pagination';
    pagination.setAttribute('aria-label', 'Paginación de ' + definition.title);

    status.className = 'visually-hidden';
    status.setAttribute('aria-live', 'polite');

    section.appendChild(heading);
    section.appendChild(grid);
    section.appendChild(pagination);
    section.appendChild(status);
    albumsRoot.appendChild(section);

    return {
      id: definition.id,
      title: definition.title,
      images: albumImages,
      section: section,
      grid: grid,
      pagination: pagination,
      status: status
    };
  }

  /**
   * El grid nunca muestra el original: las teselas miden ~152px (2 col) a ~285px
   * (4 col), asi que se sirven las miniaturas de assets/images/galeria/thumbs/.
   * Servir los originales suponia ~2 MB por pagina de 12 (PERF-001).
   * "../assets/images/galeria/galeria-01.jpg" -> ".../thumbs/galeria-01{,-300}.{webp,jpg}"
   */
  function thumbBase(src) {
    var slash = src.lastIndexOf('/');
    var dot = src.lastIndexOf('.');
    if (slash === -1 || dot === -1 || dot < slash) return null;
    return src.slice(0, slash) + '/thumbs/' + src.slice(slash + 1, dot);
  }

  function createThumb(item) {
    var base = thumbBase(item.src);
    var image = document.createElement('img');

    image.alt = item.alt || '';
    image.width = 600;
    image.height = 600;
    image.loading = 'lazy';
    image.decoding = 'async';

    // Sin miniatura derivable, se cae al original: peor peso, pero nunca un hueco roto.
    if (!base) {
      image.src = item.src;
      return image;
    }

    var picture = document.createElement('picture');
    var source = document.createElement('source');

    source.type = 'image/webp';
    source.srcset = base + '-300.webp 300w, ' + base + '.webp 600w';
    source.sizes = '(min-width: 1024px) 285px, (min-width: 768px) 33vw, 50vw';
    image.src = base + '.jpg';

    picture.appendChild(source);
    picture.appendChild(image);
    return picture;
  }

  function renderGrid(album, page) {
    var start = (page - 1) * IMAGES_PER_PAGE;
    var pageImages = album.images.slice(start, start + IMAGES_PER_PAGE);
    var fragment = document.createDocumentFragment();

    album.grid.setAttribute('aria-busy', 'true');
    pageImages.forEach(function (item) {
      fragment.appendChild(createThumb(item));
    });
    album.grid.replaceChildren(fragment);
    album.grid.setAttribute('aria-busy', 'false');
  }

  function createPageLink(album, page, currentPage) {
    var link = document.createElement('a');
    link.href = urlForPage(album, page);
    link.textContent = page;
    link.className = 'gallery-pagination-num';

    if (page === currentPage) {
      link.classList.add('is-current');
      link.setAttribute('aria-current', 'page');
    } else {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        goToPage(album, page);
      });
    }

    return link;
  }

  function createDirectionLink(album, page, direction) {
    var link = document.createElement('a');
    link.href = urlForPage(album, page);
    link.className = direction === 'prev'
      ? 'gallery-pagination-prev'
      : 'gallery-pagination-next';
    link.setAttribute('aria-label', direction === 'prev' ? 'Anterior' : 'Siguiente');
    link.appendChild(createChevronIcon(direction));
    link.addEventListener('click', function (event) {
      event.preventDefault();
      goToPage(album, page);
    });
    return link;
  }

  function renderPagination(album, currentPage) {
    var total = totalPages(album);
    var controls = document.createDocumentFragment();
    var info = document.createElement('span');
    var pageLinks = document.createElement('div');

    if (currentPage > 1) {
      controls.appendChild(createDirectionLink(album, currentPage - 1, 'prev'));
    }

    info.className = 'gallery-pagination-info';
    info.textContent = 'Página ' + currentPage + ' de ' + total;
    controls.appendChild(info);

    if (currentPage < total) {
      controls.appendChild(createDirectionLink(album, currentPage + 1, 'next'));
    }

    pageLinks.className = 'gallery-pagination-pages';
    for (var page = 1; page <= total; page += 1) {
      pageLinks.appendChild(createPageLink(album, page, currentPage));
    }
    controls.appendChild(pageLinks);

    album.pagination.replaceChildren(controls);
    album.status.textContent = album.title + ': página ' + currentPage + ' de ' + total + '.';
  }

  function goToPage(album, requestedPage) {
    var page = Math.max(1, Math.min(requestedPage, totalPages(album)));
    window.history.pushState({}, '', urlForPage(album, page));
    renderGrid(album, page);
    renderPagination(album, page);
    album.section.scrollIntoView({ behavior: 'smooth', block: 'start' });

    var currentPageLink = album.pagination.querySelector('.gallery-pagination-num.is-current');
    if (currentPageLink) currentPageLink.focus();
  }

  var albums = albumDefinitions.map(createAlbum);

  function renderFromUrl() {
    albums.forEach(function (album) {
      var page = pageFromUrl(album);
      renderGrid(album, page);
      renderPagination(album, page);
    });
  }

  window.history.replaceState({}, '', window.location.href);
  window.addEventListener('popstate', renderFromUrl);
  renderFromUrl();
})();
