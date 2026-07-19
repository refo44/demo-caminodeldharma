# Camino del Dharma — Orden de implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**

## Propósito

Este documento define el orden oficial de implementación, validación, migración y mantenimiento del sitio Camino del Dharma y actúa como referencia para todas las fases del proyecto.

| | |
| --- | --- |
| **Versión** | 3.1 |
| **Fecha** | 2026-07-19 |
| **Estado** | Vigente |

### Cambios principales (3.1)

- **Estado actual del proyecto:** sitio estático en producción (`v1.0.11`), Fase 2 sustancialmente completada; Fase 2.5 y auditoría de producción registradas.
- **Nueva § Fase 2.75:** auditoría integral de producción (2026-07-19), hallazgos, olas de remediación y enlace a `.audit/`.
- **Formulario de contacto:** estado real documentado (markup presente, envío no operativo en estático); respaldo en `docs/archive/contacto-formulario-estatico/` para restauración en WordPress.
- **HSTS:** despliegue **escalonado** (ADR 0018) — Fase 1 `max-age=604800` (TASK-0004/0005); Fase 2 `max-age=31536000` tras WordPress estable.
- Criterios de aceptación de Fase 2 y checklist pre-lanzamiento actualizados según evidencia de la auditoría.
- **ADR 0018:** HSTS escalonado — Fase 1 `max-age=604800`, Fase 2 `31536000` post-WordPress; ADR 0010 sustituida en lo operativo.
- Mantenimiento: incorporación de tareas post-auditoría y revisión trimestral ampliada.

### Cambios principales (3.0)

- Incorporación de criterios de aceptación por fase
- Incorporación de Fase 2.5 (QA)
- Incorporación de § Transición estático → WordPress
- Incorporación de registro ADR y referencia a `docs/adr/README.md`
- Incorporación de mantenimiento post-publicación
- Despliegue manual y CI/CD pospuesto (ADR 0015, ADR 0016)
- Congelamiento de documentación base (§ más abajo)

