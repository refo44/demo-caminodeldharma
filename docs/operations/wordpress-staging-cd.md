# Operaciones — CD de staging WordPress por tag (ADR 0046)

Runbook del canal de código a **staging** (no producción). La decisión vive en
[ADR 0046](../adr/0046-despliegue-solo-por-tag-de-version-aprobado.md) (aceptado); este documento solo
explica cómo se opera. Si difieren, manda el ADR.

| | |
| --- | --- |
| **Workflow** | [`.github/workflows/deploy-staging.yml`](../../.github/workflows/deploy-staging.yml) |
| **Scripts** | [`tools/release/`](../../tools/release/) (probados en `tests/Unit/Release_ResolutionTest.php`) |
| **Destino** | `https://teal-woodpecker-284165.hostingersite.com` (SSH `u548735796`, puerto `65002`) |
| **Fuera de alcance** | Producción, contenido, D-B (deferido) |

## 1. Modelo: MERGE ≠ RELEASE

Fusionar a `main` **no** despliega nada. Solo un tag `theme-v<SemVer>` o
`plugin-v<SemVer>` arranca el workflow. Theme y plugin son unidades independientes.

| Evento | ¿Despliega? |
| --- | --- |
| push a `main`, pull request, `workflow_dispatch`, tag `v*` | No |
| tag `theme-v<SemVer>` | Sí, solo el theme |
| tag `plugin-v<SemVer>` | Sí, solo el plugin |

Los tags `v*` son del sitio estático (ADR 0015) y nunca disparan este workflow.

## 2. Roles

- **Release Maintainer:** única persona autorizada a crear tags `theme-v*`/`plugin-v*`
  (Ruleset A). Lo designa el propietario de forma explícita.
- **Actions:** ejecuta el workflow; **no** crea tags ni figura en ningún bypass.
- Nadie puede mover ni borrar un tag publicado (Ruleset B, bypass vacío). Una
  corrección es un tag nuevo con versión nueva.

## 3. Cómo se prepara un release (sin ejecutarlo)

1. Subir la versión del componente en su PR (theme: `style.css`; plugin: cabecera y
   `CDD_CORE_VERSION`, que deben coincidir) y fusionar a `main` con `php` y `css` verdes.
2. El Release Maintainer decide explícitamente el release y crea el tag anotado sobre
   un commit ya presente en `origin/main`, con la versión **idéntica** a la del componente:

   ```bash
   git fetch origin main
   git tag -a plugin-vX.Y.Z <sha-en-origin/main> -m "Release plugin X.Y.Z to WordPress staging"
   git push origin plugin-vX.Y.Z
   ```

   Para el theme, el nombre es `theme-vX.Y.Z` y el mensaje nombra el theme. El SHA tiene
   que ser ancestro de `origin/main`. Empujar el tag es el único disparo.
3. El workflow corre solo. No hay disparo manual.

## 4. Compuertas (fallan cerradas)

1. **Validación de tag** (`resolve-release.sh`): SemVer estricto, sin prerrelease ni
   metadata de build, sin ceros a la izquierda; un solo componente por namespace; versión
   del tag == versión del componente.
2. **Ancestría:** `main` se obtiene explícitamente (`git fetch --no-tags origin main:refs/remotes/origin/main`),
   se verifica y el commit etiquetado debe ser ancestro de `origin/main` (contenido en su
   historia, no necesariamente su punta). El «commit etiquetado» es lo que resuelve
   `refs/tags/<tag>^{commit}`: un tag anotado se pela a su commit y uno ligero ya lo es.
3. **Sin symlinks:** `check-no-symlinks.sh` lee los metadatos del árbol Git del commit exacto y
   rechaza cualquier entrada de modo `120000` dentro del componente elegido (los de fuera no
   cuentan). Falla antes del artefacto, de SSH y de rsync.
4. **Calidad:** los jobs `php` y `css` de `test.yml` se repiten sobre el SHA exacto
   (`tests/Unit/Staging_Deploy_WorkflowTest.php` evita que las copias diverjan).
