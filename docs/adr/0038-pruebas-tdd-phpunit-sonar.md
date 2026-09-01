# ADR 0038: Pruebas TDD, wp-phpunit y SonarQube Cloud

## Estado

Aceptada

## Fecha

2026-08-29

## Contexto

El sitio público sigue siendo estático. Fase 3 construirá el plugin `camino-del-dharma-core`
(ADR 0024) y el theme de bloques `camino-del-dharma` (ADR 0029) en el monorepo de ADR 0014.
ADR 0027 ya pidió pruebas «donde aporten valor real», no como mandato de cobertura. Faltaba
fijar **cómo** se escribe un test, en qué nivel corre, qué entra en CI y qué hace SonarQube
Cloud — la GitHub App ya analiza este repositorio, pero el alcance no estaba declarado.

Hoy `main` no está protegida: los cambios se empujan directo a `main`. Más adelante los
Pull Request y las feature branches serán obligatorios. El gate de calidad debe servir en
ambos modos. El despliegue automático sigue pospuesto (ADR 0016); esta decisión no lo activa.

## Decisión

1. **WordPress FSE se desarrolla con TDD desde la primera línea** — plugin
   `camino-del-dharma-core` **y** theme `camino-del-dharma`. No se escribe `theme.json`,
   `templates/`, `functions.php` ni PHP de dominio «para después cubrirlo». RED → el
   cambio de producción más pequeño → REFACTOR. Un bug empieza por un test de regresión.
   No se escribe una batería especulativa de arquitectura que aún no existe. El sitio
   estático no entra en esta disciplina: sigue Stylelint + navegador.
2. **TDD en dominio y en contratos FSE.** Toda política u orquestación del plugin, y todo
   contrato observable del theme (tokens en `theme.json`, `templates/index.html` para que
   WordPress reconozca el block theme, `render_callback` de bloques de dominio) se
   especifica con un test antes del código de producción.
3. **Tres niveles, un gate barato.** Nivel 1: PHPUnit unitario (`tests/Unit/`), sin base de
   datos ni HTTP. Nivel 2: wp-phpunit (`tests/WordPress/`) para contratos in-process de
   WordPress. Nivel 3: harnesses Docker aislados (`tools/qa-*.sh`) para HTTP, wp-admin y CLI.
   El comando canónico `composer test` = `php -l` + `composer audit --locked` + units. No
   incluye `test:wp` ni `qa-*.sh`.
4. **El plugin posee el dominio; el theme FSE solo ensambla.** CPTs, taxonomías, meta, roles,
   `.ics`, estado de evento por fecha de fin (`America/Bogota`) y `render_callback` de bloques
   de dominio viven en el plugin y se prueban ahí. `theme.json`, `templates/` y `parts/` se
   asertan como contratos de presentación, no como reglas de negocio.
5. **No fingir WordPress.** Si el objeto de la prueba es un CPT, meta, rewrite o
   `render_callback`, el runner es wp-phpunit. Brain Monkey, WP_Mock y bosques de stubs están
   fuera. No se inventa `tests/Integration/` en paralelo.
6. **Kit el día uno del código FSE.** El `composer.json` de raíz, `tests/Unit/`,
   `tests/WordPress/` y `phpunit-wp.xml.dist` se crean **el mismo día** que la primera línea
   de plugin o theme — no «cuando el harness no baste». Los árboles
   `wordpress/wp-content/plugins/camino-del-dharma-core/` y
   `wordpress/wp-content/themes/camino-del-dharma/` existen hoy **vacíos de código** para
   Sonar; no son el arranque de Fase 3 (la reorg a `static/` no se adelanta). Stylelint
   (`npm run lint:css`) sigue siendo el gate del CSS estático.
7. **CI de calidad ≠ despliegue.** Cuando existan tests PHP, `.github/workflows/test.yml`
   corre en push a `main` y en PR: lint CSS + `composer test`. No lleva secretos de deploy ni
   scanner de Sonar. El CD sigue gobernado por ADR 0016. Mientras `main` no esté protegida,
   el mismo workflow (si existe) corre en el push; no espera a que el PR sea obligatorio.
