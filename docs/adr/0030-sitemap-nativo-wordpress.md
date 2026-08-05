# ADR 0030: Sitemap nativo de WordPress (`/wp-sitemap.xml`) reemplaza al `sitemap.xml` manual

## Estado

Aceptada

## Fecha

2026-08-01

## Contexto

`docs/15-assets-strategy.md` §12.2 exige, para la maqueta estática, un `sitemap.xml` mantenido a mano
en la raíz (solo URLs indexables; actualizar `<lastmod>` al cambiar una página), declarado en
`robots.txt` como `Sitemap: https://caminodeldharma.org/sitemap.xml`.

WordPress genera desde el núcleo (WP 5.5+) su propio sitemap XML en `/wp-sitemap.xml` — un índice que
enumera automáticamente tipos de contenido públicos (`page`, `post`, el CPT `event`) en sub-sitemaps
paginados. `.audit/audit-schedule.md` (Hito 2 §5) ya había marcado como riesgo a verificar que
"WordPress genera los suyos — no deben duplicar ni contradecir", sin resolver todavía cuál de los dos
prevalece.

Mantener ambos sitemaps activos a la vez es peor que elegir uno: Google Search Console recibiría dos
listas de URLs potencialmente distintas para el mismo dominio, y un sitemap manual desactualizado
(nadie recuerda tocarlo tras publicar un evento nuevo desde wp-admin) generaría inconsistencias que un
sitemap automático no tiene, porque se regenera solo con cada publicación.

## Decisión

Para la implementación WordPress (Fase 3 en adelante), se usa **el sitemap nativo de WordPress**
(`/wp-sitemap.xml` y sus sub-sitemaps) como única fuente de verdad. El `sitemap.xml` manual **queda
deprecado para WordPress**:

1. `robots.txt` del theme declara `Sitemap: https://caminodeldharma.org/wp-sitemap.xml`.
2. No se instala ningún plugin ni código propio para generar o mantener un `sitemap.xml` alternativo.
3. Verificación obligatoria antes de dar esto por cerrado: los tipos de contenido y taxonomías que
   **no** deben tener archivo público (`event_city`, `event_type` — ADR 0022) no deben aparecer en
   `/wp-sitemap.xml`. El comportamiento nativo de WordPress ya excluye taxonomías registradas como no
   públicas o sin archivo, pero hay que confirmarlo contra la configuración real de
   `camino-del-dharma-core` antes de publicar, no asumirlo.
4. Si `/wp-sitemap.xml` incluyera algo que no debe ser público (p. ej. un archivo de autor no
   contemplado en `docs/11-arbol-urls-final.md`), se excluye vía los filtros nativos
   (`wp_sitemaps_add_provider`, `wp_sitemaps_taxonomies`, `wp_sitemaps_post_types`) — no se apaga el
   sitemap nativo completo para resolver un caso puntual.
5. **`static/` no cambia.** Sigue con su `sitemap.xml` manual mientras esa implementación sea la que
   está en producción (ADR 0001, ADR 0002); esta decisión aplica solo a la implementación WordPress.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Mantener el `sitemap.xml` manual también en WordPress, desactivando el nativo | Reintroduce el mismo problema que motivó dejar de escribir JSON-LD a mano para contenido dinámico: alguien tiene que acordarse de actualizarlo en cada publicación; el nativo se regenera solo. |
| Mantener ambos activos | Descartada explícitamente — dos sitemaps para el mismo dominio es la causa raíz que este ADR evita, no una opción viable. |
| Plugin de terceros para generar sitemap (fuera de una suite SEO completa) | Innecesario: el núcleo de WordPress ya resuelve esto sin dependencias nuevas — coherente con el orden de preferencia de ADR 0025 (APIs nativas antes que plugins). |

## Consecuencias

**Beneficios:**

- Un sitemap siempre actualizado automáticamente con cada evento o artículo publicado, sin paso manual
  que alguien pueda olvidar.
- Sin dependencias nuevas; usa exclusivamente el núcleo de WordPress (ADR 0025, preferencia 1).
- Elimina la ambigüedad que el propio Hito 2 del calendario de auditorías señalaba como riesgo abierto.

**Riesgos:**

- El sitemap nativo de WordPress es menos configurable que uno escrito a mano: por defecto incluye
  todo tipo de contenido público. Hay que verificar explícitamente (punto 3 de la Decisión) que no
  arrastre nada que ADR 0022 u otra decisión haya marcado como no indexable.
- `robots.txt` deja de coincidir entre `static/` y WordPress (`/sitemap.xml` vs `/wp-sitemap.xml`) —
  es intencional (implementaciones separadas, ADR 0014), pero debe quedar así de explícito para que
  nadie "corrija" uno copiando el otro.

**Trabajo futuro:**

- Al construir `camino-del-dharma-core`, confirmar en Search Console que `/wp-sitemap.xml` no expone
  `event_city`, `event_type` ni ningún archivo fuera de `docs/11-arbol-urls-final.md`.
- Actualizar `robots.txt` del theme con la URL correcta al implementarlo.

## Referencias

- `docs/15-assets-strategy.md` §12.2 — actualizado en la misma sesión para reflejar esta decisión.
- `.audit/audit-schedule.md` — Hito 2 §5, riesgo que este ADR resuelve.
- ADR [0022](0022-sin-urls-de-filtro-por-ciudad.md) — taxonomías sin archivo público, verificación
  cruzada obligatoria contra el sitemap nativo.
- ADR [0025](0025-politica-plugins-terceros.md) — preferencia por APIs nativas de WordPress.
- ADR [0001](0001-maqueta-estatica-como-base-definitiva.md), ADR
  [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) — `static/` no se toca.
