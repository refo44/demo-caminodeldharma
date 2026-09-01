# ADR 0042: Meta en Gutenberg — sin metabox clásico sin sync; no es defecto de corte

## Estado

Aceptada

## Fecha

2026-09-01

## Contexto

Una auditoría read-only (2026-09-01) comparó este plugin con el fallo «revistalogos #30»: un
metabox clásico (`add_meta_box`) muestra valores en el DOM; Gutenberg Publicar envía REST **sin**
esas claves en `meta.*`; el guard o el front no ven lo que el editor cree haber guardado.

En este repositorio **no hay** `add_meta_box` ni JS admin de meta. El contenido de dominio llega
por migración/CLI. Un editor no puede tropezar con ese transporte hoy. Las filas `META-001`–
`META-005` del backlog eran riesgos **latentes**, no bugs de producción ni gates de staging/corte.

El metabox de autores sigue siendo trabajo futuro de ADR [0037](0037-cpt-autor-blog-url-author.md).
El CPT `event` ya declara `custom-fields` y meta `show_in_rest`; `blog_author` no registra meta
propia (bio = contenido, foto = destacada).

## Decisión

1. **`META-001`–`META-005` son restricciones de diseño para UI wp-admin futura, no defectos
   abiertos ni trabajo de pre-staging / corte.** No implementar paneles ni JS de sync «por si
   acaso». No relajar el guard REST de autores.
2. **Hasta el corte:** los editores conservan la meta importada. Gutenberg basta para título y
   cuerpo. **No** construir metaboxes clásicos en la sesión de staging (OWN-005).
3. **Cuando se construya UI de autores** (ADR 0037): panel nativo de Gutenberg
   (`PluginDocumentSettingPanel` o equivalente) **o** metabox clásico **con** sync demostrado a
   `core/editor` (`editPost({ meta: { authors } })`) en el mismo request que Publicar. Criterio
   de aceptación de esa unidad = META-001 (TDD, ADR 0038). El guard existente **detecta** el
   transporte roto; no lo corrige.
4. **Cuando se construya UI de evento / SEO / compartir:** la misma regla; META-002 y META-003
   se fusionan en esa unidad; META-005 son los tests REST de persistencia de **ese** camino, no
   una suite ahora sobre `meta_input` del importador.
5. **META-004:** añadir `custom-fields` a `blog_author` en el **mismo** commit que el primer
   `register_post_meta` de ese CPT, no antes (YAGNI). ADR 0037 no exige meta extra hoy.
6. **Native-first** (ADR [0025](0025-politica-plugins-terceros.md)): preferir panel Gutenberg
   frente a metabox clásico. ACF u otros constructores de campos siguen vetados.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Tratar META-* como bugs high y arreglarlos antes de staging | Descartada: no hay UI; inflaría el corte |
| Relajar el guard de autores para que un metabox futuro «funcione» sin REST | Descartada: el guard es la regla de negocio (ADR 0037) |
| Añadir `custom-fields` a `blog_author` preventivamente | Descartada: no hay meta que persistir |
| Copiar el JS de sync de otro repo antes de decidir la UI | Descartada: ADR 0025 / YAGNI |

## Consecuencias

- El corte no espera paneles de meta. Staging no añade `add_meta_box`.
- La sesión futura de autores (y la de evento/SEO) hereda un criterio de aceptación explícito.
- La auditoría `.audit/gutenberg-meta-transport-audit-2026-09-01.md` queda como etiqueta de
  advertencia, no como cola de implementación de Fase 3.

## Referencias

- OWN-019 · backlog `META-001`–`META-005` (decididas como restricciones)
- ADR [0024](0024-plugin-dominio-theme-presentacion.md), [0025](0025-politica-plugins-terceros.md),
  [0037](0037-cpt-autor-blog-url-author.md), [0038](0038-pruebas-tdd-phpunit-sonar.md)
- `.audit/gutenberg-meta-transport-audit-2026-09-01.md`