5. **Entorno `staging`** con política de despliegue solo para tags `theme-v*` y `plugin-v*`.

## 5. Artefacto y checksum

Se construye solo el directorio del componente con `git archive` del SHA etiquetado. El
resumen del job registra tag, SHA y SHA256 del tarball. Este flujo **no** decide D-B
(qué es «el artefacto de producción»); queda diferido.

## 6. Preflight remoto (solo lectura)

Antes de cualquier escritura se comprueba: `STAGING_WP_ROOT` igual al contrato de
staging (`check-staging-target.sh`); no es el `public_html` de producción; WordPress
instalado; `wp_get_environment_type()` == `staging`; `home` == URL de staging; ABSPATH
== raíz configurada; el destino cuelga de `wp-content`. Se registra el SHA256 del
`.htaccess` raíz.

## 7. Transporte

`rsync --recursive --times --delete` (sin `--links`: no hay symlinks que copiar) **solo** del directorio del componente
hacia su directorio remoto. `--delete` no sale de ese directorio. Nunca se toca el
`.htaccess` raíz, `wp-config.php`, `uploads` ni el core de WordPress. SSH usa
`StrictHostKeyChecking yes` con `known_hosts` dedicado; nunca se desactiva la verificación.

No se ejecuta `migrate`, `seed`, `convert`, `demo purge`, aprovisionamiento de CF7 ni
`--confirm-production`.

## 7b. Purga de caché de páginas

Tras el rsync, el workflow ejecuta `wp litespeed-purge all` por SSH (el plugin LiteSpeed Cache de
staging debe estar activo; el tema queda omitido con `--skip-themes`). Sin esa purga LiteSpeed sirve
HTML en caché que apunta al `?ver=` anterior de `main.css`, y como el CSS se cachea 7 días
(`max-age=604800`) los visitantes conservan el estilo viejo (visto en el release `theme-v0.5.5`).
Si la purga falla, el job **avisa** (`::warning::`) y no falla: el código ya está en staging. En ese caso,
purgar a mano en hPanel (LiteSpeed Cache → «Purge all») y comprobar la página.

## 8. Verificación posterior

Versión instalada == versión del tag; entorno sigue en `staging`; `.htaccess` raíz
sin cambios; estado activo (aviso); HTTP 200–399 en `/`.

## 9. Configuración en GitHub

**Environment `staging`**, ya configurado. Solo admite tags `theme-v*` y `plugin-v*`.
No existe un Environment `production`. Un tag de componente autoriza **staging**, no
producción. Fusionar a `main` no despliega ningún entorno.

Variables (no son secretos):

| Nombre | Valor |
| --- | --- |
| `STAGING_SSH_HOST` | `82.29.157.100` |
| `STAGING_SSH_PORT` | `65002` |
| `STAGING_SSH_USER` | `u548735796` |
| `STAGING_WP_ROOT` | `/home/u548735796/domains/teal-woodpecker-284165.hostingersite.com/public_html` |

Secretos del mismo entorno, solo nombres: `STAGING_SSH_PRIVATE_KEY` (clave dedicada de
Actions, no una clave personal) y `STAGING_SSH_KNOWN_HOSTS` (huella del host comprobada
fuera de banda antes de guardarla). La clave privada y `known_hosts` no van en Git.
`StrictHostKeyChecking` permanece en `yes`. `StrictHostKeyChecking=no` no se usa.

Si falta una variable o un secreto, el workflow falla cerrado y no escribe.

**Rulesets de tags** (patrones `theme-v*`, `plugin-v*`, `v*`):

- **A — creación:** restringe la creación; bypass solo para el Release Maintainer
  autorizado, nunca Actions. En un repo de usuario el bypass se define por rol, no por
  persona: elegirlo es decisión explícita del propietario.
- **B — inmutabilidad:** restringe update, delete y force push; lista de bypass **vacía**.

## 10. Inspeccionar un fallo

