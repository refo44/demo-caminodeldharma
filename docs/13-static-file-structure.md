# Camino del Dharma — Estructura de archivos estáticos

**Geografía del proyecto: repositorio y archivos estáticos**

Define dónde viven los archivos del proyecto: documentación, contenido fuente, implementación estática, theme WordPress y assets.

**Depende de:** `12-theme-file-structure`, `15-assets-strategy`. **Referencia:** `16-content-source-inventario`, `adr/0014-monorepo-static-wordpress`, `adr/0013-fuentes-de-verdad-duales-y-alcance-despliegue`

---

## 1. Estructura del repositorio por fase

El layout del repo **evoluciona** según la fase (ADR 0014). No mezclar HTML estático y plantillas del theme en el mismo directorio. No desplegar el ZIP estático sobre un document root WordPress después del corte (ADR 0032).

### 1.1 Fase 2 — Sitio estático en producción (estado actual)

```
demo-caminodeldharma/
├── docs/                      Documentación (01–23, adr/)
├── content-source/            Fuentes editoriales; no se despliega (16)
├── index.html, 404.html       Sitio estático en la raíz
├── robots.txt, sitemap.xml, llms.txt
├── .htaccess
├── favicon.ico, favicon.svg
├── comunidad/, linaje/, practica/, eventos/, galeria/, contacto/, donaciones/, blog/
├── assets/
├── scripts/                   No se despliegan
├── tests/                     Taxonomía de pruebas (ADR 0038); suites en Fase 3
└── .sonarcloud.properties      Alcance SonarQube Cloud
```

La **raíz** es el sitio desplegado en Hostinger. Ver README y Fase 4 en `17-orden-implementacion`.

### 1.2 Fase 3 — Monorepo (static/ + wordpress/)

**Primer paso de Fase 3:** mover el sitio estático de la raíz a `static/` (ADR 0014).

```
demo-caminodeldharma/
├── static/                    Referencia congelada (ex raíz)
│   ├── index.html, 404.html
│   ├── comunidad/, practica/, eventos/, …
│   ├── assets/
│   ├── robots.txt, sitemap.xml
│   └── …
├── wordpress/
│   └── wp-content/
│       ├── themes/
│       │   └── camino-del-dharma/    Theme (12)
│       └── plugins/
│           └── camino-del-dharma-core/   Solo si aplica
├── docs/
├── scripts/
├── tests/                     PHPUnit + Gherkin (ADR 0038)
├── .sonarcloud.properties
└── .github/                   test.yml el día uno del PHP; no deploy.yml (ADR 0016)
```

**Reglas:**

- `static/` = contrato de aceptación; comparar plantillas WP contra HTML aprobado.
- `wordpress/` = theme y plugins **propios** versionados. **No** core WP, `wp-config.php`, uploads ni credenciales en Git.
- Despliegue estático (pre-corte): contenido de `static/` → `public_html`.
- Despliegue theme (post-corte): solo `wordpress/…/camino-del-dharma/` → servidor (ADR 0013).

### 1.3 Post-Fase 3 — WordPress como implementación única

- `static/` archivada (tag Git); no desplegar.
- Theme = única implementación activa.
- Repo puede simplificarse hacia `wordpress/wp-content/themes/camino-del-dharma/` o `theme/` en la raíz.

---

## 2. Reglas

| Ubicación | Regla |
|-----------|--------|
| **docs/** | Markdown y `adr/`. No se despliega. |
| **content-source/** | Referencia editorial. No enlazar desde el sitio. |
| **Raíz (Fase 2)** | Sitio estático en producción. |
| **static/ (Fase 3+)** | Sitio público en producción durante transición; recibe mantenimiento. Referencia de paridad con theme. |
| **wordpress/…/camino-del-dharma/** | Theme; assets en `assets/` del theme. |
| **scripts/** | Mantenimiento local. |
| **tests/** | PHPUnit / Gherkin (ADR 0038). No se despliega. Vacío de suites hasta Fase 3. |

### Fuentes de verdad (post-corte WordPress)

| Qué | Dónde |
| --- | ----- |
| Código (theme, CSS, JS, plantillas de bloques) | Git |
| Contenido (entradas, eventos, Pages, medios subidos) | WordPress (BD + `uploads/`) |

Copy institucional: `content-source/` no se convierte en HTML-como-fuente (ADR 0033). Detalle en ADR 0013 y `docs/contrato-migracion-static-wordpress.md`.

### Environments (nombres de este repo)

| Environment | Uso | Estado |
| ----------- | --- | ------ |
| LOCAL | Docker WordPress (`WP_ENVIRONMENT_TYPE=local`, ADR 0023) | Playbook listo; compose aún no versionado |
| STAGING | WordPress en Hostinger separado de producción | Planificado; hostname no versionado aquí |
| PRODUCTION | `https://caminodeldharma.org` | Actual: sitio estático en `public_html` |

No mezclar credenciales, BD, uploads, fixtures ni política de indexación entre environments.

---

## 3. Flujo de assets

1. **Referencia:** `content-source/` (16).
2. **Estrategia:** `15-assets-strategy`.
3. **Estático:** `assets/` en raíz (Fase 2) o `static/assets/` (Fase 3).
4. **Theme:** `wordpress/…/camino-del-dharma/assets/`.
5. Sincronizar estático → theme durante migración; nunca enlazar a `content-source/`.

---

## 4. Qué no versionar en Git (WordPress)

- Núcleo de WordPress.
- `wp-config.php`, credenciales.
- `wp-content/uploads/` de producción.
- Cachés, backups, plugins de terceros no propios.

---

## Cierre

Geografía oficial por fase: raíz (Fase 2) → monorepo `static/` + `wordpress/` (Fase 3) → theme único (post-corte). Alineado con 12, 15, 16, ADR 0013, ADR 0014 y `17-orden-implementacion` §2.7.

---

**Versión:** 2.3
