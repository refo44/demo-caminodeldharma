# Conflict map

| Conflict group | Tasks | Superficie compartida | Regla |
|---|---|---|---|
| CG-HTACCESS | TASK-0004, TASK-0008, TASK-0012 (y TASK-0001 solo si necesita AddType) | `.htaccess` | Ejecutar en serie, en orden 0004 → 0008 → 0012; TASK-0001 coordina antes de tocar .htaccess |
| CG-CONTACTO | TASK-0002, TASK-0003 | `contacto/index.html` | Serie: 0002 → (decisión) → 0003 |
| CG-HTML-GLOBAL | TASK-0007, TASK-0011, TASK-0010, TASK-0006 | `<head>`/markup de las 14 páginas, gallery.js | Serie en orden 0007 → 0011 → 0010; 0006 se inserta cuando se desbloquee (nunca simultánea con otra del grupo) |
| NONE | TASK-0001, TASK-0005, TASK-0009 | archivos nuevos o solo verificación | Paralelizables con cualquier tarea salvo nota explícita |

Ninguna pareja de tareas del mismo grupo está aprobada para paralelismo.