Pestaña *Actions* → run del tag → job que falló. `validate`/`php`/`css` fallidos: no se
escribió nada. `deploy` fallido antes de `rsync`: nada cambió en remoto. Después de
`rsync`: revisar el resumen del job y repetir con un tag de versión nueva; no se
reutilizan ni mueven tags.

## 11. Producción: límite, no un cambio de variables

Hoy producción es el estático `https://caminodeldharma.org`, document root
`/home/u548735796/domains/caminodeldharma.org/public_html`. Este workflow no lo escribe.
El ZIP estático sigue en ADR 0015. El corte de dominio es otra sesión (ADR 0047): no es
desplegar un theme o un plugin.

**No** se prepara producción sustituyendo los valores `STAGING_*` ni apuntando el
entorno `staging` al sitio público. Aunque el día del corte el servidor, la cuenta o
incluso el usuario SSH coincidieran, los dos destinos siguen siendo entornos lógicos
distintos.

Diseño futuro, **no implementado** y sin valores inventados:

| Entorno `staging` (existe) | Entorno `production` (no existe) |
| --- | --- |
| `STAGING_SSH_HOST` | `PRODUCTION_SSH_HOST` |
| `STAGING_SSH_PORT` | `PRODUCTION_SSH_PORT` |
| `STAGING_SSH_USER` | `PRODUCTION_SSH_USER` |
| `STAGING_WP_ROOT` | `PRODUCTION_WP_ROOT` |
| `STAGING_SSH_PRIVATE_KEY` | `PRODUCTION_SSH_PRIVATE_KEY` |
| `STAGING_SSH_KNOWN_HOSTS` | `PRODUCTION_SSH_KNOWN_HOSTS` |

Host, puerto, usuario y ruta, cuando se conozcan, serán variables. La clave privada y
`known_hosts` serán secretos de ese entorno, nunca archivos del repositorio. No se
asumen iguales a staging hasta verificarlos en el corte. La huella del host de
producción se comprueba fuera de banda antes de guardarla. `StrictHostKeyChecking=yes`.
Un workflow de producción incompleto falla cerrado, antes de cualquier escritura. El
`.htaccess` de producción no se sustituye a ciegas. Migración, seed, convert y
`--confirm-production` no forman parte del deploy de código; `--confirm-production`
sigue prohibido salvo que un procedimiento de producción ya aprobado lo exija.

El flujo previsto, todavía sin construir, es: un release ya validado en staging, luego
una autorización explícita de corte, luego un mecanismo propio de producción, el
entorno `production`, un preflight, el deploy del componente y del SHA ya aprobados, y
la verificación posterior. **Qué dispara ese mecanismo** —el mismo tag, un workflow de
promoción, otro namespace u otra aprobación— no está decidido. Hace falta un ADR antes
de implementar CD de producción (D-B sigue diferida).

## 12. Primer release de staging

`theme-v0.5.3` fue el primer release tag-gated que llegó a staging con éxito
(2026-09-24).

| | |
| --- | --- |
| Commit | `ca351064f3697a764f9ca62285054809906cdd8b` |
| Run | [36029070539](https://github.com/refo44/demo-caminodeldharma/actions/runs/36029070539) |
| Resultado | SUCCESS |
| Componente | theme `0.5.3` |
| Entorno | `staging` |
| Producción | no se tocó |

Ese run comprobó `wp` en el SSH no interactivo, `home` igual a la URL de staging,
`known_hosts` con puerto, preflight, rsync solo del theme, `.htaccess` raíz intacto y
HTTP 200. GitHub sigue conservando una sola ejecución pendiente por grupo de
concurrencia: un tag a la vez.

### Sonda HTTP y 403 del runner

Hostinger responde 403 a las IP de los runners de GitHub aunque el sitio responda 200 desde otras
redes (visto en `theme-v0.5.5`). La versión instalada, el entorno y el `.htaccess` ya se verifican
por SSH, así que un 403 en la sonda `/` es un **aviso**; 4xx/5xx distintos de 403 y los tiempos
agotados siguen fallando el despliegue.

