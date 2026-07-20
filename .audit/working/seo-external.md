# Auditoría SEO externa — visibilidad orgánica (continuación 2026-07-19)

La auditoría original solo cubrió **SEO interno/técnico** (working/seo.md: 8/8 PASS). Esta continuación
evalúa la **visibilidad real en buscadores**: indexación, posiciones por consultas temáticas, keywords
y comparación con competidores. Evidencia cruda en `raw/seo-external/` (HTML de SERPs + JSON parseado).

## 1. Metodología y herramientas

| Herramienta | Uso | Resultado |
|---|---|---|
| DuckDuckGo HTML (`html.duckduckgo.com`, región co-es) | 12 consultas con posiciones | OK para `site:` y marca; bloqueó por anti-bot las consultas temáticas (páginas "anomaly" guardadas) |
| Bing (`bing.com/search`, cc=CO) | 12 consultas | NO utilizable vía curl (shell JS sin resultados) — limitación registrada |
| WebSearch (búsqueda real, índice Google-like, US) | 8 consultas temáticas | OK — fuente principal para consultas temáticas |
| curl | Verificación de estado de URLs indexadas residuales | OK |
| Google Search Console / Bing Webmaster | — | **SIN ACCESO** — limitación: keywords exactas de indexación e impresiones no verificables; ver TASK-0015 |
| Panel de navegador (Google directo) | — | Navegación denegada en esta sesión — posiciones Google exactas aproximadas vía WebSearch |

## 2. Indexación (`site:caminodeldharma.org`, DDG, 2026-07-19)

| Pos | URL indexada | Estado actual |
|---|---|---|
| 1 | `https://caminodeldharma.org/` | 200 OK (canónica) |
| 2 | `https://www.caminodeldharma.org/prueba/` | 301→**404** — página de PRUEBA de la etapa WordPress, con host www |
| 4 | `https://caminodeldharma.org/category/noticias/` | 301→**404** — archivo de categoría WordPress |
| 5 | `https://caminodeldharma.org/category/sin-categoria/` | 301→**404** — archivo de categoría WordPress |
| — | `http://caminodeldharma.org/?page_id=10` (aparece en SERP de marca "camino del dharma Colombia") | 200 duplicado de portada (canonical correcta apunta a `/`); era "La comunidad" en WordPress |

**Conclusión:** el índice contiene sobre todo **residuos de la instalación WordPress anterior**; de las 13
URLs canónicas del sitemap actual solo la portada aparece con claridad. Una página titulada
"prueba" aparece en el SERP de la marca (posición 2 en DDG, 7 en otra variante) — daño de imagen directo.

## 3. Posiciones por consulta (2026-07-19)

| Consulta | Motor | ¿Aparece caminodeldharma.org? | Quién ocupa el top |
|---|---|---|---|
| camino del dharma (marca) | DDG | **SÍ — #1** (home) y #7 (/prueba/ residual) | wisdomlib, budismolibre, Facebook propio |
| camino del dharma Colombia (marca) | WebSearch | **Parcial — #4 pero con URL residual `?page_id=10`**; Facebook propio #1 | Facebook, Buddhistdoor, budismocolombia.org |
| budismo en colombia | WebSearch | **NO (top 10)** | buddhistdoor (x2), budismocolombia.org, budismo.com, urosario, bogota.gov.co, budismocolombia.co, ktccolombia |
| comunidad budista colombia | WebSearch | **NO (top 10)** | buddhistdoor, budismocolombia.org, budismo.com, sotozencolombia.org, kadampa |
| centro budista colombia meditación | WebSearch | **NO (top 10)** | meditacionencolombia.org, centroyamantaka.org, budismocolombia.org/.co, semana.com |
| budismo chan colombia | WebSearch | **NO (top 10)** — pero la comunidad es LA referencia Chan citada por Buddhistdoor en el propio snippet | buddhistdoor, budismo.com, sotozencolombia, templochan.com |
| budismo tierra pura colombia | WebSearch | **NO (top 10)** — ídem, citada en contenido de terceros | Wikipedia (x2), buddhistdoor, pijamasurf |
| budismo cali meditación | WebSearch | **NO (top 10)** | meditacionencolombia.org/cali, budismocolombia.co/centros/centro-cali, elpais.com.co, zenat.com.co |
| retiro budista colombia 2026 | WebSearch | **NO (top 10)** | tripaneer, budismocolombia.org, meditacionencolombia.org, montanadesilencio.org |

**Conclusión:** el sitio solo es localizable buscando el nombre exacto. Para todas las consultas
temáticas (budismo en Colombia, comunidad/centro budista, meditación, chan, tierra pura, Cali,
retiros) NO aparece en el top 10, pese a que artículos de terceros (Buddhistdoor) lo citan como la
referencia del budismo Chan en Colombia.

