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
   un commit ya presente en `origin/main`, con la versión **idéntica** a la del componente.
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

## 8. Verificación posterior

Versión instalada == versión del tag; entorno sigue en `staging`; `.htaccess` raíz
sin cambios; estado activo (aviso); HTTP 200–399 en `/`.

## 9. Configuración en GitHub

**Environment `staging`** (no existe `production`): tags `theme-v*` y `plugin-v*` únicamente.

Variables (`Settings → Environments → staging → Variables`):

| Nombre | Valor esperado |
| --- | --- |
| `STAGING_SSH_HOST` | host SSH de Hostinger |
| `STAGING_SSH_PORT` | `65002` |
| `STAGING_SSH_USER` | `u548735796` |
| `STAGING_WP_ROOT` | `/home/u548735796/domains/teal-woodpecker-284165.hostingersite.com/public_html` |

Secretos (solo nombres aquí; los valores los carga el propietario):
`STAGING_SSH_PRIVATE_KEY` (clave dedicada, solo staging) y `STAGING_SSH_KNOWN_HOSTS`
(salida de `ssh-keyscan -p 65002 <host>` verificada fuera de banda).

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

## 11. Producción

Excluida por diseño: no hay entorno, secretos ni rutas de producción en este workflow.
Producción sigue por ZIP manual desde `static/` (ADR 0015) y el corte WordPress tiene su
propio checklist.

## 12. Riesgos aún sin comprobar (requieren un tag real)

- `wp` disponible en el PATH SSH no interactivo de Hostinger.
- `home` coincide exactamente con la URL de staging.
- Formato de `known_hosts` con puerto (`[host]:65002`).
- Staging responde 2xx/3xx en `/`.
- Concurrencia: GitHub conserva solo una ejecución pendiente por grupo.
