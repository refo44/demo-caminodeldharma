# ADR 0036: URLs de álbum `/galeria/{slug}` permitidas; noindex hasta volumen

## Estado

Aceptada

## Fecha

2026-08-29

## Contexto

En producción (`https://caminodeldharma.org/galeria`) hay **una** Page `/galeria` con tres grupos
(General, 2023, 2021). OWN-008 (2026-08-29, primera frase) pedía **la misma agrupación** y,
en un primer cierre, una sola URL.

El propietario quiere **además** poder abrir un álbum en una URL propia, p. ej. `/galeria/2023`.

Hoy 2023 y 2021 tienen **cinco** fotos cada uno. Indexar esas URLs sería contenido delgado y
duplicaría las mismas imágenes que ya están en `/galeria` — el mismo tipo de riesgo que ADR 0031
(tags) y que evitó ADR 0022 (archivos por ciudad).

## Decisión

1. **`/galeria` se KEEP** como hub. Sigue mostrando los **mismos tres grupos**, mismos títulos y
   mismas fotos que el live (OWN-007 / OWN-008). Esa es la URL canónica de la galería para SEO.
2. Se permite una taxonomía de álbum (p. ej. `gallery_album`) con rutas públicas
   `/galeria/{slug}` (sin barra final). Slugs iniciales, extraídos del JSON live:

   | Álbum en producción | URL prevista |
   | ------------------- | ------------ |
   | General | `/galeria/general` |
   | 2023 | `/galeria/2023` |
   | 2021 | `/galeria/2021` |

3. Esas URLs de término **existen** (no 404) pero se sirven **`noindex, follow` por defecto**.
   No se ofrecen a buscadores hasta que el propietario decida, **caso por caso**, que el álbum
   tiene volumen y texto propio como para ser un hub (mismo criterio cualitativo que ADR 0031).
   No hay umbral numérico automático.
4. **Colisión de rewrite:** el slug de la Page es `galeria`. El plugin
   `camino-del-dharma-core` debe hacer que `/galeria` siga siendo la **Page** y
   `/galeria/{term}` el archivo del término. Flush solo en activación/upgrade. Un término no
   puede llamarse como una Page hija futura.
5. Presentación en `/galeria`: Encabezado + bloque Galería (doc 03) **o** Query por término;
   el visitante ve el mismo agrupado. Los enlaces a `/galeria/2023` son opcionales (título del
   álbum), no sustituyen el hub.
6. No CPT de álbum. No `/galeria/2023` en el estático hoy: en el ledger son **PLANNED KEEP**.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Solo `/galeria`, sin URLs de álbum | El propietario quiere la opción `/galeria/2023`. |
| Indexar `/galeria/2023` y `/galeria/2021` desde el corte | 5 fotos; duplicado del hub; thin content. |
| CPT por álbum | URLs y objetos de más; no hace falta para 3 grupos. |
| Canonical de cada álbum hacia `/galeria` + index | Confunde: la URL existiría pero cedería siempre al hub. Mejor `noindex` hasta volumen. |

## Consecuencias

**Beneficios:** se puede compartir o enlazar un año; el editor puede crear álbumes nuevos.

**Riesgos SEO (aceptados y mitigados):**

- Duplicación: las mismas fotos viven en el hub y en el término.
- Thin pages si se indexan 2023/2021 hoy.
- `/galeria` vs rewrite: mal implementado, la Page puede dejar de responder.

**Mitigación:** hub indexable; términos `noindex, follow` hasta decisión editorial; QA HTTP de
`/galeria` **y** de `/galeria/2023` en staging.

**Trabajo futuro:** registrar taxonomía en el plugin; seed asigna términos según `#gallery-albums-data`.
No implementar en la sesión de este ADR.

## Referencias

- OWN-008 · ADR [0031](0031-tags-blog-noindex-hasta-volumen.md), [0008](0008-urls-estables-desde-la-maqueta.md), [0022](0022-sin-urls-de-filtro-por-ciudad.md)
- [`docs/redirect-ledger.md`](../redirect-ledger.md)
- [`https://caminodeldharma.org/galeria`](https://caminodeldharma.org/galeria)
