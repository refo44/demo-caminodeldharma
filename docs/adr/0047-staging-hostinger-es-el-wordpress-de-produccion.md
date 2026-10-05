# ADR 0047: El WordPress de staging es el de producción

## Estado

Aceptada. El número es **0047** porque [ADR 0046](0046-despliegue-solo-por-tag-de-version-aprobado.md) ya registra el despliegue de código a staging por tag. Esta decisión no modifica ese contrato y no autoriza el corte.

## Fecha

2026-09-23

## Contexto

OWN-005 dejó WordPress en **otra instancia Hostinger, sin dominio custom**, mientras
`caminodeldharma.org` sigue sirviendo el sitio estático. Esa instancia ya existe:

`https://teal-woodpecker-284165.hostingersite.com`

Quedaba abierto **cómo** sería el switch. Una lectura posible era borrar este WordPress
y reinstalarlo sobre el `public_html` del estático, o crear un segundo WordPress el día
del corte. El propietario no quiere eso: este sitio temporal es el que debe convertirse
en el sitio principal.

Hostinger permite dos operaciones distintas, y el corte las usa en ese orden:

1. Pasar un sitio existente a un **dominio temporal** (`*.hostingersite.com`), conservando
   sus archivos.
2. En otro sitio existente, **Cambiar dominio** (`Sitios web → ⋮ → Cambiar dominio`) y
   asignarle un dominio que ya está en la cuenta.

La segunda operación **no reinstala** WordPress. Hostinger advierte que cambiar el
dominio de un sitio puede afectar los **buzones de correo** y los **subdominios**
asociados a ese dominio.

Este ADR **no autoriza el corte**. La sesión vigente sigue siendo construir staging.

## Decisión

1. **`teal-woodpecker-284165.hostingersite.com` es el WordPress de producción.** No se
   elimina y no se crea otro para el corte. Theme, plugin, importación, medios,
   formularios, SEO y QA se terminan **en este sitio**.
2. **Hasta el corte**, `caminodeldharma.org` sigue sirviendo el estático. Este sitio
   permanece en su URL temporal, con `WP_ENVIRONMENT_TYPE` en `staging` y
   `blog_public` en `0` (no indexable, OWN-005). Esas dos marcas no se dejan así para
   siempre: cambian en el corte, sobre esta misma instalación.
3. **El corte es un cambio de dominio del mismo sitio**, en una sesión posterior, y solo
   después de backups y de staging aprobado:
   1. Terminar staging (código, importación, medios, formularios, SEO, QA).
   2. Backups verificados (estático, base de datos y `uploads/` de este WordPress).
   3. Inventariar buzones y subdominios de `caminodeldharma.org` y respaldar lo que
      haga falta. Sin ese inventario no se cambia el dominio.
   4. Liberar `caminodeldharma.org` pasando el sitio estático a un **dominio temporal**.
      Los archivos se conservan: esa URL temporal es la vía de rollback del estático.
   5. En **este** WordPress, **Cambiar dominio** y asignar `caminodeldharma.org`.
   6. Cuando `caminodeldharma.org` ya apunta a **esta** instalación, pasar el interruptor
      de entorno y la indexación. Importación, seed y convert se terminan **antes**,
      mientras el tipo sigue siendo `staging`: en `production` el pipeline exige
      `--confirm-production` y evidencia de backup (ADR 0033).

      ```bash
      wp config set WP_ENVIRONMENT_TYPE production --type=constant
      wp eval 'echo wp_get_environment_type(), PHP_EOL;'   # esperado: production
      wp option update blog_public 1
      ```

   7. Verificar WordPress en la URL definitiva: SSL, `home` / `siteurl`, permalinks,
      redirects, formularios, canonical, `WP_ENVIRONMENT_TYPE=production`,
      `blog_public=1` y el resto del
      [checklist de cutover](../cutover-checklist-wordpress.md).
   8. Solo entonces se considera retirado el estático. No se borra el día del corte.
4. **Un import fallido en staging** se revierte restaurando el backup de **esta** base
   de datos. No se borra el sitio de Hostinger para «empezar de cero» en otro.
5. Este ADR precisa el mecanismo del switch de OWN-005. No cambia el freeze, el noindex
   de staging ni la regla de no pisar el estático **antes** del corte.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| --- | --- |
| Borrar este WordPress y reinstalarlo en el `public_html` del estático el día del corte | Pierde el sitio ya creado y obliga a repetir importación, medios y QA. |
| Crear un segundo WordPress de producción y migrar la base al corte | Dos instalaciones que pueden divergir; el propietario quiere una sola. |
| Hacer el cambio de dominio ahora, en la sesión de staging | El runbook y el propietario dejan el corte para una sesión posterior. El estático sigue en producción. |

## Consecuencias

**Beneficios:**

- Una sola base, unos solos medios y un solo QA. El corte no reinstala WordPress.
- El estático queda en un dominio temporal, con archivos, como rollback.
- El hostname de staging queda versionado a propósito, para que nadie cree otro sitio.

