# demo-caminodeldharma

Maqueta estática del sitio web de la **Comunidad Buddhista Camino del Dharma** (Colombia). Incluye las páginas de inicio, comunidad, linaje, práctica, eventos, galería, blog, contribuir y contacto. El contenido y la estructura siguen la documentación en `docs/`. Pensado como base para una futura adaptación a WordPress.

## Tecnologías

- HTML5 semántico
- CSS3 (tokens, diseño responsivo)
- JavaScript mínimo con `defer` (menú, galería, accesibilidad)
- Stylelint como validación obligatoria del CSS
- Un único paso de build: `npm run build:css` minifica `assets/css/main.css` → `assets/css/main.min.css` (lo que enlazan las páginas). El resto son archivos estáticos listos para servir; npm se usa solo para herramientas de desarrollo

## Cómo ver el sitio

Abrir `index.html` en el navegador o servir la carpeta con un servidor local:

```bash
npx serve .
```

O desde la raíz del proyecto con el servidor de tu IDE (Live Server, etc.).

## Validación del CSS

Instalar las dependencias una vez:

```bash
npm install
```

Ejecutar Stylelint:

```bash
npm run lint:css
```

Este comando debe ejecutarse después de cualquier cambio en `assets/css/` y antes de cerrar una tarea, crear un commit o desplegar. La validación debe finalizar sin errores.

Stylelint valida **solo el fuente** (`main.css`); `main.min.css` está en `.stylelintignore` porque es un artefacto generado.

## Build del CSS

Las páginas enlazan `assets/css/main.min.css`, que se genera desde `assets/css/main.css`:

```bash
npm run build:css
```

**Regla:** editar siempre `main.css` (el fuente) y regenerar. Nunca editar `main.min.css` a mano: el siguiente build lo sobrescribe. Ambos se versionan en git, porque el despliegue es un ZIP manual y en el servidor no se ejecuta ningún build.

## Estructura del proyecto

- **Raíz (producción):** `index.html`, `404.html`, `robots.txt`, `sitemap.xml`, `sitemap.xsl`, `llms.txt`, `.htaccess`, `favicon.ico`, `favicon.svg`
- **Versionado (solo repositorio):** `CHANGELOG.md` (historial de despliegues), `VERSION` (versión actual; ver [`VERSION`](VERSION))
- **Secciones:** `comunidad/`, `linaje/`, `practica/`, `eventos/`, `galeria/`, `contacto/`, `donaciones/`, `blog/`
- **Assets:** `assets/css/`, `assets/js/`, `assets/images/`, `assets/favicon/`, `assets/fonts/`, `assets/audio/`
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
- **Una sola hoja de estilos bloqueante:** `assets/css/main.css`. `normalize.css` está incorporado al inicio de `main.css` (sección 0.0), no se enlaza aparte. Evita una segunda petición que bloqueaba el render y encadenaba la ruta crítica (`documento → normalize.css`)

Variantes WebP viven junto a los JPEG en `assets/images/` (p. ej. `assets/images/inicio/*.webp`). Las miniaturas de la galería del home viven en `assets/images/galeria/thumbs/` (600×600, jpg + webp); los originales de `assets/images/galeria/` se conservan porque el lightbox de `/galeria` los necesita a tamaño completo. Incluir ambos formatos en el ZIP de despliegue.

## Despliegue en Hostinger

Despliegue **manual** (ADR 0015). CI/CD pospuesto (ADR 0016). Historial en [`CHANGELOG.md`](CHANGELOG.md).

**No subir** el repositorio completo a `public_html` — solo el sitio estático.

### Fase 2 (actual): sitio en la raíz del repo

Antes de cada despliegue:

1. Actualizar `sitemap.xml` (`<lastmod>` de páginas con contenido indexable modificado; no hace falta si solo cambia infraestructura CSS/JS sin alterar el copy visible).
2. Actualizar [`VERSION`](VERSION) y [`CHANGELOG.md`](CHANGELOG.md); commit de release en git. Sincronizar también `version` en `package.json` (`npm version --no-git-tag-version $(cat VERSION)`); [`VERSION`](VERSION) es la fuente canónica y `package.json` debe seguirla.
3. `npm run lint:css` (sin errores) y `npm run build:css` (regenera `main.min.css`, que es lo que sirven las páginas). Si se tocó `main.css` y no se regenera, el ZIP sale con CSS viejo.
4. Etiquetar la versión en git (tag anotado, convención `vX.Y.Z` alineada con `VERSION`):

