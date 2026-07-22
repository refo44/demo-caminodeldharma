# Audit State

- Target: https://caminodeldharma.org
- Environment: production
- Started: 2026-07-19 (session start)
- Last checkpoint: Stage 2 implementation closed (owner deploy confirmation)
- Status: COMPLETE — audit closed 2026-07-20 (Stage 1). **Stage 2 (implementation) closed 2026-07-20** — owner confirmed production deploy v1.0.14 with all formerly READY tasks + TASK-0001 completed. Stage 3 (validation) optional / spot-check as needed; does not gate closure
- Current phase: IMPLEMENTATION_DONE — only BLOCKED tasks remain
- Completed phases: PHASE-0 through PHASE-10 (audit); Stage 2 implementation (2026-07-20)
- Partial phases: PHASE-3 — LCP RESOLVED 2026-07-20 via PSI/Lighthouse (1.4s, EVID-0047); INP still unmeasurable (no CrUX field data, limitation 2). CLS corrected: 0.081 mobile / 0.005 desktop
- Blocked phases: none
- Accepted findings: 14 (0 CRITICAL, 1 HIGH remediated via TASK-0002, 9 MEDIUM partially/fully addressed, 3 LOW, 1 INFORMATIONAL)
- Evidence artifacts: 52 ledger records + raw/ tree
- URLs discovered: 13 indexable + 404 + entry points + machine files (full coverage)
- URLs tested: all discovered
- Source commit reviewed: be896db2214c4dafdc8adad89f8496421c8b6071 (deploy parity at audit time; production now at v1.0.14 per owner)
- HSTS decision: DEFERRED (ADR 0020) — revisit after WordPress cutover + >=30 stable days. TASK-0004/0005 remain BLOCKED
- Tasks: 20 in implementation/tasks.jsonl (**14 COMPLETED**, **6 BLOCKED**, **0 READY**, **0 IMPLEMENTED_PENDING_VALIDATION**). Completed 2026-07-20 (deploy v1.0.14): TASK-0001, 0002, 0006, 0007, 0008, 0009, 0010, 0011, 0013, 0014, 0015, 0017, 0018 (+ TASK-0019 audit-input, already COMPLETED). Still BLOCKED: TASK-0003 (form backend decision), 0004/0005 (HSTS ADR 0020), 0012 (DNS + HSTS), 0016 (editorial plan), 0020 (city sanghas)
- Pending manual inputs: see manual-inputs-pending.md — deploy, GSC, GBP and link requests marked delivered per owner confirmation 2026-07-20
- Audit schedule: see audit-schedule.md — next full audit after WordPress cutover + 30 stable days
- Next executable action: **none in Stage 2** — await community decisions on BLOCKED tasks or post-WordPress HSTS (TASK-0004). Optional: Stage 3 spot validation of deploy v1.0.14

## Actualización 2026-07-21

- **TASK-0017 REABIERTA (READY).** Figuraba COMPLETED por el cierre en bloque del deploy v1.0.14, pero la verificación del 2026-07-21 muestra que ninguno de sus 4 criterios se cumple (sin URL en sitemap, sin `EventSeries` en `practica/index.html`, sin entrada en `llms.txt`, sin página que enlazar). Prerrequisito resuelto: puerta por WhatsApp.
- **TASK-0014 → PARTIALLY_COMPLETED.** Peticiones de enlace enviadas y URL en Facebook hechas; **Instagram pendiente**. **GBP descartado** por inelegibilidad (sin sede ni dirección física). **Directorio budismo.com retirado** de las recomendaciones.
- **Corte a WordPress aplazado** a después del 10 de agosto (tras el Encuentro Nacional). Se probará antes en servidor y URL temporales; Hitos 1 y 2 del cronograma se mantienen íntegros.
- **Tareas: 21 — 12 COMPLETED, 1 PARTIALLY_COMPLETED, 2 READY, 6 BLOCKED.**
- **TASK-0021 nueva (READY):** etiqueta visible de modalidad y ciudad en los eventos, derivada del JSON-LD existente. Propuesta del propietario; señal local legítima ahora que GBP quedó descartado.
- **Próximas acciones ejecutables: TASK-0017 y TASK-0021.**
- Entregables externos en `docs/informes-seo/` (informe general y auditoría técnica) + `docs/24-brief-editorial-blog-y-visibilidad.md`.
- **TASK-0022 nueva (BLOCKED):** historial de encuentros presenciales por ciudad. El propietario confirma encuentros en Bogotá, Medellín y Barranquilla **nunca publicados en la web**, con cadencia de **2–3 al año por ciudad**. Espera el listado (dato de archivo, no decisión).
- **TASK-0020 reformulada:** pasa a depender de TASK-0022. Las páginas por ciudad solo son legítimas si se sostienen sobre el historial de encuentros de esa ciudad.
- **Estructura confirmada:** práctica semanal **en línea**; encuentros presenciales 2–3 al año por ciudad. Refuerza la centralidad de TASK-0017.
- **Tareas: 22 — 12 COMPLETED, 1 PARTIALLY_COMPLETED, 2 READY, 7 BLOCKED.**
- **TASK-0017 IMPLEMENTED_PENDING_VALIDATION** (2026-07-21): `/practica/meditacion-semanal-en-linea` creada y ya visible en producción. Los 4 criterios verificados. CTA de WhatsApp; el enlace de Zoom no se publica.
- **FUNC-003 nuevo (MEDIA, corregido en fuente):** enlaces de navegación a `/practica` resolvían a la raíz por usar ruta relativa bajo la política canónica sin barra final. Afectaba a `/practica/videos` **desde su publicación** y se reintrodujo al enlazar la página nueva. **Segunda aparición de la misma causa que FUNC-002** → regla elevada a **ADR 0008**: enlaces internos con ruta absoluta de raíz.
- **Detectado por el propietario**, no por la auditoría. Añadido al checklist de verificación del corte a WordPress.
- **Hallazgos: 15** (1 ALTA, 9 MEDIA, 4 BAJA, 1 INFORMATIVA).
- **Pendiente de despliegue:** correcciones de FUNC-003, separadores de año en `/eventos`, JSON-LD de los 5 encuentros y carga diferida de imágenes.

