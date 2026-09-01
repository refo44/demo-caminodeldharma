# wordpress/

Árboles de primer partido (alcance de SonarQube Cloud y del kit TDD, ADR 0038).

| Ruta | Rol | Estado |
| --- | --- | --- |
| `wp-content/plugins/camino-del-dharma-core/` | Plugin de dominio (ADR 0024) | Scaffold WU-03: bootstrap mínimo nacido de un test en rojo. El dominio llega en WU-05+. |
| `wp-content/themes/camino-del-dharma/` | Block theme FSE (ADR 0029) | Sin código hasta WU-04 (también TDD desde la primera línea). |

Ambos árboles se montan en el entorno Docker local (ADR 0023,
`docs/docker-wordpress-playbook.md`) y en el harness efímero de wp-phpunit
(`tools/run-phpunit-wp.sh`).

Guía de pruebas: [`docs/guia-pruebas-plugin-theme-fse.md`](../docs/guia-pruebas-plugin-theme-fse.md).
