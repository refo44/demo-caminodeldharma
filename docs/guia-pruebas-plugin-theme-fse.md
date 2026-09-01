# Guía de pruebas: plugin `camino-del-dharma-core` + theme FSE

Playbook de este repositorio. El plugin posee el dominio · wp-phpunit es el nivel 2 · el theme
FSE solo ensambla · Sonar no sustituye PHPUnit.

**Decisión:** ADR [0038](adr/0038-pruebas-tdd-phpunit-sonar.md).

---

## Tres tiempos (no mezclar)

| Tiempo | Qué hay que probar | Gate |
| --- | --- | --- |
| **Hoy (Fase 2)** | Sitio estático en la raíz. HTML/JSON de producción (ADR 0034). CSS con Stylelint. Árboles `wordpress/…` vacíos de código (placeholders para Sonar). | `npm run lint:css`. Verificación en navegador para JS/a11y. Sonar **no** mira el estático. |
| **Fase 3 (cuando el owner la arranque)** | Plugin + block theme. **TDD desde la primera línea** (plugin y theme). El kit PHPUnit nace con esa primera línea. | `composer test` (barato) + `npm run lint:css`. `composer test:wp` y `qa-*.sh` en local. |
| **Después del corte** | WordPress es la implementación activa. El estático queda archivo. Sonar sigue siendo solo plugin + theme. | El mismo gate. Deploy sigue manual (ADR 0016) hasta otra decisión. |