**Riesgos:**

- «Cambiar dominio» puede afectar correo y subdominios. El inventario previo es gate
  del corte, no un detalle opcional.
- Tras el cambio hay que comprobar `home`, `siteurl` y las URLs canónicas: no asumir
  que Hostinger reescribió todo el contenido.
- Mientras el sitio siga en `*.hostingersite.com`, `WP_ENVIRONMENT_TYPE` permanece
  en `staging` y `blog_public` en `0`. Ni el dominio ni la indexación cambian solos:
  en el corte se fijan `production` y `blog_public 1` a propósito.
- Pasar a `production` antes de terminar la importación bloquea `import`, `seed` y
  `convert` salvo `--confirm-production` y backup (ADR 0033).

**Trabajo futuro:**

- Ejecutar el corte solo con el checklist y con autorización expresa del propietario.
- No retirar el estático del dominio temporal hasta evaluar la ventana de rollback.

## Implementación

2026-09-26. El propietario autorizó el corte en una sesión posterior.
Esta sección registra el hecho. No reescribe la decisión de arriba.

- `https://caminodeldharma.org/` sirve este mismo WordPress (7.1.2).
- Raíz: `/home/u548735796/domains/caminodeldharma.org/public_html`.
- `WP_ENVIRONMENT_TYPE` es `production`. `blog_public` es `1`.
- `home` y `siteurl` son `https://caminodeldharma.org`.
- Theme `camino-del-dharma` 0.6.3 (`theme-v0.6.3`, run 36214533651).
- Plugin `camino-del-dharma-core` 0.7.10 (`plugin-v0.7.10`,
  run 36214533614).
- Canonical de la portada: `https://caminodeldharma.org/`.
- Robots de la portada: `index,follow,max-image-preview:large`.
- `robots.txt` anuncia `https://caminodeldharma.org/wp-sitemap.xml`.
- `/sitemap.xml` redirige 301 a `/wp-sitemap.xml` (200).
- En el corte, el `.htaccess` no cambió. Ese SHA256 queda como evidencia
  histórica del corte, no como invariante permanente del archivo entero:
  `a03537dae616a03b9f009c7c8d9979edc273561ac41db187613db9f4ecc88940`
- Tras el cambio de dominio, `/eventos` respondía 404: el CPT estaba
  registrado y las rewrite rules guardadas no tenían rutas `eventos`
  (95 reglas, 0 de eventos). Reparación: `wp rewrite flush`, sin
  `--hard`. Quedaron 143 reglas. Archivo y 10 fichas responden 200.
