# Playbook — Migración static → WordPress (Camino del Dharma)

Playbook operativo de **este** repositorio para Fase 3. Complementa —no sustituye— los ADR,
`docs/17-orden-implementacion.md` y el contrato
[`contrato-migracion-static-wordpress.md`](contrato-migracion-static-wordpress.md) (ADR 0032).

**No implementar WordPress** en una sesión que solo actualice documentación.

Nota histórica (2026-07-31): el propietario aportó aprendizajes de otro sitio con forma parecida
(estático → CMS, hosting compartido). Este playbook **no copia** nombres, slugs, CPTs, hosts ni
pipelines de otros proyectos. El backlog de dueño de la auditoría 2026-08-19 está **cerrado**
(`backlog-decisiones-owner-migracion.md`: Fase 3 cerrada v1.21; `POST-*` no se implementan
en el corte). No reabrir autores/galería/ICS sin decisión nueva. CF7 en el corte: ADR 0041.

**Contrato (ADR 0032):** cinco entregables. Ruta **estático de producción → FSE** (sin theme PHP clásico).
Theme activado ≠ migración completa. Template ≠ Page. Deploy success ≠ application success.
El HTML live es fuente de contenido de producción hasta el corte (ADR 0034).

---

## Mapa: qué ya cubre este proyecto y qué es nuevo

| # | Aprendizaje | Estado en Camino del Dharma |
| - | ----------- | --------------------------- |
| 1 | El estático es el contrato visual/de comportamiento | **Cubierto** — ADR 0001, 0002; excepciones registradas (ADR 0021); CSS static = ADR 0009; CSS WP = ADR 0029 |
| 2 | Monorepo con separación dura | **Ya cubierto** (estructura de carpetas, ADR 0014) + **decisión nueva hoy** (plugin=dominio/theme=presentación, ADR 0024) |
| 3 | Unidades de trabajo con estado durable | **Nuevo — recomendado adoptar** (§3 abajo) |
| 4 | Jerarquía de fuentes de verdad | **Nuevo — recomendado formalizar** (§4 abajo), implícito hasta ahora |
| 5 | Alcance por fases con límites explícitos | **Ya cubierto** (criterios de aceptación por fase en `docs/17`); el grep de patrones prohibidos es **nuevo, recomendado** |
| 6 | Migración de contenido: importador WP-CLI | **Decidido** — ADR 0033 (aún no implementado) |
| 7 | Matriz de cobertura static → WP | **Creada** — `docs/matriz-migracion-static-wordpress.md` (inventario; completar estrategias en Fase 3) |
| 8 | QA en 4 niveles con honestidad probatoria | **Parcialmente cubierto** (`Pass (local)` ya en ADR 0023/playbook Docker) — **recomendado generalizar** a los 4 niveles |
| 9 | Entorno local Docker | **Ya cubierto** — ADR 0023, `docs/docker-wordpress-playbook.md` |
| 10 | Despliegue acotado y manual | **Ya cubierto** — ADR 0015 (manual), ADR 0016 (CI/CD pospuesto); el detalle de `workflow_dispatch` aplica **cuando** se retome ADR 0016 |
| 11 | Política de plugins de terceros minimalista | **Decisión nueva hoy** — ADR 0025 (política) + ADR 0026 (Contact Form 7, primer caso) |
| 12 | Invariantes transversales | **Nuevo — recomendado adoptar como checklist de QA** (§12 abajo) |
| 13 | Checklist portable | **Nuevo — recomendado usar como checklist de arranque de Fase 3** (§13 abajo) |
| 14 | Anti-patrones | **Nuevo — recomendado como referencia** (§14 abajo) |

Las secciones "ya cubiertas" no se repiten en detalle aquí — sus ADR y documentos son la fuente de
verdad. Las secciones nuevas o parcialmente nuevas se desarrollan abajo.

---

## 3. Unidades de trabajo (WU) con estado durable

**Aprendizaje:** una migración larga no debe vivir solo en el historial de conversación; vive en
artefactos versionados que permiten reanudar el trabajo entre sesiones sin releer todo el contexto.

Camino del Dharma ya practica esto parcialmente (`TASK-NNNN.md`, `waves.md`, `.audit/decisions.md`),
pero esos artefactos nacieron de la auditoría (Fase 2.75), no de la implementación de Fase 3. Al iniciar
Fase 3, recomendado crear el equivalente:

