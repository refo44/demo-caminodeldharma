# Architecture Decision Records (ADR)

Registro de decisiones técnicas y estructurales del proyecto **Camino del Dharma**.

Los ADR capturan el **contexto**, la **decisión** y las **consecuencias** de elecciones que podrían cuestionarse o revertirse meses después sin recordar por qué se tomaron.

---

## Cuándo crear un ADR

Crear un ADR cuando una decisión:

- afecte la arquitectura, el despliegue, la seguridad o la estructura de contenido;
- implique elegir entre varias alternativas razonables;
- establezca una restricción que deba mantenerse en fases posteriores;
- pueda resultar difícil de entender sin conocer su contexto original.

---

## Estados

| Estado | Significado |
| ------ | ----------- |
| **Propuesta** | En discusión; aún no vincula al proyecto. |
| **Aceptada** | Decisión vigente; la implementación y la documentación deben alinearse con ella. |
| **Rechazada** | Alternativa descartada; se conserva como registro histórico. |
| **Sustituida** | Reemplazada por un ADR posterior; enlazar ambos. |
| **Obsoleta** | Ya no aplica al contexto actual; conservar por trazabilidad. |

**Regla de inmutabilidad:** un ADR **aceptado** no se edita para cambiar su sentido. Si la arquitectura cambia, se crea un **nuevo** ADR que sustituye al anterior y se actualiza el estado del ADR previo a **Sustituida**, con enlace bidireccional.

---

## Formato

Cada ADR es un archivo Markdown numerado:

```text
docs/adr/NNNN-titulo-en-kebab-case.md
```

Plantilla mínima:

```markdown
# ADR NNNN: Título de la decisión

## Estado

Aceptada | Propuesta | Rechazada | Sustituida | Obsoleta

## Fecha

YYYY-MM-DD

## Contexto

Qué problema existía, qué restricciones había y por qué era necesario decidir.

## Decisión

Qué se decidió exactamente.

## Alternativas consideradas

Qué otras opciones se evaluaron.

## Consecuencias

Beneficios, riesgos, limitaciones y trabajo futuro.

## Referencias

Documentos, issues, commits o ADR relacionados.
```

---

## Índice

