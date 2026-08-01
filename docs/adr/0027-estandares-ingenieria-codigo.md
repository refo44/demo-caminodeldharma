# ADR 0027: Estándares de ingeniería y estilo de código para Fase 3

## Estado

Aceptada

## Fecha

2026-07-31

## Contexto

Fase 3 introduce el primer código PHP propio del proyecto (theme `camino-del-dharma` + plugin
`camino-del-dharma-core`, ADR 0024). Hasta ahora el proyecto solo tenía HTML/CSS/JS estático, sin
ningún estándar de codificación explícito para PHP. El propietario pidió fijar, antes de escribir la
primera línea de código WordPress, qué nivel de práctica de ingeniería y qué estilo se espera, para que
cualquier implementador —humano o agente, en cualquier sesión futura— parta del mismo criterio.

## Decisión

1. Quien implemente Fase 3 debe operar con el criterio de un **desarrollador WordPress senior,
   ingeniero de software senior y arquitecto de software senior**: código production-grade, no un
   prototipo desechable.
2. Aplicar, **cuando sean relevantes, apropiados y aplicables** —no de forma dogmática ni como
   ejercicio de estilo— SOLID, KISS, YAGNI, patrones de diseño OOP y funcionales, prácticas de Extreme
   Programming (refactorización incremental, pruebas donde aporten valor real — no como mandato
   universal), Clean Code, Clean Architecture y naming claro.
3. **Guardrail explícito:** estas prácticas son herramientas, no un mandato de complejidad. Para un
   plugin de 2 CPTs y un theme de 13 plantillas, aplicar un patrón (p. ej. Strategy, Repository) solo
   si resuelve un problema real de este proyecto, no porque el patrón exista. Coherente con YAGNI —que
   está en la misma lista de prácticas pedidas— y con la disciplina que el proyecto ya aplica
   (`docs/03-wordpress-content-model.md`: "evita scope creep"). Sobre-ingeniería es tan indeseable como
   código descuidado.
4. **Estándares de WordPress y PHP, no negociables** (a diferencia de los patrones de diseño, que son
   situacionales):
   - **WordPress Coding Standards (WPCS)** vía PHPCS, con el ruleset oficial de WordPress.
   - Prefijo único en funciones, hooks, clases y opciones de BD para evitar colisiones (`cdd_` o
     `camino_del_dharma_`), tanto en el plugin como en el theme.
   - Baseline de seguridad de WordPress, siempre, sin excepción: escapar salida (`esc_html`,
     `esc_attr`, `esc_url`), sanitizar entrada (`sanitize_text_field` y equivalentes), nonces en
     formularios propios, `$wpdb->prepare()` para cualquier consulta con datos externos.
   - Cadenas de texto envueltas para traducción (`__()`, `_e()` con text domain propio) aunque hoy el
     sitio sea monolingüe en español — mantiene la puerta abierta a inglés sin refactor futuro (el
     selector de idioma ya se retiró hasta que exista esa versión, ver `.audit/decisions.md`,
     2026-07-20).
5. Herramientas recomendadas para hacer esto verificable, no solo declarativo: `phpcs` con
   `WordPress-Extra` o `WordPress-Core`, `php -l` (ya cubierto como nivel 1 de QA en
   `docs/docker-wordpress-playbook.md` §4), y opcionalmente PHPStan/Psalm si el plugin crece en
   complejidad.

## Alternativas consideradas

| Alternativa | Decisión |
| --- | --- |
| Sin estándar explícito, "buen criterio" implícito | Descartada: sin un estándar escrito, cada sesión o implementador parte de un criterio distinto — el mismo problema que motivó el resto de ADR de esta sesión |
| Aplicar todos los patrones y prácticas siempre, de forma exhaustiva | Descartada: sobre-ingeniería para el tamaño real de este proyecto (2 CPTs, 13 plantillas); contradice KISS/YAGNI, que están en la misma lista de prácticas pedidas por el propietario |
| Solo WPCS, sin mención a prácticas de arquitectura | Descartada: WPCS cubre estilo (espaciado, naming de archivo), no decisiones de diseño (dónde vive la lógica, cómo se separan responsabilidades) — insuficiente para lo pedido |

## Consecuencias

**A favor**

- Criterio de calidad explícito y verificable (PHPCS, `php -l`) en vez de un estándar tácito que varía
  entre sesiones.
- El guardrail anti-sobre-ingeniería evita que "aplicar SOLID" se convierta en excusa para complejidad
  innecesaria en un proyecto de este tamaño.
- La baseline de seguridad de WordPress queda como no negociable, separada de las prácticas de diseño
  que sí son situacionales.

**Contrapartidas aceptadas**

- Añade fricción de revisión (linting, prefijos, escaping) desde la primera línea de código de Fase 3 —
  aceptada a cambio de no acumular deuda técnica desde el inicio.

## Referencias

- ADR [0024](0024-plugin-dominio-theme-presentacion.md) — este ADR fija cómo se escribe ese código
- `docs/docker-wordpress-playbook.md` §4 — nivel 1 de QA (`php -l`, sintaxis)
- `docs/03-wordpress-content-model.md` — criterio "evita scope creep" ya vigente en el proyecto
- `.audit/decisions.md` (2026-07-20) — retiro del selector de idioma hasta que exista versión en inglés
