# ADR 0024: Plugin propio para el dominio; el theme solo presenta

## Estado

Aceptada

## Fecha

2026-07-31

## Contexto

`docs/03-wordpress-content-model.md` §3.1 define el CPT `sangha` como "opcional... si se implementa" y
`docs/17-orden-implementacion.md` § Fase 3 solo dice "crear theme" y "CPT `event`; roles editoriales",
sin fijar dónde vive ese código dentro de la estructura que ya prevé ADR 0014
(`wordpress/wp-content/themes/camino-del-dharma/` y `plugins/camino-del-dharma-core/` — hasta hoy,
"solo si hay plugin propio").

Un playbook de aprendizajes aportado por el propietario desde otro proyecto WordPress con la misma
forma de migración (Revista de Filosofía LOGO ET SPES, static → WordPress) identificó como patrón de
valor separar con dureza el **dominio** (CPTs, taxonomías, roles, queries, comandos WP-CLI propios) en
un plugin propio, del **theme** (que solo presenta). El anti-patrón que evita explícitamente: "theme
monolítico con todo metido en `functions.php`", que mezcla presentación con reglas de negocio y hace
imposible cambiar de theme sin perder datos o lógica.

Camino del Dharma tiene, para el arranque de Fase 3, el CPT `event` previsto (docs/17 § Fase 3 punto
5), más roles editoriales y las taxonomías `event_city`/`event_type` (ADR 0022). El CPT `sangha`
(docs/03 §3.1) sigue marcado como "opcional... fuera del alcance actual" en `docs/12-theme-file-structure.md`
§2.1 — no está en el mapa de pantallas ni en los wireframes, y queda fuera del alcance inicial de Fase 3
por decisión del propietario (2026-07-31; ver nota al final de este ADR). Esta decisión fija la
arquitectura para cuando `sangha` sí se implemente, en una fase posterior.

## Decisión

1. Se crea **`camino-del-dharma-core`** como plugin propio **desde el inicio de Fase 3** — ya no
   condicionado a "si aplica" como decía ADR 0014 hasta hoy (ver nota añadida en ese ADR).
2. El plugin es dueño de todo el dominio: registro de CPTs (`event` ahora; `sangha` cuando se
   implemente — ver nota), taxonomías (`event_city`, `event_type`), meta fields, roles editoriales,
   comandos WP-CLI propios y cualquier query de negocio.
3. El theme `camino-del-dharma` **nunca** registra CPTs, taxonomías ni roles. Solo consume lo que el
   plugin expone (templates, template parts, hooks) para presentar.
4. Si el plugin se desactiva, el theme debe degradar con seguridad (sin fatales) — no duplicar lógica
   de negocio en el theme como fallback.
5. Esta decisión **no** cambia si o cuándo se implementa cada CPT — eso lo sigue gobernando
   `docs/03-wordpress-content-model.md` y las decisiones de contenido correspondientes (p. ej.
   TASK-0020 para `sangha`). Solo fija **dónde vive el código** una vez que se implementa.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Todo en el theme (`functions.php` con CPTs y lógica) | Descartada: mezcla presentación con dominio; cambiar de theme perdería la lógica de negocio — el anti-patrón que señala el playbook de origen |
| Plugin condicionado a "si aplica" (ADR 0014, alcance abierto) | Sustituida por esta decisión: con el CPT `event` ya previsto, la condición ya se cumple — el plugin se crea desde el inicio, sin evaluarlo caso por caso |
| Un plugin distinto por cada CPT | Descartada por ahora: sobre-ingeniería para el alcance actual (1 CPT); un solo plugin `-core` es más simple de mantener en este tamaño de proyecto |

## Consecuencias

**A favor**

- El theme puede cambiar, incluso reemplazarse, sin perder CPTs, datos ni roles.
- Separación de responsabilidades más fácil de auditar y de dar mantenimiento.
- Alinea con la arquitectura de 3 servicios de ADR 0023 (`docs/docker-wordpress-playbook.md` ya
  bind-monta theme y plugin por separado).

**Contrapartidas aceptadas**

- Más superficie a versionar y probar desde el día uno (un plugin más, con su propio ciclo de
  activación) frente a empezar solo con el theme.
- Actualiza tácitamente el "si aplica" de ADR 0014 respecto al plugin — anotado en ese ADR en vez de
  reabrirlo, porque el resto de su decisión (estructura del monorepo) no cambia.

## Nota (2026-07-31, misma sesión)

Al redactar este ADR se afirmó que "sangha" era uno de dos CPTs ya previstos para el arranque de Fase 3.
Es inexacto: `docs/12-theme-file-structure.md` §2.1 y `docs/17-orden-implementacion.md` § Fase 3 nunca
incluyeron `sangha` en el alcance inicial (solo `event`); tampoco existe wireframe ni entrada en el mapa
de pantallas para sangha. El propietario confirmó sacar `sangha` del alcance de esta fase: se trata como
fase separada, posterior, una vez TASK-0020 tenga ciudades confirmadas y se diseñe su wireframe. La
arquitectura de este ADR (plugin dueño del dominio) no cambia por esto — sigue rigiendo para `event` hoy
y para `sangha` cuando le llegue su turno.

## Referencias

- ADR [0014](0014-monorepo-static-wordpress.md) — estructura del monorepo; nota añadida sobre esta decisión
- ADR [0022](0022-sin-urls-de-filtro-por-ciudad.md) — taxonomías `event_city`/`event_type`
- ADR [0023](0023-entorno-local-wordpress-docker.md) y `docs/docker-wordpress-playbook.md` — bind-mount separado theme/plugin
- `docs/03-wordpress-content-model.md` §3.1 (CPT `sangha`), `docs/17-orden-implementacion.md` § Fase 3
- Playbook de aprendizajes, Revista de Filosofía LOGO ET SPES, 2026-07-31 (§2 y §11)