```bash
git tag -a "v$(cat VERSION)" -m "Release v$(cat VERSION)"
```

Para publicar el tag en el remoto: `git push origin "v$(cat VERSION)"`.

5. Generar ZIP de producción (**solo en el Escritorio**, no dentro del repositorio):

```bash
VERSION=$(cat VERSION)
zip -r "$HOME/Desktop/camino-del-dharma-v${VERSION}.zip" \
  index.html 404.html robots.txt sitemap.xml sitemap.xsl llms.txt .htaccess favicon.ico favicon.svg \
  assets comunidad linaje practica eventos galeria contacto donaciones blog \
  -x "*.DS_Store" -x "*__MACOSX*"
```

Los archivos `camino-del-dharma-v*.zip` están en `.gitignore`; no copiarlos ni commitearlos al repo.

6. Subir y extraer en `public_html` (File Manager de Hostinger).

### Fase 3 (tras reorg): sitio en `static/`

Mismo procedimiento, pero el ZIP se genera **desde el contenido de `static/`** (no incluir `docs/`, `wordpress/`, `scripts/`). Ver `17-orden-implementacion` § Transición.

WordPress se despliega manualmente a **staging separado**; no instalar sobre producción hasta el corte final.

## Scripts

Desde la raíz del repositorio:

| Script | Descripción | Requisito |
| -------- | ------------- | ------------ |
| `scripts/optimize-images.sh` | Optimiza JPEG/PNG en `assets/images/` (tamaño, calidad, metadatos) | [ImageMagick](https://imagemagick.org/) (`brew install imagemagick`) |
| `scripts/rename-gallery-to-kebab.sh` | Renombra imágenes en `assets/images/galeria/` a `galeria-01.jpg`, `galeria-02.jpg`, … | Ninguno |
| `scripts/build-fonts.sh` | Regenera `marlowe-escapade-subset.woff2` (subset a "Camino del Dharma", 52,1 KB → 3,4 KB) | `pyftsubset` (`pip install 'fonttools[woff]' brotli`) |

Ejemplo:

```bash
./scripts/optimize-images.sh
./scripts/rename-gallery-to-kebab.sh
```

## Documentación

En `docs/` están la identidad corporativa, mapa de pantallas, arquitectura de información, copy, árbol de URLs, estructura de archivos estáticos, el **orden de implementación** (incl. fases WordPress) y el registro de **decisiones arquitectónicas** (`docs/adr/`). El índice de documentos está en `docs/00-orden-documentos.md`.

Colaboración, lint y despliegue: `CONTRIBUTING.md`. Licencia del código: `LICENSE`. Seguridad: `SECURITY.md`.

## Próximos pasos

Según `docs/17-orden-implementacion.md` (v3.0, § Transición): producción = sitio estático (raíz hoy; `static/` en Fase 3). WordPress en staging paralelo. Despliegues **manuales**; registro de migración en [`docs/migracion-static-wordpress.md`](docs/migracion-static-wordpress.md). ADR: [`docs/adr/README.md`](docs/adr/README.md).

## Autor

**Comunidad Buddhista Camino del Dharma**  
Personería Jurídica Especial – Ministerio del Interior de Colombia  

- Correo: <caminodeldharma1@gmail.com>  
- WhatsApp: +57 320 662 7608  

**Maqueta estática (código y estructura):** Rafael Figueredo Oropeza.  

## Licencia

**Código (HTML, CSS, JavaScript, scripts):** [MIT](https://opensource.org/licenses/MIT). Puedes usar, modificar y redistribuir el código bajo los términos de la licencia MIT.

**Contenido y recursos:** © Comunidad Buddhista Camino del Dharma. Todos los derechos reservados. Los textos, imágenes y materiales de identidad son de uso exclusivo de la comunidad.
