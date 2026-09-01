# demo-caminodeldharma

Sitio **estático en producción** de la **Comunidad Buddhista Camino del Dharma** (Colombia). Recibe visitas en `https://caminodeldharma.org`. Incluye inicio, comunidad, linaje, práctica, eventos, galería, blog, contribuir, contacto y privacidad.

El nombre histórico «maqueta» en ADRs antiguos (ADR 0001) significa **base definitiva**, no prototipo desechable. Eventos, posts y galería hardcodeados en HTML/JSON son **contenido de producción** (ADR 0034).

Hasta el corte, el sitio publicado gobierna el copy, contenido y presentación; el `VERSION` y commit
vigentes del repo son el insumo de extracción y deben compararse con Hostinger (OWN-006/007,
ADR 0034/0040). Base para una futura migración **directa** a WordPress Full Site Editing (block
theme), sin etapa de theme clásico PHP (ADR 0029, ADR 0032).

## Tecnologías

- HTML5 semántico
- CSS3 (tokens, diseño responsivo)
- JavaScript mínimo con `defer` (menú, galería, accesibilidad)
- Stylelint como validación obligatoria del CSS
- Un único paso de build: `npm run build:css` minifica `static/assets/css/main.css` → `static/assets/css/main.min.css` (lo que enlazan las páginas). El resto son archivos estáticos listos para servir; npm se usa solo para herramientas de desarrollo

## Git y contribución

**Trunk-based development** (ADR 0043): `main` está **protegida**; no hay push directo. Todo cambio entra por **Pull Request** desde una rama corta.

