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
- **Tareas: 20 — 12 COMPLETED, 1 PARTIALLY_COMPLETED, 1 READY (TASK-0017), 6 BLOCKED.**
- **Próxima acción ejecutable: TASK-0017.**
- Entregables externos en `docs/informes-seo/` (informe general y auditoría técnica) + `docs/24-brief-editorial-blog-y-visibilidad.md`.
