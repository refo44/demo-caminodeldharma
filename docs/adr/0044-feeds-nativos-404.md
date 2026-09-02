# ADR 0044: Feeds nativos de WordPress responden 404

## Estado

Aceptada

## Fecha

2026-09-01

## Contexto

WU-10 midió `/feed`, `/blog/feed` y `/comments/feed` (y alias del núcleo) como **200** en
WordPress local. En producción estática publicada esas rutas son **404**. No están en
[`docs/11-arbol-urls-final.md`](../11-arbol-urls-final.md): si una URL no está en el árbol, no
existe. El núcleo además anuncia RSS con `rel=alternate` en el `head`.

El propietario (OWN-025 / D-03, 2026-09-01) no acepta superficies indexables nuevas en el corte.
Un RSS editorial queda para **después** del corte (POST-010), no «nunca».

## Decisión

1. En el corte, las rutas de feed nativas (incluidos `/?feed=rss2`, `/feed/rss2`, `/feed/atom`,
   feeds de comentarios y de autor) responden **HTTP 404 real**, igual que el estático live.
2. Se retira el autodiscovery `rel=alternate` de RSS/Atom del `head`.
3. No se redirige 301 a `/blog`. No se deja un 200 con `noindex`.
4. Un feed público futuro exige fila POST-010 + actualización de `docs/11` y del ledger; no se
   implementa «por si acaso».

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Aceptar 200 y añadir feeds al árbol | Superficie nueva indexable; no está en producción |
| 200 + `noindex` | Sigue existiendo el documento; no paridad con 404 live |
| 301 a `/blog` | Inventa una redirección que el estático no tiene |
| Prohibir RSS para siempre | El propietario quiere poder publicarlo después (POST-010) |

## Consecuencias

**Beneficios:** paridad de rutas con `caminodeldharma.org`; sin feed accidental en Search Console.

**Trabajo:** first-party en `camino-del-dharma-core` (TDD, ADR 0038), en `main` **antes** de
crear staging Hostinger (OWN-035). Issue
[#11](https://github.com/refo44/demo-caminodeldharma/issues/11).

El estático no cambia.

## Referencias

- OWN-025 · D-03 · POST-010
- [`docs/11-arbol-urls-final.md`](../11-arbol-urls-final.md)
- ADR [0008](0008-urls-estables-desde-la-maqueta.md), [0032](0032-contrato-migracion-static-wordpress.md)