**Depende de:** `02-identidad-corporativa`, `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `06-wireframes`, `09-ui-copy-sheet`, `11-arbol-urls-final`, `12-theme-file-structure`, `13-static-file-structure`, `14-css-architecture`, `15-assets-strategy`, `16-content-source-inventario`, `18-tendencias-ux-ui-sistema-editorial`, `19-accesibilidad-estandares`, `migracion-static-wordpress`, `docs/adr/README.md`, `.audit/` (auditoría de producción 2026-07-19)

---

## Estado actual del proyecto (2026-07-19)

| Aspecto | Estado |
| ------- | ------ |
| **Fase activa** | Fase 2 (maqueta estática) **en producción**; mantenimiento y remediación post-auditoría |
| **Versión desplegada** | `1.0.11` (`VERSION`) |
| **URL producción** | https://caminodeldharma.org |
| **Estructura repo** | HTML en **raíz** (Fase 3 no iniciada; carpeta `static/` aún no existe) |
| **WordPress** | No iniciado (Fase 3 pendiente) |
| **Auditoría producción** | **COMPLETE** — ver § Fase 2.75 y `.audit/` |
| **GA4** | **Desactivado** (v1.0.12) hasta política de privacidad + consentimiento — TASK-0006 |
| **Score global auditoría** | 84/100 — SEO 100; 0 críticos; 2 hallazgos ALTOS (formulario, `.ics`) |
| **Próxima acción técnica** | `.audit/implementation/tasks/TASK-0004.md` (activar HSTS) + `TASK-0005` (verificación) |

La maqueta cumple la estructura §2.1 (13 URLs indexables + 404), despliegue idéntico al repo auditado (14/14 páginas byte-identical) y SEO técnico impecable. Pendientes de cierre operativo: formulario de contacto sin backend, archivos `.ics` inexistentes, HSTS por activar, consentimiento GA4 (decisión organizativa).

---

## Principios transversales

### Fuente única de verdad

**Repositorio Git como fuente única de verdad.**

- Todo cambio debe realizarse en el repositorio. No se editarán archivos directamente en el servidor de producción.
- El servidor (Hostinger) es un **destino de despliegue**, no un entorno de edición.
- `content-source/` es la fuente canónica del copy; el código y los assets del sitio viven en el repo según `13-static-file-structure` y `15-assets-strategy`.
- Cambios manuales en producción se pierden en el siguiente despliegue y no deben realizarse.
- Decisión formal: ADR 0004, ADR 0005.

### Política de cambios

Toda modificación que afecte **estructura**, **navegación**, **identidad visual** o **arquitectura** debe reflejarse **primero** en la documentación correspondiente (`docs/`) antes de implementarse.

Orden recomendado:

1. Actualizar el doc afectado (p. ej. `05`, `11`, `14`, `15`).
2. Implementar en código.
3. Validar según los criterios de aceptación de la fase activa.

Excepciones permitidas sin doc previo: corrección de errores tipográficos alineados con `content-source/`, bugs de accesibilidad y ajustes de performance que no alteren la arquitectura.

### Estrategia de versionado

| Aspecto | Regla |
| -------- | ------ |
| **Ramas** | Desarrollo en ramas de feature; `main` es la rama de producción. |
| **Integración** | Merge a `main` mediante Pull Request con revisión. |
| **Etiquetas** | Releases etiquetados (`v1.0.0`, `v1.1.0`, …) al desplegar a producción. |
| **Versión en repo** | Archivo `VERSION` y entrada en `CHANGELOG.md` (ver README). |
| **Producción** | Solo desde `main` (o tag asociado a un commit de `main`). |

Semántica sugerida: **MAJOR** (cambio estructural o de URLs), **MINOR** (nueva sección o funcionalidad), **PATCH** (correcciones y ajustes menores).

### Congelamiento de documentación base

A partir de la versión 3.0 de este documento, la **documentación arquitectónica base** se considera **suficiente y congelada**. Los cambios sobre esos documentos deben ser **excepcionales** y responder a necesidades reales del proyecto (error, requisito nuevo validado, inconsistencia con `content-source/`), no a refinamiento continuo.

Documentos congelados:

- `02-identidad-corporativa`
- `03-wordpress-content-model`
- `04-mapa-pantallas`
- `05-arquitectura-informacion-navegacion`
- `06-wireframes`
- `11-arbol-urls-final`
- `14-css-architecture`
- `17-orden-implementacion` (este documento)
- `19-accesibilidad-estandares`
- `23-sistema-editorial`

**Documentación nueva permitida sin levantar el congelamiento:** ADR en `docs/adr/`, entradas en `docs/migracion-static-wordpress.md`, guías operativas puntuales, `CHANGELOG.md`, actualizaciones de `12`, `13`, `15` cuando la implementación lo exija de forma concreta, respaldos en `docs/archive/` (p. ej. formulario de contacto para WordPress).

Evitar ciclos de refinamiento permanente que retrasen la implementación. Prioridad: **código y validación** según las fases definidas aquí.

---

## Fase 1: Documentación y diseño

1. **Completar identidad:** Extraer paleta y tipografía del PDF `Identidad CAMINO DEL DHARMA- (1).pdf` → actualizar `02-identidad-corporativa.md`
2. **Wireframes:** Estructura de bloques por pantalla según `06-wireframes` (y `04-mapa-pantallas`); en papel, Figma o HTML
3. **Validar documentación:** Revisar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** `18-tendencias-ux-ui-sistema-editorial` como filtro para decisiones de diseño

### Criterios de aceptación — Fase 1

La fase se considera **cerrada** cuando:

- [ ] Paleta y tipografía documentadas en `02-identidad-corporativa`
- [ ] Wireframes o equivalente HTML de bloques por pantalla según `04` y `06`
- [ ] Sin contradicciones entre docs referenciados (regla de dependencias en `00-orden-documentos`)
- [ ] Copy y estructura alineados con `content-source/` (prioridad máxima)
- [ ] Decisiones de diseño revisadas contra `18-tendencias-ux-ui-sistema-editorial`

---

## Fase 2: Maqueta estática

1. **Maqueta responsiva** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad)
2. Contenido según: `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `09-ui-copy-sheet`, `02-identidad-corporativa`
3. Assets desde `content-source/` copiados a `assets/` en la raíz del repo (regla en `15-assets-strategy`, inventario en `16-content-source-inventario`). Imágenes optimizadas con `scripts/optimize-images.sh`; galería con nombres unificados vía `scripts/rename-gallery-to-kebab.sh`.
4. **Validar contra checklist** de `18-tendencias-ux-ui-sistema-editorial` (§8) antes de dar por cerrada la fase
5. **Validar responsive:** Comportamiento en móvil, tablet y desktop antes de pasar a WordPress
6. **Validar CSS:** Ejecutar `npm run lint:css` después de cada cambio en `assets/css/` y antes de cerrar una tarea, crear un commit o desplegar. No se acepta una entrega con errores de Stylelint.

### 2.1 Estructura HTML final (base para WordPress)

