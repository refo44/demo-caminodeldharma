# Operaciones — Plugins de terceros

Registro durable exigido por FABLE5 v2.4 §12. Política: **native-first** (ADR 0025) —
1) APIs de WordPress core y bloques Gutenberg; 2) código first-party en
`camino-del-dharma-core`; 3) plugin de terceros **solo** con ADR aceptada que lo apruebe.

| | |
| --- | --- |
| **Versión** | 1.1 |
| **Fecha** | 2026-08-31 |
| **Estado** | Vigente |

## Aprobados

| Plugin | ADR | Estado | Condiciones |
| --- | --- | --- | --- |
| Contact Form 7 | ADR 0026 | Aprobado; **elegible en el corte** (ADR 0041 / OWN-018) | Destino `caminodeldharma1@gmail.com`. Local/staging con datos sintéticos en WU-09. Antes de activarlo en un entorno WordPress: aplicar el delta de `/privacidad` (párrafos del formulario). Revisión legal **no** es prerrequisito. Entrega real verificada en staging Hostinger antes del release. Fallback operativo: CF7 deshabilitado + WhatsApp/correo, registrado en matriz y checklist. |

## Prohibidos sin ADR nueva

ACF, Elementor, Divi, Yoast, RankMath, suites de optimización, plugins de analítica,
plugins de calendario, plugins de lightbox, y cualquier antispam adicional a CF7.

## Reglas de versionado

- El código de plugins de terceros **no** se versiona en Git (`.gitignore`).
- Instalación/actualización: manual en cada entorno; registrar versión instalada aquí
  cuando exista un entorno real.

## Historial

| Fecha | Cambio |
| --- | --- |
| 2026-08-31 | Creación del registro (WU-00). CF7 único aprobado; sin entornos WordPress aún. |
| 2026-08-31 | ADR 0041 / OWN-018: CF7 deja de estar gated por revisión legal; elegible en el corte. |
