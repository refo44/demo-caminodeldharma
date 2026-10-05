# Git workflow (trunk-based)

Canonical guide: `docs/git-workflow.md`. Decision: ADR 0043.

## Branch names

Follow [Conventional Branch](https://conventionalbranch.org/) purpose prefixes only.
Form: `<type>/<description>` in lowercase, hyphens only.

Allowed:

- `feature/` or `feat/`
- `fix/` or `bugfix/`
- `hotfix/`
- `release/`
- `chore/`

Forbidden in this repository: every AI Agent Source Prefix from
[Conventional Branch](https://conventionalbranch.org/), including `cursor/`,
`copilot/`, `claude/`, `codex/`, `ai/`, and any prefix that section adds later.
Also forbidden: any other prefix, and an unprefixed name.

Claude, Cursor, and every other agent use the purpose prefix that matches the change.
Do not put the agent name in the branch.

## Always

- `main` is protected. Never commit, push, or merge directly to `main`.
- Run `git fetch origin main` before creating a branch and before every merge of `main`.
- Branch from `origin/main`.
- Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) in English.
