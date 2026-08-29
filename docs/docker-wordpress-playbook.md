# Playbook — Entorno local de WordPress con Docker

Referencia operativa de **este** proyecto para cuando se implemente ADR 0023. Complementa la
decisión; no la sustituye.

**CURRENT STATE:** no hay `docker-compose.yml` en el repo. No ejecutar ni desplegar nada desde este
documento.

Environments de este repo: **LOCAL** (este playbook), **STAGING** (Hostinger, hostname no versionado),
**PRODUCTION** (`https://caminodeldharma.org`, hoy estático). No mezclar credenciales, BD, uploads,
fixtures ni indexación.

Nota histórica: el propietario aportó gotchas de Docker de otro sitio WordPress (2026-07-31). Las
versiones PHP/MariaDB y los nombres de theme/plugin de **abajo** son de Camino del Dharma / Hostinger.

---

## 1. El problema que resuelve

Sin PHP/WP-CLI/WordPress locales, toda la QA de runtime (activación, comandos CLI, render de
plantillas, cookies) queda `Unverified` y rehén del staging. Instalar el stack nativo
(Homebrew/MAMP/Local) contamina la máquina, no es reproducible ni versionable.

**Aprendizaje central:** un `docker-compose.yml` de tres servicios convierte «sin runtime» en «runtime
completo, reproducible y desechable» en minutos, sin tocar la máquina, y el archivo queda versionado
junto al código que valida.

## 2. Arquitectura mínima (3 servicios)

```text
db         → mariadb:11.8           (volumen db_data; healthcheck)
wordpress  → wordpress:php8.3       (Apache; volumen wp_data; puerto local)
wpcli      → wordpress:cli-php8.3   (mismos mounts y DB; para wp <cmd>)
```

Versiones confirmadas en el hPanel de Hostinger (2026-08-01): **PHP 8.3.30** (vía Información de PHP,
`Avanzado`) y **MariaDB 11.8.8-MariaDB-log** (vía `SELECT VERSION();` en phpMyAdmin, base temporal
`u548735796_version_check`, borrada tras la comprobación). Prerrequisito de ADR 0023 cerrado.

Reglas que demostraron su valor en el proyecto de origen:

1. **Bind-mount solo del código propio.** El core de WordPress y la base de datos viven en volúmenes
   Docker; del repositorio se montan únicamente el theme (y el plugin propio, si llega a existir):

   ```yaml
   volumes:
     - wp_data:/var/www/html
     - ./wordpress/wp-content/themes/camino-del-dharma:/var/www/html/wp-content/themes/camino-del-dharma
     # si se crea un plugin propio (docs/17 Fase 3 paso 2, "si aplica"):
     # - ./wordpress/wp-content/plugins/camino-del-dharma-core:/var/www/html/wp-content/plugins/camino-del-dharma-core
   ```

   Beneficio doble: lo que se edita en Git es exactamente lo que corre (sin copias), y el alcance de
   los mounts **replica el alcance del despliegue** — coherente con `docs/13-static-file-structure.md`
   y con la regla de "no versionar core/BD/uploads" ya establecida en `docs/17-orden-implementacion.md`.

2. **Servicio `wpcli` separado**, con la imagen oficial `wordpress:cli`, los mismos mounts y la misma
   configuración de entorno que el servicio web. Uso: `docker compose run --rm wpcli wp <comando>`. Es
   la puerta de entrada de toda la QA scriptable.

3. **Healthcheck en la base de datos + `depends_on: condition: service_healthy`** en los otros dos
   servicios. Sin esto, el primer arranque sufre condiciones de carrera contra la inicialización de
   MariaDB/InnoDB:

   ```yaml
   healthcheck:
     test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
     interval: 5s
     timeout: 5s
     retries: 12
   ```

4. **`name:` explícito** en el compose (p. ej. `camino-del-dharma`) — nombre de proyecto estable,
   volúmenes predecibles entre sesiones.

## 3. Gotchas descubiertas (los que cuestan horas)

1. **`WORDPRESS_CONFIG_EXTRA` se evalúa en tiempo de ejecución y por contenedor.** La imagen oficial
   genera un `wp-config.php` que lee esa variable del entorno del proceso. Si defines
   `WP_ENVIRONMENT_TYPE=local` solo en el servicio `wordpress`, el servicio `wpcli` seguirá reportando
   `production` en `wp_get_environment_type()`. Duplicar `WORDPRESS_CONFIG_EXTRA` (al menos
   `WP_ENVIRONMENT_TYPE`) en **todos** los servicios que ejecutan PHP. Si el theme/plugin propio llega
   a tener guards por entorno, el primer rechazo del CLI por variable no duplicada es una prueba
   gratuita de que el guard funciona — documentarlo como evidencia antes de corregir la variable.

2. **UID/GID del CLI.** Ejecutar `wpcli` como `user: "33:33"` (www-data de la imagen Apache) evita
   desorden de permisos entre archivos creados por el contenedor web (uploads, `.htaccess`) y los
   creados por el CLI.

3. **Credenciales fuera del compose.** `.env` (gitignored) + `.env.example` versionado como plantilla,
   y sintaxis `${VAR:?mensaje}` para que el compose falle rápido y con mensaje claro si el `.env` no
   existe:

   ```yaml
   MARIADB_PASSWORD: ${MARIADB_PASSWORD:?define MARIADB_PASSWORD en .env}
   ```

   El usuario admin de WordPress vive solo en el volumen local, nunca en Git; el servicio escucha solo
   en `localhost`.

