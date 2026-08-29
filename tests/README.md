# Tests

Taxonomía y oficio: [`docs/guia-pruebas-plugin-theme-fse.md`](../docs/guia-pruebas-plugin-theme-fse.md)
(ADR [0038](../docs/adr/0038-pruebas-tdd-phpunit-sonar.md)).

| Directorio | Nivel | Cuándo se llena |
| --- | --- | --- |
| `Support/` | Bootstrap unitario (`ABSPATH` dummy). No es una suite. | Día uno del PHP propio |
| `Unit/` | PHPUnit sin WordPress | El mismo día |
| `WordPress/` | wp-phpunit | El mismo día |
| `Features/` | Contratos HTTP / wp-admin / CLI (Gherkin) | Con el primer harness |

Esta carpeta existe hoy para que SonarQube Cloud trate `tests/` como tests
(`sonar.tests=tests`) y no como producción. El alcance de Sonar es **solo** plugin +
theme FSE; el estático no se analiza.

No scaffoldear PHPUnit, Composer ni Docker QA hasta que el owner arranque Fase 3. Ese
arranque es TDD: primer test en rojo, después el código.
