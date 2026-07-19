# ADR 0009: CSS y tokens invariantes en la migración

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

La identidad visual está tokenizada en `:root` (`--brand-*` y roles semánticos) según `14-css-architecture` y `02-identidad-corporativa`. La maqueta usa **un solo CSS principal** (`assets/css/main.css`) validado con Stylelint.

Durante migraciones a CMS es común fragmentar estilos en plugins, bloques o hojas ad hoc, lo que degrada performance, accesibilidad y coherencia visual.

## Decisión

Durante la migración a WordPress (y en mantenimiento posterior salvo ADR nuevo), **no cambiarán**:

- Arquitectura CSS (capas, naming, especificidad) definida en `14-css-architecture`.
- Tokens de identidad (`--brand-*`) y roles semánticos en `:root`.
- Principio de **un solo CSS de implementación** (`main.css`) encolado desde el theme.
- Prohibición de estilos inline y clases temporales de la maqueta.

WordPress puede aportar `theme.json` para alinear paleta y tipografías del editor de bloques, pero el CSS público sigue siendo `main.css` heredado de la maqueta.

Cambios permitidos sin ADR: correcciones de bugs, accesibilidad (contraste, focus) y ajustes alineados con `content-source/`. Cambios de identidad o arquitectura CSS requieren actualizar `02`, `14` y posiblemente un ADR sustitutorio.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Reescribir CSS en theme desde cero | Desecha QA de Fase 2; alto riesgo de regresiones. |
| Múltiples hojas por componente | Contradice `18-tendencias` §8 y complica lint/caché. |
| CSS-in-JS o framework utility | Fuera de alcance; JS mínimo sin frameworks. |
| Page builders con estilos embebidos | Pérdida de control editorial y de performance. |

## Consecuencias

**Beneficios:**

- Continuidad visual garantizada entre estático y WordPress.
- Stylelint sigue aplicando a un único punto de verdad.
- Tokens centralizados facilitan mantenimiento.

**Riesgos:**

- Plugins WordPress pueden inyectar CSS; hay que limitarlos o neutralizarlos en theme.
- Evolución de identidad requiere proceso formal (docs + posible ADR).

**Trabajo futuro:**

- Encolar `main.css` con `filemtime` para cache busting (`12-theme-file-structure`).
- Verificar que el editor de bloques no introduzca colores fuera de `theme.json`.

## Referencias

- `docs/14-css-architecture`
- `docs/18-tendencias-ux-ui-sistema-editorial` §8
- `docs/17-orden-implementacion` §2.5 (Invariantes de diseño)
- `docs/12-theme-file-structure`
- ADR 0001, ADR 0002