| Artefacto propuesto | Propósito |
| -------------------- | --------- |
| `.audit/fase3-execution-state.md` | Dónde estamos, qué sigue, blockers, protocolo de reanudación |
| `.audit/fase3-validation-matrix.md` | Evidencia de QA con estados honestos (`Unverified` / `Pass (local)` / `Pass`) |
| `docs/migracion-static-wordpress.md` | Ya existe — sigue siendo el ledger de correcciones aprobadas |

**Protocolo de reanudación sugerido** (funciona igual con una persona o con un agente):
`git status` → leer `fase3-execution-state.md` → re-ejecutar la última QA registrada → continuar desde
"Next exact action".

**Cómo dividir el trabajo:** WU pequeñas con commits atómicos — p. ej. scaffold del plugin → modelo de
datos (CPT `sangha`/`event`) → theme → plantillas → migración de contenido → fixtures de prueba →
Contact Form 7 → despliegue a staging. Cada WU define: fuentes vinculantes (qué doc/ADR la rige),
criterios de aceptación, plan de QA, rollback.

---

## 4. Fuentes de verdad (por tipo, no una sola jerarquía)

No hay un único árbol para todo (ADR 0034).

| Tipo | SOURCE OF TRUTH hoy | PRESENTATION / GENERATED |
| ---- | ------------------- | ------------------------ |
| Copy institucional | **HTML live** (OWN-007, ADR 0040) | HTML/CSS |
| Eventos, posts, JSON de galería | **HTML / JSON embebido live** | cards, listados, `gallery.js` |
| Arquitectura | ADR, luego `docs/01`–`24` | — |
| CSS servido | `assets/css/main.css` | `main.min.css` |
| URLs | `sitemap.xml` + ADR 0008 | — |

**Regla:** si repo y producción divergen, **registrar** (UNCLEAR o ledger) — no resolver en
silencio. El HTML/JSON publicado es la base temporal de contenido pre-corte (ADR 0034/0040).

---

## 6. Migración de contenido: extractor + importador

**Decisión:** ADR 0033. No implementado. CPT `sangha` sigue fuera del alcance inicial de Fase 3
(ADR 0024). Arquitectura prevista, de dos pasos:

```text
HTML / JSON live         →  extractor read-only (Pages, eventos, posts, galería, media refs)
                            ↓
                   payload versionado revisable (migration-payload.json)
                            ↓
                   validate / dry-run
                            ↓
                   importador WP-CLI en camino-del-dharma-core  (ADR 0033)
                   + seed de imágenes → Media Library (OWN-009-img)
                            ↓
                   WordPress (BD + Media Library)
```

El extractor **no está implementado**. Preferir parseo determinista a reescribir a mano.
Conteos: [`conteos-reconciliacion-migracion.md`](conteos-reconciliacion-migracion.md).

**Seed de imágenes:** sube attachments reales (galería, carteles, fotos de página **y huérfanas**).
Huérfanas: en la biblioteca, **ocultas** (OWN-003) — no álbum, no Page. No es `seed/teardown` de
fixtures. No marcar `_cdd_fixture`. No borrar media de producción al re-ejecutar.

Reglas de este proyecto (ADR 0033):

| Regla | Por qué importa aquí |
| ----- | --------------------- |
| Payload versionado en Git | El servidor de producción no necesita el repo completo para reconstruir el contenido |
| `validate → plan → import → verify` | Dry-run por defecto; escritura solo con `--apply` — evita pisar contenido real por accidente |
| Idempotencia | Re-ejecutar el importador = `skip unchanged`, no duplica eventos ni sanghas |
| Metadatos `_source_key`, `_source_hash` | Detecta si alguien editó a mano en WordPress algo que también vive en el payload versionado |
| `--force` solo en campos "owned" por la migración | No pisa ediciones editoriales hechas directamente en WordPress tras el corte |
| Guard de producción (`--confirm-production` + evidencia de backup) | Coherente con ADR 0005 (producción sin edición manual) |
| Texto publicado verbatim | La validación falla si el payload diverge del HTML publicado sin una diferencia aprobada en el ledger; protege contra errores editoriales silenciosos |

**Separar fixtures de datos reales:** si se usan datos de prueba para desarrollar el theme/plugin,
marcarlos (`_cdd_fixture = 1`) y usar comandos `seed/verify/teardown` **solo de esos fixtures**.
El seed de **imágenes de producción** (OWN-009-img) no entra en ese teardown. Nunca mezclar demo
con contenido institucional real.

