# Fuentes — Camino del Dharma

Referencia: **Identidad CAMINO DEL DHARMA** (manual de marca) y `docs/02-identidad-corporativa.md`.

## Estructura

```
fonts/
├── README.md
├── downtown/
│   └── downtown-demo-regular.ttf    # Headings
├── marlowe-escapade/
│   ├── marlowe-escapade.woff2       # Display / logo (preferido)
│   ├── marlowe-escapade.woff
│   └── marlowe-escapade.ttf
└── licenses/
    └── OnlineWebFonts-CC-BY-4.0.txt # Atribución MarloweEscapade e iconos
```

- **Una carpeta por familia:** cada fuente en su propio directorio, con nombres de archivo claros (sin espacios ni hashes).
- **Licencias:** textos de atribución en `licenses/`; el sitio muestra el crédito en el footer.

## Fuentes

| Familia           | Uso en el sitio        | Origen / licencia                    |
|-------------------|------------------------|--------------------------------------|
| Downtown          | `--font-heading` (h1, h2, h3) | DaFont, 100% Free; atribución recomendada |
| MarloweEscapade   | `--font-display` (nombre del sitio en header) | OnlineWebFonts, CC BY 4.0; crédito en footer |

## Uso en CSS

En `assets/css/main.css`:

- `--font-heading`: "Downtown DEMO Regular", "MarloweEscapade", serif
- `--font-display`: "MarloweEscapade", serif
- `--font-body`: system-ui, sans-serif

Las rutas `@font-face` apuntan a `fonts/<familia>/<archivo>`. No cambiar nombres de carpeta ni de archivo sin actualizar `main.css`.