La maqueta estática debe construirse con la misma estructura de rutas finales del sitio.  
La fase WordPress será una **adaptación directa del HTML a plantillas PHP**, sin rediseñar ni cambiar la estructura visual.

Estructura recomendada:

- `/index.html`
- `/comunidad/index.html`
- `/linaje/index.html`
- `/practica/index.html`
- `/eventos/index.html`
- `/galeria/index.html`
- `/contacto/index.html`
- `/donaciones/index.html`
- `/blog/index.html` (y entradas en `/blog/{slug}/`)
- `/404.html`
- `/assets/`

**Regla:**

- `/ruta/` → `ruta/index.html`
- URLs limpias desde el inicio
- No inventar rutas temporales

### 2.2 Correspondencia futura con WordPress

La maqueta define el layout definitivo. En Fase 3 solo se envolverá el HTML con WordPress:

| HTML estático             | WordPress                                |
| ------------------------- | ---------------------------------------- |
| `/index.html`             | `front-page.php`                         |
| `/comunidad/index.html`   | `page-comunidad.php`                     |
| `/linaje/index.html`      | `page-linaje.php`                        |
| `/practica/index.html`    | `page-practica.php`                      |
| `/eventos/index.html`     | `archive-event.php` o `page-eventos.php` |
| `/galeria/index.html`     | `page-galeria.php`                       |
| `/contacto/index.html`    | `page-contacto.php`                      |
| `/donaciones/index.html`  | `page-donaciones.php`                    |
| `/blog/index.html`        | `index.php` o `home.php` (según modelo)  |
| `/404.html`               | `404.php`                                |

### 2.3 Reglas para la maqueta

La maqueta debe comportarse como el sitio real:

- Usar clases definitivas (no temporales)
- No usar estilos inline
- Un solo CSS principal (`main.css`)
- HTML semántico desde el inicio
- Estructura de bloques igual a `06-wireframes`
- Microcopy final desde `09-ui-copy-sheet`

**Componentes implementados en la maqueta:** navegación principal + subnav (Galería, Blog, Contribuir, Contacto); título del sitio (site-title) en Inicio; hero con imagen contenida y fondo de color; calendario estático (un mes) en Eventos; sección Recitación práctica de la comida en Práctica (con enlace de descarga PDF); sección Mantras para la práctica en Práctica (Amitābha y Guān Shì Yīn Púsà con reproductor de audio); sidebar CTA contribuir en Inicio (junto a «Un poco de nuestra comunidad»); página Contribuir (donaciones) y Blog.

### 2.4 Simulación de estados dinámicos

Antes de WordPress, se validan flujos con contenido estático:

- **Eventos:**
  - Versión con evento
  - Versión sin evento (mensaje amable)
- **Single evento (opcional):** `/eventos/retiro/index.html`

Esto permite validar navegación real sin backend.

### 2.5 Invariantes de diseño (congelamiento arquitectónico)

Durante la migración a WordPress **no se rediseña** el sitio. WordPress adapta la maqueta; aporta motor de contenido, administración y eventos dinámicos (ADR 0002, ADR 0012).

Se consideran **estables** (congelamiento arquitectónico, no de contenido):

- Arquitectura de información y estructura de páginas
- Navegación principal y URLs (ADR 0008)
- Componentes y jerarquía visual
- Sistema de diseño, tokens de identidad y arquitectura CSS (ADR 0009)
- Modelo de contenido (`03-wordpress-content-model`)
- Jerarquía editorial y voz (`07`, `08`, `21`, `23`)
- Criterios de accesibilidad (`19`)

**Permitido durante la transición** (el sitio estático sigue en producción y recibe mantenimiento):

- Correcciones de errores y compatibilidad
- Mantenimiento de contenido y actualización de eventos
- Mejoras de accesibilidad, SEO y rendimiento
- Ajustes menores necesarios para producción estable

Cualquier cambio **estructural** importante debe documentarse y **reflejarse también en WordPress** (registro en `docs/migracion-static-wordpress.md`).

### 2.6 Referencia definitiva (no prototipo)

- La maqueta **no es un prototipo**; es la base visual y funcional del theme.
- La fase WordPress es **cambiar motor, no rediseñar.**

### 2.7 Convivencia temporal (resumen)

Detalle completo en **§ Transición estático → WordPress**. Resumen:

- Monorepo (desde Fase 3): `static/` (producción) + `wordpress/` (desarrollo) + `docs/` (compartido).
- Despliegues manuales; CI/CD pospuesto (ADR 0015, ADR 0016).
- Registro de diferencias: `docs/migracion-static-wordpress.md`.