---

## 7. Matriz de cobertura static → WordPress

La matriz vive en [`docs/matriz-migracion-static-wordpress.md`](matriz-migracion-static-wordpress.md)
(ADR 0032). Plantillas vigentes: `templates/*.html` (ADR 0029), no `front-page.php`.

No considerar una URL migrada hasta que su fila tenga estrategia de contenido, presentación,
routing, comportamiento y QA. `sangha` no entra en el corte inicial salvo decisión nueva.

---

## 8. QA en cuatro niveles con honestidad probatoria

Generaliza la distinción `Pass (local)` vs `Pass` ya adoptada para Docker (ADR 0023) a las cuatro capas
completas:

| Nivel | Qué valida | Requiere runtime |
| ----- | ---------- | ------------------ |
| 1 — Estático | Sintaxis PHP, JSON/YAML si aplica, checksums CSS/JS, greps de patrones prohibidos | No — siempre disponible |
| 2 — Componente | CPTs, meta fields, comandos WP-CLI propios, idempotencia de migración/fixtures | Sí — Docker local (ADR 0023) basta |
| 3 — Integración | Activación de plugin/theme, permalinks, Contact Form 7 entregando, plugins de terceros aprobados | Sí — Docker local basta |
| 4 — Regresión UX | Paridad de **copy, contenido y estilos** contra `https://caminodeldharma.org` (OWN-007), no solo el repo local; teclado; sin cookies (ADR 0019) | Sí — Docker + staging; evidencia frente al live |

**Reglas de honestidad que ya aplican en este proyecto y se mantienen:**
- `Pass (local)` **no** sustituye a `Pass` para nada que dependa del hosting real de Hostinger:
  versión de PHP real, `.htaccess`/Apache real, HTTPS, cabeceras, entrega efectiva de correo (Contact
  Form 7 a caminodeldharma1@gmail.com).
- Nada se marca `Pass` sin evidencia ejecutada y registrada; lo no probado queda `Unverified` — mismo
  estándar que ya usa `.audit/` para los hallazgos de la auditoría.
- Pasar el nivel 1 no certifica una WU completa.

---

## 12. Invariantes transversales (checklist de QA continua)

Controles a verificar durante todo el desarrollo de Fase 3, no solo al final:

| Invariante | Cómo se verifica | Referencia en este proyecto |
| ---------- | ------------------ | ----------------------------- |
| CSS del estático | Un `main.css`; checksums vs maqueta | ADR 0009 (solo static) |
| CSS / tokens WordPress | `theme.json` + hoja complementaria; no page builders | ADR 0029, ADR 0025 |
| Sin datos demo en producción | Grep de marcadores de fixture (`_cdd_fixture`) antes de desplegar | §6 arriba |
| Sin cookies anónimas | `curl -I` + inspección de red sin `Set-Cookie` | ADR 0019 |
| Sin registro de dominio en el theme | Grep de `register_post_type`/`register_taxonomy` en `themes/camino-del-dharma/` (debe estar vacío) | ADR 0024 |
| Flush de rewrite solo en activación | Grep de `flush_rewrite_rules` — nunca en cada carga de página | Buena práctica WP estándar |
| Sin secretos en el repo | Grep de patrones de credenciales; secretos solo en `.env` (gitignored) | `docs/docker-wordpress-playbook.md` §3 |
| Guards por entorno funcionando | Probar que el plugin rechaza operaciones destructivas en `production` antes de corregir configuración | `docs/docker-wordpress-playbook.md` §3.1 |

---

## 13. Checklist portable — arranque de Fase 3

**Antes de escribir código WordPress:**
- [x] ADR: monorepo `static/` + `wordpress/` (ADR 0014)
- [x] ADR: plugin dueño del dominio vs. theme presentación (ADR 0024)
- [x] ADR: theme de bloques / FSE (ADR 0029)
- [x] ADR: contrato de migración (ADR 0032), import vs fixtures (ADR 0033), estático live = producción (ADR 0034)
- [x] Inventario + conteos + redirect ledger (docs de ADR 0034)
- [x] Alcance de fase — criterios de aceptación ya en `docs/17-orden-implementacion.md`
- [x] Matriz de cobertura — `docs/matriz-migracion-static-wordpress.md` (completar estrategias al implementar)
- [ ] Harness: `fase3-execution-state.md` + `fase3-validation-matrix.md` (§3 arriba)
- [ ] Baseline visual / tokens del estático actual (ADR 0029: `theme.json` inicial)

