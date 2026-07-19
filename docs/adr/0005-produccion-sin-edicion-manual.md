# ADR 0005: Producción sin edición manual

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Hostinger expone File Manager, FTP y, en fase WordPress, el panel de administración. Es tentador corregir un typo, subir una imagen o tocar `.htaccess` directamente en `public_html`. Esos cambios:

- no quedan en Git (ADR 0004);
- se pierden en el siguiente despliegue con `--delete` (ADR 0007);
- dificultan auditorías, rollback y onboarding.

## Decisión

**No se editarán archivos del sitio directamente en el servidor de producción.**

Flujo obligatorio:

1. Cambio en repositorio local o remoto (rama → PR).
2. Validación (`npm run lint:css`, QA según fase).
3. Despliegue desde artefacto generado en CI o ZIP documentado en README.
4. Smoke test post-despliegue.

Excepciones **temporales** (deben revertirse entrando el cambio al repo en el mismo día):

- Emergencia de caída del sitio cuando el pipeline aún no esté operativo.
- Debe documentarse en `CHANGELOG.md` o issue con el diff a incorporar.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Producción como entorno de edición habitual | Deriva crónica; imposible reproducir estado. |
| Staging en Hostinger sin Git | Mejora parcial; sigue sin trazabilidad si se edita allí. |
| Solo WordPress admin para contenido (fase 3) | Válido para entradas y eventos; theme, CSS y assets siguen en Git. |

## Consecuencias

**Beneficios:**

- Estado de producción siempre explicable desde un commit.
- Despliegues con `rsync --delete` seguros (ADR 0007).
- Menos sorpresas en auditorías SEO y accesibilidad.

**Riesgos:**

- Latencia mayor para cambios triviales hasta automatizar CI/CD.
- Requiere formación de editores de contenido en flujo WordPress vs archivos estáticos.

**Trabajo futuro:**

- Completar pipeline GitHub Actions (ADR 0006).
- Definir entorno staging opcional sincronizado desde Git.

## Referencias

- `docs/17-orden-implementacion` Fase 4
- `README.md` (procedimiento ZIP actual)
- ADR 0004, ADR 0006, ADR 0007