> **Nota:** Hasta el inicio de la **Fase 3**, el sitio HTML permanece en la **raíz** del repositorio. La carpeta `static/` **no existe aún**; la reorganización raíz → `static/` es el **primer paso** de Fase 3 y **no debe adelantarse**.

### Criterios de aceptación — Fase 2

La fase se considera **cerrada para producción estática** cuando se cumplen los ítems marcados. Los pendientes explícitos se remedian en § Fase 2.75 o se resuelven en WordPress (formulario con backend).

- [x] Todas las páginas de la estructura §2.1 existen y son navegables (13/13 en sitemap; auditoría 2026-07-19)
- [x] Prioridad de páginas (§ más abajo) implementada según `04-mapa-pantallas`
- [x] Responsive validado en móvil, tablet y desktop (auditoría: 360/390/1440 sin overflow)
- [x] Checklist de `18-tendencias-ux-ui-sistema-editorial` (§8) completado en implementación
- [x] Accesibilidad estructural revisada según `19-accesibilidad-estandares` (skip link, landmarks, contraste en muestras, teclado; pase AT no verificado en auditoría)
- [x] `npm run lint:css` finaliza sin errores (obligatorio antes de cada despliegue)
- [~] Lighthouse ≥ 90 en las cuatro categorías — **no verificado en auditoría** (herramienta no disponible en sesión de audit); repetir en Fase 2.5 o mantenimiento trimestral
- [x] No existen enlaces rotos internos (400 refs OK; auditoría EVID-0015)
- [x] No existen imágenes informativas sin `alt` adecuado (100 % en auditoría)
- [~] El sitio funciona correctamente sin JavaScript — navegación y lectura sí; **galería** requiere JS (AEO-001, severidad baja); formulario no entrega sin JS ni con JS
- [x] SEO técnico inicial según `15-assets-strategy` (§12): títulos, canonical, OG, robots, sitemap — **100/100** en auditoría
- [~] Fase 2.5 (QA) — parcial; auditoría de producción (§2.75) cubre parte del alcance con limitaciones documentadas
- [ ] **Formulario de contacto operativo** — pendiente: estático tiene `action="#"` sin handler (FUNC-001); ver §2.75 y `docs/archive/contacto-formulario-estatico/`
- [ ] **Descargas `.ics` de eventos** — pendiente: archivos `/ical/*.ics` referenciados pero inexistentes (FUNC-002)

---

## Fase 2.5: QA (control de calidad)

Etapa explícita entre maqueta validada y WordPress (o despliegue, mientras la maqueta estática esté en producción). Hace visible el trabajo de verificación que de otro modo quedaría implícito.

### Navegadores

Probar en la versión estable más reciente de:

- Chrome
- Firefox
- Safari
- Edge

### Dispositivos y viewports

- **Desktop** (≥ 1280 px)
- **Tablet** (~768 px)
- **Móvil** (Android e iPhone, o emulación fiel en DevTools)

### Validaciones automáticas

- **Lighthouse** (Performance, Accessibility, Best Practices, SEO) — umbral ≥ 90
- **W3C Validator** (HTML) — opcional; corregir errores graves
- **`npm run lint:css`** — sin errores

### Validaciones manuales

- Navegación completa con teclado (`19` §11)
- Focus visible en enlaces, botones e inputs
- Formulario de contacto: labels y focus OK; **envío end-to-end pendiente** hasta backend estático o WordPress (FUNC-001)
- Contraste en combinaciones críticas de marca
- Lectura de flujos clave (screen reader opcional pero recomendado en releases)

### Criterios de aceptación — Fase 2.5

- [~] Sitio probado en los cuatro navegadores indicados — recomendado antes de corte WordPress; auditoría usó un entorno
- [x] Sitio probado en desktop, tablet y móvil (viewports auditados)
- [~] Lighthouse ≥ 90 en las cuatro categorías — pendiente de corrida local con Lighthouse instalado
- [x] Sin enlaces rotos internos (auditoría)
- [x] Incidencias documentadas — ver `.audit/findings.jsonl` y backlog §2.75

---

## Fase 2.75: Auditoría de producción (2026-07-19)

Auditoría de solo lectura sobre https://caminodeldharma.org y el código fuente (commit auditado `be896db2`; paridad deploy 14/14). **No implementó cambios.** Workspace completo en [`.audit/`](../.audit/README.md).

### Resultado resumido

