# ADR 0046: El WordPress de staging es el de producción

## Estado

Aceptada

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

## Referencias

- OWN-005, OWN-036
- [cutover-checklist-wordpress.md](../cutover-checklist-wordpress.md)
- [wordpress-manual-deployment.md](../operations/wordpress-manual-deployment.md)
- [How to switch to a temporary domain in Hostinger](https://www.hostinger.com/support/how-to-switch-to-a-temporary-domain-in-hostinger-dashboard/)
- [Cómo conectar un dominio diferente a un sitio existente en Hostinger](https://www.hostinger.com/es/support/6807580-como-conectar-un-dominio-diferente-a-tu-sitio-web-existente-en-hostinger/)
- ADR 0013, ADR 0015, ADR 0032
