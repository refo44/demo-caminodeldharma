# ADR 0019: Sin analítica con cookies — GA4 descartado de forma definitiva

## Estado

Aceptada

## Fecha

2026-07-20

## Contexto

La auditoría de producción (2026-07-19) registró **PRIV-001**: Google Analytics 4 fijaba cookies
`_ga*` en el primer render, sin mecanismo de consentimiento y sin política de privacidad publicada.
GA4 se desactivó en la v1.0.12 conservando el ID de propiedad (`G-B8FY69RGSS`) para una eventual
reactivación, que quedaba pendiente de decisión organizativa (TASK-0006).

Al evaluar esa reactivación, los datos de la auditoría mostraron algo determinante: **la analítica
respondería una pregunta que el proyecto no tiene**.

- CrUX devuelve «No hay datos» para el origen: el tráfico está por debajo del umbral de Google
  (EVID-0047).
- El sitio está ausente de la página 1 en todas las consultas amplias y locales medidas
  (EVID-0037/0040); solo 4 de 13 URLs aparecen en `site:`.
- Autoridad de dominio DR 0,4 / DA 2–6 (EVID-0043/0045/0046).

Con ese volumen, GA4 produciría un puñado de sesiones mensuales: ruido estadístico, no información.
El cuello de botella no es qué hacen las visitas, sino que no llegan — y esa pregunta la responde
**Google Search Console**, que es gratuito, no usa cookies y no requiere banner.

A ello se suma que el propósito del sitio no es comercial. La señal de participación real —que alguien
acuda a la meditación del lunes— es directamente observable, y quien se acerca a la comunidad pasa por
un canal humano (WhatsApp) donde puede preguntarse cómo llegó. A esta escala, el contacto directo
aporta más contexto que la analítica agregada.

## Decisión

1. **No se instalará Google Analytics ni ninguna analítica basada en cookies** en el sitio.
2. La medición de búsqueda se hace con **Search Console** (y opcionalmente Bing Webmaster Tools):
   sin cookies, sin banner, sin implicación de consentimiento.
3. Si en el futuro se necesitara medición de comportamiento en el sitio, la vía será **analítica sin
   cookies** (Plausible, Fathom, Umami, GoatCounter o equivalente), **nunca** volver a GA4.
4. Se conserva el ID `G-B8FY69RGSS` únicamente como registro histórico; no se reactiva.
5. Esta decisión **no elimina** la necesidad de una política de privacidad (ver Consecuencias).

### Criterio para reconsiderar

Ambas condiciones a la vez, no una sola:

- el tráfico es suficiente para que los datos sean estadísticamente significativos, **y**
- existe una decisión concreta que dependa de conocer el comportamiento dentro del sitio y que ninguna
  otra fuente responda.

Mientras el contacto directo con quien llega siga siendo viable, esa vía es preferible.

## Alternativas consideradas

| Alternativa | Decisión |
| ----------- | -------- |
| **Reactivar GA4 con Consent Mode v2 y banner** | Rechazada. Coste de UX y de superficie legal a cambio de datos incompletos (quien rechaza no se mide) y estadísticamente vacíos al volumen actual. Un banner de cookies contradice además el registro editorial de `docs/21` |
| **Analítica sin cookies desde ya** | Rechazada por ahora. Es la opción correcta *si* algún día hace falta, pero hoy Search Console cubre la pregunta abierta |
| **Solo logs del servidor** | Disponible en Hostinger sin coste ni instrumentación; suficiente como complemento puntual |
| **Mantener GA4 desactivado sin decidir** | Rechazada: dejaba TASK-0006 bloqueada de forma indefinida y el ID en un limbo |

## Consecuencias

**Beneficios**

- El sitio **no fija cookies propias**: verificado el 2026-07-20 (sin `Set-Cookie` en producción).
- No se necesita banner de consentimiento, lo que preserva la experiencia y el tono del sitio.
- Menor superficie legal frente a la Ley 1581/2012 y frente a visitantes de la UE.
- Sin scripts de terceros para analítica: contribuye al 99/100 móvil y 100/100 escritorio de
  PageSpeed (EVID-0047/0048).

**Lo que esta decisión NO resuelve**

- **Embeds de vídeo:** el sitio incrusta 8 reproductores de YouTube y 2 de Vimeo, ninguno con
  `youtube-nocookie` (verificado 2026-07-20). Al reproducir, esos terceros pueden fijar cookies.
  Migrar los embeds a `youtube-nocookie.com` sigue pendiente y permanece dentro de TASK-0006.
- **Política de privacidad:** sigue siendo recomendable. La Ley 1581/2012 cubre el tratamiento de
  datos personales en general —no solo cookies—, y el sitio recoge datos por canales de contacto.
  La conclusión jurídica corresponde a asesoría, no a esta decisión técnica.

**Costes aceptados**

- No habrá datos de comportamiento dentro del sitio (recorridos, permanencia, puntos de abandono).
- Si el sitio crece, habrá un periodo sin histórico comparativo. Se acepta conscientemente.

## Referencias

- Hallazgo **PRIV-001** y **TASK-0006** en `.audit/`
- `.audit/working/seo-external.md`, `.audit/working/authority-backlinks.md` (volumen y autoridad)
- EVID-0043/0045/0046 (autoridad), EVID-0047/0048 (PSI y ausencia de datos CrUX)
- `CHANGELOG.md` v1.0.12 (desactivación de GA4)
- `docs/21-manual-voz-copywriting-editorial.md` §15 (registro editorial que el banner contradice)
