# Camino del Dharma — Orden de implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**

## Propósito

Este documento define el orden oficial de implementación, validación, migración y mantenimiento del sitio Camino del Dharma y actúa como referencia para todas las fases del proyecto.

| | |
| --- | --- |
| **Versión** | 3.10 |
| **Fecha** | 2026-08-31 |
| **Estado** | Vigente |

### Cambios principales (3.10)

- ADR 0041 / OWN-018: Contact Form 7 es elegible en el corte sin esperar asesoría legal. El
  disclaimer de `/privacidad` basta para lanzar. En WordPress se actualizan solo los párrafos del
  formulario. FABLE5 v2.4.

### Cambios principales (3.9)

- OWN-017 y ADR 0040 retiran permanentemente la fuente legacy sin respaldo. Producción publicada es
  el único baseline pre-corte; el repo `VERSION` se compara con Hostinger.
- FABLE5 v2.3, backlog v1.20 y documentación operativa quedan alineados con ese retiro.

### Cambios principales (3.8)

- Backlog de dueño v1.19: Fase 3 sigue cerrada (0 abiertas). Se añaden `POST-001`–`POST-007`
  (inglés / i18n) como decisiones de **fases posteriores**; no bloquean ni se implementan en el
  corte (`docs/backlog-decisiones-owner-migracion.md`).

### Cambios principales (3.7)

- Reglas `.cursor` de fuentes editoriales alineadas con OWN-007 y ADR 0034: el sitio publicado gana
  antes del corte. El tratamiento como referencia protegida fue sustituido después por ADR 0040.
- FABLE5 v2.2 eliminó el gate documental de ese momento. Fase 3 quedó lista para iniciar WU-00/WU-01
  cuando el propietario lo autorice; WordPress continúa sin implementación.

### Cambios principales (3.6)

- Taxonomía de pruebas y TDD para el plugin + theme FSE (ADR 0038). Alcance de
  SonarQube Cloud en `.sonarcloud.properties`. El kit PHPUnit se crea el día uno del
  PHP propio, no antes.

### Cambios principales (3.5)

- Backlog de dueño de la auditoría **cerrado** (v1.18). ADR 0035–0037: singles de eventos, álbumes
  `/galeria/{slug}` noindex, CPT autor `/author/{slug}`.
- ICS generado; pasados sin calendario; fecha de fin automática; autores no son Users WP.

### Cambios principales (3.4)

- El estático live es **contenido de producción** (ADR 0034): inventario, conteos, redirect ledger.
- Eventos/blog/galería hardcodeados no son demo. Extraer, no reescribir. Freeze/delta al corte.

### Cambios principales (3.3)

- Aclara que Fase 3 es **static → FSE** (sin theme clásico PHP intermedio). §2.2 mapea HTML estático a `templates/*.html` solamente.
- Contrato de migración, matriz y checklist de cutover (ADR 0032, ADR 0033). Completitud ≠ theme activado.
- Documentación nueva de migración permitida sin levantar el congelamiento de la base (ADR + contrato).

### Cambios principales (3.1)

- **Estado actual del proyecto:** sitio estático en producción (`v1.0.11`), Fase 2 sustancialmente completada; Fase 2.5 y auditoría de producción registradas.
- **Nueva § Fase 2.75:** auditoría integral de producción (2026-07-19), hallazgos, olas de remediación y enlace a `.audit/`.
- **Formulario de contacto:** estado real documentado (markup presente, envío no operativo en estático); respaldo en `docs/archive/contacto-formulario-estatico/` para restauración en WordPress.
- **HSTS:** **aplazado** (ADR 0020) — no se activa durante la transición; se revisa tras el corte a WordPress y ≥30 días estables. ADR 0018 queda sustituida en lo operativo.
- Criterios de aceptación de Fase 2 y checklist pre-lanzamiento actualizados según evidencia de la auditoría.
- **ADR 0018:** HSTS escalonado — sustituida en lo operativo por ADR 0020.
- **ADR 0020:** HSTS aplazado hasta después del corte a WordPress. Motivo: sitio de 2 días, ~9 clics/28 días (EVID-0052) y migración inminente que puede tocar TLS y redirects.
- **ADR 0019:** sin analítica con cookies. GA4 descartado definitivamente; medición por Search Console.
  *(Nota de vigencia 2026-08-30: los embeds ya usan `youtube-nocookie.com`; `/privacidad` está
  publicada — ADR 0039.)*
- Mantenimiento: incorporación de tareas post-auditoría y revisión trimestral ampliada.

### Cambios principales (3.0)