| ADR | Título | Estado |
| --- | ------ | ------ |
| [0001](0001-maqueta-estatica-como-base-definitiva.md) | Maqueta estática como base definitiva | Aceptada |
| [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) | WordPress como adaptación sin rediseño | Aceptada |
| [0003](0003-eliminar-pwa-y-web-app-manifest.md) | Eliminar PWA y Web App Manifest | Aceptada |
| [0004](0004-git-como-fuente-unica-de-verdad.md) | Git como fuente única de verdad | Aceptada |
| [0005](0005-produccion-sin-edicion-manual.md) | Producción sin edición manual | Aceptada |
| [0006](0006-github-actions-para-despliegue.md) | GitHub Actions para CI/CD | Aceptada |
| [0007](0007-rsync-como-mecanismo-de-sincronizacion.md) | rsync como mecanismo de sincronización | Aceptada |
| [0008](0008-urls-estables-desde-la-maqueta.md) | URLs estables desde la maqueta | Aceptada |
| [0009](0009-css-y-tokens-invariantes-en-migracion.md) | CSS y tokens invariantes en la migración | Sustituida (WordPress) → [0029](0029-theme-bloques-full-site-editing.md) |
| [0010](0010-hsts-desactivado-hasta-auditoria.md) | HSTS desactivado hasta auditoría | Sustituida → [0018](0018-hsts-despliegue-escalonado.md) |
| [0011](0011-implementaciones-separadas-durante-migracion.md) | Implementaciones separadas durante migración | Sustituida → [0014](0014-monorepo-static-wordpress.md) |
| [0012](0012-wordpress-como-motor-de-contenido.md) | WordPress como motor de contenido | Aceptada |
| [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md) | Fuentes de verdad duales y alcance del despliegue | Aceptada |
| [0014](0014-monorepo-static-wordpress.md) | Monorepo con carpeta static/ al iniciar Fase 3 | Aceptada |
| [0015](0015-despliegue-manual-temporal.md) | Despliegue manual temporal | Aceptada |
| [0016](0016-automatizacion-ci-cd-pospuesta.md) | Automatización CI/CD pospuesta | Aceptada |
| [0017](0017-repositorio-unico-durante-transicion.md) | Repositorio único durante la transición | Aceptada |
| [0018](0018-hsts-despliegue-escalonado.md) | HSTS — despliegue escalonado (transición → año) | Sustituida en lo operativo → [0020](0020-hsts-aplazado-hasta-wordpress.md) |
| [0019](0019-sin-analitica-con-cookies.md) | Sin analítica con cookies — GA4 descartado definitivamente | Aceptada |
| [0020](0020-hsts-aplazado-hasta-wordpress.md) | HSTS aplazado hasta después del corte a WordPress | Aceptada |
| [0021](0021-lightbox-galeria-nativo-wordpress.md) | Lightbox de la galería — nativo de WordPress, no propio | Aceptada |
| [0022](0022-sin-urls-de-filtro-por-ciudad.md) | La ciudad es taxonomía, no URL — sin archivos de eventos por ciudad | Aceptada |
| [0023](0023-entorno-local-wordpress-docker.md) | Entorno de desarrollo local de WordPress con Docker | Aceptada |
| [0024](0024-plugin-dominio-theme-presentacion.md) | Plugin propio para el dominio; el theme solo presenta | Aceptada |
| [0025](0025-politica-plugins-terceros.md) | Plugins de terceros solo con aprobación por ADR | Aceptada |
| [0026](0026-contact-form-7.md) | Contact Form 7 para el formulario de contacto | Aceptada |
| [0027](0027-estandares-ingenieria-codigo.md) | Estándares de ingeniería y estilo de código para Fase 3 | Aceptada |
| [0028](0028-privacidad-aplazada-conscientemente.md) | Política de privacidad aplazada conscientemente | Aceptada |
| [0029](0029-theme-bloques-full-site-editing.md) | Theme de bloques (Full Site Editing) en vez de PHP clásico con CSS congelado | Aceptada |
| [0030](0030-sitemap-nativo-wordpress.md) | Sitemap nativo de WordPress (`/wp-sitemap.xml`) reemplaza al `sitemap.xml` manual | Aceptada |
| [0031](0031-tags-blog-noindex-hasta-volumen.md) | Tags nativos en el blog (`post_tag`) — habilitados; archivo noindex hasta tener volumen | Aceptada |
| [0032](0032-contrato-migracion-static-wordpress.md) | Contrato de migración static → WordPress (cinco entregables) | Aceptada |
| [0033](0033-importador-contenido-vs-fixtures.md) | Importador de contenido institucional vs fixtures | Aceptada |
| [0034](0034-static-live-como-fuente-contenido-produccion.md) | Sitio estático live como fuente de contenido de producción hasta el corte | Aceptada |
| [0035](0035-todos-los-eventos-tienen-single.md) | Todo evento tiene ficha pública; los pasados no se inscriben | Aceptada |
| [0036](0036-urls-album-galeria-noindex.md) | URLs de álbum `/galeria/{slug}` permitidas; noindex hasta volumen | Aceptada |
| [0037](0037-cpt-autor-blog-url-author.md) | CPT de autor del blog; perfil `/author/{slug}` | Aceptada |
| [0038](0038-pruebas-tdd-phpunit-sonar.md) | Pruebas TDD, wp-phpunit y SonarQube Cloud | Aceptada |

### Correspondencia con decisiones consolidadas

