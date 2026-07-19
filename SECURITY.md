# Security Policy

## Supported versions

| Version | Supported |
| ------- | --------- |
| Latest release on `main` | Yes |
| Older tagged releases | Best effort |

## Reporting a vulnerability

If you discover a security issue (XSS, misconfiguration, exposed credentials, etc.):

1. **Do not** open a public GitHub issue for sensitive reports.
2. Email **caminodeldharma1@gmail.com** with:
   - Description of the issue
   - Steps to reproduce
   - Impact assessment (if known)
   - Affected URL or file path

We aim to acknowledge reports within **7 days** and provide a remediation plan when applicable.

## Scope

In scope:

- Site served at `https://caminodeldharma.org`
- Repository code: HTML, CSS, JS, `.htaccess`, deployment workflows
- Third-party embeds and external links configured in the project

Out of scope:

- Social media accounts (Facebook, Instagram)
- Hostinger platform vulnerabilities (report to Hostinger)
- WordPress core/plugins (when deployed — report separately)

## Safe harbor

Good-faith security research that respects user privacy and avoids data destruction is appreciated. Do not access data that is not yours, perform denial-of-service tests, or modify production without coordination.

## Known considerations

- HSTS is intentionally disabled pending audit (ADR 0010).
- Production must not be edited manually; deploy from Git (ADR 0004, ADR 0005).

See `docs/adr/` for architectural security decisions.