8. **SonarQube Cloud analiza solo WordPress.** Automatic Analysis permanece ON. El alcance
   en `.sonarcloud.properties` es únicamente el plugin y el theme FSE. El sitio estático
   (`assets/`, HTML, `scripts/`) **queda fuera**. No se añade `sonar-project.properties` ni
   SonarScanner en CI mientras Automatic Analysis esté ON. Cobertura 0.0 % es el
   comportamiento esperado de Automatic Analysis, no una suite ausente.
9. **WPCS sigue siendo estilo, no prueba.** PHPCS/WPCS (ADR 0027) es el ruleset de código PHP
   cuando exista PHP propio. No sustituye PHPUnit. PHPStan/Psalm siguen opcionales hasta que
   el plugin tenga un seam que `php -l` no cubra. Behat, Playwright, Pest, Mockery y wp-env
   no se instalan «por completeness».

La guía operativa es [`docs/guia-pruebas-plugin-theme-fse.md`](../guia-pruebas-plugin-theme-fse.md).

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Dejar wp-phpunit para «cuando el plugin crezca» | Descartada: los contratos de CPT, meta e ICS son el trabajo de Fase 3; fingirlos con mocks da falsa confianza |
| Suite única de PHPUnit que arranca WordPress siempre | Descartada: el gate de CI dejaría de ser barato; los tests de política no necesitan BD |
| Activar SonarScanner en GitHub Actions ahora | Descartada: Automatic Analysis ya está ON; un segundo scanner hace fallar el check de GitHub |
| Exigir PR y protección de `main` en este ADR | Descartada: el propietario mantiene push directo a `main` hasta una decisión posterior; el gate se diseña para ambos modos |
| Activar `deploy.yml` junto con `test.yml` | Descartada: contradice ADR 0016; esta decisión solo cubre el gate de calidad |
| Incluir el sitio estático en Sonar | Descartada: el propietario limita Sonar al plugin y al theme FSE; el estático se valida con Stylelint y navegador |

## Consecuencias

### A favor

- Cualquier implementador (humano o agente) parte del mismo oficio de test antes de escribir
  el primer PHP de Fase 3.
- Sonar cubre solo plugin + theme; el HTML/JS/CSS estático no aparece como código propio.
- El gate futuro de PR es barato y no arrastra Docker.

### Contrapartidas aceptadas

- Hasta el primer PHP de Fase 3 no hay `composer test` que ejecutar. Los árboles bajo
  `wordpress/` son placeholders (README), no implementación.
- Automatic Analysis no importa cobertura PHPUnit; nadie «arregla» el 0 % instalando un
  scanner.
- Mientras `main` no esté protegida, el gate de CI (cuando exista) es informativo, no un
  bloqueo de merge.

## Nota operativa (2026-09-01)

La política de push directo a `main` descrita en **Contexto** y **Consecuencias** quedó
**sustituida en operación** por [ADR 0043](0043-trunk-based-conventional-branch-commits.md):
`main` protegida, PR obligatorio, Conventional Branch/Commits. El diseño del gate (CI en
push y PR) sigue vigente; el merge queda bloqueado sin checks verdes.

## Referencias

- Guía: [`docs/guia-pruebas-plugin-theme-fse.md`](../guia-pruebas-plugin-theme-fse.md)
- Git: [ADR 0043](0043-trunk-based-conventional-branch-commits.md), [`docs/git-workflow.md`](../git-workflow.md)
- ADR [0014](0014-monorepo-static-wordpress.md), [0024](0024-plugin-dominio-theme-presentacion.md), [0027](0027-estandares-ingenieria-codigo.md),
  [0029](0029-theme-bloques-full-site-editing.md), [0016](0016-automatizacion-ci-cd-pospuesta.md),
  [0023](0023-entorno-local-wordpress-docker.md)
- [Automatic analysis](https://docs.sonarsource.com/sonarqube-cloud/analyzing-source-code/automatic-analysis)
- PHPUnit 9.x · [wp-phpunit](https://github.com/wp-phpunit/wp-phpunit)
