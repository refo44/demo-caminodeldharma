# ADR 0018: HSTS — despliegue escalonado durante la transición a WordPress

## Estado

**Sustituida en lo operativo** por [ADR 0020](0020-hsts-aplazado-hasta-wordpress.md) (2026-07-20): HSTS queda **aplazado** hasta después del corte a WordPress; la Fase 1 nunca llegó a activarse. El análisis técnico de este documento sigue siendo válido.

## Fecha

2026-07-19

## Contexto

La auditoría de producción del 2026-07-19 (`.audit/hsts-decision.md`) concluyó que el host `caminodeldharma.org` cumple los prerrequisitos técnicos para activar HSTS solo-host con confianza **ALTA**: redirects HTTP→HTTPS correctos, certificado válido (apex + www), TLS 1.3, cero contenido mixto, paridad deploy fuente/producción.

ADR 0010 reservaba HSTS hasta esa auditoría. Tras cerrarla, queda decidir **cuándo** fijar un `max-age` largo (31536000 s = 1 año).

El proyecto está en **Fase 2** (estático en producción) con **Fase 3** (WordPress en staging y corte futuro) por delante. Durante meses puede haber cambios en `.htaccess`, certificados, redirects o el propio stack de hosting. Un `max-age` de un año ofrece máxima protección, pero acorta la ventana de recuperación si algo falla: los visitantes recurrentes quedan anclados a HTTPS hasta un año, y la reversión efectiva depende de que vuelvan tras servir `max-age=0`.

Para un sitio de comunidad con bajo riesgo de ataque dirigido, la auditoría ya consideraba seguro el año completo; aun así, durante la **transición estático → WordPress** se adopta un despliegue escalonado: primero `max-age` corto, luego el año cuando WordPress esté **estable en producción**.

## Decisión

1. **Activar HSTS ahora** (TASK-0004): no dejar la cabecera comentada.
2. **Fase 1 — transición** (desde la activación hasta el corte WordPress estable): servir solo-host con **`max-age=604800`** (7 días).
3. **Fase 2 — producción WordPress estable:** subir a **`max-age=31536000`** (1 año) tras smoke test post-corte y al menos 30 días sin incidencias TLS/redirect en producción.
4. **`includeSubDomains` y `preload`:** siguen **RECHAZADOS** (sin cambio; evaluación aparte en TASK-0012 / ADR futuro).
5. **Reversión:** siempre con `Strict-Transport-Security "max-age=0"` sobre HTTPS válido; nunca retirar la cabecera sin más.

Cabecera Fase 1 (`.htaccess` línea ~103):

```apache
Header always set Strict-Transport-Security "max-age=604800"
```

Cabecera Fase 2 (misma línea, tras criterio de cierre):

```apache
Header always set Strict-Transport-Security "max-age=31536000"
```

Registrar cada cambio de fase en `CHANGELOG.md` con fecha y commit de despliegue.

## Alternativas consideradas

| Alternativa | Decisión |
| ----------- | -------- |
| **`max-age=31536000` desde el día uno** | Aprobada por la auditoría como técnicamente segura; **no preferida** durante la transición por menor margen de reversión. Válida si la comunidad renuncia al escalonado. |
| **`max-age=300` (5 min) o `86400` (1 día)** | Válida como variante **ultra-conservadora** si se quiere salida de emergencia casi inmediata; menos protección real en semanas de transición. Sustituir `604800` por el valor elegido en TASK-0004. |
| **No activar HSTS hasta WordPress** | Rechazada: la auditoría no encontró bloqueadores; el estático seguirá en producción meses y merece HSTS acotado ya. |
| **`includeSubDomains` / preload** | Rechazados (`.audit/hsts-decision.md`). |

## Consecuencias

**Beneficios del escalonado:**

- HSTS operativo durante la transición (mejor que seguir sin cabecera).
- Si hay un error de TLS o redirect, la política expira en días (Fase 1), no en un año.
- Camino claro hacia `max-age=31536000` cuando el riesgo operativo baje.

**Costes / riesgos:**

- Protección HSTS más débil en Fase 1 que con un año (ventana de downgrade mayor para visitantes que no re-visitan en la semana).
- Requiere disciplina: no olvidar la subida a Fase 2 tras el corte WordPress (checklist en `17-orden-implementacion` § Transición y checklist pre-lanzamiento).
- Renovación de certificado (vence 2026-10-12) debe monitorizarse en ambas fases.

**Trabajo futuro:**

- TASK-0004: activar Fase 1.
- TASK-0005: verificar Fase 1.
- Tras corte WordPress: tarea de subida a `max-age=31536000` (registrar en `CHANGELOG`; opcional TASK dedicada en `.audit/` o ticket de mantenimiento).
- TASK-0012: evaluación `includeSubDomains` solo después de Fase 2 estable.

## Referencias

- Sustituye la parte operativa de activación de [ADR 0010](0010-hsts-desactivado-hasta-auditoria.md) (estado **Sustituida** para la decisión de cuándo/cómo activar).
- `.audit/hsts-decision.md`, `.audit/hsts-decision.json`
- `.audit/implementation/tasks/TASK-0004.md`, `TASK-0005.md`
- `docs/17-orden-implementacion` (§2.75, § Transición, checklist pre-lanzamiento)
- `.htaccess` (cabecera HSTS)
