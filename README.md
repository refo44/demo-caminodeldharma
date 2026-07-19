# demo-caminodeldharma

Maqueta estática del sitio web de la **Comunidad Buddhista Camino del Dharma** (Colombia). Incluye las páginas de inicio, comunidad, linaje, práctica, eventos, galería, blog, contribuir y contacto. El contenido y la estructura siguen la documentación en `docs/`. Pensado como base para una futura adaptación a WordPress.

## Tecnologías

- HTML5 semántico
- CSS3 (tokens, diseño responsivo)
- JavaScript mínimo con `defer` (menú, galería, accesibilidad)
- Stylelint como validación obligatoria del CSS
- Sin proceso de build: archivos estáticos listos para servir; npm se usa únicamente para herramientas de desarrollo

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

## Estructura del proyecto

- **Raíz:** `index.html`, `404.html`, `robots.txt`, `sitemap.xml`, `VERSION`
- **Versionado:** `CHANGELOG.md` (historial de despliegues), `VERSION` (versión actual)
- **Secciones:** `comunidad/`, `linaje/`, `practica/`, `eventos/`, `galeria/`, `contacto/`, `donaciones/`, `blog/`
- **Assets:** `assets/css/`, `assets/js/`, `assets/images/`
- **Documentación:** `docs/` (identidad, mapa de pantallas, copy, URLs, orden de implementación)
- **Scripts:** `scripts/` (ver abajo)

## Despliegue en Hostinger

El README documenta el proyecto; el historial de publicaciones vive en [`CHANGELOG.md`](CHANGELOG.md).

Antes de cada despliegue:

1. **Actualizar `sitemap.xml`:** revisar las páginas HTML modificadas desde el último despliegue y actualizar su `<lastmod>` (formato `YYYY-MM-DD`). Solo incluir URLs de páginas indexables; no añadir `llms.txt`, `site.webmanifest` ni otros archivos no HTML. Este paso es **obligatorio antes** de incrementar `VERSION`.
2. Actualizar [`VERSION`](VERSION) y [`CHANGELOG.md`](CHANGELOG.md).
3. Ejecutar `npm run lint:css` (sin errores).
4. Generar el ZIP de producción con el nombre `camino-del-dharma-vX.Y.Z.zip` (según la versión en `VERSION`).
5. Subir y extraer en `public_html` del File Manager de Hostinger.

Para detectar páginas modificadas, comparar contra el último commit de release (p. ej. `git diff HEAD~1 -- '*.html'`) o revisar manualmente que cada URL del sitemap refleje la fecha del cambio más reciente.

El ZIP de despliegue incluye solo archivos de producción (HTML, assets, `robots.txt`, `sitemap.xml`, `sitemap.xsl`, `llms.txt`, `site.webmanifest`, `.htaccess`). No incluye `docs/`, `content-source/`, `node_modules/` ni `.git/`.

## Scripts

Desde la raíz del repositorio:

| Script | Descripción | Requisito |
| -------- | ------------- | ------------ |
| `scripts/optimize-images.sh` | Optimiza JPEG/PNG en `assets/images/` (tamaño, calidad, metadatos) | [ImageMagick](https://imagemagick.org/) (`brew install imagemagick`) |
| `scripts/rename-gallery-to-kebab.sh` | Renombra imágenes en `assets/images/galeria/` a `galeria-01.jpg`, `galeria-02.jpg`, … | Ninguno |

Ejemplo:

```bash
./scripts/optimize-images.sh
./scripts/rename-gallery-to-kebab.sh
```

## Documentación

En `docs/` están la identidad corporativa, mapa de pantallas, arquitectura de información, copy, árbol de URLs, estructura de archivos estáticos y el **orden de implementación** (incl. fases WordPress). El índice de documentos está en `docs/00-orden-documentos.md`.

## Próximos pasos

Según `docs/17-orden-implementacion.md`, la maqueta estática (esta fase) se validará en responsive y luego se adaptará a **plantillas WordPress** sin cambiar la estructura visual ni las URLs.

## Autor

**Comunidad Buddhista Camino del Dharma**  
Personería Jurídica Especial – Ministerio del Interior de Colombia  

- Correo: <caminodeldharma1@gmail.com>  
- WhatsApp: +57 320 662 7608  

**Maqueta estática (código y estructura):** Rafael Figueredo Oropeza.  

## Licencia

**Código (HTML, CSS, JavaScript, scripts):** [MIT](https://opensource.org/licenses/MIT). Puedes usar, modificar y redistribuir el código bajo los términos de la licencia MIT.

**Contenido y recursos:** © Comunidad Buddhista Camino del Dharma. Todos los derechos reservados. Los textos, imágenes y materiales de identidad son de uso exclusivo de la comunidad.
