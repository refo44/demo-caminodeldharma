# ADR 0043: Trunk-based development con Conventional Branch y Conventional Commits

## Estado

Aceptada

## Fecha

2026-09-01

## Contexto

Fase 3 concentra estático, WordPress first-party, migración y CI (`test.yml`). Hasta
2026-09-01 se permitía push directo a `main`. Eso contradice la regla ya escrita en
[ADR 0004](0004-git-como-fuente-unica-de-verdad.md) («rama de feature → PR → `main`») y
dificulta revisión, gates de CI y trazabilidad.

Se adopta **trunk-based development**: `main` es el único tronco integrado; todo cambio
llega por Pull Request desde una rama de vida corta.

Para que humanos, agentes y CI lean la intención del cambio, los nombres de rama siguen
[Conventional Branch 1.1.0](https://conventionalbranch.org/) y los mensajes de commit
[Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/).

## Decisión

1. **`main` está protegida.** No se aceptan pushes directos. Integración solo vía Pull
   Request desde una rama prefijada.
2. **Trunk-based:** una rama de feature/fix/chore por unidad de trabajo; merge a `main`
   cuando el gate pasa; la rama se borra tras el merge.
3. **Conventional Branch** — forma `<type>/<description>` en minúsculas, guiones, sin
   espacios ni guiones bajos:
   - `feature/` o `feat/` — funcionalidad nueva
   - `fix/` o `bugfix/` — corrección
   - `hotfix/` — urgente sobre producción
   - `release/` — preparación de versión (p. ej. `release/v1.2.0`)
   - `chore/` — docs, CI, dependencias, tooling
   - Prefijos de agente IA cuando aplique: `cursor/`, `copilot/`, `claude/`, `codex/`,
     `ai/` ([Conventional Branch § AI Agent Source Prefixes](https://conventionalbranch.org/))
4. **Conventional Commits** — mensajes en **inglés** (regla del repo para Git):

   ```text
   <type>[optional scope]: <description>

   [optional body]

   [optional footer(s)]
   ```

   Tipos habituales: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`,
   `ci`, `chore`. Breaking changes: `!` tras el tipo/scope o pie `BREAKING CHANGE:`.
5. **Gate de merge en `main`:** checks obligatorios `php` y `css` de `.github/workflows/test.yml`;
   conversaciones del PR resueltas. Revisiones aprobatorias: 0 (repositorio unipersonal;
   el PR sigue siendo obligatorio).
6. **Etiquetas del PR:** cada Pull Request lleva **al menos una** etiqueta de GitHub relevante
   al cambio; **varias** cuando el PR abarca más de un ámbito. Guía de mapeo en
   [`docs/git-workflow.md`](../git-workflow.md).
7. **Despliegue de producción estática** sigue manual desde `main` (ADR 0015). La protección
   de rama no activa CD automático (ADR 0016).

Guía operativa: [`docs/git-workflow.md`](../git-workflow.md).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Seguir con push directo a `main` | Sin revisión ni historial limpio; CI solo en PR de forma inconsistente. |
| GitFlow (`develop` + releases largas) | Sobrecarga para un solo maintainer y entregas pequeñas (TBD). |
| Nombres de rama libres | No escala con agentes/CI; ya generaba ramas opacas (`fase3-wordpress`). |
| Conventional Commits en español | El repo exige inglés en mensajes Git; el cuerpo puede aclarar en español si hace falta. |

## Consecuencias

**Beneficios:**

- `main` siempre integrable y con CI verde antes del merge.
- Ramas y commits legibles para humanos, Sonar, Copilot y futuros changelogs.
- Alineación con ADR 0004 y con la guía de agentes.

**Limitaciones:**

- Ramas legacy sin prefijo (p. ej. `fase3-wordpress`) pueden seguir abiertas hasta
  merge; **nuevas ramas** deben cumplir Conventional Branch.
- No hay hook local obligatorio en el repo; la norma la refuerzan protección de rama,
  documentación y reglas de Cursor.

**Trabajo futuro:**

- Opcional: [commit-check-action](https://github.com/marketplace/actions/commit-check-action)
  en CI para rechazar ramas/commits inválidos antes del merge.

## Referencias

- [Conventional Branch 1.1.0](https://conventionalbranch.org/)
- [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/)
- [ADR 0004](0004-git-como-fuente-unica-de-verdad.md), [ADR 0015](0015-despliegue-manual-temporal.md), [ADR 0016](0016-automatizacion-ci-cd-pospuesta.md), [ADR 0038](0038-pruebas-tdd-phpunit-sonar.md)
- [`docs/git-workflow.md`](../git-workflow.md), [`CONTRIBUTING.md`](../../CONTRIBUTING.md)
