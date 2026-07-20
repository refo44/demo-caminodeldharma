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
| **Dualidad de grafía** | **budista** (público general) **+ buddhista** (practicantes y conocedores) | Ver §9 |

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

---

## 8. Verificación directa en Google Colombia (2026-07-20 — supersede §3 para estas consultas)

Navegación real a google.com (hl=es, gl=co) desde el panel de navegador; texto de SERPs archivado
en `raw/seo-external/google-co-serps-2026-07-20.md` (EVID-0037).

| Consulta | Posición verificada en Google CO |
|---|---|
| site:caminodeldharma.org | 4 URLs principales (home, 1 post, 2 eventos) + omitidas por similitud; **sin residuos WP en primer plano** |
| camino del dharma (marca) | **#1** home; #2 ficha en ecoespiritualidad.org (cita **SIN enlace** — ver corrección abajo); #3 Facebook |
| budismo chan colombia | **#1** |
| budismo tierra pura colombia | **#1** + PAA "¿Qué es la Comunidad Camino del Dharma?" |
| budismo en colombia | Ausente de página 1; pack local (GBP) + budismocolombia.org dominan |
| comunidad budista colombia | Ausente de página 1; pack local primero |
| budismo cali | Ausente de página 1 (pese al evento "Pausa Profunda – Cali" indexado); pack local + páginas Cali de Kadampa/Diamante |