| Métrica | Valor |
| ------- | ----- |
| Estado | COMPLETE · verificador ACCEPT |
| Score global | **84/100** |
| Hallazgos | 0 CRÍTICO · 2 ALTO · 4 MEDIO · 3 BAJO · 1 INFORMATIVO |
| SEO / SEO técnico | **100/100** — sin hallazgos accionables |
| Decisión HSTS | **ACTIVATE NOW — Fase 1** `max-age=604800` (7 d) → **Fase 2** `31536000` post-WordPress — `.audit/hsts-decision.md`, **ADR 0018** |

### Hallazgos que afectan operación (prioridad de remediación)

| ID | Severidad | Tema | Acción recomendada | Tarea |
| -- | --------- | ---- | ------------------ | ----- |
| FUNC-001 | ALTA | Formulario contacto no entrega (`action="#"`) | CTAs WhatsApp/correo (corto plazo) o backend real (WordPress) | TASK-0002 READY · TASK-0003 BLOCKED |
| FUNC-002 | ALTA | Descargas `.ics` → 404 | Crear `ical/*.ics` para ambos eventos | TASK-0001 READY |
| SEC-001 | MEDIA | HSTS comentado en `.htaccess:103` | Activar Fase 1 (`max-age=604800`) | TASK-0004 + TASK-0005 READY |
| PRIV-001 | MEDIA | GA4 sin consentimiento ni política | Decisión organizativa + TASK-0006 BLOCKED |
| PERF-001 | MEDIA | Imágenes sobredimensionadas | Logo + srcset galería | TASK-0007 READY |
| PERF-002 | BAJA | CSS/JS sin versionado `?v=` | TASK-0011 READY |
| SEC-002 | MEDIA | CSP mínima | Report-Only → enforce | TASK-0008 READY |
| SEC-003 | BAJA | Sin `security.txt` | TASK-0009 READY |
| AEO-001 | BAJA | Galería solo client-side | Pre-render primera página | TASK-0010 READY |

Detalle completo: `.audit/findings.jsonl`, paquetes en `.audit/remediation/`, tareas atómicas en `.audit/implementation/tasks/`.

### Olas de implementación (post-auditoría)

Orden en `.audit/implementation/waves.md`:

1. **WAVE-1** — HSTS (0004→0005), `.ics` (0001), contacto (0002); gate: HSTS verificado, sin 404 en `/ical/`, `/contacto` sin vía muerta
2. **WAVE-2** — Imágenes (0007)
3. **WAVE-3** — *(vacía: SEO/SD/contenido en verde)*
4. **WAVE-4** — CSP (0008), `security.txt` (0009), privacidad (0006 BLOCKED), `includeSubDomains` (0012 BLOCKED)
5. **WAVE-5** — Versionado assets (0011)
6. **WAVE-6** — Pre-render galería (0010)

**Arranque recomendado por la auditoría:** TASK-0004 → TASK-0005 → TASK-0001 → TASK-0002.

Implementar en sesiones separadas; cada tarea incluye criterios de aceptación, validación y rollback. Respetar `conflict-map.md` (no editar `.htaccess`, `contacto/index.html` ni las 14 páginas HTML en paralelo dentro del mismo conflict group).

### Respaldo formulario de contacto

Antes de TASK-0002 (retiro temporal del formulario) o de cualquier cambio en `/contacto`, el markup y estilos vigentes están archivados en:

**`docs/archive/contacto-formulario-estatico/`**

Contiene página completa, bloque `<main>`, extracto CSS y README con instrucciones para restaurar en estático o replicar en `page-contacto.php` (WordPress).

### Limitaciones de la auditoría (relevantes para este doc)

- Sin Lighthouse/axe en la sesión → LCP/INP no puntuados; CLS = 0 sí medido
- Sin datos de Search Console / GA4 interno
- Formulario inspeccionado en código, no enviado en vivo
- Conclusiones legales de privacidad fuera de alcance

Ver `.audit/limitations.md`.

### Relación con ADR 0010 e ADR 0018 (HSTS)

La auditoría satisface el checklist de ADR 0010. **ADR 0018** (Aceptada) define el despliegue escalonado: **Fase 1** ahora con `max-age=604800`; **Fase 2** con `max-age=31536000` tras WordPress estable en producción (≥30 días sin incidencias TLS/redirect). Alternativa ultra-conservadora: `max-age=300` o `86400`. Año completo desde el día uno sigue técnicamente válido pero no es la preferencia del proyecto durante la transición.

Implementación Fase 1: TASK-0004; verificación: TASK-0005. Registrar en `CHANGELOG.md`. `includeSubDomains` / preload: rechazados (TASK-0012).

---

## Transición estático → WordPress

