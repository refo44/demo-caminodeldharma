# Operaciones — Despliegue manual WordPress (acotado)

Runbook durable exigido por FABLE5 v2.4 §12. **Ningún despliegue está autorizado por este
documento**: cada escritura externa (staging u hosting) requiere autorización expresa del
propietario en la sesión vigente (OWN-005, ADR 0015).

| | |
| --- | --- |
| **Versión** | 1.1 |
| **Fecha** | 2026-08-31 |
| **Estado** | Vigente — staging aún no creado; `.htaccess` añadido al alcance en WU-08B |

## Alcance

- **Qué se despliega a WordPress:** únicamente código first-party versionado:
  `wordpress/wp-content/themes/camino-del-dharma/`,
  `wordpress/wp-content/plugins/camino-del-dharma-core/` y, desde WU-08B,
  `wordpress/.htaccess` al document root.
- **Qué nunca se despliega desde este repo:** core de WordPress, `wp-config.php`,
  credenciales, base de datos, `uploads/`, `docs/`, `scripts/`, `tests/`, `static/`
  sobre un document root WordPress (ADR 0013, ADR 0032).
- **Destino durante la transición:** instancia Hostinger **separada, sin dominio custom,
  no indexable** (OWN-005). Nunca el `public_html` de producción estática antes del corte.

## Procedimiento (cuando exista autorización expresa)

1. Verificar rama/commit y QA local verde (niveles 1–3, `.audit/fase3-validation-matrix.md`).
2. Empaquetar **solo** los dos directorios first-party (ZIP por directorio o File Manager).
3. Subir a `wp-content/themes/` y `wp-content/plugins/` del staging.
4. Activar plugin, luego theme. Verificar sin warnings/fatals y `debug.log` limpio.
4b. Copiar `wordpress/.htaccess` al document root **conservando el bloque
   `# BEGIN WordPress` que ese servidor ya tenga**: las reglas propias van encima y WordPress
   reescribe el suyo al guardar enlaces permanentes. Comprobar después con `curl` las entradas
   del ledger (un salto por regla, sin cadenas ni loops).
5. Verificar noindex del staging (ajuste de lectura de WordPress + ausencia de dominio).
6. Registrar evidencia como `Pass` (staging) en la matriz; `Pass (local)` no la sustituye.

## Contenido

El contenido **no** se despliega por archivos: se importa con el pipeline WP-CLI
(`validate → plan → import → verify`, ADR 0033/0034) con dry-run previo y `--apply` explícito.
Nunca ejecutar el importador contra producción sin guard, confirmación y backup verificado.

## Producción estática (sin cambios)

El sitio estático sigue desplegándose por ZIP manual desde `static/` según README
(«Despliegue en Hostinger»). Este runbook no lo modifica ni lo autoriza.

## Rollback

- Staging: desactivar theme/plugin o restaurar el ZIP anterior de los dos directorios.
- El corte a producción tiene su propio checklist y rollback:
  `docs/cutover-checklist-wordpress.md`. Este runbook no autoriza el corte.