| Decisión consolidada | ADR |
| -------------------- | --- |
| WordPress como CMS | [0012](0012-wordpress-como-motor-de-contenido.md) |
| Maqueta estática como referencia definitiva | [0001](0001-maqueta-estatica-como-base-definitiva.md) |
| Convivencia temporal static/ + wordpress/ | [0014](0014-monorepo-static-wordpress.md), [0017](0017-repositorio-unico-durante-transicion.md) |
| Sin PWA ni manifest | [0003](0003-eliminar-pwa-y-web-app-manifest.md) |
| Git como fuente de verdad del código | [0004](0004-git-como-fuente-unica-de-verdad.md), [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md) |
| Producción no se edita directamente | [0005](0005-produccion-sin-edicion-manual.md) |
| Despliegue manual temporal | [0015](0015-despliegue-manual-temporal.md) |
| CI/CD pospuesto | [0016](0016-automatizacion-ci-cd-pospuesta.md) (implementación de [0006](0006-github-actions-para-despliegue.md) diferida) |
| HSTS / transporte | [0010](0010-hsts-desactivado-hasta-auditoria.md) y [0018](0018-hsts-despliegue-escalonado.md) (históricos), [0020](0020-hsts-aplazado-hasta-wordpress.md) (vigente) |
| Privacidad / medición | [0019](0019-sin-analitica-con-cookies.md) — sin cookies de analítica; medición vía Search Console |
| Lightbox de la galería | [0021](0021-lightbox-galeria-nativo-wordpress.md) — visor nativo de Gutenberg; no se implementa uno propio en la maqueta |
| URLs de filtro por ciudad | [0022](0022-sin-urls-de-filtro-por-ciudad.md) — la ciudad es taxonomía, no dirección; los eventos de cada ciudad van dentro de `/sanghas/{ciudad}` |
| Entorno de desarrollo local WordPress | [0023](0023-entorno-local-wordpress-docker.md) — Docker, replicando versiones de Hostinger; tarea separada de implementar el theme |
| Dominio en plugin, presentación en theme | [0024](0024-plugin-dominio-theme-presentacion.md) — `camino-del-dharma-core` dueño de CPTs/taxonomías/roles desde el inicio de Fase 3 |
| Plugins de terceros | [0025](0025-politica-plugins-terceros.md) — solo con ADR propio; vetados por defecto: ACF, page builders, suites SEO todo-en-uno |
| Formulario de contacto en WordPress | [0026](0026-contact-form-7.md) — Contact Form 7, buzón caminodeldharma1@gmail.com |
| Estándares de código en Fase 3 | [0027](0027-estandares-ingenieria-codigo.md) — criterio senior + SOLID/KISS/YAGNI/Clean Code cuando aplique; WPCS y seguridad de WordPress no negociables |
| Política de privacidad | [0028](0028-privacidad-aplazada-conscientemente.md) — aplazada hasta asesoría legal; gate obligatorio antes de publicar Contact Form 7 |
| Arquitectura del theme WordPress | [0029](0029-theme-bloques-full-site-editing.md) — theme de bloques (Full Site Editing); paleta, tipografía y espaciado editables desde wp-admin vía `theme.json`/Global Styles; sustituye a [0009](0009-css-y-tokens-invariantes-en-migracion.md) solo para WordPress |
| Sitemap en WordPress | [0030](0030-sitemap-nativo-wordpress.md) — `/wp-sitemap.xml` nativo reemplaza al `sitemap.xml` manual solo para WordPress; `static/` no cambia |
| Tags del blog | [0031](0031-tags-blog-noindex-hasta-volumen.md) — `post_tag` habilitado para editores; archivo de tag existe pero noindex hasta volumen suficiente, criterio cualitativo |
| Contrato de migración static → WordPress | [0032](0032-contrato-migracion-static-wordpress.md) — cinco entregables; ruta **static → FSE** (sin theme clásico intermedio); template ≠ Page; deploy success ≠ application success |
| Importación de contenido vs fixtures | [0033](0033-importador-contenido-vs-fixtures.md) — WP-CLI, create-missing-only; copy institucional vs `content-source/`; eventos/blog/galería live = HTML hasta extraer (ADR 0034) |
| Estático live como contenido de producción | [0034](0034-static-live-como-fuente-contenido-produccion.md) — hardcoded ≠ dummy; extracción; conteos; freeze/delta; patterns ≠ content |
| Fichas de todos los eventos | [0035](0035-todos-los-eventos-tienen-single.md) — 10 singles; pasados sin inscripción; slugs en ledger |
| URLs de álbum de galería | [0036](0036-urls-album-galeria-noindex.md) — `/galeria/{slug}` existe; noindex hasta volumen; hub `/galeria` KEEP |
| Autores del blog | [0037](0037-cpt-autor-blog-url-author.md) — CPT `blog_author`; `/author/{slug}`; usuario WP no firma |
| Pruebas / TDD / Sonar | [0038](0038-pruebas-tdd-phpunit-sonar.md) — tres niveles; kit el día uno del PHP; Automatic Analysis + `.sonarcloud.properties` |

---

## Relación con otros documentos

- **`17-orden-implementacion`:** orden de fases y criterios de cierre; referencia este registro.
- **Pruebas:** `docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038). Alcance Sonar: `.sonarcloud.properties`.
- **Contrato de migración:** `docs/contrato-migracion-static-wordpress.md`, matriz y cutover checklist (ADR 0032).
- **Decisiones de dueño:** `docs/backlog-decisiones-owner-migracion.md` — cerrado (v1.18); no son ADR.
- **`docs/` numerados:** guías de implementación; deben respetar los ADR vigentes.
- **`CHANGELOG.md`:** historial de despliegues; no sustituye a los ADR.

La implementación y la documentación general del proyecto deben mantenerse alineadas con los ADR en estado **Aceptada**.