## Actualización 2026-07-21 (tarde) — rendimiento y corrección de estados

- **Tres tareas más figuraban COMPLETED sin cumplir su DoD**, misma causa ya detectada en TASK-0017: cierre en bloque del deploy v1.0.14 por confirmación global del propietario, sin artefacto de validación por tarea. Con TASK-0017 son **cuatro** casos; deja de ser un fallo aislado y pasa a ser un problema del procedimiento de cierre.
  - **TASK-0007 → COMPLETED reatribuida a v1.0.19.** El DoD no se cumplía en v1.0.14: el 2026-07-21 `logo.png` seguía en 1000×1000 / 35,5 KB.
  - **TASK-0010 → NOT DONE.** `curl /galeria | grep -c '<img'` = 1; el DoD exige ≥12. Sin `<noscript>`. **AEO-001 sigue abierto.**
  - **TASK-0011 → NOT DONE.** Nunca se implementó: ninguna referencia css/js lleva `?v=` y `git log -S'?v=' --all` no registra commits. **PERF-002 sigue abierto.**
- **PERF-001 → RESOLVED** (v1.0.19, no v1.0.14). `logo.png` 240×240; miniaturas del home; y `gallery.js` sirviendo `assets/images/galeria/thumbs/` con `srcset` 300w/600w — antes cada página de galería entregaba ~2 MB en 12 originales para teselas de ~285 px. Desviación documentada: miniaturas de 600 px (no ≤500) y `srcset` 300/600 (no variantes `-400`); 600 px es lo que exige una tesela de ~285 px a DPR2.
- **Nota de corrección:** en el análisis previo se afirmó que `/galeria` necesitaba los originales a tamaño completo para un lightbox. **Es falso**: `gallery.js` no tiene lightbox ni manejador de clic sobre las imágenes; los únicos `click` son de paginación. Los originales se servían como simples teselas.
- **Otros cambios de rendimiento (v1.0.18–v1.0.19), ya en producción:** `normalize.css` incorporado a `main.css` (una sola hoja bloqueante); `main.min.css` vía `npm run build:css` (9,0 → 5,8 KB con Brotli); MarloweEscapade subsetada a "Camino del Dharma" (52,1 → 3,4 KB); `srcset` en las imágenes del home.
- **Deriva repo↔producción detectada:** `assets/images/logo.png` es 7.423 B en el repo (escala de grises + alfa) y 10.079 B en producción (RGBA). Ambos 240×240 y ambos cumplen el DoD, pero **no son el mismo fichero**. Conviene alinearlos en el próximo despliegue.
- **Tareas: 22 — 10 COMPLETED, 2 NOT DONE, 1 PARTIALLY_COMPLETED, 1 IMPLEMENTED_PENDING_VALIDATION, 2 READY, 7 BLOCKED.**
- **Próximas acciones ejecutables:** TASK-0011 (`?v=`, PERF-002 — ahora más relevante: al renombrar a `main.min.css` la caché se invalidó una vez, pero las siguientes ediciones del mismo fichero volverán a chocar con los 7 días de `max-age`), TASK-0010 (fallback sin JS, AEO-001), TASK-0021.
- **Recomendación de procedimiento:** no marcar COMPLETED sin artefacto de validación por tarea (comando + salida) en `implementation/results/`, tal como ya prevé el campo `validation_result_artifact` de `tasks.jsonl`.
- `raw/` **no se ha tocado**: es la evidencia congelada del 2026-07-19.
