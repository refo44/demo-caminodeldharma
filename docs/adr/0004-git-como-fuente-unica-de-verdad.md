# ADR 0004: Git como fuente única de verdad

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El sitio se despliega en Hostinger y puede editarse accidentalmente vía File Manager o FTP. Sin una regla explícita, surgen divergencias entre lo versionado y lo servido en producción. Los despliegues futuros (GitHub Actions + rsync, ADR 0006 y 0007) sobrescribirían cambios hechos solo en el servidor.

El proyecto ya versiona HTML, CSS, JS, assets, documentación y configuración Apache (`.htaccess` en repo; ver nota en `.gitignore` para entornos WordPress futuros).

## Decisión

El **repositorio Git** es la **única fuente de verdad** para código, assets del sitio, configuración de despliegue y documentación técnica.

- Todo cambio debe originarse en el repositorio (rama de feature → PR → `main`).
- `content-source/` es la fuente canónica del **copy editorial**; el sitio implementado en repo debe reflejarlo sin parafrasear.
- El servidor de producción es un **destino de despliegue**, no un entorno de edición (complementa ADR 0005).
- Releases de producción se asocian a commits/tags en `main` y a entradas en `VERSION` / `CHANGELOG.md`.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Edición directa en producción para hotfixes | Deriva irreproducible; se pierde en el siguiente deploy. |
| Google Drive / ZIP como fuente principal | Sin historial, diffs ni revisión por PR. |
| WordPress como única fuente (fase futura) | Aplica al contenido dinámico; markup, theme y assets siguen en Git. |

## Consecuencias

**Beneficios:**

- Trazabilidad completa de cambios.
- Despliegues reproducibles y rollback posible.
- Colaboración segura vía PRs.

**Riesgos:**

- Requiere disciplina del equipo; hotfixes urgentes deben entrar al repo antes o inmediatamente después del deploy.

**Trabajo futuro:**

- Automatizar despliegue desde `main` (ADR 0006).
- Documentar flujo en `CONTRIBUTING.md`.

## Referencias

- `docs/17-orden-implementacion` (Principios transversales)
- `README.md` (despliegue, VERSION, CHANGELOG)
- ADR 0005, ADR 0006, ADR 0007
