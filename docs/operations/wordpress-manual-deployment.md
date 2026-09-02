# Operaciones — Despliegue manual WordPress (acotado)

Runbook durable (OWN-034: ya no hay prompt FABLE5). **Ningún despliegue está autorizado por este
documento**: cada escritura externa (staging u hosting) requiere autorización expresa del
propietario en la sesión vigente (OWN-005, ADR 0015).

| | |
| --- | --- |
| **Versión** | 2.1 |
| **Fecha** | 2026-09-01 |
| **Estado** | Vigente — staging **no** se crea hasta D-02/D-03/D-04 en `main` (OWN-035) más «go» del propietario |

## Alcance

- **Qué se despliega a WordPress:** únicamente código first-party versionado:
  `wordpress/wp-content/themes/camino-del-dharma/`,
  `wordpress/wp-content/plugins/camino-del-dharma-core/` y, desde WU-08B,
  `wordpress/.htaccess` al document root.
- **Qué nunca se despliega desde este repo:** core de WordPress, `wp-config.php`,
  credenciales, base de datos, `uploads/`, `docs/`, `scripts/`, `tests/`, `static/`
  sobre un document root WordPress (ADR 0013, ADR 0032).
- **Contact Form 7 no se despliega desde Git.** El repositorio posee la *definición* del
  formulario, no el código del plugin (WU-09). CF7 se instala desde WordPress.org en cada
  entorno y su versión se anota en `docs/operations/third-party-plugins.md`.
- **Destino durante la transición:** instancia Hostinger **separada, sin dominio custom,
  no indexable** (OWN-005). Nunca el `public_html` de producción estática antes del corte.

---

## 1. Qué se sube (código) y qué se importa (contenido)

Son dos canales distintos y no deben mezclarse:

| | Canal | Contenido |
| --- | --- | --- |
| **Código** | ZIP / File Manager / SFTP | theme, plugin, `.htaccess` |
| **Contenido** | WP-CLI sobre `migration/payload.json` | páginas, eventos, entradas, fichas de autor, álbumes, medios, meta de compartir y SEO |

**El contenido no viaja en un ZIP.** Se importa con el pipeline WP-CLI descrito en §4, que
lee `migration/payload.json` y toma los archivos reales de `static/` como *source-root*.
Por eso el repositorio (o al menos `migration/payload.json` + `static/`) debe estar
disponible en el servidor, o el pipeline se ejecuta desde una copia local con `--ssh`.

---

## 2. Provisión del entorno de staging (antes de subir nada)

