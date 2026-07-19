# ADR 0016: Automatización CI/CD pospuesta

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

ADR 0006 estableció **GitHub Actions** como mecanismo objetivo de CI/CD (lint en PR + deploy vía rsync). ADR 0007 definió `rsync --delete` para sincronización.

Durante la transición estático → WordPress:

- La estructura del monorepo aún no está en producción (`static/` pendiente de reorg).
- WordPress vive en staging separado con despliegue manual (ADR 0015).
- Los límites de `rsync` difieren entre estático y theme (ADR 0013).
- Activar pipelines de despliegue prematuramente añade riesgo operativo sin beneficio inmediato.

## Decisión

**La automatización de despliegue (GitHub Actions + SSH + rsync) queda pospuesta** hasta que:

1. La estructura `static/` + `wordpress/` esté estable en el repositorio.
2. WordPress esté validado en staging y el flujo de corte a producción esté definido.
3. Los directorios de sync estén documentados con precisión (estático vs theme vs plugins propios).

Mientras tanto:

- **Despliegue:** manual según ADR 0015.
- **Validación local:** `npm run lint:css` antes de commit y despliegue (obligatorio).
- **Workflows en `.github/`:** ninguno activo. Crear `lint.yml` y `deploy.yml` al activar CI/CD (ADR 0006).

ADR 0006 y ADR 0007 **permanecen válidos como decisión de dirección**; su **implementación operativa** queda en espera. Cuando se active la automatización, crear ADR que documente activación o marcar este ADR como **Sustituida**.

## Alternativas consideradas

| Alternativa | Motivo de descarte (por ahora) |
| ----------- | ------------------------------ |
| Activar deploy.yml de inmediato | Riesgo de sync incorrecto durante transición. |
| Eliminar ADR 0006 / workflows | Pierde dirección acordada; mejor posponer implementación. |
| Solo CI sin CD | Aceptable como paso intermedio futuro; no prioritario ahora. |

## Consecuencias

**Beneficios:**

- Foco en desarrollo del theme y mantenimiento del estático en producción.
- Evita incidentes de despliegue automático mal acotado.

**Negativas:**

- Sin validación CSS automática en CI hasta activar lint en PRs.
- Rollback depende de tags y ZIPs manuales.

**Trabajo futuro:**

- Tras corte WordPress: evaluar lint en PR + deploy acotado al theme.
- Crear `.github/workflows/lint.yml` y `deploy.yml` cuando se active la automatización (ADR 0006).

## Referencias

- ADR 0006 (dirección CI/CD — implementación diferida)
- ADR 0007 (rsync — aplicación diferida)
- ADR 0013, ADR 0015
- `docs/17-orden-implementacion` Fase 4