- **Ramas:** [Conventional Branch](https://conventionalbranch.org/) — `feature/…`, `fix/…`, `chore/…`, `cursor/…`, etc.
- **Commits:** [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) en inglés — `feat(scope): summary`
- **CI obligatorio en merge:** jobs `php` y `css` de `.github/workflows/test.yml`
- **Etiquetas del PR:** al menos una relevante por PR; varias si el cambio abarca más de un ámbito

Guía completa: [`docs/git-workflow.md`](docs/git-workflow.md). Contribución: [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Cómo ver el sitio

Abrir `static/index.html` en el navegador o servir la carpeta `static/` con un servidor local:

```bash
npx serve static
```

O sirviendo `static/` con el servidor de tu IDE (Live Server, etc.).

## Validación del CSS

Instalar las dependencias una vez:

```bash
npm install
```

Ejecutar Stylelint:

```bash
npm run lint:css
```

Este comando debe ejecutarse después de cualquier cambio en `static/assets/css/` y antes de cerrar una tarea, crear un commit o desplegar. La validación debe finalizar sin errores.

Stylelint valida **solo el fuente** (`main.css`); `main.min.css` está en `.stylelintignore` porque es un artefacto generado.

## Build del CSS

Las páginas enlazan `assets/css/main.min.css` (ruta relativa al sitio), que se genera desde `static/assets/css/main.css`:

```bash
npm run build:css
```

**Regla:** editar siempre `main.css` (el fuente) y regenerar. Nunca editar `main.min.css` a mano: el siguiente build lo sobrescribe. Ambos se versionan en git, porque el despliegue es un ZIP manual y en el servidor no se ejecuta ningún build.

## Estructura del proyecto

Monorepo de Fase 3 (ADR 0014): sitio estático desplegable en `static/`; WordPress first-party en `wordpress/`.

- **`static/` (producción):** `index.html`, `404.html`, `robots.txt`, `sitemap.xml`, `sitemap.xsl`, `llms.txt`, `.htaccess`, `favicon.ico`, `favicon.svg`
- **Secciones:** `static/comunidad/`, `static/linaje/`, `static/practica/`, `static/eventos/`, `static/galeria/`, `static/contacto/`, `static/donaciones/`, `static/blog/`, `static/privacidad/`
- **Assets:** `static/assets/css/`, `static/assets/js/`, `static/assets/images/`, `static/assets/favicon/`, `static/assets/fonts/`, `static/assets/audio/`
- **WordPress (solo repositorio, en desarrollo):** `wordpress/wp-content/themes/camino-del-dharma/`, `wordpress/wp-content/plugins/camino-del-dharma-core/`
- **Versionado (solo repositorio):** `CHANGELOG.md` (historial de despliegues), `VERSION` (versión actual; ver [`VERSION`](VERSION))
- **Documentación (solo repositorio):** `docs/` (identidad, mapa de pantallas, copy, URLs, orden de implementación)
- **Scripts (solo repositorio):** `scripts/` (ver abajo)

### SEO e indexación

- `robots.txt` — acceso de rastreadores y referencia al sitemap
- `sitemap.xml` — URLs indexables con `<lastmod>`; vista legible vía `sitemap.xsl`
- `llms.txt` — índice curado para agentes de IA ([convención llmstxt.org](https://llmstxt.org/)); no sustituye al sitemap

El sitio es web tradicional (sin PWA ni `site.webmanifest`). Metadatos SEO, Open Graph, JSON-LD y `<meta name="theme-color">` están en el `<head>` de cada HTML.

### Rendimiento (PageSpeed)

En el home y donde aplique:

- Imágenes WebP con `<picture>` (JPEG como fallback)
- `preload` del hero y de la fuente Inter 400
- `fetchpriority="high"` en la imagen LCP del hero
- `loading="lazy"` y `decoding="async"` en imágenes bajo el pliegue
- **Una sola hoja de estilos bloqueante:** `static/assets/css/main.css`. `normalize.css` está incorporado al inicio de `main.css` (sección 0.0), no se enlaza aparte. Evita una segunda petición que bloqueaba el render y encadenaba la ruta crítica (`documento → normalize.css`)

Variantes WebP viven junto a los JPEG en `static/assets/images/` (p. ej. `static/assets/images/inicio/*.webp`). Las miniaturas de la galería del home viven en `static/assets/images/galeria/thumbs/` (600×600, jpg + webp); los originales de `static/assets/images/galeria/` se conservan porque el lightbox de `/galeria` los necesita a tamaño completo. Incluir ambos formatos en el ZIP de despliegue.

## Despliegue en Hostinger

Despliegue **manual** (ADR 0015). CI/CD pospuesto (ADR 0016). Historial en [`CHANGELOG.md`](CHANGELOG.md).

**No subir** el repositorio completo a `public_html` — solo el sitio estático.

### Fase 3 (actual): sitio en `static/`

Antes de cada despliegue:

1. Actualizar `static/sitemap.xml` (`<lastmod>` de páginas con contenido indexable modificado; no hace falta si solo cambia infraestructura CSS/JS sin alterar el copy visible).
2. Actualizar [`VERSION`](VERSION) y [`CHANGELOG.md`](CHANGELOG.md); commit de release en git. Sincronizar también `version` en `package.json` (`npm version --no-git-tag-version $(cat VERSION)`); [`VERSION`](VERSION) es la fuente canónica y `package.json` debe seguirla.
3. `npm run lint:css` (sin errores) y `npm run build:css` (regenera `main.min.css`, que es lo que sirven las páginas). Si se tocó `main.css` y no se regenera, el ZIP sale con CSS viejo.
4. Etiquetar la versión en git (tag anotado, convención `vX.Y.Z` alineada con `VERSION`):

```bash
git tag -a "v$(cat VERSION)" -m "Release v$(cat VERSION)"
```

Para publicar el tag en el remoto: `git push origin "v$(cat VERSION)"`.

1. Generar ZIP de producción **desde el contenido de `static/`** (**solo en el Escritorio**, no dentro del repositorio; no incluir `docs/`, `wordpress/`, `scripts/`):

```bash
VERSION=$(cat VERSION)
cd static
zip -r "$HOME/Desktop/camino-del-dharma-v${VERSION}.zip" \
  index.html 404.html robots.txt sitemap.xml sitemap.xsl llms.txt .htaccess favicon.ico favicon.svg \
  assets comunidad linaje practica eventos galeria contacto donaciones blog privacidad \
  -x "*.DS_Store" -x "*__MACOSX*"
cd ..
```

Los archivos `camino-del-dharma-v*.zip` están en `.gitignore`; no copiarlos ni commitearlos al repo.

1. Subir y extraer en `public_html` (File Manager de Hostinger).

> Hasta la v1.0.35 el sitio vivía en la raíz del repo y el ZIP se generaba desde ahí (Fase 2,
> histórico). La reorganización raíz → `static/` es el primer paso de Fase 3 (ADR 0014).

WordPress se despliega en **otra instancia Hostinger, sin dominio custom** (staging), hasta el switch. Producción sigue siendo el estático en `caminodeldharma.org`. No instalar WordPress sobre ese `public_html` hasta el corte. Activar el theme **no** crea Pages ni sustituye el checklist de cutover.

## Scripts

Desde la raíz del repositorio:

| Script | Descripción | Requisito |
| -------- | ------------- | ------------ |
| `scripts/optimize-images.sh` | Optimiza JPEG/PNG en `static/assets/images/` (tamaño, calidad, metadatos) | [ImageMagick](https://imagemagick.org/) (`brew install imagemagick`) |
| `scripts/rename-gallery-to-kebab.sh` | Renombra imágenes en `static/assets/images/galeria/` a `galeria-01.jpg`, `galeria-02.jpg`, … | Ninguno |
| `scripts/build-fonts.sh` | Regenera `marlowe-escapade-subset.woff2` (subset a "Camino del Dharma", 52,1 KB → 3,4 KB) | `pyftsubset` (`pip install 'fonttools[woff]' brotli`) |

Ejemplo:

```bash
./scripts/optimize-images.sh
./scripts/rename-gallery-to-kebab.sh
```

## Documentación

En `docs/` están la identidad corporativa, mapa de pantallas, arquitectura de información, copy, árbol de URLs, estructura de archivos estáticos, el **orden de implementación** (incl. fases WordPress) y el registro de **decisiones arquitectónicas** (`docs/adr/`). Índice: `docs/00-orden-documentos.md`. Inventario de producción y contrato de migración: `docs/inventario-contenido-produccion-static.md`, `docs/contrato-migracion-static-wordpress.md`. Decisiones de dueño: `docs/backlog-decisiones-owner-migracion.md` (Fase 3 cerrada; `POST-*` fases posteriores). Agentes: `AGENTS.md`, `CLAUDE.md`.

Colaboración, lint, pruebas y despliegue: `CONTRIBUTING.md`. **Git (trunk-based, Conventional Branch/Commits):**
[`docs/git-workflow.md`](docs/git-workflow.md) (ADR 0043). Guía de pruebas (TDD, wp-phpunit,
FSE, Sonar): [`docs/guia-pruebas-plugin-theme-fse.md`](docs/guia-pruebas-plugin-theme-fse.md)
(ADR 0038). Licencia del código: `LICENSE`. Seguridad: `SECURITY.md`.

## Estado actual vs plan futuro

| | |
| --- | --- |
| **Actual (producción)** | Sitio **estático live**. HTML en `static/` (monorepo ADR 0014, Fase 3 iniciada). Hostinger via ZIP (ADR 0015). Eventos/blog/galería en HTML = producción (ADR 0034). WordPress **en desarrollo** (`wordpress/` first-party: plugin `camino-del-dharma-core` + theme FSE `camino-del-dharma`; pendiente corte a producción). |
| **Fase 3 (en curso)** | Ruta **única:** maqueta estática → **FSE / block theme** (ADR 0029). **No** hay theme clásico PHP intermedio. Plugin `camino-del-dharma-core` (ADR 0024). Staging separado hasta el corte. Estado durable: `.audit/fase3-execution-state.md`. |

La migración no está completa porque un theme esté desplegado. Contrato: [`docs/contrato-migracion-static-wordpress.md`](docs/contrato-migracion-static-wordpress.md). Inventario: [`docs/inventario-contenido-produccion-static.md`](docs/inventario-contenido-produccion-static.md). Matriz: [`docs/matriz-migracion-static-wordpress.md`](docs/matriz-migracion-static-wordpress.md). Cutover: [`docs/cutover-checklist-wordpress.md`](docs/cutover-checklist-wordpress.md).

**Tras el corte:** un ZIP de HTML estático **no** debe escribir sobre el document root de WordPress (`STATIC DEPLOY ≠ WORDPRESS CODE DEPLOY ≠ WORDPRESS CONTENT`, ADR 0013).

## Próximos pasos

Según `docs/17-orden-implementacion.md` (§ Transición): producción = sitio estático (`static/` desde el arranque de Fase 3). WordPress en staging paralelo. Despliegues **manuales**. Ledger de diferencias: [`docs/migracion-static-wordpress.md`](docs/migracion-static-wordpress.md). ADR: [`docs/adr/README.md`](docs/adr/README.md). Agentes: [`AGENTS.md`](AGENTS.md).

## Autor

**Comunidad Buddhista Camino del Dharma**  
Personería Jurídica Especial – Ministerio del Interior de Colombia  

- Correo: <caminodeldharma1@gmail.com>  
- WhatsApp: +57 320 662 7608  

**Maqueta estática (código y estructura):** Rafael Figueredo Oropeza.  

## Licencia

**Código (HTML, CSS, JavaScript, scripts):** [MIT](https://opensource.org/licenses/MIT). Puedes usar, modificar y redistribuir el código bajo los términos de la licencia MIT.

**Contenido y recursos:** © Comunidad Buddhista Camino del Dharma. Todos los derechos reservados. Los textos, imágenes y materiales de identidad son de uso exclusivo de la comunidad.
