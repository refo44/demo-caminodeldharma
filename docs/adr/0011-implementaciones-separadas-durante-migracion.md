# ADR 0011: Implementaciones separadas durante la migración

## Estado

Sustituida por [ADR 0014](0014-monorepo-static-wordpress.md) (layout con carpeta `static/`). Los principios de no mezclar implementaciones siguen vigentes.

## Fecha

2026-07-19

## Contexto

La Fase 3 prevé adaptar la maqueta estática a un theme WordPress (ADR 0001, ADR 0002). Durante meses convivirían dos implementaciones del mismo sitio. Mezclar en la misma raíz archivos como `index.html` y `front-page.php`, o `comunidad/index.html` y `page-comunidad.php`, genera:

- dos fuentes de verdad visuales;
- riesgo de editar la versión equivocada;
- duplicación de contenido y assets;
- confusión en despliegue (¿qué se sube a `public_html`?).

El documento `13-static-file-structure` mencionaba `theme-camino-del-dharma/` en la raíz del repo; conviene fijar una estructura explícita **antes** de iniciar la migración para evitar reorganizaciones costosas.

## Decisión

**No mezclar** la implementación estática y el theme WordPress en el mismo nivel de directorio.

### Durante la migración (Fase 3 en curso)

```text
camino-del-dharma/
├── index.html, 404.html, …     # Implementación estática (referencia + producción actual)
├── comunidad/, practica/, …
├── assets/                       # Assets del sitio estático en producción
├── docs/                         # Documentación compartida
├── scripts/
└── wordpress/
    └── wp-content/
        └── themes/
            └── camino-del-dharma/
                ├── front-page.php
                ├── functions.php
                ├── assets/       # Copia/sincronización desde raíz durante migración
                └── …
```

Reglas:

| Implementación | Ubicación | Rol |
| -------------- | --------- | --- |
| **Estática** | Raíz del repo | Referencia visual y funcional congelada (ADR 0001); sitio en producción hasta el corte |
| **WordPress** | `wordpress/wp-content/themes/camino-del-dharma/` | Adaptación al motor de contenido; staging hasta validación |
| **Documentación** | `docs/` | Compartida; incluye ADR y orden de implementación |

- Los assets pueden **copiarse o sincronizarse** del estático al theme durante la migración; no editar assets solo en el theme sin reflejar el cambio en la fuente acordada (raíz mientras el estático siga siendo referencia).
- **Prohibido** colocar plantillas PHP junto a `index.html` en la raíz.
- Despliegue estático (Fase 4 actual) sigue usando la raíz; el theme no se despliega a producción hasta el corte.

### Tras migración validada (post-Fase 3)

Cuando WordPress esté en producción y pase Fase 2.5 en staging:

1. La **implementación estática deja de mantenerse** como sitio activo.
2. El theme WordPress pasa a ser la **única implementación activa**.
3. Reorganizar el repositorio (ADR futuro o actualización de `13`) hacia una de estas formas:

```text
# Opción A — repo solo del theme
theme/
    assets/
    front-page.php
    …

# Opción B — theme dentro de árbol wp-content
wp-content/themes/camino-del-dharma/
```

La opción concreta depende de si el repo versiona solo el theme o una instalación WordPress completa.

4. Retirar del despliegue los HTML estáticos de la raíz (o archivar en rama/tag de referencia).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Theme en raíz mezclado con HTML estático | Confusión crónica; dos fuentes de verdad (ver Contexto). |
| `theme-camino-del-dharma/` en raíz (doc 13 v1.9) | Mejor que mezclar archivos, pero compite visualmente con el sitio estático desplegado; `wordpress/` deja claro el alcance. |
| Repo separado para WordPress | Fragmenta historial; duplica docs y ADR; innecesario para este equipo. |
| Eliminar estático al iniciar Fase 3 | Pierde referencia de validación y rollback visual. |

## Consecuencias

**Beneficios:**

- Roles claros: raíz = estático; `wordpress/` = CMS.
- Despliegues independientes durante transición.
- Paridad verificable plantilla a plantilla contra HTML congelado.

**Riesgos:**

- Duplicación temporal de assets; requiere script o checklist de sincronización.
- Reorganización post-migración es trabajo explícito (no automático).

**Trabajo futuro:**

- Actualizar `12-theme-file-structure` y `13-static-file-structure` con rutas definitivas.
- Script opcional `scripts/sync-assets-to-theme.sh`.
- ADR de corte de producción estática → WordPress.

## Referencias

- `docs/13-static-file-structure` §1 (fases de estructura)
- `docs/12-theme-file-structure`
- `docs/17-orden-implementacion` §2.7, Fase 3
- ADR 0001, ADR 0002, ADR 0004
