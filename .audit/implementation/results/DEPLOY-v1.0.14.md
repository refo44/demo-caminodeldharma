# Etapa 2 — Cierre por deploy v1.0.14

- **Fecha:** 2026-07-20
- **Confirmación:** propietario del proyecto
- **Versión en producción:** v1.0.14
- **Ledger:** `implementation/tasks.jsonl`

## Tareas marcadas COMPLETED

| Task | Área |
|---|---|
| TASK-0001 | Rutas `.ics` absolutas + MIME `text/calendar` |
| TASK-0002 | Formulario → CTAs WhatsApp/correo |
| TASK-0006 | Embeds nocookie + privacidad |
| TASK-0007 | Logo/galería + srcset |
| TASK-0008 | CSP |
| TASK-0009 | security.txt |
| TASK-0010 | Galería pre-render sin JS |
| TASK-0011 | Versionado CSS/JS |
| TASK-0013 | Deploy SEO (redirects + on-page) |
| TASK-0014 | GBP + peticiones de enlace (off-page) |
| TASK-0015 | Search Console + Bing |
| TASK-0017 | Meditación semanal como entidad citable |
| TASK-0018 | Contenido formato pregunta-respuesta |

TASK-0019 ya estaba COMPLETED (baseline competidores).

## Tareas que permanecen BLOCKED

TASK-0003, 0004, 0005, 0012, 0016, 0020 — ver `implementation/backlog.md`.

## Validación

Validación formal Stage 3 no ejecutada en esta sesión; el propietario asumió deploy completo. Spot-check opcional contra los `validation_commands` de cada tarea.

---

## Corrección posterior (2026-07-21)

Este registro se conserva **tal cual se emitió el 2026-07-20**; la tabla de arriba refleja lo que se declaró entonces, no lo que estaba realmente implementado.

Al verificar los criterios contra producción y contra el repositorio, tres de las tareas listadas no cumplían su Definition of Done:

| Task | Estado corregido | Comprobación |
|---|---|---|
| TASK-0007 | COMPLETED, reatribuido a **v1.0.19** | En v1.0.14 el DoD no se cumplía: `logo.png` seguía en 1000×1000 / 35,5 KB el 2026-07-21. |
| TASK-0010 | **NOT DONE** | `curl https://caminodeldharma.org/galeria \| grep -c '<img'` → 1 (el DoD exige ≥12). |
| TASK-0011 | **NOT DONE** | Sin `?v=` en ninguna referencia css/js; `git log -S'?v=' --all` sin commits. |

**Causa probable:** el cierre se hizo por confirmación global del propietario, sin artefacto de validación por tarea. En `implementation/results/` solo existen registros de TASK-0001, TASK-0006 y de este deploy; las demás tareas se marcaron COMPLETED sin evidencia asociada.

**Recomendación:** para el próximo cierre, exigir un artefacto de validación por tarea (comando + salida) antes de marcar COMPLETED, tal como ya prevé el campo `validation_result_artifact` de `implementation/tasks.jsonl`.
