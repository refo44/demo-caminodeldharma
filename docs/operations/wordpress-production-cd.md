# Operaciones — CD de producción WordPress por tag (ADR 0048)

Un tag `theme-v<SemVer>` o `plugin-v<SemVer>` copia solo ese componente al
WordPress de `https://caminodeldharma.org/`. Fusionar a `main` no despliega.

| | |
| --- | --- |
| **Workflow** | [`.github/workflows/deploy-production.yml`](../../.github/workflows/deploy-production.yml) |
| **Guardia** | [`tools/release/check-production-target.sh`](../../tools/release/check-production-target.sh) |
| **Destino** | `/home/u548735796/domains/caminodeldharma.org/public_html` |
| **URL** | `https://caminodeldharma.org` |
| **Entorno GitHub** | `production` |

## 1. Qué escribe

Solo `wp-content/themes/camino-del-dharma` o
`wp-content/plugins/camino-del-dharma-core`, el que nombre el tag. El
`.htaccess` de la raíz se registra antes y debe seguir igual después. No se
copian `uploads`, `wp-config.php`, el núcleo ni otro plugin.

## 2. Qué tiene que cumplirse en el servidor

- El directorio de arriba existe y su `pwd -P` es esa misma ruta.
- `wp_get_environment_type()` es `production`.
- `home` es `https://caminodeldharma.org`.
- WordPress está instalado en esa raíz.

Si algo de eso falla, el job se detiene antes de `rsync`.

## 3. Secretos y variables del entorno `production`

| Nombre | Tipo |
| --- | --- |
| `PRODUCTION_SSH_HOST` | variable |
| `PRODUCTION_SSH_PORT` | variable |
| `PRODUCTION_SSH_USER` | variable |
| `PRODUCTION_WP_ROOT` | variable, igual a la raíz de arriba |
| `PRODUCTION_SSH_PRIVATE_KEY` | secreto |
| `PRODUCTION_SSH_KNOWN_HOSTS` | secreto, con la entrada `[host]:puerto` |

No se rellenan copiando los nombres `STAGING_*` encima de la ruta canónica.

## 4. Un tag que ya se publicó

El push de un tag usa el workflow que está dentro de ese commit.
`plugin-v0.7.13` quedó con el workflow de staging. Cuando este archivo ya
está en `main`, Actions → Deploy production → Run workflow, input
`plugin-v0.7.13`, despliega los archivos de ese tag con las guardias de
`main`.

## 5. Si el job falla

Antes de `rsync`, el servidor no cambió. Después de `rsync`, el resumen del
job tiene el SHA256 del artefacto. No se mueve ni se reutiliza el tag. La
corrección es un tag de versión nuevo.
