# Fuentes — Camino del Dharma

Según **Identidad CAMINO DEL DHARMA** (manual de marca) y `docs/02-identidad-corporativa.md`.

## Incluidas en el repo

- **downtown-demo-regular.ttf** — Downtown v1.01 por Nelson Fraga & Roman Tunik.  
  Uso: títulos y headings. Licencia: 100% Free (DaFont). Atribución recomendada (CC BY 4.0 en algunas fuentes).

## Añadir manualmente (manual de marca)

- **MarloweEscapade** — Fuente de display del logo (FaceType/MyFonts, comercial).  
  Si tienes el archivo del manual de marca o licencia, colócalo aquí como:
  - `marlowe-escapade.woff2` (recomendado para web), o
  - `marlowe-escapade.woff` o `marlowe-escapade.ttf`  
  El CSS ya tiene la regla `@font-face`; al añadir el archivo se cargará automáticamente.

## Uso en CSS

- `--font-heading`: Downtown DEMO Regular (y MarloweEscapade como fallback si está disponible).
- `--font-display`: MarloweEscapade (fallback a serif si no está el archivo).
