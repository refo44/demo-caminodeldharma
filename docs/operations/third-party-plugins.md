# Operaciones — Plugins de terceros

Registro durable exigido por FABLE5 v2.3 §12. Política: **native-first** (ADR 0025) —
1) APIs de WordPress core y bloques Gutenberg; 2) código first-party en
`camino-del-dharma-core`; 3) plugin de terceros **solo** con ADR aceptada que lo apruebe.

| | |
| --- | --- |
| **Versión** | 1.0 |
| **Fecha** | 2026-08-31 |
| **Estado** | Vigente |

## Aprobados

| Plugin | ADR | Estado | Condiciones |
| --- | --- | --- | --- |
| Contact Form 7 | ADR 0026 | Aprobado; **gated en producción** (ADR 0039) | Destino `caminodeldharma1@gmail.com`. Local/staging con datos sintéticos permitido. Producción bloqueada hasta: (a) `/privacidad` actualizada para describir un formulario con envío server-side (el aviso vigente dice que el formulario no envía — no reescribirlo fuera de ese cambio aprobado), y (b) revisión legal. Entrega real verificada en staging Hostinger antes del release. El corte puede proceder con CF7 ausente/deshabilitado si la matriz y el checklist lo registran. |

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