4. **Declarar el entorno y activar el log de debug desde el día uno:**

   ```yaml
   WORDPRESS_CONFIG_EXTRA: |
     define( 'WP_ENVIRONMENT_TYPE', 'local' );
     define( 'WP_DEBUG', true );
     define( 'WP_DEBUG_LOG', true );
     define( 'WP_DEBUG_DISPLAY', false );
   ```

   Un `debug.log` vacío tras activar el theme y navegar las pantallas es evidencia barata y objetiva
   de «sin warnings ni fatales».

5. **Puerto parametrizado con default** (`"${WORDPRESS_PORT:-8080}:80"`) para convivir con otros
   proyectos dockerizados en la misma máquina.

6. **Fijar versiones de imagen que imiten al hosting** (`wordpress:php8.3` + `mariadb:11.8`, nunca
   `latest`): la paridad de versión PHP/MariaDB con Hostinger es lo que da valor probatorio a `php -l`
   y al resto de la QA local. Versiones reales confirmadas 2026-08-01 (ver §2).

## 4. Método: qué QA ejecutar en cuanto el entorno levanta

Secuencia repetible, cada paso deja evidencia registrable:

1. **Sintaxis:** `php -l` sobre todos los `.php` propios del theme vía `wpcli`.
2. **Activación:** `wp theme activate` (+ `wp plugin activate` si existe plugin propio) + `debug.log`
   vacío.
3. **Permalinks + jerarquía:** fijar estructura de permalinks y `curl` de las URLs canónicas
   (`docs/11-arbol-urls-final.md`) esperando 200. La URL **pública** de este proyecto **no lleva barra
   final** (ADR 0008). Un ejemplo tipo `/%postname%/` de WordPress no es prueba de que el incoming
   route coincida con la política canónica; hay que verificar HTTP en la forma sin barra. `get_permalink()`
   solo no basta (ADR 0032).
4. **Comandos propios, en escalera** (WP-CLI en `camino-del-dharma-core`, CPT `event`; `sangha` fuera
   del alcance inicial): dry-run → `--apply` → re-ejecución, idempotencia (ADR 0033).
5. **Ciclo de vida de fixtures** (no contenido real): teardown → seed → verify → reseed (sin
   duplicados) → teardown (sin huérfanos) → teardown de nuevo (no-op). Nunca teardown genérico contra
   Pages institucionales (ADR 0033).
6. **Invariantes de front:** `curl -I` buscando ausencia de `Set-Cookie` de analítica (ADR 0019);
   grep del HTML renderizado buscando recursos de hosts externos no aprobados.
7. **Plugins de terceros aprobados** (p. ej. Contact Form 7, TASK-0003): instalar con
   `wp plugin install` y verificar comportamiento observable, no solo el declarado.

**Regla de honestidad que conviene copiar:** distinguir en la matriz de validación un estado
`Pass (local)` de `Pass`. La evidencia local **no sustituye** al staging para lo que depende del
hosting real (versión PHP real, `.htaccess`/Apache real, HTTPS, cabeceras, correo — Contact Form 7
entregando de verdad a caminodeldharma1@gmail.com se valida en staging, no solo en local).

## 5. Límites deliberados (qué NO hace este entorno)

- **No participa en ningún despliegue.** Producción/staging siguen siendo despliegue manual
  (ADR 0015); Docker es solo banco de pruebas, no aparece en el procedimiento de publicación.
- **No sustituye el gate de staging** de `docs/17-orden-implementacion.md` § Transición (Fase 2.5
  sobre el theme, Hito 2 del calendario de auditorías).
- **No versiona estado:** `docker compose down -v` lo destruye todo; cada máquina re-ejecuta
  importación/seed. Exige que la carga de datos (CPT `sangha`, `event`) sea scriptable e idempotente.
- **Kubernetes sobra** para un stack de 3 servicios — Compose es suficiente.

## 6. Checklist portable (para cuando se implemente)

- [ ] `docker-compose.yml` en la raíz con `name:`, 3 servicios (db con healthcheck, wordpress, wpcli)
      y versiones de imagen fijadas a la paridad de Hostinger: PHP 8.3, MariaDB 11.8 (confirmadas
      2026-08-01).
- [ ] Bind-mounts SOLO del theme `camino-del-dharma` (y plugin propio si se crea); core y BD en
      volúmenes.
- [ ] `WORDPRESS_CONFIG_EXTRA` con `WP_ENVIRONMENT_TYPE` + debug log, **duplicado en `wpcli`**.
- [ ] `wpcli` con `user: "33:33"`.
- [ ] `.env` gitignored + `.env.example` versionado + `${VAR:?…}`.
- [ ] Puerto parametrizado con default.
- [ ] Comentario de uso en cabecera del compose (up, run wpcli, URL, admin).
- [ ] Primera sesión de QA en escalera (§4) con resultados registrados en `migracion-static-wordpress.md`
      o equivalente.
- [ ] Kit de pruebas (ADR 0038) el mismo día que exista PHP propio: `composer test` +
      `composer test:wp`. Harnesses de nivel 3 usan un compose **efímero** (`-p cdd-qa-<slice>`,
      `down -v`), no este volumen de desarrollo.

## 7. Referencias

- ADR [0023](adr/0023-entorno-local-wordpress-docker.md) — la decisión y sus alternativas
- ADR [0032](adr/0032-contrato-migracion-static-wordpress.md), [0033](adr/0033-importador-contenido-vs-fixtures.md)
- ADR [0038](adr/0038-pruebas-tdd-phpunit-sonar.md) y `docs/guia-pruebas-plugin-theme-fse.md`
- `docs/17-orden-implementacion.md` § Transición estático → WordPress
- `.audit/audit-schedule.md` — Hito 2
- `docs/13-static-file-structure.md`, ADR [0014](adr/0014-monorepo-static-wordpress.md)
- TASK-0003 (`.audit/implementation/tasks/TASK-0003.md`) — Contact Form 7
