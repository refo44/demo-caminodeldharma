# Contributing — Camino del Dharma

Gracias por contribuir al sitio web de la Comunidad Buddhista Camino del Dharma. Este repositorio combina maqueta estática, documentación técnica y (futuro) theme WordPress.

## Antes de empezar

1. Lee `docs/17-orden-implementacion.md` para conocer la fase activa del proyecto.
2. Revisa los [ADR vigentes](docs/adr/README.md) (`docs/adr/`). Las decisiones **Aceptada** son obligatorias.
3. Copy de producción: **sitio publicado** (OWN-007, ADR 0040). No parafrasearlo ni restaurar
   materiales legacy encima. Eventos, posts y galería también se extraen del HTML/JSON (ADR 0034).
4. Migración futura: [`docs/contrato-migracion-static-wordpress.md`](docs/contrato-migracion-static-wordpress.md). Un template no crea una Page. No desplegar HTML estático sobre un document root WordPress.
5. Guías para agentes: [`AGENTS.md`](AGENTS.md), [`CLAUDE.md`](CLAUDE.md).
6. Pruebas y TDD: [`docs/guia-pruebas-plugin-theme-fse.md`](docs/guia-pruebas-plugin-theme-fse.md) (ADR 0038).

## Flujo Git

**Vigente desde 2026-09-01 (ADR 0043):** trunk-based development. **`main` está protegida**;
integración **solo por Pull Request**. Producción estática se despliega manual desde `main`
(ADR 0015); merge ≠ deploy.

Guía operativa: [`docs/git-workflow.md`](docs/git-workflow.md).

### Ramas — [Conventional Branch](https://conventionalbranch.org/)

Forma `<type>/<description>` en minúsculas, con guiones:

| Prefijo | Uso |
| ------- | --- |
| `feature/` o `feat/` | Funcionalidad nueva |
| `fix/` o `bugfix/` | Corrección |
| `hotfix/` | Urgente |
| `release/` | Versión (`release/v1.2.0`) |
| `chore/` | Docs, CI, deps |
| `cursor/`, `copilot/`, `claude/`, `codex/`, `ai/` | Trabajo de agente IA |

Ejemplo: `git checkout -b feature/add-login-page`

### Commits — [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)

Mensajes en **inglés**:

```text
<type>[optional scope]: <imperative description>
```

Ejemplos: `feat(theme): add safe asset version helper`, `fix(migrate): exclude bookkeeping keys from hash`, `docs: record git workflow in ADR 0043`.

### Flujo

1. `git checkout main && git pull`
2. `git checkout -b feature/short-description`
3. Implementar; validaciones locales (abajo).
4. Commits Conventional Commits; push de la rama.
5. Abrir PR hacia `main`; **añadir al menos una etiqueta relevante** (varias si aplica);
   esperar checks `php` y `css`; resolver conversaciones.
6. Merge; borrar la rama.

**Prohibido:** push directo a `main`.

### Etiquetas del Pull Request

Cada PR debe llevar **al menos una** etiqueta de GitHub que describa el cambio. Usa **varias**
cuando el PR toca más de un ámbito (p. ej. código WordPress + documentación).

| Etiqueta | Cuándo usarla |
| -------- | ------------- |
| `documentation` | Docs, ADR, README, CONTRIBUTING, guías, reglas Cursor |
| `enhancement` | Funcionalidad nueva (`feature/…`, `feat/…`) |
| `bug` | Corrección (`fix/…`, `bugfix/…`, `hotfix/…`) |
| `help wanted` | Necesitas revisión o input extra del maintainer |
| `question` | El PR depende de una decisión antes del merge |

```bash
gh pr create --base main --label documentation --title "docs: …" --body "…"
gh pr edit 4 --add-label documentation,enhancement   # varias etiquetas
```

Listar etiquetas disponibles: `gh label list`.

## Transición estático → WordPress

**Estado actual:** producción estática; Fase 3 no iniciada. `wordpress/` tiene árboles
placeholder (README, sin código) para Sonar; no hay theme ni plugin implementados.

**Ruta de Fase 3:** maqueta estática → **FSE** (ADR 0029). No construir un theme clásico PHP como puente.

Durante Fase 3, registrar cambios que afecten solo una implementación en [`docs/migracion-static-wordpress.md`](docs/migracion-static-wordpress.md). Cambios de diseño, CSS, navegación o a11y en producción deben portarse también a `wordpress/`. Completitud del corte: cinco entregables y matriz (ADR 0032), no «theme activado».

**Prohibido tras el corte:** subir el ZIP estático a `public_html` de WordPress. Theme/plugin solo a sus directorios (ADR 0013). No desplegar `uploads/`, `wp-config.php`, core ni base de datos.

## Validaciones locales

```bash
npm install
npm run lint:css
```

Stylelint debe finalizar **sin errores** antes de commit, push, PR o despliegue.

Cuando exista PHP propio (Fase 3): `composer test` es el gate barato (sintaxis + audit del
lockfile + units). `composer test:wp` y `tools/qa-*.sh` son locales, con Docker, y no
entran en CI. Oficio y taxonomía: [`docs/guia-pruebas-plugin-theme-fse.md`](docs/guia-pruebas-plugin-theme-fse.md)
(ADR 0038).

SonarQube Cloud (Automatic Analysis) ya escanea el repo. El alcance está en
`.sonarcloud.properties`. No añadir un scanner en Actions mientras Automatic Analysis esté
ON. El 0.0 % de cobertura en Sonar es esperado.

Para cambios de CSS/HTML significativos, revisar también:

- `docs/19-accesibilidad-estandares` (§10–11)
- Checklist de `docs/18-tendencias-ux-ui-sistema-editorial` (§8)

## Política de cambios

Si el cambio afecta **estructura**, **navegación**, **identidad visual** o **arquitectura**:

1. Actualizar el documento correspondiente en `docs/` (o crear ADR en `docs/adr/`).
2. Implementar en código.
3. Validar según criterios de la fase en `17-orden-implementacion`.

## Decisiones arquitectónicas (ADR)

Nueva decisión estructural → archivo numerado en `docs/adr/` siguiendo la plantilla del [README de ADR](docs/adr/README.md).

Los ADR aceptados son **inmutables**. Para cambiar una decisión, crear un ADR nuevo y marcar el anterior como **Sustituida**.

## Despliegue

**Manual únicamente** (ADR 0015). Automatización pospuesta (ADR 0016).

### Fase 2 (actual): raíz del repo → `public_html`

Ver `README.md`: sitemap, `VERSION`, `CHANGELOG.md`, `npm run lint:css`, `npm run build:css` (regenera `main.min.css`), ZIP acotado al sitio estático.

**No subir** `docs/`, `wordpress/`, `scripts/`, `tests/` ni el repo completo.

### Fase 3: `static/` → `public_html`

ZIP generado solo desde `static/` tras reorganización del repo.

### WordPress (futuro)

Theme y plugin propio desplegados manualmente a **staging separado** hasta el corte final. Post-corte: sync acotado al theme y al plugin propio (ADR 0013); automatización futura (ADR 0006, diferida por ADR 0016). Un File Manager/FTP en verde no prueba navegación, contenido en BD ni JS.

## Commits

- [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/) en **inglés**.
- Un commit por unidad lógica de cambio.
- No incluir `node_modules/`, ZIPs de despliegue ni secretos.

## Licencia

Código (HTML, CSS, JS, scripts): MIT — ver `LICENSE`.  
Contenido y recursos de marca: © Comunidad Buddhista Camino del Dharma.

## Contacto

- Correo: <caminodeldharma1@gmail.com>
- WhatsApp: +57 320 662 7608
