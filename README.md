# demo-caminodeldharma

Maqueta estática del sitio web de la **Comunidad Buddhista Camino del Dharma** (Colombia). Incluye las páginas de inicio, comunidad, linaje, práctica, eventos, galería, blog, contribuir y contacto. El contenido y la estructura siguen la documentación en `docs/`. Pensado como base para una futura adaptación a WordPress.

## Tecnologías

- HTML5 semántico
- CSS3 (tokens, diseño responsivo)
- JavaScript mínimo con `defer` (menú, galería, accesibilidad)
- Sin proceso de build: archivos estáticos listos para servir

## Cómo ver el sitio

Abrir `index.html` en el navegador o servir la carpeta con un servidor local:

```bash
npx serve .
```

O desde la raíz del proyecto con el servidor de tu IDE (Live Server, etc.).

## Estructura del proyecto

- **Raíz:** `index.html`, `404.html`
- **Secciones:** `comunidad/`, `linaje/`, `practica/`, `eventos/`, `galeria/`, `contacto/`, `donaciones/`, `blog/`
- **Assets:** `assets/css/`, `assets/js/`, `assets/images/`
- **Documentación:** `docs/` (identidad, mapa de pantallas, copy, URLs, orden de implementación)
- **Scripts:** `scripts/` (ver abajo)

## Scripts

Desde la raíz del repositorio:

| Script | Descripción | Requisito |
|--------|-------------|------------|
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

- Correo: caminodeldharma1@gmail.com  
- WhatsApp: +57 320 662 7608  

**Maqueta estática (código y estructura):** Rafael Figueredo Oropeza.  

## Licencia

**Código (HTML, CSS, JavaScript, scripts):** [MIT](https://opensource.org/licenses/MIT). Puedes usar, modificar y redistribuir el código bajo los términos de la licencia MIT.

**Contenido y recursos:** © Comunidad Buddhista Camino del Dharma. Todos los derechos reservados. Los textos, imágenes y materiales de identidad son de uso exclusivo de la comunidad.
