# Decision log

- 2026-07-19 Phase 0: Workspace created; commit be896db2 matches expected_commit; full-scope audit proceeds.
- 2026-07-19 Phase 1: Lighthouse/axe unavailable and not installed (audit-only rule); browser API + scripted checks substitute, recorded in limitations.
- 2026-07-19 Phase 2: 13 URLs ≪ max_urls → full coverage instead of sampling; probes added for 404, entry points, machine files.
- 2026-07-19 Phase 3: Performance runs kept (3 desktop + mobile) but paint metrics marked unreliable due to pane throttling; no timing-based severity claims allowed into findings.
- 2026-07-19 Phase 4: First link-integrity script produced false positives (meta content parsed as URLs); discarded and replaced by attribute-scoped v2 (EVID-0015). The false-positive artifact kept for transparency.
- 2026-07-19 Phase 4: styles.css 404 probe was an auditor guess, not a site reference — excluded from findings.
- 2026-07-19 Phase 4A: HSTS basic host-only = ACTIVATE_HSTS_NOW; includeSubDomains REJECTED; preload REJECTED. Rationale in hsts-decision.md.
- 2026-07-19 Post-audit: HSTS deployment strategy revised to STAGED per ADR 0018 — Phase 1 max-age=604800 (7d) during static→WordPress transition; Phase 2 max-age=31536000 after WordPress stable. Full-year day-one remains approved but not preferred.
- 2026-07-19 Phase 6: FUNC-001 severity HIGH (not CRITICAL): site remains reachable through working WhatsApp/mail channels; the silent-loss failure mode justifies HIGH. Cross-validated via two independent sources (markup + full JS review).
- 2026-07-19 Phase 6: PRIV-001 marked REMEDIATION_BLOCKED: consent approach and policy text are organizational decisions; task created as BLOCKED.
- 2026-07-19 Phase 7: HSTS activation task placed in WAVE-1 (high-value, low-risk) despite being security-domain work: it is the audit's primary objective, has no prerequisites, and is a one-line reversible change.
- 2026-07-19 Phase 7: Preload gets no task (REJECTED, not a candidate); includeSubDomains gets a BLOCKED evaluation task (TASK-0012) to make the future decision path explicit.
