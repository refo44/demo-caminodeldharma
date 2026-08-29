# Tests

Taxonomía y oficio: [`docs/guia-pruebas-plugin-theme-fse.md`](../docs/guia-pruebas-plugin-theme-fse.md)
(ADR [0038](../docs/adr/0038-pruebas-tdd-phpunit-sonar.md)).

| Directorio | Nivel | Cuándo se llena |
| --- | --- | --- |
| `Unit/` | PHPUnit sin WordPress | Día uno del PHP propio (Fase 3) |
| `WordPress/` | wp-phpunit | El mismo día |
| `Features/` | Contratos HTTP / wp-admin / CLI (Gherkin) | Con el primer harness |

Esta carpeta existe hoy para que SonarQube Cloud trate `tests/` como tests
(`sonar.tests=tests`) y no como producción. No scaffoldear PHPUnit, Composer ni
Docker QA hasta que el owner arranque Fase 3.
