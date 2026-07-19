# ADR 0014: Monorepo con carpeta static/ al iniciar Fase 3

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

ADR 0011 (Aceptada → **Sustituida** por este ADR) estableció separar implementación estática y theme WordPress, con el estático en la **raíz** del repo durante la migración.

Tras ratificar WordPress (ADR 0012) y definir administración por terceros, se refine la estructura del monorepo: la referencia estática congelada debe vivir en una carpeta **`static/`** explícita, no mezclada con archivos de proyecto (`docs/`, `scripts/`, `.github/`) ni con plantillas PHP.

**Estado actual (Fase 2):** el sitio estático está en la **raíz** del repo y es lo desplegado en producción. No se reorganiza hasta iniciar Fase 3.

## Decisión

### Al iniciar Fase 3 (primer paso de migración)

Reorganizar el repositorio hacia monorepo con dos implementaciones delimitadas:

```text
camino-del-dharma/
├── static/                         # Referencia congelada (ex raíz estática)
│   ├── index.html
│   ├── 404.html
│   ├── comunidad/
│   ├── practica/
│   ├── eventos/
│   ├── assets/
│   ├── robots.txt
│   ├── sitemap.xml
│   └── …
├── wordpress/
│   └── wp-content/
│       ├── themes/
│       │   └── camino-del-dharma/
│       │       ├── assets/
│       │       ├── inc/
│       │       ├── parts/
│       │       ├── front-page.php
│       │       ├── page-comunidad.php
│       │       ├── functions.php
│       │       └── style.css
│       └── plugins/
│           └── camino-del-dharma-core/   # Solo si hay plugin propio
├── docs/
│   └── adr/
├── scripts/
├── .github/
└── README.md
```

Reglas:

| Carpeta | Rol |
| ------- | --- |
| **`static/`** | **Producción pública** durante Fase 3; recibe mantenimiento. Referencia de paridad para el theme. |
| **`wordpress/`** | Theme y plugins propios; staging hasta el corte. No core WP, credenciales ni uploads. |
| **`docs/`**, **`scripts/`** | Compartidos; no se despliegan a producción. |

- **Prohibido** mezclar `index.html` y `front-page.php` en el mismo directorio.
- Despliegue producción: **solo** `static/` (ADR 0015). WordPress en staging separado.
- Registro de diferencias: `docs/migracion-static-wordpress.md`.
- Assets: sincronizar de `static/assets/` al theme durante desarrollo del theme.

### Tras migración validada

1. Implementación estática **deja de mantenerse** como sitio activo (`static/` archivada en tag o rama; no desplegar).
2. Theme WordPress = **única implementación activa**.
3. Repo puede simplificarse a `wordpress/wp-content/themes/camino-del-dharma/` como raíz efectiva del código del sitio (o `theme/` según ADR 0011 post-corte).

### Despliegue durante transición

- **Pre-corte:** despliegue estático desde contenido de `static/` (actualizar README y pipeline al mover archivos).
- **Post-corte:** despliegue solo del theme según ADR 0013.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Estático permanente en raíz mezclado con `docs/` | Confunde implementación vs tooling; ADR 0011 inicial. |
| Dos repos (estático + WP) | Fragmenta historial y ADR. |
| Eliminar `static/` al iniciar Fase 3 | Pierde referencia de paridad visual. |

## Consecuencias

**Beneficios:**

- Límites claros en el monorepo.
- `static/` como contrato de aceptación plantilla a plantilla.
- `wordpress/` contiene solo lo versionado del CMS custom.

**Riesgos:**

- Reorganización única al iniciar Fase 3 (actualizar CI, README, ZIP).
- Duplicación temporal de assets entre `static/` y theme.

**Trabajo futuro:**

- Script `scripts/move-static-to-folder.sh` o commit de reorg documentado.
- Actualizar `13-static-file-structure` y `17-orden-implementacion` §2.7.
- Tag Git pre-reorg como punto de rollback.

## Referencias

- Sustituye layout de rutas de ADR 0011 (estado → Sustituida)
- ADR 0012, ADR 0013
- `docs/13-static-file-structure`
- `docs/17-orden-implementacion` §2.7, Fase 3
