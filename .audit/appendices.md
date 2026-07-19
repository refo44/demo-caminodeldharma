# Apéndices

- **URLs probadas y tipos de página:** `url-inventory.csv`, `page-types.md` (13 indexables + 404 + entradas HTTP/HTTPS + archivos máquina + 2 .ics rotos).
- **Resumen de crawl/integridad:** `raw/crawl/internal-integrity-v2.txt` (400 refs internas OK, 0 rotas; v1 descartada por falsos positivos — ver decisions.md).
- **Runs de rendimiento crudos:** `metrics/performance-runs.csv`; auditoría de imágenes: `raw/performance/image-audit.txt`.
- **Accesibilidad (estructura, contraste, galería):** `raw/accessibility/*.txt`.
- **Cabeceras de seguridad exactas:** `raw/headers/*.txt` (entradas, clases de estado, caching de assets) y `raw/security/hsts/*` (TLS, transporte, CT).
- **Inventario de datos estructurados:** `raw/structured-data/jsonld-raw.json` + validación `jsonld-validation.txt` + metadatos `metadata-inventory.txt`.
- **Consola:** limpia en las páginas probadas (`raw/console/home-cookies.txt` incluye el estado de cookies).
- **Dependencias externas y propósito:** ver `technology.md` (GA4 analítica; YouTube/Vimeo embeds; wa.me contacto; sociales enlaces; fuentes autoalojadas).
- **Resultados de tareas agénticas:** `raw/agent-tasks/calendar-dialog.txt` y `working/agentic.md` (contacto por formulario FALLA; WhatsApp OK; calendario 2/4).
- **Cobertura de revisión de código:** `raw/source-review/js-review.txt`, `deploy-parity.txt` (14/14 idénticas), `working/code-architecture.md`.
- **Sondas de exposición:** `raw/security/exposure-probe.txt` (21 rutas).