**Decisiones consolidadas** (ADR 0012, 0014, 0015, 0016, 0017). Una sola base documental y un solo repositorio.

### Objetivo

- El **sitio estático** permanece la versión **oficial en producción** hasta que WordPress esté desarrollado, validado en staging y listo para reemplazarlo.
- Durante ese periodo el estático **sigue recibiendo mantenimiento**: correcciones, contenido, accesibilidad, SEO, rendimiento.
- **WordPress** se desarrolla en paralelo en staging; **no** se instala sobre producción hasta el corte final.
- La migración **no implica rediseño** (ADR 0002).

### Por qué WordPress

Administración por **terceros** sin HTML, Git ni acceso al servidor. WordPress aporta: gestión de contenido, usuarios, medios, entradas, eventos y flujos editoriales.

### Organización del repositorio

**Antes de implementar WordPress**, reorganizar (ADR 0014):

```text
camino-del-dharma/
├── static/                 # Sitio público actual (producción en Fase 3)
│   ├── index.html, 404.html
│   ├── assets/, blog/, comunidad/, …
│   ├── robots.txt, sitemap.xml, .htaccess, …
├── wordpress/
│   └── wp-content/
│       ├── themes/camino-del-dharma/
│       └── plugins/camino-del-dharma-core/   # si aplica
├── docs/                   # Incluye adr/, migracion-static-wordpress.md
├── scripts/
├── README.md, CHANGELOG.md, VERSION, package.json
```

**Estado actual (Fase 2):** HTML en la **raíz** del repo (= producción). La carpeta `static/` se crea al **iniciar Fase 3**.

> **Nota:** Hasta el inicio de la **Fase 3**, el contenido HTML permanece en la **raíz** del repositorio. La reorganización a `static/` forma parte del primer paso de Fase 3 y **no debe adelantarse**.

**No versionar en Git:** core WordPress, `wp-config.php`, credenciales, BD, cachés, backups, `uploads/` de producción.

### Fuentes de verdad durante la transición

| Ámbito | Fuente de verdad |
| ------ | ---------------- |
| Sitio público (producción) | `static/` |
| Implementación WordPress en desarrollo | `wordpress/` |
| Decisiones, requisitos, criterios | `docs/` |

No son equivalentes. Cambios en diseño, estructura, navegación, CSS, JS, a11y o SEO en producción **deben portarse a WordPress**. Contenido temporal (p. ej. eventos que caducan antes del corte) puede quedar solo en static.

**Registro obligatorio:** `docs/migracion-static-wordpress.md`.

### Despliegue durante la transición

**Manual únicamente** (ADR 0015). **CI/CD pospuesto** (ADR 0016).

| Destino | Qué subir | Dónde |
| ------- | --------- | ----- |
| **Producción (estático)** | Solo contenido de `static/` | `public_html` Hostinger |
| **Staging (WordPress)** | Theme y plugins propios | Entorno separado |

**Prohibido:** subir el repo completo, `docs/`, `scripts/` o `wordpress/` en desarrollo a producción pública.

Procedimiento estático: README (ZIP acotado a `static/` tras reorg). Validar con `npm run lint:css` antes de desplegar.

### Fuentes de verdad tras el corte (WordPress en producción)

| Dominio | Fuente de verdad |
| ------- | ---------------- |
| Código (theme, plugins propios, CSS, JS, plantillas) | **Git** |
| Contenido (páginas, entradas, eventos, usuarios, medios subidos) | **WordPress** (BD + uploads) |

Cuando se automatice el despliegue, `rsync --delete` **solo** sobre directorios versionados (theme, plugins propios) — nunca todo `public_html` ni `uploads/` (ADR 0013).

### Migración final (corte a producción)

1. Pausa temporal de cambios editoriales en static.
2. Revisar `docs/migracion-static-wordpress.md` (sin pendientes estructurales).
3. Última migración de contenido a WordPress.
4. Backup completo del sitio estático (tag Git final).
5. Backup WordPress (BD + medios).
6. Validar WordPress en staging (Fase 2.5).
7. Verificar: navegación, formularios, eventos, blog, SEO, a11y, redirects, HTTPS, caché, **HSTS Fase 1** (`max-age=604800`).
8. Cambio controlado a producción.
9. Smoke test del sitio público.
10. Static deja de recibir mantenimiento; **conservar** en tag/rama de archivo (no borrar de inmediato).
11. **Tras ≥30 días estables:** subir HSTS a Fase 2 (`max-age=31536000`, ADR 0018) y registrar en `CHANGELOG.md`.

WordPress pasa a ser la **única implementación activa**.

