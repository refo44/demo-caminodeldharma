# Scope

- Project: Camino del Dharma
- Website: https://caminodeldharma.org (production)
- Allowed domains: caminodeldharma.org
- Excluded paths: none
- Source: DOCS/demo-caminodeldharma @ be896db2214c4dafdc8adad89f8496421c8b6071 (main, clean)
- Authentication: NONE (public site)
- Security test level: passive only
- Form submission: NOT permitted
- Account creation: NOT permitted
- Write/deploy: NOT permitted — audit is read-only against site and repo
- Remediation mode: external_agent_task_backlog (no fixes applied in this session)
- Max URLs: 500; max pages per template: 5; performance runs per profile: 3
- Report language: Spanish
- HSTS decision required: yes
  - Candidate: `Header always set Strict-Transport-Security "max-age=31536000"` (evaluated; superseded operationally by staged rollout ADR 0018 — Phase 1 `max-age=604800` while the temporary static site is live)
  - includeSubDomains candidate: false; preload candidate: false
  - Known subdomains: [] (none declared)
  - Config path: DOCS/demo-caminodeldharma/.htaccess

## Non-goals

- No implementation, deploy, commit, or configuration change.
- No active security testing (no fuzzing, no exploitation, no form submission).
- No claims about third-party AI platform inclusion.
