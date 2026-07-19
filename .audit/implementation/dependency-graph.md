# Dependency graph

Aristas (A → B = B depende de A):

```text
TASK-0004 → TASK-0005   (verificación tras activación)
TASK-0004 → TASK-0008   (serialización .htaccess: CSP tras HSTS)
TASK-0004 → TASK-0012   (includeSubDomains requiere HSTS básico activo)
TASK-0005 → TASK-0012   (…y verificado)
TASK-0002 → TASK-0003   (form real sustituye al estado CTA)
TASK-0007 → TASK-0011   (versionado tras cambios de markup de imágenes)
TASK-0011 → TASK-0010   (pre-render galería tras versionado, mismo grupo HTML)
```

Sin dependencias entrantes: TASK-0001, TASK-0002, TASK-0004, TASK-0006 (bloqueada por decisión, no por tarea), TASK-0007, TASK-0009.

**Ciclos: ninguno** (grafo acíclico verificado por inspección de la lista de aristas).
