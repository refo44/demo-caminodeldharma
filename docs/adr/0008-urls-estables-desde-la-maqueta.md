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
- Al añadir entradas de blog o eventos, seguir convención de slug en `23-sistema-editorial`.

## Referencias

- `docs/11-arbol-urls-final`
- `docs/17-orden-implementacion` §2.1
- `.htaccess` (redirects legacy)
- `sitemap.xml`
- ADR 0001, ADR 0002
