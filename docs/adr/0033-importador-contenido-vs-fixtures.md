# ADR 0033: Importador de contenido institucional vs fixtures

## Estado

Aceptada

## Fecha

2026-08-19

## Contexto

ADR 0013 separa **código** (Git) de **contenido editorial** (WordPress: BD + uploads) *después* del
corte. No define cómo entra el contenido institucional en la base de datos la primera vez, ni cómo
distinguir datos de prueba de contenido real.

`docs/playbook-migracion-static-wordpress.md` §6 dejó pendiente esa decisión hasta cargar eventos,
galería y blog. Activar el theme o el plugin `camino-del-dharma-core` (ADR 0024) **no** crea Pages,
posts ni media. Un template de bloques tampoco.

Riesgos si no se decide ahora:

- Editores o agentes tratan fixtures de desarrollo como contenido de producción.
- Un importador con `--force` pisa ediciones hechas en wp-admin.
- Un teardown genérico borra Pages institucionales.
- El HTML estático se usa como segunda fuente editorial y diverge de `content-source/`.

Este ADR no implementa el importador. Fija el contrato para cuando se escriba.

## Decisión

### Dos conceptos distintos

| | Importador de contenido real | Fixtures / seeds |
| --- | --- | --- |
| Propósito | Cargar contenido institucional persistente | Bootstrap, demo o tests temporales |
| Marcador | Ninguno de fixture | Identificable de forma inequívoca (p. ej. post meta `_cdd_fixture = 1`) |
| Persistencia | Permanente | Removible |
| Cleanup | **Nunca** automático ni destructivo contra contenido real | Solo objetos propios del fixture |
| Producción | Permitido con production guard | **Prohibido** como contenido público |

No mezclar ambos en el mismo comando. No usar un teardown genérico (`wp post delete --force` masivo,
reset de BD, `wp site empty`) contra contenido real.

### Fuente canónica del copy

Precedencia de **contenido editorial** (no sustituye el orden docs vs ADR para arquitectura):

```text
content-source/          (copy institucional aprobado)
  > contenido estructurado del proyecto (docs/03, 09, 16; payload versionado del importador)
    > HTML estático generado (maqueta: contrato de presentación, no fuente editorial)
```

El HTML de producción **no** debe convertirse en una segunda redacción *institucional* frente a
`content-source/`. **Complemento (ADR 0034, 2026-08-19):** para eventos, posts, JSON de galería y
cards publicadas, el HTML live **sí** es la fuente de producción actual hasta extraerse. No tratarlo
como demo. Tras el corte, wp-admin es la SoT editorial (ADR 0013).

### Preferencia de implementación (cuando se construya)

- Comando WP-CLI en `camino-del-dharma-core` (ADR 0024), no como side-effect de `register_activation_hook`.
- Pipeline: `validate → plan → import → verify`.
- Dry-run por defecto; escritura solo con flag explícito (p. ej. `--apply`).
- Idempotente: re-ejecutar no duplica.
- **create-missing-only** por defecto: si el objeto ya existe, skip.
- No sobrescribir ediciones de wp-admin.
- No borrar contenido.
- Production guard: en `WP_ENVIRONMENT_TYPE=production` exigir confirmación explícita y evidencia de
  backup (coherente con ADR 0005).
- QA posterior al import (matriz + checklist de cutover).

*(Nota 2026-08-28, propietario: las **imágenes** se cargan a la Media Library con un comando
llamado **seed**. Ese seed es el **importador de media real**, no un fixture. Sin `_cdd_fixture`.
Sin teardown de attachments de producción. Mismas reglas: dry-run, `--apply`, idempotente,
create-missing-only. OWN-009-img. Huérfanas: mismo seed, **ocultas** en el sitio (OWN-003).)*

### Pages institucionales

Antes del cutover debe existir una estrategia explícita para crear o importar al menos:

Inicio (ajustes de lectura / front page), Comunidad, Linaje, Práctica, Práctica/videos, Meditación
semanal, Galería, Contacto, Donaciones, Blog (página de entradas si aplica). `/eventos` es archivo
del CPT `event` — **no** publicar una Page con slug `eventos` (`docs/12-theme-file-structure.md`).
`/privacidad` sigue aplazada (ADR 0028) y no se inventa copy.

Eventos y entradas de blog vigentes en la maqueta tienen filas en la matriz; se importan como CPT
`event` y `post`, no como Pages genéricas, salvo diferencia registrada.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Crear Pages a mano solo en wp-admin, sin importador | Frágil, no repetible entre local/staging, fácil de olvidar una ruta. Admisible como respaldo, no como única estrategia. |
| Ejecutar la carga en la activación del plugin | Impredecible; contradice el playbook de este repo y ADR 0005. |
| Usar el HTML estático como fuente editorial del importador | Convierte el artefacto de presentación en copy *institucional*; `content-source/` pierde prioridad. **Nota 2026-08-19 (ADR 0034):** esta alternativa se refiere al copy institucional. Extraer eventos/posts/galería del HTML live **sí** está decidido. |
| Fixtures sin marcador, o teardown de «todos los posts» | Contamina o destruye contenido real. |
| ACF / page builder como almacén de contenido institucional | Vetado por ADR 0025. |

## Consecuencias

**Beneficios:**

- Local y staging pueden sembrar datos de prueba sin poner en riesgo producción.
- El cutover tiene un dueño para «existen las Pages».
- Las ediciones editoriales post-import sobreviven a re-ejecuciones.

**Riesgos:**

- create-missing-only no actualiza copy si `content-source/` cambia después del primer import: hace
  falta un procedimiento editorial (editar en wp-admin o un `--force` acotado y documentado, nunca
  por defecto).
- El marcador de fixture y los nombres de comandos WP-CLI se eligen al implementar; este ADR no los
  congela más allá del ejemplo `_cdd_fixture`.

**Trabajo futuro:**

- Implementar el importador en Fase 3 (fuera de este ADR).
- Completar la columna «Import strategy» de `docs/matriz-migracion-static-wordpress.md`.

## Referencias

- ADR [0012](0012-wordpress-como-motor-de-contenido.md), [0013](0013-fuentes-de-verdad-duales-y-alcance-despliegue.md)
- ADR [0024](0024-plugin-dominio-theme-presentacion.md), [0028](0028-privacidad-aplazada-conscientemente.md)
- ADR [0032](0032-contrato-migracion-static-wordpress.md)
- `docs/contrato-migracion-static-wordpress.md`
- `docs/16-content-source-inventario.md`
- `content-source/Pagina web Camino del Dharma/Contenido_Web_Camino_del_Dharma.docx`
