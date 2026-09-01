# ADR 0037: CPT de autor del blog; perfil `/author/{slug}`

## Estado

Aceptada

## Fecha

2026-08-29

## Contexto

OWN-010 preguntaba si el «Por…» de las **entradas del blog** (no de los eventos) debía seguir
siendo copy o Users de WordPress.

En el estático live no hay `/author/`. El byline es texto: «Por Comunidad Camino del Dharma»
(Círculos) y «Por Zheng Gong» (Sangha). Doc 15 pedía JSON-LD `Person` vs `Organization` desde
un registro fijo, **sin** usar el usuario que publica.

El propietario descarta A (copy / lista hardcodeada), B (Users + `/author/` nativo) y C
(híbrido). Quiere **fichas CPT**, buscador al asignar, y URL pública `/author/{slug}`
(contrato tipo revista de autores, **no** el modelo académico: sin ORCID, DOI, afiliación,
issues ni rol Author de WP).

## Decisión

1. **Byline = CPT**, no copy y no `post_author`. Nombre interno p. ej. `blog_author` (no sale
   en la URL). Registrar en `camino-del-dharma-core`, no en el theme.
2. **URL pública:** `/author/{slug}` (sin barra final, ADR 0008). Semilla:

   | Título | Slug previsto | URL |
   | ------ | ------------- | --- |
   | Zheng Gong | `zheng-gong` | `/author/zheng-gong` |
   | Comunidad Camino del Dharma | `comunidad-camino-del-dharma` | `/author/comunidad-camino-del-dharma` |

   El slug de la Comunidad se puede cambiar **antes del corte** si el editor elige otro
   `post_name`; entonces se actualiza el ledger. Tras el corte: KEEP o 301, no silencio.
3. **Todas las fichas son el mismo tipo.** No hay rama Person / Organization en código ni en
   campos. «Comunidad Camino del Dharma» es una ficha más.
4. **Usuario WP** = quién entra a wp-admin. Invisible en byline y JSON-LD. Metabox nativo
   «Autor» no manda (ocultar o ignorar).
5. **`/author/` de usuarios WP:** apagado (**404**). No es el perfil. No crear una Page con
   slug `author`.
6. **Asignación:** meta del `post` `authors` = array de IDs del CPT, únicos, orden = byline.
   Varios autores por entrada. Sin default. Metabox con buscador (REST, ≥2 caracteres, no
   precargar catálogo). No alta inline. Solo fichas **publicadas** aparecen en el buscador.
7. **Publicar** exige ≥1 ficha publicada. Borrador puede ir sin autor. Posts ya publicados
   sin meta **no** se despublican al activar el plugin. Un post publicado no puede quedar en
   cero autores al guardar.
8. **Campos de la ficha:** título (nombre), slug, contenido (bio), imagen destacada. Sin
   `entity_type`, ORCID ni afiliación.
9. **`query_var` del CPT ≠ `author`.** Debe ser p. ej. `blog_author`. Si queda `author`,
   `/author/zheng-gong` reescribe a `index.php?author=zheng-gong` (archivo de **users**) y el
   single del CPT da **404** aunque REST y el archivo de fichas respondan 200. `rewrite.slug`
   = `author`, `with_front` = false. `capability_type` = `blog_author` / `blog_authors` (no
   `author`). Flush solo en activación/upgrade.
10. **JSON-LD (doc 15):** cada ficha emite el mismo `@type` (`Thing`), con `name` y `url` =
    permalink `/author/{slug}`. `publisher` del post sigue siendo la Organization del sitio.
    Varios autores = array. Inicio y single leen el mismo meta. Se acaba el «Por…» pegado en
    el extracto.
11. **Front:** «Por {título}» enlazado al permalink de la ficha. Varios: fórmula de voz del
    sitio («Por A y B»). Single del CPT: bio, foto, listado de posts cuyo `authors` contiene
    ese ID (query por meta, no `author` de WP).
12. **`/comunidad` se KEEP.** No se mueve la bio del fundador fuera de esa Page en el corte
    (OWN-007). La ficha CPT tiene bio propia; el editor la rellena. Duplicar el texto largo
    o resumirlo es editorial, no un 301.
    *(OWN-016: en WordPress, la Page `/comunidad` **añade enlaces** a `/author/zheng-gong` y
    a la ficha de la Comunidad. El estático no se cambia ahora.)*
13. **SEO del archivo de fichas** (`/author`, listado): `noindex, follow` hasta volumen
    (mismo espíritu que ADR 0031 / 0036). Los **singles** `/author/{slug}` sí se indexan
    (nombre, bio, foto, entradas). No Co-Authors Plus (ADR 0025).
14. **Eventos** no usan este CPT.

Registro mínimo (el 404 de §9 es el bug a no repetir):

```php
register_post_type( 'blog_author', array(
    'public'       => true,
    'show_in_rest' => true,
    'has_archive'  => true,
    'query_var'    => 'blog_author', // NUNCA 'author'
    'rewrite'      => array(
        'slug'       => 'author',
        'with_front' => false,
    ),
    'capability_type' => array( 'blog_author', 'blog_authors' ),
    'map_meta_cap'    => true,
    'supports'        => array( 'title', 'editor', 'thumbnail' ),
) );
```

QA HTTP del **single** (no solo REST ni el archivo). Equivalente futuro a un script de
permalinks; no portar código de otro repo.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| A — copy / lista fija | Tercer autor = deploy; el nombre se duplica. |
| B — Users + `/author/` nativo | Mezcla login y firma; cuentas dummy; archivos de usuario delgados. |
| C — híbrido + apagar `/author/` | Más piezas que A; el propietario quiere perfil público. |
| Prefijo `/autores/` o `/revista/autores/` | El dueño quiere `/author/{slug}`. |

## Consecuencias

**Beneficios:** tercer autor sin deploy; quien publica ≠ quien firma; URL de perfil estable.

**Riesgos:** colisión `query_var` `author` (404); Page `author`; archivos de user vivos;
JSON-LD `Thing` no es el `Person`/`Organization` del HTML live (destino WP; el estático no
cambia hasta el corte). El archivo `/author` con dos fichas es delgado → noindex.

**Trabajo futuro:** plugin (CPT, meta, metabox, JS, guardas); semilla de 2 fichas; asignar
las 2 entradas; templates `single-blog_author.html` / archivo; no implementar en esta
sesión. La UI de asignación en Gutenberg queda acotada por ADR
[0042](0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md) (panel nativo o clásico **con**
sync REST; el guard no se relaja).

## Referencias

- OWN-010 · ADR [0008](0008-urls-estables-desde-la-maqueta.md), [0024](0024-plugin-dominio-theme-presentacion.md), [0025](0025-politica-plugins-terceros.md), [0031](0031-tags-blog-noindex-hasta-volumen.md), [0042](0042-gutenberg-meta-sin-metabox-clasico-sin-sync.md)
- [`docs/15-assets-strategy.md`](../15-assets-strategy.md) §12.4
- [`docs/redirect-ledger.md`](../redirect-ledger.md)
