# Operaciones — Plugins de terceros

Registro durable de plugins de terceros. Política: **native-first** (ADR 0025) —
1) APIs de WordPress core y bloques Gutenberg; 2) código first-party en
`camino-del-dharma-core`; 3) plugin de terceros **solo** con ADR aceptada que lo apruebe.

| | |
| --- | --- |
| **Versión** | 1.3 |
| **Fecha** | 2026-09-01 |
| **Estado** | Vigente |

## Aprobados

| Plugin | ADR | Estado | Condiciones |
| --- | --- | --- | --- |
| Contact Form 7 | ADR 0026 | Aprobado; **elegible en el corte** (ADR 0041 / OWN-018). **Entrega = gate** (ADR 0045 / OWN-033) | Destino de producción `caminodeldharma1@gmail.com`. Prueba técnica de staging puede ir a `refo44@gmail.com`. El form público no queda en Gmail personal. Revisión legal **no** es prerrequisito. Cortar con CF7 off ya no es el default. |

## Versiones instaladas por entorno

Registro exigido por la regla de versionado de abajo: el código no está en Git, así que la
versión que corre cada entorno solo consta aquí.

| Entorno | Plugin | Versión | Fecha | Notas |
| --- | --- | --- | --- | --- |
| Local (Docker, ADR 0023) | Contact Form 7 | **6.1.7** | 2026-08-31 (WU-09) | Instalado con `wp plugin install contact-form-7 --activate`; vive en el volumen `wp_data`, nunca en el árbol del repositorio. Formulario provisionado con `wp cdd-core contact provision --apply`. `wp_mail()` devuelve `false` (sin MTA en el contenedor): la validación está probada, la **entrega no**. |
| Staging Hostinger | Contact Form 7 | — | — | Pendiente (OWN-035). Prueba técnica: `refo44@gmail.com`. Gate de corte: buzón comunitario (ADR 0045). |
| Producción | Contact Form 7 | — | — | Pendiente del corte. |

## Procedimiento por entorno (WU-09)

El orden importa: ADR 0041 punto 3 exige que el aviso describa un envío real **antes** de
activar el formulario. `wp cdd-core contact provision` lo comprueba y rehúsa si no se cumplió.

```bash
wp plugin install contact-form-7 --activate
wp cdd-core migrate convert --apply   # actualiza /privacidad y pone el bloque en /contacto
wp cdd-core contact provision --apply # crea el formulario y su correo
```

`provision` es create-missing-only: lo que un editor cambie luego en wp-admin no se pisa. Tras
instalarlo, anotar la versión en la tabla de arriba.

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
| 2026-08-31 | WU-09: CF7 **6.1.7** instalado y provisionado en el entorno local; registro de versiones por entorno y procedimiento de instalación. Entrega real sigue `Unverified`. |
