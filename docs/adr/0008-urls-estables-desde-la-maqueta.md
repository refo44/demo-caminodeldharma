# ADR 0008: URLs estables desde la maqueta

## Estado

Aceptada

## Fecha

2026-07-19

## Contexto

El sitio define un árbol de URLs canónico en `11-arbol-urls-final` y lo implementa desde la maqueta estática (`/comunidad/`, `/practica/`, `/blog/{slug}/`, etc.). Cambiar URLs después del lanzamiento afecta SEO, enlaces externos, materiales impresos y Google Search Console.

La maqueta usa URLs limpias desde el inicio (`/ruta/` → `ruta/index.html`) con reglas en `.htaccess` para HTTPS, host canónico, eliminación de `index.html` visible y redirects de rutas legacy.

## Decisión

La **estructura de URLs definida en la maqueta estática es definitiva** y se mantiene en WordPress y en producción.

- No inventar rutas temporales en desarrollo.
- Redirects 301 solo para **legacy** documentados (p. ej. `/encuentro-nacional-2026/` → `/eventos/encuentro-nacional-2026`).
- Cambios de URL requieren: actualización de `11-arbol-urls-final`, `sitemap.xml`, redirects en `.htaccess`, ADR nuevo si el cambio es estructural, y solicitud de reindexación en Search Console.
- Versionado semántico: cambios de URL pueden implicar incremento **MAJOR** en `VERSION`.

### Regla derivada: los enlaces internos deben ser absolutos de raíz

*(Añadida 2026-07-21 tras la segunda aparición del mismo fallo.)*

La política canónica es **sin barra final** (`/practica`, no `/practica/`). Eso tiene una consecuencia que no es evidente y que ya ha roto el sitio dos veces:

**Un navegador que está en `/practica` trata `practica` como archivo, no como carpeta.** Por tanto, una ruta relativa se resuelve contra `/`, no contra `/practica/`:

| Página actual | Enlace relativo | Resuelve a | Esperado |
|---|---|---|---|
| `/practica` | `meditacion-semanal-en-linea` | `/meditacion-semanal-en-linea` ❌ | `/practica/meditacion-semanal-en-linea` |
| `/practica/videos` | `..` | `/` ❌ | `/practica` |

**Regla:** todos los enlaces internos —`href` y rutas de descarga— usan **ruta absoluta de raíz** (`/practica/...`). Las rutas relativas solo son admisibles para recursos que cuelgan de la raíz (`../../assets/...`), donde el acotado en la raíz las hace equivalentes.

**Historial de esta clase de fallo:**

1. **FUNC-002** (auditoría 2026-07-19): la descarga `.ics` usaba ruta relativa y devolvía 404 bajo la política sin barra.
2. **FUNC-003** (2026-07-21): `/practica/videos` llevaba en producción con sus tres enlaces `href=".."` resolviendo a la raíz en lugar de a `/practica`; el mismo patrón se reintrodujo al enlazar la página nueva de meditación semanal.

Ambos son la misma causa. Por eso la regla queda aquí, en el ADR que fija la política de URLs, y no como nota suelta: **la política sin barra final y las rutas relativas son incompatibles**, y quien adopte la primera debe descartar las segundas.

**En WordPress:** usar siempre `home_url()` / `get_permalink()`, nunca rutas relativas escritas a mano en las plantillas.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| URLs distintas en maqueta y WordPress | Rompe ADR 0001 y ADR 0002; duplica redirects. |
| Slugs en español vs inglés mixtos ad hoc | Inconsistencia; ya resuelto en árbol final. |
| Parámetros query para secciones | Peor SEO y UX; contradice URLs limpias acordadas. |

## Consecuencias

**Beneficios:**

- SEO estable; sitemap coherente con rutas reales.
- Migración WordPress sin sorpresas de enlaces rotos.
- `.htaccess` centraliza política de canonicalización.

**Riesgos:**

- Rigidez ante cambios de nomenclatura editorial; deben gestionarse con redirects.

**Trabajo futuro:**

- Mantener `sitemap.xml` con `<lastmod>` en cada release.
- Verificar rutas absolutas en enlaces internos tras cada página nueva y tras el corte a WordPress.
- Al añadir entradas de blog o eventos, seguir convención de slug en `23-sistema-editorial`.

## Referencias

- `docs/11-arbol-urls-final`
- `docs/17-orden-implementacion` §2.1
- `.htaccess` (redirects legacy)
- `sitemap.xml`
- ADR 0001, ADR 0002