- Incorporación de criterios de aceptación por fase
- Incorporación de Fase 2.5 (QA)
- Incorporación de § Transición estático → WordPress
- Incorporación de registro ADR y referencia a `docs/adr/README.md`
- Incorporación de mantenimiento post-publicación
- Despliegue/CD manual y automatización de deploy pospuesta (ADR 0015, ADR 0016). El CI de calidad
  pasa a ser obligatorio al existir tests PHP (ADR 0038).
- Congelamiento de documentación base (§ más abajo)

**Depende de:** `02-identidad-corporativa`, `03-wordpress-content-model`, `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `06-wireframes`, `09-ui-copy-sheet`, `11-arbol-urls-final`, `12-theme-file-structure`, `13-static-file-structure`, `14-css-architecture`, `15-assets-strategy`, `18-tendencias-ux-ui-sistema-editorial`, `19-accesibilidad-estandares`, `inventario-contenido-produccion-static`, `migracion-static-wordpress`, `docs/adr/README.md`, `.audit/` (auditoría de producción 2026-07-19)

---

## Estado actual del proyecto (2026-08-29)

Tres tiempos (no mezclar):

| Tiempo | Hecho |
| ------ | ----- |
| **CURRENT STATE** | Fase 2 **live** (`https://caminodeldharma.org`, visitas reales). HTML en la **raíz**. Eventos (10), posts (2) y galería JSON (35+3) en HTML son producción (ADR 0034). La fuente legacy fue eliminada (ADR 0040). `static/` **no existe**. `wordpress/` tiene árboles placeholder (README, sin código) para Sonar (ADR 0038). WordPress **no iniciado**. ZIP manual (ADR 0015). `VERSION` en la raíz. |
| **HISTORICAL STATE** | Auditoría 2026-07-19 (Fase 2.75). Docs previos a ADR 0029 describían un theme clásico PHP (`docs/04` conserva esa tabla como histórico). Restos de un WordPress anterior en `.htaccess`. El nombre «maqueta» en docs antiguos no significa prototipo desechable (ADR 0001). |
| **FUTURE PLAN** | Fase 3: reorg ADR 0014. **Estático de producción → FSE** (ADR 0029); no hay theme PHP intermedio. Plugin ADR 0024. Extracción + import (ADR 0033/0034). WP en **otra instancia Hostinger sin dominio custom** (staging, OWN-005) hasta el switch. Corte: `docs/cutover-checklist-wordpress.md`. Producción estática **sigue** hasta el corte. |

