# Respaldo — página de contacto y formulario (sitio estático)

Copia de referencia para **restaurar o replicar** la página `/contacto` y su formulario cuando se implemente WordPress (o si la auditoría TASK-0002 retira el formulario en el estático).

## Metadatos del snapshot

| Campo | Valor |
|-------|--------|
| Fecha del respaldo | 2026-07-19 |
| Commit fuente | `8430f96cac500d276919a3cefccdda085263a478` |
| Archivo vivo | `contacto/index.html` |
| Estilos vivos | `assets/css/main.css` (secciones Forms, page-contact, botones, iconos) |
| Imagen de la sección | `assets/images/contacto/contacto-comunidad.jpg` |
| URL en producción | https://caminodeldharma.org/contacto |

## Contenido de esta carpeta

| Archivo | Descripción |
|---------|-------------|
| `contacto-index.html` | Página completa tal como está en el repo (header, main, footer, formulario). |
| `contacto-main-content.html` | Solo el bloque `<main>`: copy, imagen, formulario, WhatsApp/correo y redes. |
| `contacto-formulario-estilos.css` | Extracto de `main.css` con variables, formulario, botón Enviar y bloque `.contact-social`. |

## Formulario — especificación

Copy canónico (content-source): intro «La práctica se fortalece cuando se comparte»; campos **Nombre**, **Correo electrónico**, **Mensaje**; botón **Enviar**.

Markup actual (líneas 127–147 de `contacto/index.html`):

- `action="#"` `method="post"` — placeholder estático; **no entrega mensajes** (hallazgo audit FUNC-001).
- Campos: `name="nombre"`, `name="correo"`, `name="mensaje"`; todos `required`.
- IDs: `contact-name`, `contact-email`, `contact-message`.
- Iconos Lucide inline en labels de correo/mensaje y en el botón.

Canales alternativos ya presentes bajo el formulario: WhatsApp `+57 320 662 7608`, `caminodeldharma1@gmail.com`.

## Cómo restaurar en el sitio estático

1. Copiar el bloque del formulario desde `contacto-main-content.html` (o la página entera desde `contacto-index.html`) a `contacto/index.html`.
2. Los estilos siguen en `assets/css/main.css`; no hace falta el extracto salvo como referencia offline.
3. Ajustar rutas relativas si la estructura de carpetas cambia (`../assets/...`).

## Cómo usar en WordPress

1. **HTML:** pegar el contenido de `contacto-main-content.html` en una plantilla de página, bloque HTML personalizado o patrón reutilizable del theme.
2. **CSS:** las reglas de `contacto-formulario-estilos.css` ya viven en el theme como parte de `main.css` (ADR 0009 — tokens y CSS invariantes). Si el theme carga `main.css` completo, no duplicar; si solo necesitas el formulario, importar este extracto o fusionar las secciones indicadas en el comentario del archivo.
3. **Formulario funcional:** sustituir `action="#"` por el endpoint de Contact Form 7, WPForms, `admin-post.php` o handler del theme; conservar labels, `for`/`id` y `aria-label="Formulario de contacto"` para accesibilidad.
4. **Imagen:** copiar o referenciar `assets/images/contacto/contacto-comunidad.jpg` en la biblioteca de medios.

## Relación con la auditoría

- `.audit/findings.jsonl` → **FUNC-001**: formulario no operativo en hosting estático.
- `.audit/implementation/tasks/TASK-0002.md`: retiro temporal del formulario por CTAs (opcional antes de WordPress).
- Este respaldo **no se modifica** cuando cambie el sitio vivo; crear un nuevo snapshot con fecha y commit si hace falta otra versión.

## Documentos relacionados

- `docs/04-mapa-pantallas.md` — pantalla Contacto
- `docs/09-ui-copy-sheet.md` — copy de campos y botón
- `docs/03-wordpress-content-model.md` — modelo de contenido
- `docs/migracion-static-wordpress.md` — migración general
