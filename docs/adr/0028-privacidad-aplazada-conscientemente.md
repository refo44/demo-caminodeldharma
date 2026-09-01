# ADR 0028: Política de privacidad aplazada conscientemente

## Estado

Sustituida → [0039](0039-aviso-privacidad-provisional-estatico.md)

El aplazamiento de **publicar** `/privacidad` queda sustituido por ADR 0039. El gate de Contact
Form 7 y la espera de revisión legal los sustituye
[ADR 0041](0041-cf7-corte-sin-asesoria-legal.md).

## Fecha

2026-07-31

## Contexto

El informe SEO técnico (`docs/informes-seo/02-auditoria-seo-tecnica.md` §9, hallazgo PRIV-001b) señala
que publicar la política de privacidad **no es una tarea técnica**: la conclusión jurídica corresponde
a asesoría legal. Cubre la Ley 1581/2012 de Colombia (tratamiento de datos personales) y, por
visitantes desde España detectados en Search Console (1 clic / 2 impresiones), abre además la pregunta
de si aplica el RGPD.

ADR 0019 ya descartó GA4 y toda analítica con cookies, lo que resuelve el consentimiento de cookies —
pero eso **no cubre** el tratamiento de datos personales en general (contacto por WhatsApp/correo hoy,
y en el futuro los envíos de Contact Form 7, ADR 0026).

A la fecha de este ADR:

- El sitio no sirve ninguna cookie propia (ADR 0019).
- El único canal de contacto operativo es humano — WhatsApp/correo (TASK-0002).
- Contact Form 7 (ADR 0026) todavía no existe: se implementa en Fase 3.

Es decir, hoy no hay en el sitio ningún mecanismo automatizado que recolecte datos personales sin
intervención humana directa.

## Decisión

Se aplaza conscientemente la publicación de `/privacidad/` hasta conseguir asesoría legal, con el mismo
criterio que ADR 0020 aplicó a HSTS: no fijar una posición legal sin haberla evaluado, cuando el riesgo
actual es bajo y hay una migración en curso que de todos modos va a tocar esta página.

**Condición de revisión, no un plazo fijo:** la política de privacidad debe estar publicada **antes de
que Contact Form 7 (ADR 0026) entre en producción**. Ese es el momento en que el sitio empieza a
recolectar datos personales de forma automatizada (nombre, correo, mensaje) y el vacío legal deja de
ser teórico. A diferencia de HSTS (revisión a ≥30 días), aquí el disparador es un evento concreto del
propio proyecto, no un plazo de calendario.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Redactar ahora un texto genérico sin asesoría legal | Descartada: el propio informe técnico advierte que la conclusión jurídica no es tarea técnica; publicar un texto mal fundamentado puede ser peor que no publicar nada |
| Bloquear el inicio de Fase 3 hasta resolver esto | Descartada: el riesgo actual es bajo (sin cookies, sin recolección automatizada); bloquear toda la migración por esto sería desproporcionado |

## Consecuencias

- PRIV-001b permanece **DEFERRED_BY_OWNER**, no se cierra: el vacío legal sigue existiendo: se acepta
  de forma consciente y acotada, no por omisión.
- **Antes de publicar Contact Form 7 en producción, este ADR se revisa** — queda como gate explícito
  de TASK-0003, no como nota aparte que se pueda pasar por alto.
- La pregunta de RGPD (visitantes desde España) se resuelve en la misma revisión legal, no por
  separado.

## Referencias

- `docs/informes-seo/02-auditoria-seo-tecnica.md` §9 — hallazgo PRIV-001b
- ADR [0019](0019-sin-analitica-con-cookies.md) — resuelve cookies, no datos personales en general
- ADR [0020](0020-hsts-aplazado-hasta-wordpress.md) — mismo criterio de aplazamiento consciente, aplicado a HSTS
- ADR [0026](0026-contact-form-7.md) — evento que dispara la revisión de este ADR
- ADR [0039](0039-aviso-privacidad-provisional-estatico.md) — sustituye el aplazamiento de publicación
- ADR [0041](0041-cf7-corte-sin-asesoria-legal.md) — sustituye el gate de CF7 y la espera legal
- `.audit/implementation/tasks/TASK-0003.md`
- `.audit/decisions.md`
