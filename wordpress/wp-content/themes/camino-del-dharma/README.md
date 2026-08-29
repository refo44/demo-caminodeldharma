# camino-del-dharma

Block theme / Full Site Editing (ADR 0029). **Sin código todavía.**

Fase 3 no ha empezado. Esta carpeta existe para que SonarQube Cloud tenga un
`sonar.sources` que no es el sitio estático, y para aterrizar TDD el día uno.

No crear `theme.json`, `templates/` ni `functions.php` sin un test en rojo antes.
El theme ensambla; no registra CPTs ni taxonomías. Guía:
`docs/guia-pruebas-plugin-theme-fse.md` (ADR 0038).