- El estático sigue en
  [palegreen-cod-365706.hostingersite.com](https://palegreen-cod-365706.hostingersite.com/)
  (HTTP 200, no es WordPress). No se ha purgado. La purga no es parte
  del corte hecho: exige otra autorización del propietario, sin fecha.
- Los backups privados del corte siguen fuera de `public_html`.
- La entrega de correo del formulario sigue sin verificar y el
  formulario está oculto. Ver ADR 0045.

El propietario autorizó después el retiro del estático. `static/` sale
del árbol vigente; el historial de este repositorio lo conserva. La
raíz desplegada
`/home/u548735796/domains/palegreen-cod-365706.hostingersite.com/public_html`
se eliminó. El hostname responde 404 y ya no sirve el sitio
estático. La entrada en hPanel sigue en ese momento. El tar
`static-pre-cutover-20260926-040601.tar.gz`
(`ce9b08ae24716faea907787b8270d06eee687f8216890b19bc532ebb6dc3c107`)
conserva esa copia, incluidos los archivos que no están en Git. La
decisión de arriba no se reescribe. La entrada del sitio palegreen en
hPanel no forma parte de este retiro.

### Reconciliación del `.htaccess` (2026-09-26, posterior al corte)

Revisión forense de solo lectura. No se modificó el archivo en vivo, no se
restauró el backup y no se ejecutó `wp rewrite flush`. El disparador exacto
no está confirmado. La procedencia probable es la regeneración del interior
de `# BEGIN WordPress` … `# END WordPress`. LiteSpeed no escribió este
delta.

Instantánea observada y aceptada:

`c40e7e4440269d24920771e2ee92da32d894efda5a194f48395ae93f7f89013b`

mtime `2026-09-26 17:05:55.249615305 +0000`, 6475 bytes, 176 líneas.
Ruta: `/home/u548735796/domains/caminodeldharma.org/public_html/.htaccess`.

- Las líneas anteriores al marcador de WordPress, incluido el bloque
  LiteSpeed, son idénticas byte a byte a la evidencia del corte.
- WordPress quitó, de dentro de su marcador, el `mod_expires` generado por
  Hostinger. Añadió los comentarios habituales en es_CO y
  `RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]`.
- El front controller quedó igual.
- `/`, `/eventos`, `/blog`, `/robots.txt`, `/wp-sitemap.xml` y las 11 URLs
  del sitemap de eventos respondían 200 en el host canónico. Sin
  redirección a otro hostname.

Política a partir de aquí: el hash del archivo entero es una instantánea,
no el invariante. Una regeneración legítima del interior del marcador de
WordPress puede cambiar ese hash. La verificación separa tres regiones:
reglas propias de Camino, bloque gestionado por LiteSpeed y bloque
gestionado por WordPress. Un cambio no explicado fuera de los bloques
gestionados sigue exigiendo revisión. No se copia el `.htaccess` vivo al
repositorio: el interior del marcador es contenido generado en runtime.
`wordpress/.htaccess` sigue siendo el artefacto de reglas propias; al
copiarlo se conserva el bloque WordPress que el servidor ya tenga.

El archivo vivo conserva, encima de ese marcador, el comentario de staging
que deja desactivado el redirect canónico. Ese texto ya estaba en la
evidencia del corte y no forma parte de este delta. `wordpress/.htaccess`
sí lleva los redirects HTTPS y de host. Esta reconciliación no los activa
en el servidor.

### Retiro de la entrada de sitio palegreen en hPanel

Orden ya registrado arriba: este staging pasó a ser la producción
canónica; después, con otra autorización, se retiró el `public_html`
estático. [PR #47](https://github.com/refo44/demo-caminodeldharma/pull/47)
retiró `static/` de `main`. La revisión forense del `.htaccess` y la
reconciliación de la instantánea
([PR #48](https://github.com/refo44/demo-caminodeldharma/pull/48))
dejaron la producción independiente de palegreen.

El propietario autorizó entonces quitar solo la entrada de sitio
`palegreen-cod-365706.hostingersite.com`. Se hizo en hPanel. No se tocó
`caminodeldharma.org`.

Después, `/home/u548735796/domains/` contiene solo
`caminodeldharma.org`. El directorio palegreen ya no existe. El hostname
puede seguir resolviendo y, en la verificación, respondió 403 Forbidden.
No sirve el sitio estático. No hubo cambio manual de DNS.

La producción canónica siguió sana: rutas públicas en 200, `/sitemap.xml`
en 301 hacia `/wp-sitemap.xml`, las 11 URLs de eventos en 200, checksums
del núcleo correctos y el `.htaccess` en
`c40e7e4440269d24920771e2ee92da32d894efda5a194f48395ae93f7f89013b`.

No queda un sitio estático servido como rollback. La copia histórica
sigue en `282230c41589348722a80985046d16cb07d19a1c` y en el tar ya citado.
Los backups de base de datos, `wp-config`, `.htaccess` y rewrite rules
siguen fuera de `public_html`. Staging sigue retirado. El correo del
formulario sigue diferido. Un tag de componente despliega ese código a
la raíz canónica (ADR 0048). La decisión original de este ADR no se reescribe.

### Plugin 0.7.11

El corte queda registrado con plugin 0.7.10 (`plugin-v0.7.10`,
run 36214533614) y theme 0.6.3. El código posterior añade el cierre
manual y programado de la inscripción
([#52](https://github.com/refo44/demo-caminodeldharma/issues/52)).
Cabecera y `CDD_CORE_VERSION` son `0.7.11`. El tag de esa release, sobre
el commit de `main` después del merge, es `plugin-v0.7.11`. El theme no
cambia. Ese tag no reescribe esta decisión ni el CD de producción (D-B).

### Plugin 0.7.12

El código posterior publica `offers.validFrom` cuando el editor guardó
la apertura de la inscripción
([#54](https://github.com/refo44/demo-caminodeldharma/issues/54)).
Cabecera y `CDD_CORE_VERSION` son `0.7.12`. El tag de esa release, sobre
el commit de `main` después del merge, es `plugin-v0.7.12`. El theme no
cambia.

### Plugin 0.7.13

El código posterior rechaza una zona de `offers.validFrom` fuera de
±23:59. Cabecera y `CDD_CORE_VERSION` son `0.7.13`. El tag de esa
release, sobre el commit de `main` después del merge, es
`plugin-v0.7.13`. El theme no cambia. Ese tag no escribe la raíz de
producción.

## Referencias

- OWN-005, OWN-036
- [cutover-checklist-wordpress.md](../cutover-checklist-wordpress.md)
- [wordpress-manual-deployment.md](../operations/wordpress-manual-deployment.md)
- [How to switch to a temporary domain in Hostinger](https://www.hostinger.com/support/how-to-switch-to-a-temporary-domain-in-hostinger-dashboard/)
- [Cómo conectar un dominio diferente a un sitio existente en Hostinger](https://www.hostinger.com/es/support/6807580-como-conectar-un-dominio-diferente-a-tu-sitio-web-existente-en-hostinger/)
- ADR 0013, ADR 0015, ADR 0032, ADR 0046