| Aspecto | Estado |
| ------- | ------ |
| **Fase activa** | Fase 2 **en producción** (sitio estático live); mantenimiento; Fase 3 **no iniciada** |
| **URL producción** | [https://caminodeldharma.org](https://caminodeldharma.org) |
| **Estructura repo** | HTML en **raíz** (carpeta `static/` aún no existe) |
| **WordPress** | No iniciado (Fase 3 pendiente; decisiones de **corte** cerradas). i18n/inglés = `POST-*` (fases posteriores, no el corte). Árboles placeholder bajo `wordpress/` para Sonar (ADR 0038); sin `theme.json` ni PHP. FABLE5 v2.3 puede iniciar WU-00/WU-01 cuando el propietario lo autorice. Activar un theme futuro **no** crea Pages (ADR 0032). |
| **Contrato de migración** | `docs/contrato-migracion-static-wordpress.md` + inventario ADR 0034 |
| **Auditoría producción** | **COMPLETE** (2026-07-19) — ver § Fase 2.75 y `.audit/` |
| **GA4** | **Descartado de forma definitiva** (ADR 0019) |
| **HSTS** | Aplazado (ADR 0020) |

La fila «versión desplegada `1.0.11`» del 2026-07-19 es **histórica**. La versión de código vigente está en `VERSION`.

La maqueta cumple la estructura §2.1 (URLs indexables en `sitemap.xml` + 404). Pendientes de cierre operativo: formulario de contacto sin backend; aviso de privacidad publicado y provisional (ADR 0039). HSTS aplazado (ADR 0020). **ADR 0019** descarta la analítica con cookies.

---

## Principios transversales

### Fuentes de verdad durante producción estática

**Git gobierna el código y los artefactos de despliegue; el sitio publicado gobierna el contenido
visible hasta el corte (OWN-007).**

- Todo cambio debe realizarse en el repositorio. No se editarán archivos directamente en el servidor de producción.
- El servidor (Hostinger) es un **destino de despliegue**, no un entorno de edición.
- Copy institucional y presentación: **sitio publicado** (OWN-007, ADR 0040).
- Eventos, posts y JSON de galería: el HTML live es la fuente de producción **hasta extraerse** (ADR 0034). No descartarlo como maqueta.
- El código y los assets viven en el repo según `13-static-file-structure` y `15-assets-strategy`.
- Cambios manuales en el servidor se pierden en el siguiente ZIP y no deben realizarse.
- Decisión formal: ADR 0004, ADR 0005, ADR 0034.

### Política de cambios

Toda modificación que afecte **estructura**, **navegación**, **identidad visual** o **arquitectura** debe reflejarse **primero** en la documentación correspondiente (`docs/`) antes de implementarse.

Orden recomendado:

1. Actualizar el doc afectado (p. ej. `05`, `11`, `14`, `15`).
2. Implementar en código.
3. Validar según los criterios de aceptación de la fase activa.

Excepciones permitidas sin doc previo: corrección de errores tipográficos alineados con el sitio
publicado, bugs de accesibilidad y ajustes de performance que no alteren la arquitectura.

### Estrategia de versionado

| Aspecto | Regla |
| -------- | ------ |
| **Ramas** | [Conventional Branch](https://conventionalbranch.org/) en ramas cortas (`feature/…`, `fix/…`, `chore/…`, `cursor/…`). Ver `docs/git-workflow.md`. |
| **Integración** | **`main` protegida** (ADR 0043): solo Pull Request; checks `php` + `css` obligatorios. CI en push a `main` y en PR (ADR 0038). |
| **Etiquetas** | Releases etiquetados (`v1.0.0`, `v1.1.0`, …) al desplegar a producción. |
| **Versión en repo** | Archivo `VERSION` y entrada en `CHANGELOG.md` (ver README). |
| **Producción** | Solo desde `main` (o tag asociado a un commit de `main`). |

Semántica sugerida: **MAJOR** (cambio estructural o de URLs), **MINOR** (nueva sección o funcionalidad), **PATCH** (correcciones y ajustes menores).

### Congelamiento de documentación base

A partir de la versión 3.0 de este documento, la **documentación arquitectónica base** se considera
**suficiente y congelada**. Los cambios sobre esos documentos deben ser **excepcionales** y responder
a necesidades reales del proyecto (error, requisito nuevo validado, inconsistencia con producción o
una decisión vigente), no a refinamiento continuo.

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

**Documentación nueva permitida sin levantar el congelamiento:** ADR en `docs/adr/`, `docs/contrato-migracion-static-wordpress.md`, `docs/matriz-migracion-static-wordpress.md`, `docs/cutover-checklist-wordpress.md`, entradas en `docs/migracion-static-wordpress.md`, guías operativas puntuales (incl. `docs/guia-pruebas-plugin-theme-fse.md`), `CHANGELOG.md`, actualizaciones de `12`, `13`, `15` cuando la implementación lo exija de forma concreta, respaldos en `docs/archive/` (p. ej. formulario de contacto para WordPress).

Evitar ciclos de refinamiento permanente que retrasen la implementación. Prioridad: **código y validación** según las fases definidas aquí.

---

## Fase 1: Documentación y diseño

1. **Completar identidad (histórico, realizado):** la paleta y tipografía se extrajeron a
   `02-identidad-corporativa.md`; la fuente legacy fue retirada después por ADR 0040.
2. **Wireframes:** Estructura de bloques por pantalla según `06-wireframes` (y `04-mapa-pantallas`); en papel, Figma o HTML
3. **Validar documentación:** Revisar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** `18-tendencias-ux-ui-sistema-editorial` como filtro para decisiones de diseño

### Criterios de aceptación — Fase 1

La fase se considera **cerrada** cuando:

- [ ] Paleta y tipografía documentadas en `02-identidad-corporativa`
- [ ] Wireframes o equivalente HTML de bloques por pantalla según `04` y `06`
- [ ] Sin contradicciones entre docs referenciados (regla de dependencias en `00-orden-documentos`)
- [x] Copy y estructura implementados desde las fuentes iniciales; la producción publicada pasa a
  gobernar durante la migración bajo OWN-007
- [ ] Decisiones de diseño revisadas contra `18-tendencias-ux-ui-sistema-editorial`

---

## Fase 2: Maqueta estática

1. **Maqueta responsiva** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad)
2. Contenido según: `04-mapa-pantallas`, `05-arquitectura-informacion-navegacion`, `09-ui-copy-sheet`, `02-identidad-corporativa`
3. Assets versionados en `assets/` en la raíz del repo (regla en `15-assets-strategy`; inventario
   vigente en `inventario-contenido-produccion-static`). Imágenes optimizadas con
   `scripts/optimize-images.sh`; galería con nombres unificados vía
   `scripts/rename-gallery-to-kebab.sh`.
4. **Validar contra checklist** de `18-tendencias-ux-ui-sistema-editorial` (§8) antes de dar por cerrada la fase
5. **Validar responsive:** Comportamiento en móvil, tablet y desktop antes de pasar a WordPress
6. **Validar CSS:** Ejecutar `npm run lint:css` después de cada cambio en `assets/css/` y antes de cerrar una tarea, crear un commit o desplegar. No se acepta una entrega con errores de Stylelint.

### 2.1 Estructura HTML final (base para WordPress)

La maqueta estática debe construirse con la misma estructura de rutas finales del sitio.  
La fase WordPress es una **adaptación sin rediseño** (ADR 0002) al theme de bloques (ADR 0029), no un rediseño ni un volcado de HTML estático sobre WordPress.

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

### 2.2 Correspondencia futura con WordPress (static → FSE)

Ruta **única** de Fase 3 (ADR 0029, ADR 0032): cada HTML de la maqueta se adapta a una plantilla de
**bloques** (`templates/*.html`). No hay theme clásico PHP en el medio. El copy editorial de
eventos/posts/galería vive en la BD, no en `templates/` ni en `patterns/` (ADR 0034).

> Docs anteriores a ADR 0029 hablaban de `front-page.php` / `page-*.php`. Eso es **HISTORICAL STATE**.
> No se implementa. Un archivo en `templates/` **no** crea la Page ni la URL (ADR 0032).

La maqueta define el layout definitivo. En Fase 3 se adapta a FSE **sin rediseño** (ADR 0002):

| HTML estático | Plantilla FSE |
| ------------- | ------------- |
| `/index.html` | `templates/front-page.html` |
| `/comunidad/index.html` | `templates/page-comunidad.html` |
| `/linaje/index.html` | `templates/page-linaje.html` |
| `/practica/index.html` | `templates/page-practica.html` |
| `/eventos/index.html` | `templates/archive-event.html` (no Page slug `eventos`) |
| `/galeria/index.html` | `templates/page-galeria.html` — bloque Gutenberg; `gallery.js` no se migra (ADR 0021) |
| `/contacto/index.html` | `templates/page-contacto.html` + Contact Form 7 (ADR 0026), elegible en el corte (ADR 0041) |
| `/privacidad/index.html` | `templates/page.html` (fallback; ADR 0039) |
| `/donaciones/index.html` | `templates/page-donaciones.html` |
| `/blog/index.html` | `templates/home.html` |
| `/404.html` | `templates/404.html` |

Filas, JS y assets por URL: `docs/matriz-migracion-static-wordpress.md`. Geografía del theme: `docs/12-theme-file-structure.md`.

### 2.3 Reglas para la maqueta

La maqueta debe comportarse como el sitio real:

- Usar clases definitivas (no temporales)
- No usar estilos inline
- Un solo CSS principal (`main.css`)
- HTML semántico desde el inicio
- Estructura de bloques igual a `06-wireframes`
- Microcopy final desde `09-ui-copy-sheet`

**Componentes implementados en la maqueta:** navegación principal + subnav (Galería, Blog,
Contribuir, Contacto); título del sitio (site-title) en Inicio; hero con imagen contenida y fondo de
color; calendario estático (un mes: eventos rellenos; lunes de meditación con borde y tooltip; aviso
«Toca de nuevo para ver el evento.» al primer toque en puntero grueso) en Eventos; sección Mantras
para la práctica en Práctica (Amitābha y Guān Shì Yīn Púsà con reproductor de audio); nota de un
evento vigente en Inicio (rótulo, cartel `medium`, «Ver evento», junto a «Un poco de nuestra
comunidad»); página Contribuir (donaciones) y Blog. La antigua recitación de la comida y su PDF
quedaron retirados por OWN-002.

### 2.4 Simulación de estados dinámicos

Antes de WordPress, se validan flujos con contenido estático:

- **Eventos:**
  - Versión con evento
  - Versión sin evento (mensaje amable)
- **Single de evento en Fase 2:** tres URLs implementadas. En Fase 3, los 10 eventos tienen single
  público según ADR 0035.

Esto permite validar navegación real sin backend.

### 2.5 Invariantes de diseño (congelamiento arquitectónico)

Durante la migración a WordPress **no se rediseña** el sitio. WordPress adapta la maqueta; aporta motor de contenido, administración y eventos dinámicos (ADR 0002, ADR 0012).

Se consideran **estables** (congelamiento arquitectónico, no de contenido):

- Arquitectura de información y estructura de páginas
- Navegación principal y URLs (ADR 0008)
- Componentes y jerarquía visual
- Sistema visual base y CSS del estático. En WordPress se traduce a `theme.json` y Global Styles
  editables según ADR 0029.
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
- Despliegues manuales; automatización de deploy/CD pospuesta (ADR 0015, ADR 0016). CI de calidad
  obligatorio cuando existan tests PHP (ADR 0038).
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
- [~] El sitio funciona correctamente sin JavaScript — navegación y lectura sí; **galería** requiere JS (AEO-001, severidad baja; **sigue abierto**: TASK-0010 figuró COMPLETED sin implementarse, ver `.audit/README.md`); formulario no entrega sin JS ni con JS
  - En WordPress se resuelve solo: el bloque de galería renderiza en servidor (ADR 0021). Si antes del corte se añadiera un visor propio a la maqueta, **primero** hay que cerrar TASK-0010: si no, se aumenta la dependencia de JS que este mismo criterio señala.
- [x] SEO técnico inicial según `15-assets-strategy` (§12): títulos, canonical, OG, robots, sitemap — **100/100** en auditoría
- [~] Fase 2.5 (QA) — parcial; auditoría de producción (§2.75) cubre parte del alcance con limitaciones documentadas
- [ ] **Formulario de contacto operativo** — pendiente: estático tiene `action="#"` sin handler (FUNC-001); ver §2.75 y `docs/archive/contacto-formulario-estatico/`
- [~] **Descargas `.ics` de eventos** — existen dos archivos en el estático. En WordPress, Círculos
  se genera mientras siga vigente y Encuentro 2026 responde 410; no se importan como media
  (OWN-009/012/013).

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

Auditoría de solo lectura sobre
[https://caminodeldharma.org](https://caminodeldharma.org) y el código fuente (commit auditado
`be896db2`; paridad deploy 14/14). **No implementó cambios.** Workspace completo en
[`.audit/`](../.audit/README.md).

### Resultado resumido

| Métrica | Valor |
| ------- | ----- |
| Estado | COMPLETE · verificador ACCEPT |
| Score global | **84/100** |
| Hallazgos | 0 CRÍTICO · 2 ALTO · 4 MEDIO · 3 BAJO · 1 INFORMATIVO |
| SEO / SEO técnico | **100/100** — sin hallazgos accionables |
| Decisión HSTS | **HISTÓRICA:** la auditoría recomendó activarlo por fases; ADR 0020 lo aplazó hasta ≥30 días después del corte WordPress |

### Hallazgos que afectan operación (prioridad de remediación)

| ID | Severidad | Tema | Acción recomendada | Tarea |
| -- | --------- | ---- | ------------------ | ----- |
| FUNC-001 | ALTA | Formulario contacto no entrega (`action="#"`) | CTAs WhatsApp/correo (corto plazo) o backend real (WordPress) | TASK-0002 READY · TASK-0003 BLOCKED |
| FUNC-002 | ALTA | Descargas `.ics` → 404 | Crear `ical/*.ics` para ambos eventos | TASK-0001 READY |
| SEC-001 | MEDIA | HSTS comentado en `.htaccess:103` | No activar durante la transición; reevaluar según ADR 0020 | TASK-0004 + TASK-0005 SUPERSEDED |
| PRIV-001 | MEDIA | GA4 sin consentimiento ni política | Decisión organizativa | TASK-0006 BLOCKED |
| PERF-001 | MEDIA | Imágenes sobredimensionadas | Logo + srcset galería | TASK-0007 READY |
| PERF-002 | BAJA | CSS/JS sin versionado `?v=` | Versionar assets | TASK-0011 READY |
| SEC-002 | MEDIA | CSP mínima | Report-Only → enforce | TASK-0008 READY |
| SEC-003 | BAJA | Sin `security.txt` | Publicar archivo | TASK-0009 READY |
| AEO-001 | BAJA | Galería solo client-side | Pre-render primera página | TASK-0010 READY |

Detalle completo: `.audit/findings.jsonl`, paquetes en `.audit/remediation/`, tareas atómicas en `.audit/implementation/tasks/`.

### Olas de implementación (post-auditoría)

Orden en `.audit/implementation/waves.md`:

1. **WAVE-1 (histórica)** — HSTS (0004→0005), `.ics` (0001), contacto (0002). HSTS quedó
   sustituido por ADR 0020; no ejecutar esas tareas durante la transición.
2. **WAVE-2** — Imágenes (0007)
3. **WAVE-3** — *(vacía: SEO/SD/contenido en verde)*
4. **WAVE-4** — CSP (0008), `security.txt` (0009), privacidad (0006 BLOCKED), `includeSubDomains` (0012 BLOCKED)
5. **WAVE-5** — Versionado assets (0011)
6. **WAVE-6** — Pre-render galería (0010)

**Arranque histórico recomendado por la auditoría:** TASK-0004 → TASK-0005 → TASK-0001 → TASK-0002.
La recomendación HSTS fue sustituida por ADR 0020; el orden vigente de Fase 3 está en FABLE5 v2.4.

Implementar en sesiones separadas; cada tarea incluye criterios de aceptación, validación y rollback. Respetar `conflict-map.md` (no editar `.htaccess`, `contacto/index.html` ni las 14 páginas HTML en paralelo dentro del mismo conflict group).

### Respaldo formulario de contacto

Antes de TASK-0002 (retiro temporal del formulario) o de cualquier cambio en `/contacto`, el markup y estilos vigentes están archivados en:

**`docs/archive/contacto-formulario-estatico/`**

Contiene página completa, bloque `<main>`, extracto CSS y README histórico para restaurar el
estático. En WordPress, adaptar el contrato visual a `templates/page-contacto.html` y usar Contact
Form 7 según ADR 0026/0039; no crear `page-contacto.php` ni un handler de formulario en el theme.

### Limitaciones de la auditoría (relevantes para este doc)

- Sin Lighthouse/axe en la sesión → LCP/INP no puntuados; CLS = 0 sí medido
- Sin datos de Search Console / GA4 interno
- Formulario inspeccionado en código, no enviado en vivo
- Conclusiones legales de privacidad fuera de alcance

Ver `.audit/limitations.md`.

### Relación histórica con ADR 0010 e ADR 0018 (HSTS)

La auditoría satisfizo el checklist de ADR 0010 y recomendó el despliegue escalonado de ADR 0018.
**ADR 0020 sustituyó esa acción en lo operativo:** no activar HSTS durante la transición ni el día
del corte. Reevaluar únicamente tras ≥30 días estables en WordPress y registrar cualquier decisión
nueva en `CHANGELOG.md`. TASK-0004/TASK-0005 quedan históricas; `includeSubDomains` y preload siguen
fuera de alcance.

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

**Deploy manual únicamente** (ADR 0015). **Automatización de CD pospuesta** (ADR 0016).
El workflow de calidad de ADR 0038 no despliega y se crea cuando existan tests PHP.

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

Checklist durable (pre / cutover / post): [`docs/cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md).
Contrato de completitud: [`docs/contrato-migracion-static-wordpress.md`](contrato-migracion-static-wordpress.md).

```text
DEPLOY SUCCESS ≠ APPLICATION SUCCESS
```

Un ZIP o File Manager verde no prueba Pages, routing, JS ni SEO.

Resumen (detalle en el checklist):

1. Pausa temporal de cambios editoriales en static.
2. Matriz completa; ledger `docs/migracion-static-wordpress.md` sin pendientes estructurales.
3. Importación de contenido (ADR 0033): Pages reales, no solo templates en disco.
4. Backup completo del sitio estático (tag Git final).
5. Backup WordPress (BD + medios).
6. Validar WordPress en staging (Fase 2.5 sobre el theme).
7. Verificar: navegación, formularios, eventos, blog, SEO, a11y, redirects, HTTPS, caché. **HSTS sigue aplazado** el día del corte (ADR 0020).
8. Cambio controlado a producción. Retirar el deploy ZIP estático sobre `public_html`.
9. Smoke test anónimo del sitio público.
10. Static deja de recibir mantenimiento; **conservar** en tag/rama de archivo (no borrar de inmediato).
11. **Tras ≥30 días estables:** revisar HSTS (ADR 0020 / 0018) y registrar en `CHANGELOG.md`.

WordPress pasa a ser la **única implementación activa**. Activar el theme **no** cierra este corte.

---

## Fase 3: WordPress

**Justificación:** CMS para terceros (ADR 0012). Ver § Transición estático → WordPress.

1. **WU-00/WU-01:** preflight durable y reorganización raíz → `static/` (ADR 0014). Detenerse antes
   de Docker.
2. **WU-02, sesión separada:** entorno local Docker (ADR 0023). Validar y detenerse antes de código
   de aplicación.
3. **WU-03:** scaffold de `camino-del-dharma-core` y kit Composer/PHPUnit/wp-phpunit. El primer PHP
   nace después de una prueba roja (ADR 0038). Sonar cubre solo plugin + theme.
4. **WU-04:** scaffold del theme FSE `camino-del-dharma`, `theme.json` y baseline visual. No crear
   un theme PHP clásico como puente (ADR 0029).
5. **WU-05:** dominio y rutas de `event`, `gallery_album`, `blog_author`, calendario e `.ics`
   generado (ADR 0035–0037, OWN-009/012–015).
6. **WU-06:** extractor y WP-CLI `validate → plan → import → verify`, idempotente y
   create-missing-only (ADR 0033/0034).
7. **WU-07/WU-08:** Pages, posts, autores, media, templates, galería, comportamiento,
   accesibilidad, SEO y redirects. Template ≠ objeto editorial.
8. **WU-09:** implementar y probar Contact Form 7 localmente y en staging con datos sintéticos.
   Elegible en producción al corte (ADR 0041 / OWN-018). Actualizar en WordPress los párrafos del
   formulario en `/privacidad`. La revisión legal no es prerrequisito.
8b. **BUG-001 (justo antes de WU-10) — cerrado 2026-08-31:** el `.ics` de Círculos incluye
   **todas las sesiones** (`event_calendar_dates`), un VEVENT con UID propio cada una; diálogo y
   archivo comparten fuente, y como un enlace profundo lleva una sola entrada, el diálogo nombra
   la próxima sesión y lo dice. El `.ics` estático de la sola bienvenida no se copió ni se tocó.
9. **WU-10:** QA local completa y runbook de staging. Desplegar a la instancia Hostinger separada
   solo con autorización expresa (OWN-005).
9b. **D-08 / OWN-020 (después de WU-10, pendiente):** SEO/AEO de `/author/{slug}` con copy corto
    y fotos publicados. Decidido; **no implementado**. Issue
    [#5](https://github.com/refo44/demo-caminodeldharma/issues/5). TDD (ADR 0038). No `noindex`
    en singles. No dummy.
9c. **D-09 / OWN-021:** overflow 339 vs 320 px en `/blog/sangha-refugio-hiperconexion` **dejado**
    en el corte (paridad live). Wrap WordPress-only **post-corte** (POST-008,
    [#7](https://github.com/refo44/demo-caminodeldharma/issues/7)) cuando WP ya sirva
    `caminodeldharma.org`.
9d. **D-10 / OWN-022:** `sessionStorage` de `wp-emoji` **aceptado** (A). No desactivar. No issue.
10. **Corte final:** ejecutar
    [`cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md); archivar el estático en tag.

### Criterios de aceptación — Fase 3

La fase se considera **cerrada** cuando:

- [ ] Theme refleja la maqueta congelada (§2.6): mismas URLs, bloques, copy y paridad visual inicial (ADR 0001, ADR 0029)
- [ ] Plantillas de bloques mapeadas según `12-theme-file-structure` y la [matriz](matriz-migracion-static-wordpress.md)
- [ ] Pages institucionales **existen en la BD** (un template en disco no basta)
- [ ] CPT `event` operativo: archivo + 10 singles HTTP, estados automáticos y `.ics` vigente/410
- [ ] Taxonomía `gallery_album` y CPT `blog_author` operativos, con rutas/noindex según ADR 0036/0037
- [ ] Importador real idempotente; conteos y relaciones reconciliados contra producción
- [ ] Contenido editable desde WordPress sin romper layout
- [ ] Cinco entregables del contrato (ADR 0032) cubiertos, no solo el theme desplegado
- [ ] Kit de pruebas del día uno: `composer test` verde; wp-phpunit para CPT/meta/rewrites; dominio nuevo con TDD (ADR 0038)
- [ ] Fase 2.5 repetida sobre el theme en staging antes de producción
- [ ] [`cutover-checklist-wordpress.md`](cutover-checklist-wordpress.md) completado en staging

---

## Fase 4: Despliegue

### Vigente: despliegue manual (transición)

ADR 0015, ADR 0016. **No hay pipelines activos de despliegue.** El CI de calidad
`.github/workflows/test.yml` es independiente y se activa con los tests PHP (ADR 0038).

**Sitio estático en producción:**

1. Actualizar `sitemap.xml` (en `static/` tras reorg), `VERSION`, `CHANGELOG.md`
2. `npm run lint:css` (sin errores)
3. ZIP **solo** con contenido de `static/` (no repo completo)
4. Subir a `public_html` en Hostinger
5. Smoke test y Search Console si aplica

**WordPress:** despliegue manual del theme a **staging** hasta el corte final.

### CI de calidad y futura automatización de despliegue

Al existir tests PHP, crear `.github/workflows/test.yml` para ejecutar lint CSS + `composer test`
en push a `main` y pull requests, sin secretos, deploy ni SonarScanner (ADR 0038).

La automatización de despliegue permanece pospuesta hasta que la estructura esté estable (ADR 0016):

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
2. **Contacto** — Formulario (markup listo; **envío pendiente en el estático** — FUNC-001) + bloque Redes sociales
   junto con WhatsApp/correo. Respaldo: `docs/archive/contacto-formulario-estatico/`. En WordPress: Contact
   Form 7 (ADR 0026), elegible en el corte (ADR 0041). Actualizar en WP los párrafos del formulario en
   `/privacidad` (OWN-018). Nunca un handler del theme.
3. **La comunidad** — Quiénes somos, fundador
4. **Práctica** — Meditación, mantras (audio), talleres, retiros y videos. El PDF de recitación está
   retirado (OWN-002).
5. **El linaje** — Tradición, Chan, Tierra Pura
6. **Galería** — Hub `/galeria` con los tres álbumes actuales y bloque Galería/lightbox nativo.
   Cada álbum muestra todas sus imágenes con lazy-load; sin paginación numerada en el corte
   (ADR 0021, ADR 0036, OWN-011).
7. **Eventos** — Archivo y 10 singles siempre disponibles. Solo la promoción, inscripción y
   calendario del evento vigente son condicionales (ADR 0035, OWN-012/013).

---

## Regla

No escribir código de theme WordPress ni subir a servidor final hasta que la maqueta estática esté
validada (Fase 2 + Fase 2.5). **Excepción operativa:** la remediación post-auditoría (§2.75) sobre el
estático en producción está permitida sin iniciar Fase 3, siempre respetando decisiones posteriores;
HSTS permanece aplazado por ADR 0020.

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
- [x] Todas las páginas maquetadas (17 indexables + 404)
- [ ] Formulario de contacto **funcional** (entrega end-to-end) — pendiente; canales WhatsApp/correo operativos
- [x] Botón WhatsApp operativo
- [x] Enlaces externos (redes) verificados
- [x] Datos bancarios correctos en footer
- [x] Accesibilidad: estándares 19 aplicados en estructura (contraste muestreado, alt, teclado, focus, labels de formulario)
- [x] `npm run lint:css` finaliza sin errores
- [x] SEO técnico (`15` §12): `<title>`, canonical, OG por página; `robots.txt` y `sitemap.xml` actualizados (auditoría 100/100)
- [x] Sin `<link rel="manifest">` ni PWA (15 §11)
- [x] Eventos con URL propia: JSON-LD `Event` completo según §12.3; listado `/eventos/` sin microdata duplicada
- [~] Descargas `.ics`: dos archivos existen en el estático; el destino WordPress genera solo las
  vigentes y devuelve 410 para finalizadas (OWN-009/012/013)
- [ ] HSTS — **aplazado** hasta después del corte WordPress (ADR 0020). Los ítems ADR 0018 Fase 1/2 quedan históricos en lo operativo.
- [x] Política de privacidad publicada (ADR 0039, provisional). GA4 descartado (ADR 0019); no hay consentimiento de analítica pendiente. CF7 elegible en el corte (ADR 0041); revisión legal = trabajo posterior, no gate.
- [ ] Google Search Console: sitemap enviado; solicitar indexación de URLs modificadas tras cada despliegue relevante

---

## Mantenimiento (post-publicación)

Tareas periódicas una vez el sitio está en producción. No son desarrollo de features, pero forman parte del ciclo de vida del sitio.

| Frecuencia | Tarea |
| ---------- | ----- |
| **Tras cada despliegue** | Smoke test de URLs clave; verificar enlaces del footer; si hay formulario, probar envío end-to-end |
| **Post-auditoría (2026-07-19)** | Ejecutar solo tareas §2.75 no sustituidas por ADR posteriores; registrar en `CHANGELOG.md` |
| **Mensual** | Revisión de enlaces rotos (internos y externos) |
| **Mensual** | Comprobar que `sitemap.xml` refleja el inventario de URLs indexables |
| **Trimestral** | Auditoría Lighthouse (home + una página interior) |
| **Trimestral** | Revisión de accesibilidad según `19-accesibilidad-estandares` (§10–11) |
| **Trimestral** | Revisión de cabeceras HTTP, HTTPS, HSTS y reglas de `.htaccess` |
| **Semestral** | Revisión de Search Console (cobertura, errores, rendimiento) |
| **Anual** | Renovar `/.well-known/security.txt` (Expires) si se publicó (TASK-0009) |
| **Según necesidad** | Limpieza de contenido obsoleto (eventos pasados, entradas de blog desactualizadas) |
| **Según necesidad** | Actualización de dependencias de desarrollo (`npm`; Stylelint) |

Incidencias detectadas en mantenimiento siguen la **política de cambios**: documentar si afectan
arquitectura o navegación; corregir directamente si son bugs o contenido editorial alineado con el
sitio publicado.

---

## Cierre

Este documento define el **orden oficial de implementación** y el **ciclo de vida completo** del sitio:

**Documentación y diseño → maqueta estática validada → QA → auditoría producción (§2.75) → remediación → transición (static/ + wordpress/) → corte WordPress → mantenimiento.**

Durante la transición: un solo repo, despliegues manuales del estático, WordPress en staging y
registro en `migracion-static-wordpress.md`. El CD sigue pospuesto (ADR 0016); el CI de calidad se
activa con los tests PHP (ADR 0038).

A partir de la versión 3.0, **priorizar implementación** sobre ampliación de documentación de alto nivel (ver § Congelamiento de documentación base). La versión 3.1 incorpora el estado real post-auditoría: el estático puede recibir mejoras de §2.75 sin bloquear la planificación de Fase 3.

---

**Versión:** 3.9 · **Fecha:** 2026-08-30 · **Estado:** Vigente
