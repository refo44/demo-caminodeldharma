# Execution waves

Regla general: dentro de una ola, las tareas del mismo conflict group se ejecutan en serie; entre grupos distintos pueden ir en paralelo si `parallel_safe_with` lo permite. Cada tarea termina con su artefacto de resultado y validación independiente antes de cerrar.

## WAVE-0 — Urgent containment and production blockers
Sin tareas: no se encontraron bloqueadores de producción que exijan contención inmediata.

## WAVE-1 — High-value, low-risk stabilization
- **TASK-0004** Activar HSTS Fase 1 `max-age=604800` (CG-HTACCESS) — criterio de seguridad de transporte (uno más del conjunto auditado, no el objetivo principal); despliegue escalonado ADR 0018.
- **TASK-0005** Verificación de producción HSTS (prio 1, depende de 0004).
- **TASK-0001** Crear archivos .ics (prio 2, paralelo seguro con 0004).
- **TASK-0002** Retirar formulario muerto → CTAs (prio 3, CG-CONTACTO).
- **TASK-0003** Formulario real (BLOCKED: decisión de producto; queda visible en esta ola por su valor).
- Gate de salida: cabecera HSTS verificada; 0 URLs 404 referenciadas; /contacto sin vía muerta.
- Esfuerzo estimado: ~1 día agregado. Rollback: por tarea (git revert / max-age=0).

## WAVE-2 — Performance and accessibility foundations
- **TASK-0007** Imágenes (logo/miniaturas/srcset) (CG-HTML-GLOBAL).
- Gate: ratios ≤2.5; paridad visual.

## WAVE-3 — SEO, structured data, content, discoverability
Sin tareas: SEO/SD/contenido sin hallazgos accionables (áreas en verde).

## WAVE-4 — Security hardening and observability
- **TASK-0008** CSP por etapas (CG-HTACCESS; después de 0004).
- **TASK-0009** security.txt (paralelo seguro).
- **TASK-0006** Consentimiento + privacidad (BLOCKED: decisión organizativa) (CG-HTML-GLOBAL).
- **TASK-0012** Evaluación includeSubDomains (BLOCKED: inventario DNS + estabilidad ≥30 días de HSTS básico) (CG-HTACCESS).
- Gate: CSP enforced sin violaciones; decisión de privacidad registrada.

## WAVE-5 — Architecture, maintainability, testing
- **TASK-0011** Versionado de assets (CG-HTML-GLOBAL; después de 0007).

## WAVE-6 — AI search and agentic readiness
- **TASK-0010** Pre-render galería sin JS (CG-HTML-GLOBAL; después de 0011).

Una tarea de ola posterior solo puede adelantarse si sus dependencias lo permiten y se reevalúa el riesgo por escrito.
