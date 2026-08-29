# wordpress/

Árboles de primer partido para SonarQube Cloud y para aterrizar el kit TDD.
**No es el arranque de Fase 3.** No hay `theme.json`, plantillas, ni PHP de plugin.

| Ruta | Rol |
| --- | --- |
| `wp-content/plugins/camino-del-dharma-core/` | Plugin de dominio (ADR 0024). Vacío de código hasta el primer test. |
| `wp-content/themes/camino-del-dharma/` | Block theme FSE (ADR 0029). Vacío de código hasta el primer test. |

La reorg raíz → `static/` (ADR 0014) sigue siendo el primer paso de Fase 3 y **no** se
adelanta. El HTML live permanece en la raíz.

Cuando el owner arranque Fase 3: TDD desde la primera línea (plugin **y** theme). Guía:
[`docs/guia-pruebas-plugin-theme-fse.md`](../docs/guia-pruebas-plugin-theme-fse.md)
(ADR 0038).