**Gate OWN-035:** no crear esta instancia hasta que D-02, D-03 y D-04 estén en `main`
([#10](https://github.com/refo44/demo-caminodeldharma/issues/10)–[#12](https://github.com/refo44/demo-caminodeldharma/issues/12))
y el propietario diga **go** en sesión.

**Seed (OWN-032):** el payload y `static/` viven en SSH, directorio privado `~/cdd-extract/`
**fuera** de `public_html`. File Manager no es el fallback automático. Si SSH queda bloqueado,
reabrir OWN-032 (opción B: `wp --ssh` desde el portátil). Staging **nunca** usa
`--confirm-production`. Probar dry-run → `--apply` → segundo `--apply` = 0 created.

1. Crear la instancia Hostinger separada, **sin dominio custom** (subdominio
   `*.hostingersite.com`).
2. Instalar WordPress limpio. **Requisito duro: el sitio debe partir vacío.**
   Si el instalador dejó contenido de demostración, borrarlo antes de importar:

   ```bash
   wp post delete 1 2 3 --force
   ```

   *Por qué es un requisito y no una recomendación:* en el entorno local la entrada demo
   «Hello world!» aparece en la sección «Del blog» del Inicio y en `/blog`, y **desplaza a
   una entrada real** («Estamos conectados, pero seguimos solos»). OWN-024 / D-02: cero demo
   en staging y producción. Código first-party pendiente
   ([#10](https://github.com/refo44/demo-caminodeldharma/issues/10)).

3. **Marcar el sitio como no indexable** mientras sea staging:

   ```bash
   wp option update blog_public 0
   ```

   Verificar después que `/wp-sitemap.xml` y las cabeceras reflejan el estado. En el entorno
   local `blog_public` vale `1`; ese valor **no** debe replicarse en staging.

4. **Instalar el paquete de idioma `es_CO`** y activarlo:

   ```bash
   wp language core install es_CO --activate
   ```

   *Por qué:* el plugin fija el locale en `es_CO`, pero si el paquete no está instalado
   WordPress sirve en inglés las cadenas de núcleo que el theme no controla. En el entorno
   local (sin salida a WordPress.org) el lightbox nativo de `/galeria` rotula
   «Close / Previous / Next» y `aria-label="Enlarged images"` sobre una página en español
   (WU-10, matriz § WU-10 D-05 / OWN-027). Tras instalar el paquete hay que **volver a verificar esas
   cadenas**. Docker local puede quedarse en inglés.

5. Fijar la zona horaria y comprobar PHP:

   ```bash
   wp option update timezone_string America/Bogota
   wp eval 'echo PHP_VERSION, PHP_EOL;'   # esperado: 8.3.x
   ```

6. Configurar una dirección de remitente real del dominio para `wp_mail()`
   (ver §6): `wordpress@localhost` es inválido y hace fallar el envío.

---

## 3. Subida del código first-party

1. Verificar rama/commit y QA local verde (niveles 1–3, `.audit/fase3-validation-matrix.md`).
2. Empaquetar **solo** los dos directorios first-party (un ZIP por directorio).
3. Subir a `wp-content/themes/` y `wp-content/plugins/` del staging.
4. Activar **primero el plugin, después el theme**:

   ```bash
   wp plugin activate camino-del-dharma-core
   wp theme activate camino-del-dharma
   ```

   El orden importa: el theme renderiza bloques dinámicos que el plugin registra.
5. Verificar ausencia de warnings/fatals y `debug.log` limpio tras navegación representativa.
6. **`.htaccess`:** copiar `wordpress/.htaccess` al document root **conservando el bloque
   `# BEGIN WordPress … # END WordPress` que ese servidor ya tenga**. Las reglas propias van
   **encima** de ese bloque, donde WordPress nunca escribe; WordPress reescribe únicamente lo
   que hay entre los marcadores al guardar los enlaces permanentes.

   Después, comprobar con `curl` las entradas de `docs/redirect-ledger.md`: **un solo salto
   por regla, sin cadenas ni loops**. Atención a la regla de HTTPS: usa dos `RewriteCond`
   encadenadas (no `[OR]`) precisamente para no crear un bucle cuando el TLS termina en el
   proxy.

7. Instalar Contact Form 7 desde WordPress.org y anotar la versión instalada en
   `docs/operations/third-party-plugins.md`. **CF7 debe estar activo antes de §5.**

---

## 4. Importación del contenido — orden WP-CLI

Todo el pipeline es **dry-run por defecto** y **create-missing-only**: sin `--apply` no
escribe nada, y con `--apply` no pisa lo que un editor haya cambiado en wp-admin. Ejecutar
cada paso primero sin `--apply`, leer el JSON, y solo entonces repetir con `--apply`.

```bash
wp cdd-core migrate validate --payload=/ruta/migration/payload.json
wp cdd-core migrate import   --payload=/ruta/migration/payload.json --source-root=/ruta/static --apply
wp cdd-core seed             --payload=/ruta/migration/payload.json --source-root=/ruta/static --apply
wp cdd-core migrate verify   --payload=/ruta/migration/payload.json
wp cdd-core migrate convert  --payload=/ruta/migration/payload.json --apply
```

Notas de cada paso:

- **`validate`** — comprueba el payload; no toca la base de datos.
- **`import --apply`** — crea páginas, eventos, entradas, fichas `blog_author`, álbumes y las
  asignaciones de imagen a álbum, y **fija los ajustes** (front page, página de entradas,
  permalinks `/blog/%postname%`, `tag_base` `blog/tag`) con el flush correcto.
- **`seed --apply`** — siembra únicamente la Media Library (81 objetos). Necesita
  `--source-root` para localizar los archivos dentro de `static/`.
- **`verify`** — reconcilia conteos por colección y lista lo que falte. Debe devolver
  `missing: []`.
- **`convert --apply`** — convierte el contenido a bloques nativos (galerías por álbum,
  `core/audio` de mantras, enlaces OWN-016, nota dinámica del Inicio) y siembra el copy de
  compartir y el SEO publicado. El `--payload` es opcional para `convert`, pero **pásalo**:
  sin él se omite la siembra de `share_*`/`seo_*`.

**El orden no es negociable.** `convert` opera sobre contenido ya importado, y
`contact provision` (§5) exige que `convert` haya actualizado el aviso de `/privacidad`.

### 4b. Consecuencia de «create-missing-only»: re-importar no repara

El importador **no actualiza objetos existentes**. Un entorno importado con un payload
antiguo se queda con los campos que aquel payload no traía, y volver a ejecutar
`import --apply` **no los rellena** (el dry-run lo confirma: `created: 0`, todo `skipped`).

Es exactamente lo que ocurre en el entorno local: `event_modality` está vacío en los 9
eventos que tienen modalidad, aunque el payload actual sí la trae y tanto el importador como
el renderizador la manejan bien. Por eso **producción publica una fila «Modalidad» que el
WordPress local no muestra** (WU-10, matriz § WU-10 D-01).

Regla operativa (OWN-023): **staging se importa una sola vez, desde cero, con el payload vigente.**
No hay backfill de `event_modality`. Si el entorno queda a medias, la opción barata es
reinstalar WordPress limpio, no forzar un update. Si hay que corregir contenido ya importado,
se edita en wp-admin o se borra el objeto y se reimporta — nunca se confía en que un segundo
`import --apply` lo arregle.

---

## 5. Contact Form 7

Con CF7 ya instalado y activo, y `convert --apply` ya ejecutado:

```bash
wp plugin activate contact-form-7
wp cdd-core contact provision            # dry-run
wp cdd-core contact provision --apply
```

`contact provision` es create-missing-only e **se niega** si CF7 está inactivo o si el aviso
de `/privacidad` todavía describe un formulario que no envía (ADR 0041 punto 3). Ese aviso lo
actualiza `convert --apply`; de ahí el orden.

Después, **probar el envío** (ADR 0045 / OWN-033):

1. Prueba técnica de staging: recepción en `refo44@gmail.com` (no es `Pass`; solo prueba MTA/CF7/Hostinger).
2. **Gate del corte con CF7 on:** el cliente confirma un mensaje sintético en
   `caminodeldharma1@gmail.com`.
3. El formulario público **nunca** queda apuntando al Gmail personal.
4. Si staging no entrega, **no** se corta con CF7 encendido (ya no es el default de ADR 0041 §5).
   Esperar, reabrir OWN-033, o acordar por escrito un corte con CF7 apagado.

---

## 6. Correo

`wp_mail()` **falla en el entorno local**: el contenedor no tiene MTA y el remitente por
defecto `wordpress@localhost` es una dirección inválida
(`Invalid address: (From): wordpress@localhost`). Por eso la entrega real solo puede probarse
en staging.

En Hostinger, fijar un remitente del propio dominio antes de probar CF7; un `From` que no
resuelva hace que el proveedor rechace el mensaje igual que en local.

---

## 7. Guard de producción

`import`, `seed` y `convert` escriben en producción **solo** con las dos banderas juntas:

```bash
--confirm-production --backup-evidence="<referencia del backup verificado>"
```

Sin ellas el pipeline se niega cuando `wp_get_environment_type()` es `production`. **Nunca**
ejecutar el importador contra producción sin guard, confirmación y **backup verificado
previamente restaurable** — no basta con que exista el archivo.

---

## 8. Verificación posterior al despliegue (staging)

Repetir en staging, con etiqueta `Pass` (no `Pass (local)`):

- plugin y theme activos sin warnings/fatals; `debug.log` vacío tras navegación representativa;
- las 41 rutas entrantes del nivel 3 (200/301/404) y las rutas `.ics` (200 vigente / 410
  finalizado / 404 inexistente);
- `verify` con `missing: []` y conteos reconciliados;
- comportamiento real de PHP/Apache/HTTPS y de las reglas del `.htaccess` (un salto por regla);
- **no indexabilidad del staging** (`blog_public 0`);
- cadenas del lightbox en español tras instalar `es_CO` (§2.4);
- entrega de CF7: prueba técnica (§5) y, para el corte, confirmación del cliente (ADR 0045);
- ausencia de cookies anónimas y de peticiones de seguimiento.

`Pass (local)` nunca sustituye a ninguna de estas: no prueba PHP/Apache/HTTPS ni el correo de
Hostinger.

---

## Producción estática (sin cambios)

El sitio estático sigue desplegándose por ZIP manual desde `static/` según README
(«Despliegue en Hostinger»). Este runbook no lo modifica ni lo autoriza.

---

## Rollback

- **Código:** desactivar theme/plugin (`wp theme activate twentytwentyfive`,
  `wp plugin deactivate camino-del-dharma-core`) o restaurar el ZIP anterior de los dos
  directorios. El `.htaccess` se revierte reponiendo la copia previa; conservar siempre una
  antes de sobrescribirlo.
- **Contenido:** el importador no borra, así que un import fallido se revierte restaurando el
  backup de base de datos tomado antes de `--apply`. En staging, la alternativa barata es
  reinstalar WordPress limpio y volver a §2.
- **Corte a producción:** tiene su propio checklist y rollback,
  `docs/cutover-checklist-wordpress.md`. **Este runbook no autoriza el corte.**
