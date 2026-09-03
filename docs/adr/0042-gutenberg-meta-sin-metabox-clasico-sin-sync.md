# ADR 0042: Meta en Gutenberg — sin metabox clásico sin sync; no es defecto de corte

## Estado

Aceptada

## Fecha

2026-09-01 · **enmendada 2026-09-03** (timing de `META-001`; cierre de `META-002`–`META-005`)

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
   *(Enmienda 2026-09-03, OWN-019: el propietario **adelanta `META-001` al pre-staging** —la UI
   de autores va antes de Hostinger,
   [#18](https://github.com/refo44/demo-caminodeldharma/issues/18)—. Cambia **solo el timing**:
   el criterio de aceptación de esta ADR sigue vigente y es el que se cumplió.)*
   *(Enmienda 2026-09-03, OWN-035: el propietario **adelanta también `META-002`–`META-005` al
   pre-staging** —los paneles de SEO y datos del evento en Gutenberg,
   [#19](https://github.com/refo44/demo-caminodeldharma/issues/19)—. El go a Hostinger espera
   ahora esa UI además de la de autores. Otra vez cambia **solo el timing**: el criterio de
   aceptación del punto 4 sigue vigente y es el que se cumplió.)*
2. **Hasta el corte:** los editores conservan la meta importada. Gutenberg basta para título y
   cuerpo. **No** construir metaboxes clásicos en la sesión de staging (OWN-005).
3. **Cuando se construya UI de autores** (ADR 0037): panel nativo de Gutenberg
   (`PluginDocumentSettingPanel` o equivalente) **o** metabox clásico **con** sync demostrado a
   `core/editor` (`editPost({ meta: { authors } })`) en el mismo request que Publicar. Criterio
   de aceptación de esa unidad = META-001 (TDD, ADR 0038). El guard existente **detecta** el
   transporte roto; no lo corrige.
   *(Cumplido 2026-09-03, plugin 0.7.4,
   [#18](https://github.com/refo44/demo-caminodeldharma/issues/18): se eligió el
   `PluginDocumentSettingPanel` nativo, así que la rama «metabox clásico» queda sin usar y el
   plugin sigue **sin** ningún `add_meta_box` —comprobado por test—. Escritura por
   `dispatch( 'core/editor' ).editPost( { meta } )`; buscador REST `status=publish` desde dos
   caracteres, sin precargar el catálogo; **guard intacto**. La otra mitad de ADR 0037 §4 —el
   control «Autor» del editor no manda— se resolvió retirando el enlace REST
   `wp:action-assign-author`: WordPress 7.1 rinde esa fila dentro del panel Resumen, no como
   panel propio, así que no existe nombre que pasar a `removeEditorPanel()`. `post_author`, su
   columna en el listado y las revisiones quedan intactos como rastro de quién creó y guardó.)*
4. **Cuando se construya UI de evento / SEO / compartir:** la misma regla; META-002 y META-003
   se fusionan en esa unidad; META-005 son los tests REST de persistencia de **ese** camino, no
   una suite ahora sobre `meta_input` del importador.
   *(Cumplido 2026-09-03, plugin 0.7.5,
   [#19](https://github.com/refo44/demo-caminodeldharma/issues/19): dos
   `PluginDocumentSettingPanel` nativos —«SEO y buscadores» para `post`/`page`/`event`/
   `blog_author`, «Datos del evento (schema.org)» solo para `event`—. Escritura por
   `dispatch( 'core/editor' ).editPost( { meta } )`; sin `wp-api-fetch` (no hay búsqueda REST);
   `includes/editor.php` encola el script **solo** en `post.php` / `post-new.php` de esos tipos.
   META-005 = round-trip REST real de la cabeza y de los datos del evento en
   `tests/WordPress/Editor_SeoPanelTest.php`, no `meta_input`. El plugin **sigue sin**
   `add_meta_box` —comprobado por test—. `seo_jsonld_extra` se difiere del panel v1: la meta y
   su sanitizador siguen registrados y editables por REST.)*
5. **META-004:** añadir `custom-fields` a `blog_author` en el **mismo** commit que el primer
   `register_post_meta` de ese CPT, no antes (YAGNI). ADR 0037 no exige meta extra hoy.
   *(Cumplido 2026-09-03, plugin 0.7.5, [#19](https://github.com/refo44/demo-caminodeldharma/issues/19):
   `blog_author` gana `custom-fields` **y** el registro `seo_*` de cabeza en el mismo commit. El
   nodo JSON-LD del perfil **sigue siendo `Thing`** (ADR 0037): cabeza más rica, nunca `@type`
   promovido. Sin copy sembrado para Zheng Gong ni Comunidad —D-08 /
   [#5](https://github.com/refo44/demo-caminodeldharma/issues/5) sigue pendiente—: esto solo hace
   que las fichas nuevas funcionen.)*
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

- Staging no añade `add_meta_box` —no lo añadió la UI de autores ni la de evento/SEO—.
- Enmienda 2026-09-03: el staging **sí** espera la UI de autores (`META-001`,
  [#18](https://github.com/refo44/demo-caminodeldharma/issues/18)) **y** la de SEO / datos del
  evento (`META-002`–`META-005`, [#19](https://github.com/refo44/demo-caminodeldharma/issues/19)),
  por decisión del propietario (OWN-019 / OWN-035) y con el criterio de aceptación de esta ADR
  cumplido, no relajado. El backfill de `seo_description` al publicar (binding rule #1) no toca
  la copia importada ni la del editor y no corre bajo WP-CLI, así que la convergencia add-only
  de `migrate convert` queda intacta.
- La sesión futura de autores (y la de evento/SEO) hereda un criterio de aceptación explícito.
- La auditoría `.audit/gutenberg-meta-transport-audit-2026-09-01.md` queda como etiqueta de
  advertencia, no como cola de implementación de Fase 3.

## Referencias

- OWN-019 · backlog `META-001`–`META-005` (decididas como restricciones)
- ADR [0024](0024-plugin-dominio-theme-presentacion.md), [0025](0025-politica-plugins-terceros.md),
  [0037](0037-cpt-autor-blog-url-author.md), [0038](0038-pruebas-tdd-phpunit-sonar.md)
- `.audit/gutenberg-meta-transport-audit-2026-09-01.md`
