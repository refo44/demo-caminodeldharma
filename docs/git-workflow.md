# Git workflow — trunk-based + Conventional Branch + Conventional Commits

**Decisión:** [ADR 0043](adr/0043-trunk-based-conventional-branch-commits.md) (2026-09-01).

## Modelo

```text
main (protected trunk)
  ↑
  PR only ← <type>/short-description
```

- **`main`** es el tronco. Integración **solo por Pull Request**.
- **Trunk-based:** ramas cortas, una unidad lógica de trabajo, merge y borrado.
- **Producción estática** se despliega manual desde `main` (ADR 0015); merge ≠ deploy.

## Protección de `main` (GitHub)

| Regla | Valor |
| ----- | ----- |
| Pull request required | Sí |
| Required status checks | `php`, `css` (`.github/workflows/test.yml`) |
| Require branches up to date | Sí (`strict`) |
| Resolve conversations | Sí |
| Force push / delete branch | No |
| Required approving reviews | 0 (maintainer único; el PR sigue obligatorio) |

## Nombres de rama — [Conventional Branch](https://conventionalbranch.org/)

Forma: `<type>/<description>`

| Prefijo | Uso |
| ------- | --- |
| `feature/` o `feat/` | Funcionalidad nueva |
| `fix/` o `bugfix/` | Bug fix |
| `hotfix/` | Fix urgente |
| `release/` | Preparar versión (`release/v1.2.0`) |
| `chore/` | Docs, CI, deps, tooling |
| `cursor/`, `copilot/`, `claude/`, `codex/`, `ai/` | Trabajo iniciado por un agente IA |

**Reglas:** minúsculas; solo `a-z`, `0-9`, guiones; sin espacios, `_` ni guiones consecutivos. En
`release/` se permiten puntos en la versión.

**Ejemplos válidos:**

```text
feature/fse-contact-form-block
fix/payload-hash-bookkeeping-keys
chore/bump-docker-image-tags
cursor/pr3-copilot-review-fixes
release/v1.0.36
```

**Ejemplos inválidos:** `Feature/Foo`, `feature/new--login`, `fase3-wordpress` (legacy;
no usar en ramas nuevas).

## Commits — [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/)

Mensajes en **inglés** (convención del repo para Git).

```text
<type>[optional scope]: <imperative description>

[optional body]

[optional footer]
```

**Ejemplos:**

```text
feat(theme): add safe asset version helper for enqueue

fix(core): exclude _source_key from payload content hash

docs: record trunk-based workflow in ADR 0043

ci: require php and css checks on main merges
```

Breaking change:

```text
feat(migrate)!: drop legacy source key from hash input

BREAKING CHANGE: payload _source_hash values change; re-run extract-payload.
```

En PRs con squash merge, el mensaje del squash debe seguir Conventional Commits.

## Flujo diario

```bash
git checkout main
git pull origin main
git checkout -b feature/short-description

# … cambios + tests locales …
npm run lint:css          # si tocaste CSS
composer test             # si tocaste PHP
composer lint:phpcs       # antes del push si tocaste plugin/theme

git add …
git commit -m "fix(scope): describe the change"
git push -u origin feature/short-description

gh pr create --base main --title "fix(scope): describe the change" --body "…" --label bug
```

Tras merge: borrar la rama remota y local.

## Etiquetas del Pull Request

**Obligatorio:** al menos **una** etiqueta relevante en cada PR. **Varias** cuando el cambio
abarca más de un tipo (p. ej. `enhancement` + `documentation`).

| Etiqueta | Cuándo |
| -------- | ------ |
| `documentation` | Docs, ADR, README, guías, `.cursor/rules` |
| `enhancement` | Feature nueva (`feature/…`, `feat/…`) |
| `bug` | Fix (`fix/…`, `bugfix/…`, `hotfix/…`) |
| `help wanted` | Falta revisión o input del maintainer |
| `question` | Decisión pendiente antes del merge |

```bash
gh pr create --label documentation …
gh pr edit <n> --add-label enhancement,documentation
gh label list
```

## Agentes (Cursor, Copilot, etc.)

1. **Nunca** commitear ni pushear directamente a `main`.
2. Crear rama con prefijo adecuado (`cursor/…` para Cursor).
3. Commits Conventional Commits en inglés.
4. Abrir PR hacia `main`; **añadir al menos una etiqueta relevante** (más de una si aplica).
5. Esperar `php` + `css` verdes.
6. Resolver hilos de review antes del merge.

## Validación local antes del PR

| Cambio | Comando mínimo |
| ------ | -------------- |
| CSS estático o theme | `npm run lint:css` |
| PHP plugin/theme/tests | `composer test` y `composer lint:phpcs` |
| Solo docs | revisión humana; CI `php`/`css` igual corre en el PR |

Ver también [`docs/guia-pruebas-plugin-theme-fse.md`](guia-pruebas-plugin-theme-fse.md) (ADR 0038).

## Referencias

- [`CONTRIBUTING.md`](../CONTRIBUTING.md)
- [`AGENTS.md`](../AGENTS.md), [`CLAUDE.md`](../CLAUDE.md)
- [ADR 0043](adr/0043-trunk-based-conventional-branch-commits.md)
