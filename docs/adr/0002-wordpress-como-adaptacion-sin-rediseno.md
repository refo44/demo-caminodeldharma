# ADR 0002: WordPress como adaptación sin rediseño

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

La Fase 3 del proyecto prevé convertir la maqueta estática en un theme de WordPress (`12-theme-file-structure`, `03-wordpress-content-model`). Sin una regla explícita, es habitual que la fase CMS se convierta en una oportunidad de rediseño: nuevos layouts, copy reescrito, CSS reestructurado o URLs alteradas.

Eso contradice la inversión ya hecha en maqueta congelada (ADR 0001) y la alineación con `content-source/`.

## Decisión

WordPress **solo reemplazará el motor de contenido y la administración**. No rediseñará el sitio.

En Fase 3:

- El HTML de la maqueta se adapta a plantillas PHP según la tabla de correspondencia en `17-orden-implementacion` §2.2.
- Se mantienen bloques, jerarquía visual, copy, tokens, arquitectura CSS y URLs (ADR 0008, ADR 0009).
- WordPress aporta: edición de contenido, CPT de eventos, blog dinámico y panel de administración.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Theme comercial adaptado | No refleja identidad ni wireframes acordados; deuda de personalización alta. |
| Rediseño durante migración | Duplica esfuerzo; invalida QA de la maqueta. |
| Headless WordPress + front separado | Complejidad innecesaria para el alcance y el equipo del proyecto. |
| Permanecer solo estático | Limita gestión editorial y eventos dinámicos a medio plazo. |

## Consecuencias

**Beneficios:**

- Alcance de Fase 3 acotado y estimable.
- Continuidad para usuarios y para SEO (URLs estables).
- El theme hereda CSS y JS ya validados.

**Riesgos:**

- WordPress puede introducir markup adicional (bloques, plugins); hay que contenerlo en `functions.php` y política de plugins mínima.
- La paridad pixel-perfect requiere revisión sistemática plantilla por plantilla.

**Trabajo futuro:**

- Implementar theme según `12-theme-file-structure`.
- Repetir Fase 2.5 (QA) en staging antes de producción WordPress.

## Referencias

- `docs/17-orden-implementacion` §2.2, §2.5, Fase 3
- `docs/12-theme-file-structure`
- `docs/03-wordpress-content-model`
- ADR 0001, ADR 0008, ADR 0009