**Correcciones respecto a la aproximación (§3):** las consultas de nicho ya están ganadas (#1),
mejor de lo estimado con índice US; el SERP de marca en Google CO está limpio (los residuos WP
afectan a DuckDuckGo/otros índices — la limpieza 410/301 sigue siendo correcta). SEO-EXT-001
rebajado ALTA→MEDIA y reformulado: la brecha real está en consultas amplias e intención local,
donde **los packs locales de Google Business Profile dominan todos los SERPs probados** — GBP pasa
a ser la acción individual de mayor palanca (TASK-0014, requiere dirección confirmada). El anuncio
de Google "¿Eres dueño de caminodeldharma.org? Prueba Search Console" sugiere que no hay propiedad
GSC conectada a este dominio (refuerza TASK-0015).

> **CORRECCIÓN 2026-07-20:** arriba registré la ficha de ecoespiritualidad.org como "cita/backlink
> existente". Fue una **inferencia a partir del SERP, no una verificación**. Comprobada la página
> (EVID-0044): la ficha describe a la comunidad en detalle pero **no contiene ningún enlace saliente
> al dominio**. Análisis de autoridad completo en `authority-backlinks.md`.


---

## 9. Dualidad de grafía: budista / buddhista (2026-07-20, EVID-0049)

Dos grafías nombran lo mismo y sirven a **públicos distintos**: quien no conoce la tradición escribe
*budista*; quien la conoce —y la propia comunidad— escribe *buddhista*. Ambas son objetivos legítimos.

**Verificación en Google (hl=es, gl=co):** la consulta "comunidad buddhista colombia" **no dispara
corrección ortográfica** y devuelve **prácticamente el mismo conjunto de resultados** que la grafía con
una d. El buscador resuelve ambas a la misma intención, de modo que **la elección editorial no tiene
coste SEO apreciable**. (El sitio está ausente de la página 1 con ambas grafías, coherente con
SEO-EXT-001.)

**Procedencia del cambio de copy:** el tagline visible «Comunidad budista Chan y Tierra Pura en
Colombia» fue **añadido** —no modificado— en el commit `fbb9e05` del propietario (2026-07-18), dos días
antes de esta continuación. La forma «buddhista Chan y Tierra Pura» nunca existió en el historial.
**No es atribuible a la auditoría.** El tagline sí se desviaba del estándar del propio proyecto:
`docs/08-voice-dictionary.md` §7 fija «Buddhismo … usar con consistencia».

**Política adoptada** (registrada en los docs de voz del proyecto):

| Contexto | Grafía |
|---|---|
| Nombre institucional | `Comunidad Buddhista Camino del Dharma` — invariable |
| Copy visible de identidad | `Buddhismo` / `buddhista` |
| Metadatos de descubrimiento (`description`, `og`, `twitter`) | `budista` / `budismo` |
| `alternateName` en JSON-LD | ambas — «Comunidad Budista Camino del Dharma» es grafía real del nombre |

**Descartado explícitamente:** ocultar la grafía alternativa mediante texto invisible o `aria-label`
con palabras clave. Es spam según las Search Essentials de Google (riesgo de acción manual sobre un
dominio que ya arrastra DA 2), los atributos ARIA no son señal de ranking —por lo que **no
funcionaría**—, y degradaría la experiencia de lectores de pantalla. En su lugar se optó por
**contenido visible**: nota editorial en `/linaje` («Sobre la palabra Buddhismo»), que cubre ambas
grafías de forma natural y responde una duda real del lector. Redacción final del propietario: vincula
la doble d al término sánscrito *buddha* y precisa que no es un nombre propio sino un título —«el
despierto»—, señalando que *budismo* y *budista* son las formas extendidas en español y nombran la
misma tradición. Se decidió desarrollarla **solo en `/linaje`** (la página de la tradición); la nota
equivalente en `/comunidad` se retiró para no duplicar el mensaje.


---

## 10. Hipótesis de URL y páginas por ciudad

Evaluadas el 2026-07-20 sin implementar, a petición del propietario: ver `url-hypotheses.md`.
Renombrado de `/comunidad` **descartado** (refutado con datos); páginas por ciudad → **TASK-0020 (BLOCKED)**.

---

## 11. Datos reales de Search Console (2026-07-20, EVID-0052) — y una reinterpretación necesaria

Export del propietario, 28 días, búsqueda web. CSV archivados en `raw/gsc/`.

### Los datos

| Métrica | Valor |
|---|---|
| Clics / impresiones totales | **9 / 35** |
| Consultas con datos | **UNA sola: «camino del dharma»** — 5 clics, 17 impresiones, CTR 29,41 %, posición media **3,35** |
| Colombia | 8 clics · 32 impresiones · posición 3,19 |
| España / Vietnam | 1/2 · 0/1 |
| Móvil / escritorio | 6 clics de 7 impresiones · 3 clics de 28 |

Páginas que reciben impresiones:

| URL | Clics | Posición |
|---|---:|---:|
| `https://caminodeldharma.org/` | 7 | 3,0 |
| `https://www.caminodeldharma.org/` | 1 | 8,33 |
| `http://caminodeldharma.org/` | 1 | 1,0 |

### El dato que obliga a reinterpretar el resto

**El gráfico contiene solo dos fechas: 2026-07-17 y 2026-07-18.** El sitio estático actual se publicó
el **2026-07-18** (`CHANGELOG` v1.0.0). Es decir: **la ventana de datos cubre prácticamente toda la
vida del sitio**, y la auditoría original del 2026-07-19 se ejecutó sobre un sitio de **un día**.

Esto **no invalida** los hallazgos técnicos —formulario, rutas `.ics`, imágenes, HSTS, CSP,
accesibilidad, rendimiento— que son independientes de la antigüedad. Pero **sí obliga a matizar las
conclusiones de visibilidad**:

| Conclusión previa | Reinterpretación |
|---|---|
| «Ausente de página 1 en consultas amplias y locales» | **Esperable a los dos días.** No es señal de descuido ni de un problema estructural: es que no ha habido tiempo. Se mantiene como brecha a trabajar, no como defecto |
| «Solo 4 de 13 URLs indexadas» | **Normal.** Es progreso de rastreo, no un fallo |
| «Dominio establecido que nunca acumuló enlaces» | **Sigue siendo cierto y es el hallazgo de fondo**: el dominio tiene 7,5 años y DR 0,4. Pero el *sitio* actual es nuevo — el déficit de autoridad viene de la etapa WordPress anterior, no de negligencia reciente |
| «#1 en budismo chan / tierra pura colombia» | **Más notable de lo que parecía**: alcanzado con dos días de vida |

### Lo que los datos confirman

**La localizabilidad solo por marca queda confirmada con datos de primera parte:** una única consulta
genera impresiones, y es el nombre exacto. Es exactamente lo que midió SEO-EXT-001 desde fuera. La
diferencia es que ahora sabemos que la causa inmediata es la edad, y la causa de fondo la autoridad.

### Hallazgo nuevo: variantes de host y protocolo en el índice

GSC contabiliza por separado `https://`, `https://www.` y `http://`. Las tres redirigen correctamente
(301 verificado), pero Google todavía no ha consolidado las señales en la canónica. Con dos días de
vida es normal y debería resolverse solo. **Vigilar**: si en 4–8 semanas siguen apareciendo separadas,
revisar la consolidación. Refuerza el valor de TASK-0015 (enviar sitemap, inspeccionar URLs).

### Consecuencia práctica

La prioridad no cambia, pero sí la expectativa temporal: **conviene volver a mirar GSC en 4–8 semanas**
antes de sacar conclusiones sobre si las acciones de autoridad y contenido funcionan. Medir hoy la
eficacia de algo que aún no ha tenido tiempo de actuar llevaría a conclusiones falsas.
