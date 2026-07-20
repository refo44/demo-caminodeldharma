# ADR 0020: HSTS aplazado hasta después del corte a WordPress

## Estado

Aceptada — **sustituye la parte operativa de [ADR 0018](0018-hsts-despliegue-escalonado.md)**
(cuándo activar). ADR 0018 pasa a **Sustituida** en lo referente al calendario; su análisis técnico
sigue siendo válido.

## Fecha

2026-07-20

## Contexto

ADR 0018 (2026-07-19) decidió un despliegue escalonado: activar HSTS **ya** con `max-age=604800`
durante la transición, y subir a un año tras el corte a WordPress. La activación nunca llegó a
producción: la línea permaneció comentada en `.htaccess` y la cabecera nunca se sirvió.

Al revisar la decisión aparecieron dos datos que ADR 0018 no tenía:

1. **El sitio estático se publicó el 2026-07-18** (`CHANGELOG` v1.0.0). Cuando se redactó ADR 0018
   tenía un día de vida.
2. **Search Console (EVID-0052): 9 clics y 35 impresiones en 28 días.** El tráfico es prácticamente
   nulo.

A eso se suma que el corte a WordPress se estima en 10–15 días y puede alterar hosting, certificados,
redirects y el propio `.htaccess` —que WordPress reescribe en su bloque `# BEGIN WordPress`.

## Decisión

**No activar HSTS mientras dure la transición a WordPress.** La línea permanece comentada en
`.htaccess`; repo y producción quedan sincronizados sin la cabecera.

Se revisará **tras el corte a WordPress y ≥30 días de estabilidad** (sin incidencias de TLS ni de
redirects). En ese momento se decidirá si conviene entrar por una fase corta o directamente con un
valor duradero, con los datos de tráfico de entonces.

`includeSubDomains` y `preload` siguen **RECHAZADOS**, sin cambio.

## Razonamiento

HSTS mitiga el downgrade a HTTP en la primera visita. Con ~9 clics en 28 días, la exposición real a
ese ataque es mínima. En cambio, fijar política de transporte justo antes de una migración que puede
tocar TLS y redirects introduce un riesgo operativo concreto: si algo falla, los visitantes que ya
recibieron la cabecera quedan anclados hasta que expire.

Es la misma lógica de ADR 0018 —acortar la ventana de recuperación durante la transición— llevada a
su conclusión: si el motivo para no poner un año es la incertidumbre de la migración, ese motivo
también aplica a poner siete días. La diferencia es de grado, no de naturaleza.

No es una decisión permanente ni una renuncia: es una cuestión de **secuencia**. HSTS se activará
sobre la plataforma definitiva, no sobre una temporal a punto de ser reemplazada.

## Alternativas consideradas

| Alternativa | Decisión |
| ----------- | -------- |
| **Fase 1 con `max-age=604800` ahora (ADR 0018)** | Sustituida. Técnicamente segura y reversible en 7 días, pero aporta protección marginal al volumen de tráfico actual y añade una variable a la migración |
| `max-age=300` como variante mínima | Rechazada: si el valor es tan corto que apenas protege, el beneficio no justifica el cambio |
| Activar después del corte pero antes de los 30 días | Rechazada: el criterio de estabilidad de ADR 0018 sigue siendo razonable |
| No usar HSTS nunca | **No es esta decisión.** El análisis técnico de `hsts-decision.md` sigue vigente: no hay bloqueadores |

## Consecuencias

- **SEC-001** pasa a `DEFERRED_BY_OWNER`. El hallazgo no se cierra: la brecha de transporte sigue
  existiendo, simplemente se acepta de forma consciente y acotada en el tiempo.
- **TASK-0004** y **TASK-0005** quedan `BLOCKED` hasta la revisión post-corte.
- **TASK-0012** (`includeSubDomains`) se pospone: requiere HSTS básico estable.
- **TASK-0008 (CSP) se libera.** Dependía de TASK-0004, pero solo por compartir `.htaccess`
  (grupo `CG-HTACCESS`), no por necesidad lógica. La CSP puede avanzar por su cuenta respetando la
  serialización del archivo. **Sin esta corrección, aplazar HSTS habría bloqueado la CSP por
  arrastre.**
- El cronograma de auditorías mantiene la revisión de HSTS en el Hito 5 (auditoría del sitio
  definitivo), donde encaja de forma natural.

## Referencias

- [ADR 0018](0018-hsts-despliegue-escalonado.md) — despliegue escalonado (sustituida en lo operativo)
- [ADR 0010](0010-hsts-desactivado-hasta-auditoria.md) — histórico
- `.audit/hsts-decision.md` — análisis técnico, vigente
- EVID-0052 (Search Console), `CHANGELOG` v1.0.0 (fecha de publicación)
- `.audit/audit-schedule.md` — Hito 5
