(function () {
  var shareButtons = document.querySelectorAll('[data-share-title]');
  if (!shareButtons.length || typeof HTMLDialogElement === 'undefined') return;

  var currentShare = null;
  var opener = null;
  var dialog = document.createElement('dialog');
  dialog.className = 'share-dialog';
  dialog.setAttribute('aria-labelledby', 'share-dialog-title');
  dialog.innerHTML =
    '<div class="share-dialog-header">' +
      '<h2 id="share-dialog-title">Compartir</h2>' +
      '<button type="button" class="share-dialog-close">' +
        '<span class="visually-hidden">Cerrar opciones para compartir</span>' +
        '<svg class="lucide-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
      '</button>' +
    '</div>' +
    '<p class="share-dialog-content-title"></p>' +
    '<div class="share-options">' +
      '<a class="share-option" data-share-platform="whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<a class="share-option" data-share-platform="facebook" target="_blank" rel="noopener noreferrer">Facebook <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<a class="share-option" data-share-platform="x" target="_blank" rel="noopener noreferrer">X <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<a class="share-option" data-share-platform="threads" target="_blank" rel="noopener noreferrer">Threads <span class="visually-hidden">(abre en nueva pestaña)</span></a>' +
      '<button type="button" class="share-option" data-copy-link>Copiar enlace</button>' +
    '</div>' +
    '<p class="share-status" aria-live="polite"></p>';
  document.body.appendChild(dialog);

  var closeButton = dialog.querySelector('.share-dialog-close');
  var contentTitle = dialog.querySelector('.share-dialog-content-title');
  var status = dialog.querySelector('.share-status');

  function absoluteUrl(value) {
    return new URL(value, window.location.href).href;
  }

  function getTemplateText(button, templateAttr) {
    var templateId = button.getAttribute(templateAttr);
    if (!templateId) return null;

    var template = document.getElementById(templateId);
    if (!template) return null;

    return template.content.textContent
      .split('\n')
      .map(function (line) { return line.trim(); })
      .join('\n')
      .replace(/\n{3,}/g, '\n\n')
      .trim();
  }

  function resolveCanonicalUrl() {
    var canonical = document.querySelector('link[rel="canonical"]');
    return canonical && canonical.href ? canonical.href : null;
  }

  function resolveShareUrl(button) {
    var shareUrl = button.getAttribute('data-share-url');
    if (shareUrl) {
      return absoluteUrl(shareUrl);
    }

    var canonicalUrl = resolveCanonicalUrl();
    if (canonicalUrl) {
      return canonicalUrl;
    }

    return window.location.href;
  }

  function injectShareUrl(text, url) {
    if (!text) return url;
    return text.replace(/\{\{SHARE_URL\}\}/g, url);
  }

  function getShareData(button) {
    var title = button.getAttribute('data-share-title');
    var url = resolveShareUrl(button);
    var whatsappTemplate = getTemplateText(button, 'data-share-whatsapp-template');
    var xText = injectShareUrl(getTemplateText(button, 'data-share-x-template') || title, url);
    var threadsText = injectShareUrl(
      getTemplateText(button, 'data-share-threads-template') || xText,
      url
    );

    return {
      title: title,
      description: button.getAttribute('data-share-description'),
      url: url,
      whatsappMessage: whatsappTemplate ? injectShareUrl(whatsappTemplate, url) : (title + ' ' + url),
      xText: xText,
      threadsText: threadsText
    };
  }

  function setIntent(platform, href) {
    dialog.querySelector('[data-share-platform="' + platform + '"]').href = href;
  }

  function openDialog(button) {
    currentShare = getShareData(button);
    opener = button;
    status.textContent = '';
    contentTitle.textContent = currentShare.title;

    var encodedUrl = encodeURIComponent(currentShare.url);
    var encodedWhatsAppText = encodeURIComponent(currentShare.whatsappMessage);
    var encodedXText = encodeURIComponent(currentShare.xText);
    var encodedThreadsText = encodeURIComponent(currentShare.threadsText);

    setIntent('whatsapp', 'https://api.whatsapp.com/send?text=' + encodedWhatsAppText);
    setIntent('facebook', 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl);
    setIntent('x', 'https://x.com/intent/post?text=' + encodedXText + '&url=' + encodedUrl);
    setIntent('threads', 'https://www.threads.com/intent/post?text=' + encodedThreadsText + '&url=' + encodedUrl);

    dialog.showModal();
    dialog.scrollTop = 0;
  }

  function copyWithFallback(text) {
    var textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    var didCopy = document.execCommand('copy');
    textarea.remove();
    return didCopy;
  }

  async function copyLink() {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(currentShare.url);
      } else if (!copyWithFallback(currentShare.url)) {
        throw new Error('Copy unavailable');
      }
      status.textContent = 'Enlace copiado.';
    } catch (error) {
      status.textContent = 'No fue posible copiar automáticamente. Copia este enlace: ' + currentShare.url;
    }
  }

  shareButtons.forEach(function (button) {
    button.addEventListener('click', function () { openDialog(button); });
  });

  closeButton.addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('click', function (event) {
    if (event.target === dialog) dialog.close();
  });
  dialog.addEventListener('close', function () {
    if (opener) opener.focus();
  });
  dialog.querySelector('[data-copy-link]').addEventListener('click', copyLink);
})();
