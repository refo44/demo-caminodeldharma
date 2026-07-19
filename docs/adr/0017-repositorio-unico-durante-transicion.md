# ADR 0017: Repositorio único durante la transición

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El proyecto necesita mantener el sitio estático en producción mientras se desarrolla WordPress. Dividir en dos repositorios fragmentaría historial, ADR, documentación (`docs/`) y criterios compartidos de diseño y contenido.

## Decisión

**Un solo repositorio Git** y **una sola base documental** (`docs/`) durante toda la transición y en el estado final del theme.

Estructura acordada (ADR 0014):

- `static/` — sitio público actual (producción durante Fase 3).
- `wordpress/` — theme y plugins propios versionados.
- `docs/` — requisitos, ADR, guías y registro de migración compartidos por ambas implementaciones.

No crear repositorio separado para WordPress salvo ADR futuro que lo justifique.

### Fuentes de verdad durante la transición (pre-corte WordPress)

| Ámbito | Fuente de verdad |
| ------ | ---------------- |
| Sitio público en producción | `static/` (o raíz en Fase 2 pre-reorg) |
| Futura implementación CMS | `wordpress/` |
| Decisiones, requisitos, criterios | `docs/` |

Estas fuentes **no son equivalentes**: `static/` manda en lo publicado hoy; cambios estructurales deben reflejarse también en `wordpress/` (ver `docs/migracion-static-wordpress.md`).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Dos repos (static + wp) | Duplica docs; divergencia de ADR y copy. |
| Submodule del theme | Complejidad innecesaria para el equipo. |
| Monorepo sin `docs/` compartido | Rompe política de cambios y trazabilidad. |

## Consecuencias

**Beneficios:**

- Un historial Git para toda la transición.
- ADR y documentación aplican a ambas implementaciones.
- Comparación directa static vs theme en el mismo clone.

**Riesgos:**

- Repo más grande; disciplina en despliegue (solo `static/` a producción — ADR 0015).

## Referencias

- ADR 0014, ADR 0015
- `docs/migracion-static-wordpress.md`
- `docs/17-orden-implementacion` § Transición
