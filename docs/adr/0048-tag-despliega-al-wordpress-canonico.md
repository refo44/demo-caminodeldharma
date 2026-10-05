# ADR 0048: Un tag de componente despliega a producción

## Estado

Aceptada el 2026-10-05. Cierra D-B de
[ADR 0046](0046-despliegue-solo-por-tag-de-version-aprobado.md). No reescribe
el contrato de staging que ese ADR ya registró.

## Fecha

2026-10-05

## Contexto

El 2026-09-26 el WordPress que vivía en
`teal-woodpecker-284165.hostingersite.com` pasó a
`https://caminodeldharma.org/`
([ADR 0047](0047-staging-hostinger-es-el-wordpress-de-produccion.md)).
La carpeta del dominio temporal ya no está en el disco. Un tag `plugin-v*`
o `theme-v*` seguía arrancando `deploy-staging.yml`, el preflight encontraba
la raíz ausente y se detenía antes de copiar archivos.

El propietario pidió que ese tag publique el componente en el WordPress
canónico. Sustituir las variables `STAGING_*` por la ruta de producción
queda rechazado: el entorno, los nombres y la comprobación son propios de
producción.

## Decisión

1. Un tag `theme-v<SemVer>` o `plugin-v<SemVer>` que ya está en la historia
   de `main` despliega solo ese componente a
   `/home/u548735796/domains/caminodeldharma.org/public_html`.
2. El workflow es `.github/workflows/deploy-production.yml`. El entorno de
   GitHub se llama `production`. Las variables y los secretos usan el
   prefijo `PRODUCTION_`.
3. `tools/release/check-production-target.sh` acepta únicamente esa raíz y
   uno de los dos directorios de primer partido. Cualquier otra ruta falla
   antes de SSH.
4. El preflight remoto exige que `pwd -P` sea esa raíz, que
   `WP_ENVIRONMENT_TYPE` sea `production` y que `home` sea
   `https://caminodeldharma.org`. Después de la copia, el `.htaccess` de la
   raíz debe conservar su SHA256.
5. La copia es `rsync` del directorio del componente, con `--delete` limitado
   a ese directorio. No se toca contenido, `uploads`, `wp-config.php` ni el
   núcleo. No hay `migrate`, `seed`, `demo` ni `contact`.
6. `workflow_dispatch` solo repite un tag de componente que ya pasó las
   mismas comprobaciones. Sirve para un tag publicado antes de que este
   workflow existiera en su commit. No despliega una rama.
7. `deploy-staging.yml` deja de existir. Un tag ya no arranca ese canal.
   `check-staging-target.sh` sigue en el repositorio como guardia del
   destino retirado y el workflow de producción no lo llama.

## Consecuencias

- El tag `plugin-v0.7.13` ya se ejecutó con el workflow de su propio commit.
  Para copiar esa versión hace falta un `workflow_dispatch` de este archivo,
  después de que esté en `main`, con el input `plugin-v0.7.13`.
- Los secretos SSH viven en el entorno `production`. No se leen desde el
  entorno `staging`.
- Un tag futuro, creado cuando este workflow ya está en el commit etiquetado,
  despliega solo con el push del tag.

## Referencias

- ADR 0046, ADR 0047
- [wordpress-production-cd.md](../operations/wordpress-production-cd.md)
