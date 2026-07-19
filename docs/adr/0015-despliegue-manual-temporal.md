# ADR 0015: Despliegue manual temporal

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Durante la transición estático → WordPress (Fase 3), coexisten dos implementaciones en un monorepo (`static/` y `wordpress/`). WordPress se desarrolla en **staging separado**; el sitio estático sigue siendo **producción pública**.

Automatizar despliegues antes de estabilizar la estructura del monorepo y los límites de cada artefacto aumenta el riesgo de publicar `docs/`, `scripts/`, código WordPress incompleto o el repositorio entero en `public_html`.

## Decisión

**Los despliegues a producción serán manuales** mientras dure la transición y hasta nueva decisión documentada en ADR.

### Sitio estático (producción)

1. Actualizar `static/sitemap.xml`, `VERSION`, `CHANGELOG.md` (rutas según fase; ver README).
2. Ejecutar `npm run lint:css` (sin errores).
3. Generar ZIP **solo** con el contenido de `static/` (o, en Fase 2 pre-reorg, equivalente desde raíz).
4. Subir y extraer en `public_html` de Hostinger (File Manager o FTP).

**Prohibido:** subir el repositorio completo, `docs/`, `wordpress/`, `scripts/`, `.github/` o archivos de desarrollo a producción.

### WordPress (staging)

- Desplegar manualmente el theme y plugins propios en **entorno de staging** separado.
- **No** instalar WordPress encima del sitio estático de producción hasta el corte final (ADR 0014, `17-orden-implementacion`).

### Verificación post-despliegue

- Smoke test: home, contacto, galería, blog, 404.
- Formulario, enlaces del footer, HTTPS.

## Alternativas consideradas

| Alternativa | Motivo de descarte (por ahora) |
| ----------- | ------------------------------ |
| rsync de todo el repo | Expone documentación y WordPress en desarrollo. |
| GitHub Actions deploy inmediato | Pospuesto en ADR 0016; límites de sync aún en definición. |
| Edición directa en Hostinger | Viola ADR 0005. |

## Consecuencias

**Beneficios:**

- Control explícito de qué llega a producción.
- Staging WordPress aislado del sitio público.
- ZIP acotado a `static/` reduce errores operativos.

**Negativas:**

- Dependencia del operador local; validaciones no automatizadas en deploy.
- Procedimiento documentado en README debe actualizarse al mover raíz → `static/`.

**Trabajo futuro:**

- Script `scripts/build-static-zip.sh` que empaquete solo `static/`.
- Tras corte WordPress: despliegue manual del theme acotado (ADR 0013) hasta activar CI/CD (ADR 0016).

## Referencias

- `README.md` (procedimiento ZIP)
- `docs/17-orden-implementacion` Fase 4, § Transición
- ADR 0005, ADR 0014, ADR 0016
- `docs/migracion-static-wordpress.md`
