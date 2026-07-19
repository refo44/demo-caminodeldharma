# ADR 0013: Fuentes de verdad duales y alcance del despliegue

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

Con WordPress ratificado como CMS (ADR 0012), el proyecto pasa a tener **dos dominios de verdad** distintos:

- **Código y presentación** — theme, CSS, JS, plantillas, plugins propios (versionados en Git).
- **Contenido editorial** — páginas, entradas, eventos, usuarios, medios subidos por administradores (base de datos y `wp-content/uploads/`).

ADR 0004 establece Git como fuente única de verdad para el **repositorio**. ADR 0007 define `rsync --delete` hacia producción, válido mientras el sitio estático ocupa `public_html` entero.

Aplicar `rsync --delete` sobre todo `public_html` **después** de WordPress borraría uploads, plugins instalados en servidor, cachés y archivos generados por el CMS. Eso es inaceptable.

## Decisión

### Dos fuentes de verdad complementarias

| Dominio | Fuente de verdad | Qué incluye |
| ------- | ---------------- | ----------- |
| **Código** | Git (repo) | Theme, CSS/JS, plantillas PHP, plugins propios, `.htaccess` de código, documentación, scripts, pipeline |
| **Contenido** | WordPress (BD + uploads) | Páginas, entradas, eventos, usuarios, opciones editoriales, imágenes subidas por administradores |

Reglas:

- Cambios de **código** → Git → PR → despliegue del theme (ADR 0005).
- Cambios **editoriales** → panel WordPress; **no** editar HTML en Git para contenido dinámico post-corte.
- Backups de producción WordPress incluyen **base de datos** y **`wp-content/uploads/`** además del theme.

### Alcance del despliegue rsync

**Fase estática (actual):** ADR 0007 sigue vigente — sync del artefacto estático a `public_html` (raíz del sitio).

**Fase WordPress (post-corte):** `rsync --delete` **solo** al directorio del theme en el servidor:

```text
origen:  wordpress/wp-content/themes/camino-del-dharma/
destino: ~/domains/caminodeldharma.org/public_html/wp-content/themes/camino-del-dharma/
```

**Nunca** sincronizar con `--delete` sobre:

- `wp-content/uploads/`
- `wp-core/` / raíz de WordPress (core no versionado en Git)
- `wp-config.php`
- plugins de terceros instalados en servidor (salvo plugins propios versionados en repo)
- cachés, backups, logs

Plugins propios (p. ej. `camino-del-dharma-core`) se versionan en `wordpress/wp-content/plugins/` y se despliegan con rsync **sin `--delete`** en la carpeta de plugins de terceros, o con sync acotado solo a la carpeta del plugin propio.

### Lo que no se incluye en Git

- Núcleo completo de WordPress.
- `wp-config.php` ni credenciales.
- `uploads/` de producción.
- Cachés, backups temporales.
- Plugins de terceros instalables de forma reproducible (documentar lista en `CONTRIBUTING.md` o doc de ops).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Git como única fuente para todo (contenido en Markdown) | Rechazado en ADR 0012; administradores no técnicos. |
| rsync de todo `public_html` con `--delete` | Borra uploads y estado WordPress. |
| Editar theme en producción vía WP admin | Viola ADR 0005; deriva no versionada. |
| Deploy solo manual de ZIP del theme | Válido como respaldo; rsync preferido (ADR 0007). |

## Consecuencias

**Beneficios:**

- WordPress administrable por terceros sin renunciar al control técnico del diseño.
- Despliegues de código seguros que no destruyen contenido editorial.
- Separación clara de responsabilidades para el equipo técnico vs editores.

**Riesgos:**

- Dos pipelines mentales: «deploy de theme» vs «contenido en WP».
- Entornos staging/producción deben tener BD y uploads propios o sincronización documentada.

**Trabajo futuro:**

- Actualizar `.github/workflows/deploy.yml` con target acotado al theme cuando WordPress esté en producción.
- Script `scripts/deploy-theme.sh` con rsync scoped.
- Política de backups: BD + uploads + verificación de restore.

## Referencias

- ADR 0004, ADR 0005, ADR 0007, ADR 0012
- `docs/03-wordpress-content-model`
- `docs/17-orden-implementacion` Fase 4
- `.github/workflows/deploy.yml`
