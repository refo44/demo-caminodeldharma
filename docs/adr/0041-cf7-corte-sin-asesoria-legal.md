# ADR 0041: Contact Form 7 en el corte sin esperar asesoría legal

## Estado

Aceptada. El **punto 5** de la decisión (fallback si el correo de staging falla) está
**sustituido** por [0045](0045-cf7-entrega-gate-corte.md) (2026-09-01). El resto de este ADR
sigue vigente.

## Fecha

2026-08-31

## Contexto

ADR [0039](0039-aviso-privacidad-provisional-estatico.md) publicó `/privacidad` como aviso
provisional y dejó Contact Form 7 (ADR 0026) gated en producción hasta (a) actualizar el aviso
para describir un formulario con envío server-side y (b) contar con revisión legal.

El propietario decide el 2026-08-31 que **no esperará asesoría legal este año** y que el corte a
WordPress **sí incluirá** Contact Form 7. El disclaimer ya publicado en `/privacidad` —documento
provisional, hechos técnicos, Ley 1581 de 2012, canales de contacto, derechos— es suficiente para
lanzar. PRIV-001b queda como recomendación jurídica posterior, no como gate de WU-09 ni del corte.

El aviso estático vigente afirma que el formulario **no envía**. Eso sigue siendo cierto en
producción estática. Deja de ser cierto el día en que WordPress procesa el formulario: el copy de
la Page `/privacidad` en WordPress debe describir el envío real **antes** de activar CF7 en ese
entorno. Eso es un delta field-scoped aprobado (OWN-007 / OWN-018), no una reescritura libre del
aviso.

## Decisión

1. **Contact Form 7 es elegible para producción en el corte.** WU-09 implementa y prueba el
   plugin (local y staging, datos sintéticos). El corte puede activarlo. La revisión legal **no**
   es prerrequisito.
2. **El disclaimer publicado basta para el lanzamiento.** Se conserva el sello «Documento
   provisional» y la frase de que el texto no sustituye una valoración jurídica. Una asesoría
   legal posterior podrá corregirlo; no bloquea 2026.
3. **En WordPress, actualizar solo los párrafos del formulario** para describir el hecho nuevo:
   el mensaje se procesa en el servidor (Contact Form 7) y se entrega a
   `caminodeldharma1@gmail.com`. Nombre, correo y contenido del mensaje; finalidad: leer y
   responder. WhatsApp y el correo directo siguen operativos. No se publica el envío en el sitio.
   Fecha de «Última actualización» al día del cambio. El resto del aviso (cookies, analítica,
   embeds, donaciones, derechos, Ley 1581) no se reescribe.
4. **El HTML estático no se toca** mientras el formulario de producción siga siendo `action="#"`.
   El importer sigue trayendo el copy live; la conversión field-scoped de WU-09 aplica el delta
   del punto 3 sobre la Page de WordPress.
5. **Entrega real** a `caminodeldharma1@gmail.com` se verifica en staging Hostinger antes del
   corte (`Pass (local)` no basta). **Sustituido 2026-09-01:** el corte con CF7 activo **espera**
   esa prueba (ADR [0045](0045-cf7-entrega-gate-corte.md) / OWN-033). El fallback «CF7 apagado +
   WhatsApp/correo» ya no es el camino por defecto.
6. No se añade otro plugin antispam sin ADR propia.

Copy aprobado para los campos del punto 3 (español de la voz del sitio):

**Aviso provisional (sustituye el segundo periodo del recuadro):** su redacción podrá cambiar si
una asesoría legal lo revisa más adelante. Cada apartado indica lo que ya está activo. *Retirar*
la cláusula «cuando el formulario de contacto pase a enviarse a un servidor».

**Resumen, viñeta del formulario:** «El formulario de contacto envía tu mensaje al correo de la
comunidad. WhatsApp y el correo directo siguen disponibles.»

**§2.2:** «El formulario de la página Contacto se procesa en el servidor del sitio (Contact Form
7) y entrega el mensaje a caminodeldharma1@gmail.com. Tratamos el nombre, el correo y el
contenido que envíes, con la finalidad de leer y responder tu consulta. No publicamos esos
envíos en el sitio. WhatsApp y el correo directo siguen siendo canales operativos.»

**§8:** retirar el disparador «cuando el formulario de contacto pase a procesarse en un
servidor» (ya ocurrió en WordPress). Conservar el resto de disparadores, incluida una posible
revisión legal posterior.

Este ADR **sustituye** el gate de Contact Form 7 y la espera de revisión legal de ADR 0039
(puntos 4–5 de aquella decisión y el disparador conservado de ADR 0028). La publicación de
`/privacidad` y el carácter provisional del texto **siguen** en ADR 0039.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Esperar asesoría legal antes de CF7 en producción (ADR 0039) | Descartada por el propietario: no ocurrirá este año; no bloqueará el corte |
| Cortar a WordPress sin Contact Form 7 hasta que haya dictamen | Descartada: el formulario real es alcance de WU-09 y del corte; WhatsApp/correo no sustituyen el envío del sitio |
| Reescribir todo `/privacidad` como texto legal definitivo | Descartada: no hay dictamen; se actualizan solo los hechos del formulario |
| Actualizar también el HTML estático ahora | Descartada: en producción el formulario aún no envía; mentiría al visitante |

## Consecuencias

- WU-09 deja de estar bloqueado por PRIV-001b. Gate restante: copy WordPress del §2.2 según este
  ADR + verificación de entrega en staging.
- OWN-018 registra la decisión de dueño. Matriz, checklist y reglas de agente se alinean.
  Entrega: ADR [0045](0045-cf7-entrega-gate-corte.md).
- PRIV-001b permanece abierto como trabajo jurídico *posterior* al corte, no como ítem de
  lanzamiento.
- El importer no pisa el delta de `/privacidad` en re-import (create-missing-only; force de campo
  solo si WU-09 lo documenta).

## Referencias

- ADR [0039](0039-aviso-privacidad-provisional-estatico.md) — publicación del aviso; gate
  sustituido por este ADR
- ADR [0028](0028-privacidad-aplazada-conscientemente.md) — aplazamiento histórico de publicar
- ADR [0026](0026-contact-form-7.md) — plugin y buzón
- ADR [0019](0019-sin-analitica-con-cookies.md) — sin cookies de analítica
- `docs/backlog-decisiones-owner-migracion.md` — OWN-018 · OWN-033
- ADR [0045](0045-cf7-entrega-gate-corte.md) — gate de entrega (sustituye el punto 5)
- `static/privacidad/index.html` — copy live hasta el corte