**Durante la implementación:**
- [ ] WU con commits pequeños y QA por unidad (§3)
- [ ] Plantillas FSE 1:1 con la maqueta (`templates/` + parts/patterns); **no** hay `page-*.php` clásicos; template ≠ Page
- [ ] Migración de contenido WP-CLI (ADR 0033)
- [ ] Fixtures de desarrollo aisladas con teardown **solo** de objetos fixture
- [ ] `docker-compose.yml` para QA local (ADR 0023, ya listo el playbook)
- [ ] Despliegue del theme/plugin solo manual y acotado (ADR 0015/0016)

**Antes del gate de lanzamiento (Fase 2.5 sobre el theme, `docs/17` § Transición):**
- [ ] Nivel 1 de QA completo en verde (§8)
- [ ] Niveles 2-3 en `Pass (local)` documentado
- [ ] Staging con los mismos pasos, registrado en la matriz de validación
- [ ] Paridad visual (nivel 4) verificada en hosting real
- [ ] Inventario de plugins de terceros (Contact Form 7, ADR 0026) configurado y verificado en staging

---

## 14. Anti-patrones a evitar

| Anti-patrón | Por qué falla |
| ------------ | -------------- |
| Migrar todo de golpe en un solo PR/sesión | Imposible de revisar, imposible de reanudar entre sesiones |
| Page builder o bloques no nativos sobre el diseño acordado | Rompe paridad con la maqueta (ADR 0001) y está vetado por defecto (ADR 0025) |
| Ejecutar la migración de contenido dentro de la activación del plugin | Peligroso e impredecible; la migración debe ser un comando explícito, no un side-effect |
| Datos demo mezclados con contenido institucional real | Contamina producción; ADR 0033 |
| Auto-deploy de WordPress sin QA de runtime | Contradice ADR 0015/0016 (despliegue manual mientras dure la transición) |
| Instalar PHP/MySQL global "para probar rápido" | No reproducible entre máquinas — por eso ADR 0023 elige Docker |
| Declarar una WU o un hallazgo "resuelto" sin evidencia ejecutada | Ya ocurrió en este proyecto (TASK-0002 y TASK-0006 se marcaron `COMPLETED` sin que el fix estuviera realmente en el código — ver revisión de esta misma sesión) — es la razón de ser de la distinción `Pass (local)` vs `Pass` |

---

## Síntesis

Tratar el estático como contrato visual y de comportamiento, separar dominio (plugin) de
presentación (theme FSE), migrar contenido con un pipeline idempotente (ADR 0033), validar los
cinco entregables (ADR 0032) con evidencia honesta, y desplegar solo código acotado de forma
manual. Un transfer verde no cierra el corte.

## Referencias

- [`contrato-migracion-static-wordpress.md`](contrato-migracion-static-wordpress.md), [`inventario-contenido-produccion-static.md`](inventario-contenido-produccion-static.md), [`matriz-migracion-static-wordpress.md`](matriz-migracion-static-wordpress.md), [`cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md)
- ADR [0001](adr/0001-maqueta-estatica-como-base-definitiva.md), [0029](adr/0029-theme-bloques-full-site-editing.md), [0032](adr/0032-contrato-migracion-static-wordpress.md), [0033](adr/0033-importador-contenido-vs-fixtures.md), [0034](adr/0034-static-live-como-fuente-contenido-produccion.md), [0040](adr/0040-retirar-content-source-produccion-como-fuente.md)
- ADR [0014](adr/0014-monorepo-static-wordpress.md), [0024](adr/0024-plugin-dominio-theme-presentacion.md)
- ADR [0038](adr/0038-pruebas-tdd-phpunit-sonar.md) y `docs/guia-pruebas-plugin-theme-fse.md`
- ADR [0015](adr/0015-despliegue-manual-temporal.md), [0016](adr/0016-automatizacion-ci-cd-pospuesta.md)
- ADR [0023](adr/0023-entorno-local-wordpress-docker.md) y `docker-wordpress-playbook.md`
- ADR [0025](adr/0025-politica-plugins-terceros.md), [0026](adr/0026-contact-form-7.md)
- `docs/17-orden-implementacion.md` § Transición
- `docs/migracion-static-wordpress.md` — ledger, no contrato
- `.audit/decisions.md`

---

**Versión:** 1.1 · **Fecha:** 2026-08-30