---

## Fase 3: WordPress

**Justificación:** CMS para terceros (ADR 0012). Ver § Transición estático → WordPress.

1. **Reorganizar repo:** raíz → `static/` (ADR 0014); actualizar README y procedimiento de despliegue
2. Crear theme en `wordpress/wp-content/themes/camino-del-dharma/`
3. Convertir HTML de `static/` a plantillas PHP (`12-theme-file-structure`)
4. Ajustar a `03-wordpress-content-model`, `11-arbol-urls-final`, `15-assets-strategy`
5. CPT `event`; roles editoriales; mantener registro en `migracion-static-wordpress.md`
6. WordPress en **staging separado**; Fase 2.5 sobre el theme
7. **Corte final** según checklist § Transición; static archivada en tag

### Criterios de aceptación — Fase 3

La fase se considera **cerrada** cuando:

- [ ] Theme refleja la maqueta congelada (§2.6): mismas URLs, bloques, copy y CSS
- [ ] Plantillas mapeadas según tabla §2.2 y `12-theme-file-structure`
- [ ] CPT `event` operativo si aplica; estados con/sin evento validados
- [ ] Contenido editable desde WordPress sin romper layout ni tokens
- [ ] Fase 2.5 repetida sobre el theme en staging antes de producción
- [ ] Checklist pre-lanzamiento (§ más abajo) completado en staging

---

## Fase 4: Despliegue

### Vigente: despliegue manual (transición)

ADR 0015, ADR 0016. **No hay pipelines de CI/CD activos** para despliegue.

**Sitio estático en producción:**

1. Actualizar `sitemap.xml` (en `static/` tras reorg), `VERSION`, `CHANGELOG.md`
2. `npm run lint:css` (sin errores)
3. ZIP **solo** con contenido de `static/` (no repo completo)
4. Subir a `public_html` en Hostinger
5. Smoke test y Search Console si aplica

**WordPress:** despliegue manual del theme a **staging** hasta el corte final.

### Futuro: automatización (pospuesta)

Cuando la estructura esté estable (ADR 0016):

- Validación en PR (`lint.yml` u equivalente, a crear)
- Deploy acotado: estático pre-corte; theme post-corte (ADR 0013)
- SSH + rsync con `--delete` solo en directorios versionados (ADR 0007)

### Criterios de aceptación — Fase 4 (estático, vigente)

- [ ] Despliegue realizado desde `main` (commit o tag documentado)
- [ ] `VERSION` y `CHANGELOG.md` actualizados
- [ ] Sitio en producción coincide con el artefacto del repositorio
- [ ] Smoke test post-despliegue: home, contacto, galería, blog, 404
- [ ] Cabeceras HTTP y `.htaccess` operativos (HTTPS, compresión, caché según config)
- [ ] Sitemap enviado o actualizado en Search Console

---

## Prioridad de páginas

1. **Inicio** — Hero, meditación semanal, caminos de participación
2. **Contacto** — Formulario (markup listo; **envío pendiente** — FUNC-001) + bloque Redes sociales + WhatsApp/correo. Respaldo: `docs/archive/contacto-formulario-estatico/`. En WordPress: formulario funcional vía plugin o handler del theme.
3. **La comunidad** — Quiénes somos, fundador
4. **Práctica** — Meditación, recitación comida, mantras (audio), talleres, retiros, videos
5. **El linaje** — Tradición, Chan, Tierra Pura
6. **Galería** — Página dedicada con álbumes titulados por año, evento o actividad; cada álbum tiene grid y paginación independiente de 12 imágenes por página. En Inicio se mantiene la fila de imágenes + enlace «Ver galería completa».
7. **Eventos** — Condicional; implementar cuando haya eventos vigentes

---

## Regla

No escribir código de theme WordPress ni subir a servidor final hasta que la maqueta estática esté validada (Fase 2 + Fase 2.5). **Excepción operativa:** remediación post-auditoría (§2.75) sobre el estático en producción está permitida y priorizada (HSTS, `.ics`, contacto, rendimiento) sin iniciar Fase 3.

---

## Registro de decisiones arquitectónicas

Las decisiones técnicas y estructurales relevantes del proyecto deben documentarse mediante **Architecture Decision Records (ADR)** en `docs/adr/`.

Crear un ADR cuando una decisión:

- afecte la arquitectura, el despliegue, la seguridad o la estructura de contenido;
- implique elegir entre varias alternativas razonables;
- establezca una restricción que deba mantenerse en fases posteriores;
- pueda resultar difícil de entender sin conocer su contexto original.

