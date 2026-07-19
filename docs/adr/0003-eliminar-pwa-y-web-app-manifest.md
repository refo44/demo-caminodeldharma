# ADR 0003: Eliminar PWA y Web App Manifest

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Durante el desarrollo inicial se evaluó un Web App Manifest (`site.webmanifest`) con favicons completos. Un manifest con `"display": "standalone"` (o enlazado desde el HTML) es la señal estándar para que navegadores como Chrome ofrezcan instalación como aplicación, a veces de forma intrusiva.

Camino del Dharma es un **sitio web tradicional** de acogida y lectura, no una app instalable. Los documentos de voz y UX (`07`, `01`, `18`) priorizan calma, claridad y ausencia de triggers invasivos. El manifest contradice esa orientación.

## Decisión

**No implementar PWA** ni Web App Manifest en ninguna fase del proyecto, salvo decisión explícita futura documentada en un nuevo ADR.

Concretamente:

- No crear ni desplegar `site.webmanifest` / `manifest.json`.
- **Prohibido** `<link rel="manifest">` en el `<head>` de cualquier página.
- No usar Service Worker ni flujo de instalación PWA.
- Mantener favicons (`favicon.ico`, `favicon.svg`, `apple-touch-icon`) independientes del manifest.
- En `.htaccess`, responder **410 Gone** a `/site.webmanifest` por cachés o despliegues anteriores.

Los usuarios pueden seguir añadiendo manualmente un acceso directo desde el navegador; no se promueve ni se bloquea.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| PWA completa (SW + manifest standalone) | Comportamiento de app instalable; intrusivo para este producto. |
| Manifest con `"display": "browser"` | Señal innecesaria; riesgo de confusión con PWA en auditorías. |
| Botón «Añadir a inicio» con `beforeinstallprompt` | CTA adicional no alineado con la sobriedad del sitio. |
| Mantener manifest «por si acaso» | Contradice identidad; añade superficie de mantenimiento sin beneficio claro. |

## Consecuencias

**Beneficios:**

- Experiencia web clásica, predecible y alineada con la voz del proyecto.
- Menos archivos y reglas de despliegue.
- Checklist pre-lanzamiento simplificado (sin manifest ni SW).

**Riesgos:**

- Sin capacidades offline (no requeridas por el producto).
- Si en el futuro se desea instalación, requiere ADR nuevo y diseño de UX explícito.

**Trabajo futuro:**

- Verificar en cada release: ausencia de `<link rel="manifest">` y 410 en `/site.webmanifest`.
- No incluir manifest en ZIP de despliegue (README).

## Referencias

- `docs/15-assets-strategy` §11
- `docs/17-orden-implementacion` (checklist pre-lanzamiento)
- `.htaccess` (regla 410 para `site.webmanifest`)
- `CHANGELOG.md` (v1.0.x — eliminación de PWA)
