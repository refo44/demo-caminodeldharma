(function () {
  var calendarButtons = document.querySelectorAll('[data-calendar-title]');
  if (!calendarButtons.length || typeof HTMLDialogElement === 'undefined') return;

  var opener = null;
  var dialog = document.createElement('dialog');
  dialog.className = 'share-dialog calendar-dialog';
  dialog.setAttribute('aria-labelledby', 'calendar-dialog-title');
  dialog.innerHTML =
    '<div class="share-dialog-header">' +
      '<h2 id="calendar-dialog-title">Añadir al calendario</h2>' +
      '<button type="button" class="share-dialog-close">' +
        '<span class="visually-hidden">Cerrar opciones de calendario</span>' +
        '<svg class="lucide-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
      '</button>' +
    '</div>' +
    '<p class="share-dialog-content-title"></p>' +
    '<div class="share-options">' +
      '<a class="share-option" data-calendar-platform="google" target="_blank" rel="noopener noreferrer">Google Calendar <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<a class="share-option" data-calendar-platform="outlook" target="_blank" rel="noopener noreferrer">Outlook <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<a class="share-option" data-calendar-platform="apple">Apple Calendar</a>' +
      '<a class="share-option" data-calendar-platform="download">Descargar archivo .ics</a>' +
    '</div>';
  document.body.appendChild(dialog);

  var closeButton = dialog.querySelector('.share-dialog-close');
  var contentTitle = dialog.querySelector('.share-dialog-content-title');
  var googleLink = dialog.querySelector('[data-calendar-platform="google"]');
  var outlookLink = dialog.querySelector('[data-calendar-platform="outlook"]');
  var appleLink = dialog.querySelector('[data-calendar-platform="apple"]');
  var downloadLink = dialog.querySelector('[data-calendar-platform="download"]');

  function absoluteUrl(value) {
    return new URL(value, window.location.href).href;
  }

  function resolveEventUrl(button) {
    var eventUrl = button.getAttribute('data-calendar-event-url');
    if (!eventUrl) {
      return window.location.href;
    }

    return absoluteUrl(eventUrl);
  }

  function resolvePosterUrl(button) {
    var posterPath = button.getAttribute('data-calendar-poster');
    if (!posterPath) return '';
    return absoluteUrl(posterPath);
  }

  function resolveCalendarDescription(button) {
    var description = button.getAttribute('data-calendar-description') || '';
    return description
      .replace(/\{\{EVENT_URL\}\}/g, resolveEventUrl(button))
      .replace(/\{\{POSTER_URL\}\}/g, resolvePosterUrl(button));
  }

  function getCalendarData(button) {
    return {
      title: button.getAttribute('data-calendar-title'),
      start: button.getAttribute('data-calendar-start'),
      end: button.getAttribute('data-calendar-end'),
      description: resolveCalendarDescription(button),
      location: button.getAttribute('data-calendar-location') || '',
      icsPath: button.getAttribute('data-calendar-ics')
    };
  }

  function toIsoDate(value) {
    return value.slice(0, 4) + '-' + value.slice(4, 6) + '-' + value.slice(6, 8);
  }

  function exclusiveEndToInclusiveIso(exclusiveEnd) {
    var year = Number(exclusiveEnd.slice(0, 4));
    var month = Number(exclusiveEnd.slice(4, 6)) - 1;
    var day = Number(exclusiveEnd.slice(6, 8));
    var date = new Date(Date.UTC(year, month, day));
    date.setUTCDate(date.getUTCDate() - 1);
    return date.toISOString().slice(0, 10);
  }

  function buildGoogleCalendarUrl(event) {
    var params = new URLSearchParams({
      action: 'TEMPLATE',
      text: event.title,
      dates: event.start + '/' + event.end,
      details: event.description,
      location: event.location
    });

    return 'https://calendar.google.com/calendar/render?' + params.toString();
  }

  function buildOutlookUrl(event) {
    var params = new URLSearchParams({
      path: '/calendar/action/compose',
      rru: 'addevent',
      subject: event.title,
      body: event.description,
      location: event.location,
      startdt: toIsoDate(event.start),
      enddt: exclusiveEndToInclusiveIso(event.end),
      allday: 'true'
    });

    return 'https://outlook.live.com/calendar/0/deeplink/compose?' + params.toString();
  }

  function icsFilename(icsPath) {
    var segments = icsPath.split('/');
    return segments[segments.length - 1] || 'evento.ics';
  }

  function openDialog(button) {
    var event = getCalendarData(button);
    opener = button;
    contentTitle.textContent = event.title;

    var icsUrl = absoluteUrl(event.icsPath);
    var filename = icsFilename(event.icsPath);

    googleLink.href = buildGoogleCalendarUrl(event);
    outlookLink.href = buildOutlookUrl(event);
    appleLink.href = icsUrl;
    appleLink.removeAttribute('download');
    downloadLink.href = icsUrl;
    downloadLink.setAttribute('download', filename);

    dialog.showModal();
    dialog.scrollTop = 0;
  }

  calendarButtons.forEach(function (button) {
    button.addEventListener('click', function () { openDialog(button); });
  });

  closeButton.addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('click', function (event) {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', function () {
    if (opener) opener.focus();
  });
})();