## 4. Causas identificadas (gap vs competidores)

1. **Autoridad/backlinks casi nula.** El artículo de Buddhistdoor que describe la comunidad enlaza
   solo a Facebook, NO al dominio (verificado 2026-07-19). El sitio no figura en el directorio
   budismo.com/directorios/colombia.php (verificado: 11 centros listados, Camino del Dharma ausente).
2. **Índice contaminado por residuos WordPress** (§2): el crawl budget y las señales de marca se
   dispersan en 404s y duplicados.
3. **Señales locales inexistentes (hasta esta continuación):** el sitio no mencionaba Cali ni el año
   de fundación (2012) en ninguna página; sin dirección, sin Google Business Profile detectado.
   Los competidores posicionan con páginas por ciudad (meditacionencolombia.org/cali,
   budismocolombia.co/centros/centro-cali, sedes Bogotá/Medellín).
4. **Contenido temático mínimo:** 1 entrada de blog; sin páginas educativas ("¿qué es el budismo
   chan?", "¿qué es la tierra pura?") que son las que capturan búsquedas informacionales
   (los competidores tienen secciones "¿Qué es budismo?", artículos, centros de retiro).
5. **Sin Search Console/Bing Webmaster verificados** (no confirmable desde fuera; el sitemap se sirve
   pero no hay evidencia de envío): la indexación de las 13 URLs nuevas depende del crawl orgánico.

## 5. Oportunidades de palabras clave

| Tipo | Keyword | Racional |
|---|---|---|
| Principal | budismo en colombia | Ya en title/H2 de la home; sin autoridad aún |
| Principal | comunidad budista colombia | En title de /comunidad |
| Secundaria (nicho, baja competencia) | budismo chan colombia / en español | La comunidad es LA referencia citada; los rivales son tibetanos/zen — mejor apuesta a corto plazo |
| Secundaria (nicho) | budismo tierra pura en español | Ídem; SERP dominado por Wikipedia/artículos, sin comunidad práctica |
| Local | budismo cali / meditación cali | Origen real de la comunidad (2012) + evento "Pausa profunda Cali" |
| Long-tail | meditación budista online en español / meditación en línea lunes | La meditación semanal virtual es única entre los competidores revisados (todos presenciales) |
| Informacional | qué es el budismo chan; recitación de amitabha; nianfo | Contenido educativo pendiente (TASK-0016) |

## 6. Cambios implementados en esta continuación (fuente; requieren deploy)

1. `.htaccess`: 410 para `/prueba`, 301 `category/*` → `/blog`, 301 `?page_id=10` → `/comunidad`,
   301 `?page_id=*` → `/` (limpieza del índice residual WordPress).
2. `index.html` JSON-LD Organization: eliminado `alternateName` "budismo en Colombia" (keyword, no
   nombre real — riesgo de spam estructurado); añadidos `foundingDate` 2012, `foundingLocation`
   Cali y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena). Dirección postal NO añadida:
   dato no verificado, pendiente de confirmación de la comunidad.
3. `index.html` + `comunidad/index.html`: mención textual "nacida/Fundada en Cali en 2012" (señal
   local verificable por terceros — Buddhistdoor).
4. `eventos/index.html`: title "Eventos y Retiros Budistas en Colombia | Camino del Dharma" +
   descripción con keyword (og/twitter sincronizados).
5. `blog/index.html`: title "Blog de Budismo — Reflexiones y Enseñanzas | Camino del Dharma"
   (og/twitter sincronizados).

Conservado sin cambios (correcto ya): titles/descriptions de home, práctica, comunidad, linaje;
sitemap; robots.txt; canónicas; llms.txt; JSON-LD Event/BlogPosting.

## 7. Acciones que requieren humanos / sesiones posteriores

- TASK-0013: desplegar y validar los cambios de esta continuación.
- TASK-0014: off-page — alta en directorio budismo.com, solicitar a Buddhistdoor el enlace al
  dominio, URL del sitio en perfiles de Facebook/Instagram, evaluar Google Business Profile
  (requiere dirección confirmada).
- TASK-0015: alta y verificación en Google Search Console + Bing Webmaster Tools; enviar sitemap;
  inspeccionar/retirar URLs residuales; obtener keywords reales de indexación (sustituye la
  aproximación de esta auditoría).
- TASK-0016: plan editorial de contenido temático (educacional Chan/Tierra Pura, página de
  meditación online, posible página Cali) — BLOCKED: decisión editorial y manual de voz.