Cada ADR debe indicar **estado**, **fecha**, **contexto**, **decisión**, **alternativas consideradas** y **consecuencias**. Estados controlados: Propuesta, Aceptada, Rechazada, Sustituida, Obsoleta (ver `docs/adr/README.md`).

Los ADR **aceptados** se consideran históricos e **inmutables**. Si una decisión cambia, se crea un **nuevo** ADR que la sustituye y se enlazan ambos; no se reescribe el ADR original.

La implementación y la documentación general del proyecto deben mantenerse alineadas con los ADR vigentes. Índice completo: [`docs/adr/README.md`](adr/README.md).

---

## Checklist pre-lanzamiento

- [x] Identidad (paleta, tipografía) definida
- [x] Todas las páginas maquetadas (13 indexables + 404)
- [ ] Formulario de contacto **funcional** (entrega end-to-end) — pendiente; canales WhatsApp/correo operativos
- [x] Botón WhatsApp operativo
- [x] Enlaces externos (redes) verificados
- [x] Datos bancarios correctos en footer
- [x] Accesibilidad: estándares 19 aplicados en estructura (contraste muestreado, alt, teclado, focus, labels de formulario)
- [x] `npm run lint:css` finaliza sin errores
- [x] SEO técnico (`15` §12): `<title>`, canonical, OG por página; `robots.txt` y `sitemap.xml` actualizados (auditoría 100/100)
- [x] Sin `<link rel="manifest">` ni PWA (15 §11)
- [x] Eventos con URL propia: JSON-LD `Event` completo según §12.3; listado `/eventos/` sin microdata duplicada
- [ ] Descargas `.ics` de calendario operativas (FUNC-002)
- [ ] HSTS Fase 1 activo (`max-age=604800`) tras TASK-0004 — ADR 0018
- [ ] HSTS Fase 2 (`max-age=31536000`) tras WordPress estable en producción
- [ ] Política de privacidad / consentimiento GA4 si aplica (PRIV-001 — decisión organizativa)
- [ ] Google Search Console: sitemap enviado; solicitar indexación de URLs modificadas tras cada despliegue relevante

---

## Mantenimiento (post-publicación)

Tareas periódicas una vez el sitio está en producción. No son desarrollo de features, pero forman parte del ciclo de vida del sitio.

| Frecuencia | Tarea |
| ---------- | ----- |
| **Tras cada despliegue** | Smoke test de URLs clave; verificar enlaces del footer; si hay formulario, probar envío end-to-end |
| **Post-auditoría (2026-07-19)** | Ejecutar olas §2.75 según `.audit/implementation/backlog.md`; registrar en `CHANGELOG.md` |
| **Mensual** | Revisión de enlaces rotos (internos y externos) |
| **Mensual** | Comprobar que `sitemap.xml` refleja el inventario de URLs indexables |
| **Trimestral** | Auditoría Lighthouse (home + una página interior) |
| **Trimestral** | Revisión de accesibilidad según `19-accesibilidad-estandares` (§10–11) |
| **Trimestral** | Revisión de cabeceras HTTP, HTTPS, HSTS y reglas de `.htaccess` |
| **Semestral** | Revisión de Search Console (cobertura, errores, rendimiento) |
| **Anual** | Renovar `/.well-known/security.txt` (Expires) si se publicó (TASK-0009) |
| **Según necesidad** | Limpieza de contenido obsoleto (eventos pasados, entradas de blog desactualizadas) |
| **Según necesidad** | Actualización de dependencias de desarrollo (`npm`; Stylelint) |

Incidencias detectadas en mantenimiento siguen la **política de cambios**: documentar si afectan arquitectura o navegación; corregir directamente si son bugs o contenido editorial alineado con `content-source/`.

---

## Cierre

Este documento define el **orden oficial de implementación** y el **ciclo de vida completo** del sitio:

**Documentación y diseño → maqueta estática validada → QA → auditoría producción (§2.75) → remediación → transición (static/ + wordpress/) → corte WordPress → mantenimiento.**

Durante la transición: un solo repo, despliegues manuales del estático, WordPress en staging, registro en `migracion-static-wordpress.md`. CI/CD pospuesto (ADR 0016).

A partir de la versión 3.0, **priorizar implementación** sobre ampliación de documentación de alto nivel (ver § Congelamiento de documentación base). La versión 3.1 incorpora el estado real post-auditoría: el estático puede recibir mejoras de §2.75 sin bloquear la planificación de Fase 3.

---

**Versión:** 3.1 · **Fecha:** 2026-07-19 · **Estado:** Vigente
