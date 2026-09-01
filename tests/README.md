# Tests

Taxonomía y oficio: [`docs/guia-pruebas-plugin-theme-fse.md`](../docs/guia-pruebas-plugin-theme-fse.md)
(ADR [0038](../docs/adr/0038-pruebas-tdd-phpunit-sonar.md)). Kit creado en Fase 3 WU-03,
el mismo día que el primer PHP propio.

| Directorio | Nivel | Comando |
| --- | --- | --- |
| `Support/` | Bootstrap unitario (`ABSPATH` dummy). No es una suite. | — |
| `Unit/` | PHPUnit sin WordPress | `composer test:unit` (dentro de `composer test`) |
| `WordPress/` | wp-phpunit en harness Docker **efímero** (`cdd-wp-phpunit`, `down -v`) | `composer test:wp` |
| `Features/` | Contratos HTTP / wp-admin / CLI (Gherkin) | Con el primer harness `tools/qa-*.sh` (aún no existe) |

`sonar.tests=tests` mantiene esta carpeta como tests para SonarQube Cloud. El alcance de
Sonar es **solo** plugin + theme FSE; el estático no se analiza.

TDD desde la primera línea: test en rojo antes de cada línea de producción del plugin o
del theme.
