# Playbook — Entorno local de WordPress con Docker

Aplicación al proyecto Camino del Dharma de un playbook portable, aportado por el propietario y
destilado en otro proyecto WordPress con la misma forma (código propio versionado en Git, hosting
compartido sin contenedores en producción, necesidad de QA real sin instalar toolchain global en la
máquina). Complementa a **ADR 0023** (la decisión y sus alternativas); este documento es la referencia
operativa para cuando se implemente.

**No se ejecuta en esta sesión** — solo se deja listo, según ADR 0023 (dockerizar es tarea separada de
implementar el theme).

**Origen:** aprendizajes de la Revista de Filosofía LOGO ET SPES (Fase 3, WordPress), 2026-07-31.

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
db         → mariadb:11            (volumen db_data; healthcheck)
wordpress  → wordpress:X.Y-phpZ    (Apache; volumen wp_data; puerto local)
wpcli      → wordpress:cli-phpZ    (mismos mounts y DB; para wp <cmd>)
```

`X.Y-phpZ` se fija según la versión real de PHP/MySQL de Hostinger (pendiente de confirmar en el
hPanel — mismo prerrequisito ya registrado en ADR 0023).

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

6. **Fijar versiones de imagen que imiten al hosting** (p. ej. `wordpress:6.8-php8.2`, nunca `latest`):
   la paridad de versión PHP con Hostinger es lo que da valor probatorio a `php -l` y al resto de la QA
   local. Confirmar la versión real antes de fijar el tag (prerrequisito de ADR 0023).

## 4. Método: qué QA ejecutar en cuanto el entorno levanta

Secuencia repetible, cada paso deja evidencia registrable:

1. **Sintaxis:** `php -l` sobre todos los `.php` propios del theme vía `wpcli`.
2. **Activación:** `wp theme activate` (+ `wp plugin activate` si existe plugin propio) + `debug.log`
   vacío.
3. **Permalinks + jerarquía:** `wp rewrite structure '/%postname%/'` y `curl` de las 14 URLs canónicas
   (`docs/11-arbol-urls-final.md`) esperando 200.
4. **Comandos propios, en escalera** (si se crean comandos WP-CLI para el CPT `sangha`/`event`):
   dry-run → `--apply` → re-ejecución, probando idempotencia.
5. **Ciclo de vida de datos demo:** teardown → seed → verify → reseed (sin duplicados) → teardown (sin
   huérfanos) → teardown de nuevo (no-op seguro).
6. **Invariantes de front:** `curl -I` buscando ausencia de `Set-Cookie` (coherente con ADR 0019 —
   sin cookies); grep del HTML renderizado buscando recursos de hosts externos.
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
      y versiones de imagen fijadas a la paridad de Hostinger (confirmar antes en hPanel).
- [ ] Bind-mounts SOLO del theme `camino-del-dharma` (y plugin propio si se crea); core y BD en
      volúmenes.
- [ ] `WORDPRESS_CONFIG_EXTRA` con `WP_ENVIRONMENT_TYPE` + debug log, **duplicado en `wpcli`**.
- [ ] `wpcli` con `user: "33:33"`.
- [ ] `.env` gitignored + `.env.example` versionado + `${VAR:?…}`.
- [ ] Puerto parametrizado con default.
- [ ] Comentario de uso en cabecera del compose (up, run wpcli, URL, admin).
- [ ] Primera sesión de QA en escalera (§4) con resultados registrados en `migracion-static-wordpress.md`
      o equivalente.

## 7. Referencias

- ADR [0023](adr/0023-entorno-local-wordpress-docker.md) — la decisión y sus alternativas
- `docs/17-orden-implementacion.md` § Transición estático → WordPress
- `.audit/audit-schedule.md` — Hito 2
- `docs/13-static-file-structure.md`, ADR [0014](adr/0014-monorepo-static-wordpress.md)
- TASK-0003 (`.audit/implementation/tasks/TASK-0003.md`) — Contact Form 7, primer plugin de terceros a
  validar con este entorno
