# ADR 0031: Tags nativos en el blog (`post_tag`) — habilitados; archivo noindex hasta tener volumen

## Estado

Aceptada

## Fecha

2026-08-01

## Contexto

El blog (`post`, `/blog/{slug}/`) no tenía, hasta hoy, ninguna decisión documentada sobre taxonomías.
El propietario pidió permitir que los artículos usen **tags nativos de WordPress** (`post_tag`) para
organización editorial. WordPress registra `post_tag` por defecto para el post type `post` y genera
automáticamente una página de archivo pública por cada término (`/tag/{slug}/` por defecto; en este
proyecto, anidada bajo el blog: `/blog/tag/{slug}/`).

El blog tiene, a la fecha de este ADR, **una sola entrada publicada**. Un archivo de tag indexado con
un solo artículo (o ninguno, si el tag se crea antes de usarlo) es contenido delgado — el mismo riesgo
que llevó a ADR 0022 a prohibir archivos públicos para `event_city`/`event_type`. La diferencia es que
`event_city`/`event_type` se prohibieron **de forma permanente** porque no hay una dirección física
real que respalde una página por ciudad (ver memoria del proyecto: ninguna sangha tiene sede fija);
los tags de blog, en cambio, organizan contenido editorial real que sí se planea que crezca — no es un
veto estructural, es una cuestión de **cuándo** hay suficiente contenido para que la página sea útil.

## Decisión

1. **`post_tag` queda habilitado** para el post type `post`. Los editores pueden crear y asignar tags
   libremente desde el editor de bloques, sin restricción de un vocabulario cerrado.
2. **El archivo de cada tag existe como URL** (`/blog/tag/{slug}/`, documentado en
   `docs/11-arbol-urls-final.md`) pero se sirve con **`noindex, follow`** por defecto — no es un 404 ni
   una taxonomía sin archivo (a diferencia de `event_city`/`event_type`, ADR 0022); simplemente no se
   ofrece a buscadores todavía.
3. **Criterio de reconsideración: cualitativo, no un umbral numérico automático** — mismo aprendizaje
   que la revisión de ADR 0022 (que derogó su umbral fijo de "~5 eventos" en favor de un criterio de
   contenido real). Un tag se vuelve indexable cuando su archivo reúne suficientes artículos como para
   funcionar como un hub temático útil, evaluado caso por caso al publicar o revisar contenido — no se
   abre automáticamente al cruzar un número fijo de entradas.
4. **Implementación técnica:** `noindex, follow` condicional en los archivos de `post_tag` (p. ej. vía
   el filtro nativo `wp_robots`, WP 5.7+), no una regla estática ni un plugin — código first-party en
   `camino-del-dharma-core` o el theme, siguiendo el mismo criterio de ADR 0025 y de
   [[wordpress-seo-dinamico-first-party]]. Se retira el `noindex` del tag concreto cuando se decida que
   ya tiene volumen suficiente, sin tocar el resto.
5. **JSON-LD:** `keywords` es un campo **opcional** en el `BlogPosting` de `docs/15-assets-strategy.md`
   §12.4 — se incluye solo si la entrada tiene tags asignados; no se inventa ni se fuerza.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Público e indexable desde ya | Con una sola entrada publicada, cada archivo de tag sería contenido delgado real hoy mismo — el mismo problema que ADR 0022 evitó para eventos. |
| Sin archivo público, nunca (mismo trato que `event_city`/`event_type`) | Los tags de blog no son un caso de "no hay dirección física real"; organizan contenido editorial genuino que se espera que crezca. Vetarlos para siempre desperdicia un hub temático legítimo cuando el blog tenga más artículos. |
| Umbral numérico automático (p. ej. "se indexa al llegar a 3 artículos") | Ya se probó con `event_city`/`event_type` (ADR 0022) y se derogó: el volumen por sí solo no garantiza que la página aporte algo distinto; se prefiere juicio editorial caso por caso. |

## Consecuencias

**Beneficios:**

- Los editores organizan el blog por tema desde el primer artículo, sin deuda técnica que resolver
  después.
- Ningún archivo de tag delgado llega a indexarse mientras el blog es nuevo.
- Dejar la puerta abierta a hubs temáticos reales cuando haya volumen, sin necesidad de una decisión de
  arquitectura nueva en ese momento — solo quitar el `noindex` tag por tag.

**Riesgos:**

- Un `noindex` "temporal" puede quedar olvidado indefinidamente por inercia si nadie revisa
  periódicamente qué tags ya tienen contenido suficiente — mitigación: revisar en cada tanda de
  publicaciones nuevas del blog, no como tarea aislada.
- Requiere código propio (filtro `wp_robots` condicional) en vez de una casilla de plugin — coherente
  con la política de no-plugins-de-terceros (ADR 0025), pero es trabajo real a implementar.

**Trabajo futuro:**

- Al construir `camino-del-dharma-core`, implementar el filtro `noindex, follow` condicional para
  archivos `post_tag`.
- Revisar periódicamente (con cada tanda de artículos nuevos) qué tags ya justifican quitar el
  `noindex`.

## Referencias

- ADR [0022](0022-sin-urls-de-filtro-por-ciudad.md) — precedente de archivo de taxonomía sin indexar y
  de la derogación de un umbral numérico en favor de criterio cualitativo.
- ADR [0025](0025-politica-plugins-terceros.md) — código first-party antes que plugin de terceros.
- ADR [0030](0030-sitemap-nativo-wordpress.md) — mismo cuidado de qué URLs expone el sitio a buscadores.
- `docs/03-wordpress-content-model.md`, `docs/11-arbol-urls-final.md`, `docs/15-assets-strategy.md`
  §12.4 — actualizados en la misma sesión.