Git **`main` protegida** (ADR 0043): PR obligatorio desde ramas
[Conventional Branch](https://conventionalbranch.org/); commits
[Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) en inglés.
Gate de merge: `php` + `css` en `.github/workflows/test.yml`. Guía:
`docs/git-workflow.md`. El despliegue sigue manual (ADR 0016).

---

## Tesis

El proyecto es testeable cuando:

1. Las decisiones de dominio viven en PHP plano dentro de `camino-del-dharma-core`.
2. Los contratos in-process de WordPress corren en WordPress real vía **wp-phpunit**.
3. HTTP y wp-admin quedan en harnesses Docker aislados (ADR 0023), nunca en producción ni en el
   volumen del desarrollador.
4. El gate de CI es barato: sintaxis (`php -l`), audit del lockfile de Composer, suite unitaria,
   Stylelint.
5. SonarQube Cloud (GitHub App, Automatic Analysis) tiene alcance explícito **solo
   WordPress** (plugin + theme) en `.sonarcloud.properties`. El estático no se analiza.

WordPress FSE se desarrolla con TDD **desde la primera línea** (plugin y theme). Full Site
Editing no mueve el dominio al theme: solo añade contratos de presentación (`theme.json`,
`templates/`, `parts/`, markup de bloques), y esos contratos también se especifican con un
test antes del archivo de producción.

---

## 1. Costura que abarata las pruebas

La frontera del sujeto bajo prueba (SUT) decide el tipo de test. No colapsar capas en el
test por conveniencia.

| Capa | Vive en | ¿Depende de WP? | Tipo de test |
| --- | --- | --- | --- |
| Política / decisión | `camino-del-dharma-core` (p. ej. estado vigente/pasado del evento, guardia de publicación de autor) | No | PHPUnit nivel 1, sociable o solitario |
| Orquestador | plugin; inyecta una interfaz | No | PHPUnit nivel 1 sociable + doble de grabación manuscrito |
| Plantilla / formateador puro | plugin (HTML desde arrays) o helper del theme | No | PHPUnit nivel 1; semántica, no layout |
| Adapter / builder / persister | plugin; posts, meta, media, `.ics` | Sí | wp-phpunit (`tests/WordPress`) |
| Publish / REST / wp-admin / CLI | hooks del plugin (importador, «Eliminar huérfanos», ajustes) | Sí — ese es el contrato | Harness Docker aislado; nunca producción |
| Theme / FSE | `theme.json`, `templates/`, `parts/`, CSS complementario | Sí al renderizar; no en helpers puros | HTTP semántico en sitio aislado; no píxeles |

Rutas de primer partido (ADR 0014, ADR 0024, ADR 0029). Los directorios existen hoy como
placeholders (README, sin código). La reorg raíz → `static/` **no** se adelanta:

```text
wordpress/wp-content/plugins/camino-del-dharma-core/
wordpress/wp-content/themes/camino-del-dharma/
tests/Unit/
tests/WordPress/
tests/Features/
```

Colaboradores **internos** (política, orquestador, mappers, validadores) se cablean reales.
Colaboradores **externos** (renderer, filesystem, HTTP, reloj, cola, entorno) se doblan.

No fingir APIs de WordPress cuando el objeto de la prueba **es** el comportamiento de
WordPress.

---

## 2. Tres niveles, un gate barato

### Nivel 1 — Unitario (CI, cuando exista PHP)

Milisegundos. Sin base de datos, sin HTTP, sin Docker **dentro** del test. Un `ABSPATH`
dummy basta para incluir archivos del plugin o del theme que lo comprueban.

- Vive en `tests/Unit/`
- Config: `phpunit.xml.dist`
- Comando: `composer test` / `composer test:unit`
- PHP 8.3 (`platform.php`), alineado con Hostinger (ADR 0023)

Sujetos típicos de este proyecto: política de evento vigente vs pasado (fecha de fin,
`America/Bogota`); omisión de inscripción / `.ics` en pasados; aislamiento del `query_var`
del CPT `blog_author`; HTML de un formateador a partir de arrays.

### Nivel 2 — Contrato WordPress (Docker local)

Runner **por defecto** para contratos WordPress in-process: `WP_UnitTestCase`, factories de
posts, meta, taxonomías, adaptadores, `render_callback` de bloques. Compose efímero, tablas
`wptests_`, `down -v` al salir.

- Vive en `tests/WordPress/`
- Config: `phpunit-wp.xml.dist`
- Comando: `composer test:wp` / `./tools/run-phpunit-wp.sh`
- **No entra** en `composer test` ni en el CI por defecto: requiere Docker

Sujetos típicos: registro de CPT `event` y `blog_author`; taxonomías `event_city` /
`event_type` / álbum de galería; meta que sobrevive a `wp_insert_post`; `render_callback`
del calendario; rewrite `/author/{slug}` sin chocar con el `author` nativo; `.ics`
generado por el plugin.

wp-phpunit es pieza de primer orden. No es un incremento «para cuando el harness no
baste». No inventar un segundo bootstrap (`tests/Integration/`,
`composer test:integration`) en paralelo.

### Nivel 3 — Aceptación (Docker local)

Flujo HTTP, wp-admin o CLI. Gherkin nombra el contrato observable; un harness lo ejecuta.
No reescribir un harness HTTP a PHPUnit por uniformidad.

- Especificación: `tests/Features/*.feature` (español de la documentación de producto)
- Ejecución: `tools/qa-*.sh` con `docker compose -p cdd-qa-<slice>`

Sujetos típicos: GET de cada URL canónica **sin barra final** (ADR 0008) espera 200;
Editor de sitio 403 para un rol que no es Administrador; importador WP-CLI
create-missing-only; pantalla de «Eliminar huérfanos» solo toca `.ics`.

El playbook Docker (ADR 0023) cubre el entorno de **desarrollo** (volumen persistente,
`Pass (local)`). Los harnesses de nivel 3 son **otro compose**, efímero, con
`trap … down -v`. No usar el volumen `camino-del-dharma` del desarrollador como fixture.

### wp-phpunit frente a `qa-*.sh`

| Usar | Runner | SUT típico en este repo |
| --- | --- | --- |
| Contrato WP in-process (nivel 2 por defecto) | `composer test:wp` | Builder, registro CPT, mapeo de meta, persister, `render_callback` |
| HTTP, wp-admin, CLI del plugin | `tools/qa-*.sh` en compose efímero | Publicar desde el editor, permalinks, Media Library, importador |
| Dominio puro (sin APIs de WP) | `composer test:unit` | Política de evento, orquestador, plantilla HTML desde arrays |

**No fingir WordPress.** Si el comportamiento es «¿se registra el CPT?» o «¿el meta
sobrevive?», usa wp-phpunit. Si es una pantalla wp-admin o una ruta HTTP, usa un harness
aislado. No uses un harness para evitar wp-phpunit.

### Relación con los niveles QA de FABLE5

FABLE5 (master prompt de Fase 3) habla de cuatro niveles de *evidencia de migración*. Esta
guía habla de *runners*. Se complementan; no se duplican:

| FABLE5 | Esta guía |
| --- | --- |
| QA 1 static checks | Gate barato: `php -l`, Stylelint, PHPCS/WPCS (ADR 0027), `composer audit --locked` |
| QA 2 component checks | PHPUnit nivel 1 + wp-phpunit nivel 2 |
| QA 3 local integration | wp-phpunit + harnesses nivel 3 + checklist del playbook Docker §4 |
| QA 4 visual / staging | Manual (teclado, 320 px, zoom 200 %) + staging Hostinger. No píxeles en CI |

`Pass (local)` nunca prueba PHP/Apache/HTTPS/correo de Hostinger.

---

## 3. Oficio del test

Los tests se **acoplan al comportamiento** y se **desacoplan de la estructura**.

Puerta de calidad (no negociable):

- **Conductual** — si cambia el comportamiento, debe cambiar el resultado del test.
- **Insensible a la estructura** — si un refactor equivalente reorganiza clases, el test sigue verde.

### Normas

| # | Norma | Hacer | No hacer |
| --- | --- | --- | --- |
| 1 | Frontera del SUT primero | Colaboradores internos reales. Doblar solo renderer, FS, HTTP, reloj, cola, env. | Mockear el orquestador, la política o la clase bajo prueba. |
| 2 | TDD en dominio nuevo | RED → el cambio de producción más pequeño → REFACTOR. Un bug empieza por un test de regresión. | Una batería especulativa de arquitectura no escrita. Adelantar el kit PHP a Fase 3. |
| 3 | Conductual e insensible a la estructura | Si cambia el comportamiento, el test falla. Si solo cambia la estructura, no. | `expects()` de orden interno, métodos privados, % de cobertura. |
| 4 | Métodos planos y autocontenidos | Construir colaboradores en cada test. Cuerpo Arrange-Act-Assert. Nombre `test_<comportamiento_observable>`. | SUT mutable en `setUp()`. Tests numerados. `$sut->execute($input)`. |
| 5 | Dobles manuscritos en interfaces públicas | `class Recording_X implements X_Interface` con `invocations`, `last_input` y `output`. | Mockery, Brain Monkey, Pest, coreografía de `createMock()` sobre internos. |
| 6 | Aislar la mutación de WordPress | `docker compose -p cdd-qa-<slice>`, puerto propio, `trap … down -v`. BD desechable. | Volumen primario del desarrollador. Producción (`caminodeldharma.org`). Contenido residual como fixture. Contenido live tratado como fixture (ADR 0033). |
| 7 | wp-phpunit es el nivel 2 | `tests/WordPress/` + wp-phpunit + `composer test:wp` desde el primer PHP del plugin. | Dejarlo para después. Inventar `tests/Integration/`. Mockear funciones de WP. |
| 8 | CI barato | `php -l` + `composer audit --locked` + units + Stylelint. En cada push a `main` y, cuando existan, en cada PR. | Todos los harnesses Docker en un PR solo de docs. Tratar 0 % de cobertura de Sonar como suite ausente. Activar `deploy.yml` (ADR 0016). |
| 9 | Composer partido | `composer.json` de raíz = tooling de test. El del plugin = runtime. `vendor/` sin versionar. | Autoload PSR-4 del código de primer partido solo para facilitar tests, si el proyecto lo prohíbe. |
| 10 | Herramienta solo con necesidad arquitectónica | PHPUnit 9.x + wp-phpunit + harnesses. PHPCS/WPCS ya está decidido (ADR 0027) como estilo, no como nivel de test. | Behat, Playwright, PHPStan, Pest, wp-env, wp-browser, una segunda librería de mocks «por completeness». |

### Nombres y cuerpo

- Clase de test: `PascalCase` + sufijo `Test`, nombrada por el clúster de comportamiento
  (`Event_StatusTest`, `Blog_Author_Query_VarTest`).
- Método: `test_` + `snake_case` del comportamiento observable. No el nombre del método de
  producción.
- BDD narra el escenario (Dado / Cuando / Entonces). AAA estructura el cuerpo.
- Variables de dominio (`$event_status_policy`, `$ics_generator`), no `$sut`, `$result`,
  `$data`.
- Dobles terminan en rol (`$recording_clock`, `$failing_renderer`, `$clock_stub`).
- Gherkin en español, el idioma de `docs/` y del copy. Un feature, un idioma. Sin selectores
  CSS, nombres de clase, IDs de BD ni `sleep`.
- Behat no se instala hasta que el volumen de escenarios justifique un runner. PHPUnit y
  los harnesses ejecutan.

Código, comentarios de código y mensajes de Git: **inglés**. Esta guía y los nombres de
escenario Gherkin: **español**.

### Doble de grabación (patrón)

El reloj es un colaborador externo de este dominio: el estado vigente/pasado del evento
sigue la fecha de fin en `America/Bogota` (OWN-013). No se usa el reloj de pared.

```php
class Recording_Clock implements Clock {

	public $invocations = 0;
	public $now;

	public function now() {
		$this->invocations++;
		return $this->now;
	}
}
```

Tipado al contrato público. Instancia **nueva** en cada test. Observar invocaciones en la
costura externa está permitido cuando *esa* es la regla de dominio («no debe generar `.ics`
si el evento ya terminó»). No añadir `expects()` sobre el orquestador o la política.

### Qué no testear

Métodos privados; orden interno de llamadas; el handbook de WordPress Core; getters
triviales; whitespace de markup; orden incidental de arrays; porcentaje de cobertura;
snapshots enormes de HTML (en HTML, semántica, no layout); paridad visual píxel a píxel
contra `https://caminodeldharma.org` (eso es QA 4, manual).

Un test debe proteger al menos uno de: invariante de dominio; comportamiento observable;
contrato de integración; permiso/seguridad; integridad de datos; fallo realista;
regresión ocurrida o plausible.

### Anti-flakiness

Nunca: red externa en la suite por defecto (`composer audit --locked` es la excepción
documentada); producción; reloj de pared; `sleep` arbitrario; filas de BD sin `ORDER BY`;
timestamps del mismo segundo como orden implícito; azar sin semilla; estado global de otro
test; contenido preexistente en el volumen WordPress del desarrollador; el HTML live de
Hostinger como fixture de PHPUnit.

Si el tiempo afecta el comportamiento, pasar un timestamp explícito o un doble de reloj.

### Estático actual (sin runner JS)

No se instala Jest, Vitest ni Playwright para los cuatro scripts de `assets/js/`. Un
cambio de menú, galería, calendario o share se verifica en el navegador (y, en a11y, el
checklist de `docs/19`). Si más adelante un seam de política vive en JS y no cabe en esa
verificación, se reabre esta norma con un ADR.

---

## 4. Extensión FSE (theme `camino-del-dharma`)

El theme se escribe con TDD desde el inicio: no aterrizar `theme.json`, `templates/` ni
`functions.php` sin un test en rojo que nombre el contrato. Los bloques de dominio se
registran en **`camino-del-dharma-core`**. El theme ensambla `templates/` y `parts/`.
Global Styles posee la paleta (`theme.json`); no hex sueltos en Git. No hay theme clásico
PHP de puente (ADR 0029): no testear `front-page.php` / `page-*.php` porque no deben existir.

| Superficie FSE | Qué asertar | Nivel |
| --- | --- | --- |
| `theme.json` | Existen los tokens semánticos alineados con `docs/02` / `assets/css/main.css`; color libre off si esa es la política; `edit_theme_options` no se amplía a Editor | Lectura JSON / unitario pequeño — no screenshot |
| `templates/` y `parts/` | `templates/index.html` existe (WordPress reconoce el block theme); landmarks en el HTML renderizado; cada vista de `docs/12` tiene plantilla | Aceptación HTTP aislada |
| Bloques de dominio (plugin) | `render_callback` sobre un post de factory; omisión de campos vacíos; calendario de eventos no es Query Loop | wp-phpunit |
| Patrones / bloques de presentación | HTML semántico y contrato de clases si la maqueta lo congela; no diffs de píxeles | HTTP nivel 3; teclado / 320 px / zoom 200 % siguen manuales |
| Site Editor como UX de admin | El rol que no es Administrador recibe 403 en el Editor de sitio | Harness: crear rol, GET, esperar 403 |
| Conversión incremental | La ruta pública sigue 200 (sin barra final) mientras conviven estático en producción y WP en staging; no borrar ambos en un paso | Aceptación por incremento; no es un test del estático live |
| Incoming routes | HTTP de `/eventos/{slug}`, `/author/{slug}`, `/galeria/{slug}` según ledger; `get_permalink()` solo no basta (ADR 0032) | Harness nivel 3 |
| `noindex` de álbumes y tags | Cabecera o meta robots en archivo de álbum / tag / archivo de autores hasta volumen (ADR 0031, 0036, 0037) | Harness nivel 3 |

La accesibilidad automática (axe, Playwright) es útil e insuficiente. No instalarla sin
necesidad. Siguen valiendo pruebas manuales de teclado, foco, 320 px y zoom 200 %
(`docs/19`). Inspección estática de CSS no es verificación en navegador.

Un template en `templates/` **no** crea una Page (ADR 0032). Un test verde de «el archivo
existe» no cierra CONTENT ni ROUTING.

---

## 5. SonarQube Cloud, SonarQube for IDE y GitHub

El análisis estático **no** es un nivel más de PHPUnit. No prueba comportamiento. Señala
issues en código de primer partido (bugs, hotspots, duplicación). Tampoco cierra una
auditoría de seguridad del producto.

### Cómo se llaman las piezas

| Producto | Dónde vive | Rol en este repo |
| --- | --- | --- |
| **SonarQube Cloud** | SaaS + GitHub App Automatic Analysis (ya instalada) | Análisis del default branch y, cuando existan, de PRs. Alcance en `.sonarcloud.properties`. |
| **SonarQube for IDE** | Extensión del editor (Cursor / VS Code) | Feedback local. Connected Mode replica el perfil Cloud. No es el gate de merge. |
| **SonarQube Server** | Self-hosted | Fuera de alcance. No instalarlo. |
| `sonar-project.properties` + scanner en CI | GitHub Actions con `SONAR_TOKEN` | Solo si Automatic Analysis está **OFF**. Nunca junto a Automatic Analysis. |

### Estado de este repositorio

La App ya escanea `refo44/demo-caminodeldharma`. Faltaba el archivo de alcance: ahora es
`.sonarcloud.properties`. Automatic Analysis **solo lee ese archivo desde el default
branch**. Un PR que solo lo añade no cambia el análisis hasta el merge; tras integrar en
`main`, el siguiente análisis ya usa el archivo.

### Método (no mezclar)

| Paso | Qué hacer | Señal de que está bien |
| --- | --- | --- |
| 1. Comprobar el método | En SonarQube Cloud: **Administration → Analysis Method**. Automatic Analysis **ON**. | Project information → Last analysis method = *Analyzed by SonarQube Cloud* |
| 2. No mezclar métodos | No añadir SonarScanner en `test.yml` ni crear `sonar-project.properties`. | Un solo análisis por push/PR; el check de PHPUnit/Stylelint es otro job |
| 3. Alcance en `main` | `.sonarcloud.properties` en `main`. | Tras aterrizar, el siguiente análisis usa `sonar.sources` / `sonar.tests` |
| 4. Solo WordPress de primer partido | `sonar.sources` = plugin + theme. El estático (`assets/`, HTML, `scripts/`) no entra. Excluir `vendor/` del plugin. | La UI de Sonar no lista `index.html`, `assets/`, `wp-includes` ni `vendor` |
| 5. Sin comodines | En `.sonarcloud.properties` no se permiten `*`, `**` ni `?`. | El archivo parsea |
| 6. Tests aparte | `sonar.tests=tests`. Si se omite, Sonar puede tratar `tests/` como producción. | Los `*Test.php` no generan issues de «código muerto» de producción |
| 7. Cobertura 0.0 % | Automatic Analysis **no** importa cobertura PHPUnit. El Quality Gate por defecto omite la condición de cobertura cuando no hay datos. | Nadie «arregla» el 0 % instalando un scanner |
| 8. Verificar el alcance | **Administration → Background Tasks → Show SonarScanner Context**, y **Code**. | Plugin + theme sí; estático, Core y vendor no |

Documentación: [Automatic analysis](https://docs.sonarsource.com/sonarqube-cloud/analyzing-source-code/automatic-analysis).

### Alcance (solo WordPress)

Rutas relativas a la raíz. Encoding UTF-8. Sin globs. El sitio estático **no** se declara
en `sonar.sources` y no se añadirá más tarde.

```properties
sonar.sources=wordpress/wp-content/plugins/camino-del-dharma-core,wordpress/wp-content/themes/camino-del-dharma
sonar.tests=tests
sonar.exclusions=wordpress/wp-content/plugins/camino-del-dharma-core/vendor
sonar.sourceEncoding=UTF-8
```

Hasta que haya PHP/JSON/HTML de theme, Sonar verá poco o nada en **Code** — es el
comportamiento correcto, no un fallo de configuración. Stylelint cubre el CSS estático;
Sonar no lo duplica.

### SonarQube for IDE

Útil en Connected Mode. No versiona reglas en Git. No se exige la extensión en clones ni en
CI. Un hallazgo solo-IDE no es gate de merge.

### Anti-patrones de Sonar

- `sonar-project.properties` con Automatic Analysis ON (se ignora).
- SonarScanner en `test.yml` con Automatic Analysis ON (análisis duplicados, check rojo).
- Medir el éxito de PHPUnit por el % de cobertura de Sonar.
- Incluir el sitio estático, WordPress Core, `vendor/` o documentación en `sonar.sources`.
- Globs en `.sonarcloud.properties`.
- Instalar PHPStan «porque Sonar no basta» sin un seam que `php -l` no cubra.

---

## 6. Kit mínimo (se crea el día uno del código FSE)

No escribir PHP, `theme.json` ni plantillas del theme antes de que el owner arranque Fase 3.
Los árboles bajo `wordpress/` son placeholders para Sonar, no implementación.

Cuando Fase 3 arranque, el **primer** commit de código del plugin o del theme va precedido
del kit y de un test en rojo.

| Artefacto | Rol |
| --- | --- |
| ADR 0038 + esta guía | Niveles, CI, comandos, oficio. Git es la fuente durable; `.cursor/rules/testing-tdd.mdc` es espejo. |
| `composer.json` de raíz (`require-dev` PHPUnit 9.x + wp-phpunit) | `composer test` = lint PHP + audit `--locked` + units. `platform.php` = 8.3. `composer test:wp` para nivel 2. |
| `tests/Unit/` + `tests/Support/bootstrap.php` | `ABSPATH` dummy en Support (no en Unit: PHPUnit lo recogería como test). `require_once` de PHP plano del plugin o helpers del theme. Sin boot de WordPress. Incluye lectura de `theme.json`. |
| `tests/WordPress/` + `phpunit-wp.xml.dist` + `run-phpunit-wp.sh` | Obligatorio el día uno del plugin **y** de bloques con `render_callback`. Compose aislado, tablas `wptests_`, `down -v`. |
| `tests/Features/*.feature` | Especificación en español. Sin Behat hasta que el volumen lo pida. |
| `tools/php-lint.sh` + `run-phpunit.sh` + `qa-<slice>.sh` | Portátil sin PHP nativo (vía Docker). Harness: proyecto compose, puerto propio, `trap down -v`, nunca producción. |
| `.github/workflows/test.yml` | Push a `main` + PR. `npm run lint:css` + `composer test`. Sin secretos de deploy. Sin scanner de Sonar. |
| `.sonarcloud.properties` | Ya existe: solo plugin + theme. No añadir el estático. |

PHPCS/WPCS (ADR 0027) entra en el job de estilo cuando exista PHP, no como sustituto de
PHPUnit. No forma parte de `composer test:unit`.

### Comandos canónicos (Fase 3+)

```bash
npm run lint:css           # CSS estático y, en Fase 3, hoja complementaria del theme
composer test              # php -l → audit --locked → units; no test:wp ni qa-*.sh
composer test:unit
composer test:wp           # PHPUnit/WordPress aislado (Docker); no CI
./tools/php-lint.sh
./tools/run-phpunit.sh
./tools/run-phpunit-wp.sh
./tools/qa-<slice>.sh      # un harness relevante; down -v al salir
```

Desde WU-03 (2026-08-31) existen todos salvo `qa-<slice>.sh` (nace con el primer harness de
nivel 3). PHPCS corre con `composer lint:phpcs` (paso propio del job PHP de `test.yml`).

`php -l` comprueba **sintaxis**. `composer audit --locked` comprueba **advisories del
lockfile**. PHPUnit nivel 1 comprueba **comportamiento puro**. `composer test:wp` comprueba
**contratos WordPress in-process**. `qa-*.sh` comprueba **HTTP / wp-admin / CLI**.
SonarQube Cloud comprueba **issues estáticos** en el alcance declarado; no es cobertura
ni PHPUnit.

### Qué no crear todavía

- Código de plugin o theme (`theme.json`, `templates/`, PHP). Los README bajo `wordpress/`
  no son implementación.
- `composer.json` / `vendor/` / `phpunit.xml.dist` sin PHP que probar.
- `.github/workflows/test.yml` vacío de sujeto (ADR 0016 pospone Actions de *deploy*; el
  `test.yml` nace con el primer PHP, no como teatro).
- Behat, Playwright, PHPStan, Pest, wp-env.

---

## 7. Kit concreto el día uno de Fase 3

Oficio ya ejecutado en otro monorepo del mismo autor (plugin + theme + PHPUnit +
wp-phpunit + harnesses). **Copiar taxonomía y archivos de tooling; no copiar dominio,
slugs, CPTs, puertos, hosts ni harnesses de PDF.**

### Archivos a crear (nombres de *este* repo)

| Artefacto | Detalle que sí copiar |
| --- | --- |
| `composer.json` raíz | `require-dev`: `phpunit/phpunit` ^9.6, `wp-phpunit/wp-phpunit` (versión = WordPress del compose, no 7.1 de otro proyecto), `yoast/phpunit-polyfills` (hace falta para `WP_UnitTestCase`). `platform.php` = **8.3** (Hostinger, ADR 0023). Scripts: `lint:php`, `audit:deps`, `test` = lint → audit → unit, `test:wp`. |
| `phpunit.xml.dist` | Bootstrap `tests/Support/bootstrap.php`. Suite `tests/Unit`. `beStrictAboutOutputDuringTests`, `failOnRisky`, `failOnWarning`, `cacheResult=false`. |
| `phpunit-wp.xml.dist` | Bootstrap `tests/WordPress/bootstrap.php`. Suite `tests/WordPress`. |
| `tests/Support/bootstrap.php` | `ABSPATH` dummy. `require_once` de PHP plano del plugin y helpers del theme **sin** boot de WP. |
| `tests/WordPress/bootstrap.php` | Autoload de raíz. `tests_add_filter( 'muplugins_loaded', … )` carga `camino-del-dharma-core.php`. Luego el bootstrap de wp-phpunit. |
| `tests/WordPress/wp-tests-config.php` | `ABSPATH` del contenedor, credenciales del compose, `$table_prefix = 'wptests_'`, `WP_ENVIRONMENT_TYPE=local`. |
| `tools/php-lint.sh` | `php -l` sobre plugin, theme y `tests/`; excluye `vendor/`. **No añadirlo mientras no haya `.php`:** con cero archivos el script canónico **falla**. Fallback Docker `wordpress:cli-php8.3` si no hay PHP nativo. |
| `tools/run-phpunit.sh` | `composer install` si falta `vendor/bin/phpunit`; corre `--testsuite unit`. Mismo fallback Docker. |
| `tools/run-phpunit-wp.sh` | `docker compose -p cdd-wp-phpunit`, puerto **distinto de 8080**, `trap … down -v`, espera a `wp core version`, monta el repo en `wpcli`. |
| `.github/workflows/test.yml` | Dos jobs: PHP (`composer test`) y Stylelint. `pull_request` + `push` a `main`. Sin secretos, sin deploy, sin SonarScanner. `npm ci --ignore-scripts`. Stylelint sobre **los dos** árboles CSS cuando el theme exista. |

`tests/Support/` es el bootstrap unitario; no meterlo en `tests/Unit/` (PHPUnit lo recogería como test). Helpers `make_*` privados en la clase de test están permitidos si son puros y devuelven objetos frescos. El doble de grabación vive **en el mismo archivo** hasta que un segundo test lo reutilice.

### Oficio que falta en esta guía y sí hay que traer

- **Sociable por defecto.** Colaboradores internos reales. Solitario solo si la ramificación lo exige.
- **Sin SUT en `setUp()`.** Cada método construye sus objetos.
- **Docblock del método:** qué comportamiento protege, no una paráfrasis del nombre.
- **Frontera de un upgrade:** si se actualiza un adaptador (generador `.ics`, cliente HTTP, renderer), el test de aceptación debe **ejecutar esa frontera**, no sustituirla por un doble. Un recording double no acepta un bump de librería.
- PHPUnit observa invocaciones en la costura **externa** cuando esa es la regla («no generar `.ics` si el evento ya terminó»). No `expects()` sobre la política.

### Qué no traer

| De ese repo | Por qué no |
| --- | --- |
| Harnesses `qa-article-pdf-*.sh`, Gherkin de PDF, Dompdf, CPTs `article`/`issue`/`author` | Dominio de una revista académica |
| `wp-phpunit` 7.1 y WordPress 7.1 | Camino pincha la versión de Hostinger (ADR 0023), no la de otro hosting |
| `platform.php` 8.2 y `Requires PHP: 7.4` | Aquí el runtime canónico es 8.3; no inventar una matriz de cuatro PHP |
| «Sin PHPCS» | ADR 0027 ya exige WPCS; es estilo, no un nivel de PHPUnit |
| `qa-author-permalinks.sh` sobre volúmenes primarios | Excepción documentada como anti-patrón; no copiarla |
| Protección de `main`, Dependabot, deploy FTPS, Pages | Otro git-flow y otro hosting (ADR 0016) |
| Cabecera «WordPress clásico» de su estándar de oficio | Camino es FSE desde el día uno (ADR 0029) |
| Composer runtime dentro del plugin «porque ellos tienen Dompdf» | Solo si *este* plugin necesita una librería de producción |

---

## Frase reusable

Proteger comportamiento de dominio con PHPUnit sociable; contratos WordPress in-process
con wp-phpunit en una BD desechable; HTTP y wp-admin con harnesses aislados; análisis
estático de GitHub con `.sonarcloud.properties` (Automatic Analysis ON, nunca como gate de
cobertura); el gate por defecto offline y rápido; el theme FSE no lleva el dominio; no
instalar una herramienta hasta que un seam no quepa en esta pila.

---

**Referencias de producto:** PHPUnit 9.x · [wp-phpunit](https://github.com/wp-phpunit/wp-phpunit) · Composer `audit --locked` · [SonarQube Cloud Automatic Analysis](https://docs.sonarsource.com/sonarqube-cloud/analyzing-source-code/automatic-analysis)

**Referencias de este repo:** ADR 0023, 0024, 0027, 0029, 0032, 0033, 0038 · `docs/12-theme-file-structure.md` · `docs/docker-wordpress-playbook.md` · `docs/19-accesibilidad-estandares.md`
