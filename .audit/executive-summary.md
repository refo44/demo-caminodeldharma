# Resumen ejecutivo — Auditoría Camino del Dharma (2026-07-19)

> **Actualización 2026-07-20 (etapa 2):** el propietario confirmó deploy **v1.0.14** y cierre de las 14 tareas de implementación ejecutables (todas las READY + TASK-0001). Etapa 2 cerrada. Pendiente: 6 tareas BLOCKED (decisiones humanas + HSTS post-WordPress). Detalle: `state.md`, `implementation/backlog.md`.

> **Revisión 2026-07-19 (continuación):** la versión original de este resumen presentaba HSTS como decisión titular con `max-age=31536000` inmediato. HSTS es **uno más** de los criterios evaluados (seguridad de transporte), no el objetivo de la auditoría, y el sitio estático actual es **temporal** (será reemplazado por WordPress). La decisión vigente es el **despliegue escalonado** del ADR 0018: **no** configurar un `max-age` de un año en esta etapa.

**Estado general: 84/100.** Sitio estático pequeño y excepcionalmente bien construido: SEO on-page, datos estructurados, accesibilidad estructural y despliegue impecables. Cero hallazgos críticos. Nota: la puntuación SEO 100 refleja solo el **SEO interno/técnico**; la visibilidad externa en buscadores se evalúa en la continuación (ver `working/seo-external.md`).

**Lo que sí está roto (1 hallazgo ALTO + 1 corregido a MEDIA):**
1. El **formulario de contacto no entrega mensajes** (envía a `action="#"` sin backend): quien lo usa cree haber escrito a la comunidad y el mensaje se pierde en silencio.
2. ~~Las descargas .ics fallan en los dos eventos~~ → **corregido 2026-07-20:** afecta a **un** evento (el otro está finalizado y no ofrece calendario) y el archivo **sí existe**; el 404 lo causa una ruta relativa bajo URLs sin barra final. Rebajado a MEDIA — ver adenda ASO/AEO.

**Riesgos medios:** HSTS pendiente — activar en **Fase 1 con `max-age=604800` (7 días)**, dejando el año completo para después del corte a WordPress estable (ADR 0018) —, CSP mínima e imágenes sobredimensionadas (logo de 1000 px (46 KB servidos) para un hueco de 44 px).

**Entregable:** 20 tareas atómicas (12 originales + 4 de SEO externo + 2 de ASO + 1 de autoridad + 1 de sanghas por ciudad) para agentes implementadores externos (14 listas, 4 bloqueadas por decisiones humanas: backend del formulario, inventario de subdominios, plan editorial, confirmación de sanghas por ciudad). Arranque recomendado por impacto: desplegar los cambios SEO ya hechos en fuente (TASK-0013) → corregir la ruta .ics (TASK-0001, <30 min) → sustituir el formulario muerto por CTAs de WhatsApp/correo (TASK-0002) → activar HSTS Fase 1 (30 min, reversible en días).

**Decisiones que solo la comunidad puede tomar:** (1) ¿formulario real con backend o solo WhatsApp/correo?; (2) texto de la política de privacidad; (3) ¿enlace de Zoom público o puerta por WhatsApp?; (4) en qué ciudades hay sangha real. *(La decisión sobre analítica ya está tomada: ADR 0019 — ver adenda final.)*

---

## Adenda de la continuación (2026-07-19) — SEO externo

**Visibilidad orgánica: nicho ganado, consultas amplias y locales perdidas** (verificado en Google real hl=es/gl=co, 2026-07-20 — EVID-0037). El sitio es **#1** para "budismo chan colombia" y "budismo tierra pura colombia" y #1 de marca con SERP limpio; pero **no aparece en la página 1** de "budismo en colombia", "comunidad budista colombia" ni "budismo cali", donde dominan los packs locales de Google Business Profile y competidores con más autoridad. En índices secundarios (DuckDuckGo) persisten residuos WordPress ("prueba", `?page_id=10`). La puntuación SEO 100 de la auditoría original medía únicamente el SEO interno; el SEO externo puntúa 45/100.

**Causas:** autoridad externa casi nula — **cuantificada el 2026-07-20: DR 0,4 / DA 6** (ver adenda de autoridad) —, índice contaminado por residuos de WordPress, cero señales locales (el sitio no decía que nació en Cali en 2012) y contenido temático mínimo (1 entrada de blog).

**Hecho en esta continuación (en fuente, pendiente de deploy — TASK-0013):** redirects 410/301 para las URLs residuales, datos estructurados con fundación (Cali, 2012) y `knowsAbout`, menciones locales en home y comunidad, títulos temáticos en eventos y blog.

**Pendiente de humanos (prioridad revisada con datos verificados):** (1) **Google Business Profile** — los packs locales dominan todos los SERPs amplios/locales probados; requiere dirección confirmada (TASK-0014); (2) Search Console — Google mismo anuncia que el dominio no tiene propiedad conectada (TASK-0015); (3) directorio budismo.com + enlace de Buddhistdoor + URL en perfiles sociales (TASK-0014); (4) plan editorial de contenido temático (TASK-0016 — la meditación semanal online es un diferenciador único frente a los competidores presenciales).

---

## Adenda de la continuación (2026-07-20) — ASO / AEO

**Lo técnico está bien; lo que falla es ser citable.** Los ocho crawlers de IA probados (GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot, Bingbot) acceden sin bloqueo ni cloaking; el coste de contexto es bajo (~400–1 600 tokens por página); no hay ni un patrón de inyección de prompts. **La brecha es de visibilidad agéntica:**

- **El sitio gana la keyword y pierde la pregunta.** Es #1 en "budismo chan colombia", pero desaparece de la página 1 en "dónde practicar budismo chan en Colombia" — la misma intención en lenguaje natural, que es como consultan los asistentes de IA. Y el AI Overview de Google para la búsqueda de marca cita a EcoEspiritualidad, Wisdom Library y Wikipedia, **no al sitio**, aunque sea el resultado orgánico #1.
- **La meditación semanal online no existe para un agente.** Es la joya de la corona (gratuita, virtual, para principiantes, sin límite geográfico, y el único formato online frente a competidores presenciales), pero vive como un párrafo: sin URL propia, sin datos estructurados, ausente de `llms.txt` y del sitemap. Quien pregunte a un asistente por meditación budista online en español no puede recibirlos como respuesta.

**Corrección importante de la auditoría original:** el hallazgo FUNC-002 decía que los archivos `.ics` "nunca se crearon" y que ambos eventos estaban rotos. Es inexacto: el archivo existe y responde 200; el 404 lo produce una ruta **relativa** que, bajo la política canónica **sin barra final**, resuelve a la raíz del sitio. Solo un evento está afectado. El arreglo prescrito (crear dos archivos) habría duplicado uno existente sin resolver nada; el correcto es usar rutas absolutas. Severidad ALTA→MEDIA, TASK-0001 reescrita.

**Nuevas tareas:** TASK-0017 (meditación semanal como entidad citable: URL propia + `Event`/`EventSeries` online recurrente + `llms.txt`) y TASK-0018 (contenido en formato pregunta-respuesta). La decisión pendiente para la comunidad: **si publicar el enlace de Zoom directo o mantener la puerta humana por WhatsApp** — es un intercambio real entre control comunitario y accesibilidad para agentes.

---

## Adenda de la continuación (2026-07-20) — Autoridad de dominio y enlaces

**La cifra que lo explica todo: DR 0,4 en un dominio de 7 años y 5 meses.** No es un sitio nuevo que necesite madurar — es un dominio establecido que **nunca acumuló enlaces**. Métricas verificadas (Dapachecker, ejecución manual del propietario, 2026-07-20): DR 0,4 · UR 4 · DA 6 · PA 18 · Spam Score 7 % · antigüedad 7a 5m. Contraste independiente (SEO Review Tools): DA 2 / PA 8.

**Buena noticia dentro del diagnóstico: no hay enlaces tóxicos.** Spam Score 7 % es sano. Esto responde la disyuntiva clásica de una auditoría de enlaces: aquí **no toca limpiar, toca construir**. Nada de trabajo de `disavow`.

**El baseline de competidores (2026-07-20) confirma que la brecha es cerrable.** Ningún rival supera **DA 20** y la mediana es **17**: es un nicho de autoridad uniformemente baja, sin ningún actor dominante. La distancia al peldaño más cercano son **6 puntos** (DA 8). Y el dato que cambia la estrategia: `budismocolombia.org` tiene **214 enlaces externos —prácticamente los mismos 207 que ustedes— pero DA 8 frente a DA 2**. Mismo volumen, cuatro veces la autoridad. **No faltan enlaces: faltan enlaces buenos.**

**Y la causa raíz es inusualmente fácil de corregir.** Tres fuentes temáticas autorizadas hablan de la comunidad y **ninguna enlaza el dominio**: Buddhistdoor la describe como la referencia Chan del país pero enlaza solo a Facebook; EcoEspiritualidad tiene una ficha completa que es el **#2 de Google para la marca** y no contiene ningún enlace saliente; el directorio budismo.com lista 11 centros y no a esta comunidad. No hace falta link building desde cero: hace falta **pedirlo** (TASK-0014, tres correos concretos).

**Advertencia sobre herramientas:** descarté deliberadamente los comprobadores de "PageRank" (dnschecker, smallseotools, prchecker) pese a figurar en la lista aportada. Google no publica PageRank desde 2016; esas páginas muestran puntuaciones propias no auditables o valores caducados presentados como si fueran de Google. Usarlas como métricas de auditoría habría sido fabricar evidencia.

**Metas de autoridad:** DA 8 como primer hito (paridad con los competidores más cercanos, alcanzable con las tres peticiones de enlace) y DA 15–17 como segundo (mediana del sector, ya requiere el plan editorial y presencia local). Re-medir cada trimestre con la misma herramienta.

**Dos autocorrecciones:** (1) el 19 de julio registré la ficha de EcoEspiritualidad como "backlink existente" — fue una inferencia a partir del SERP, no una verificación; comprobada la página, no hay tal enlace. (2) Interpreté "0 compartidos sociales" como que ni su propia audiencia amplifica el contenido; el baseline muestra 0 en los **seis** dominios, incluidos los líderes, así que la herramienta simplemente no recoge ese dato. Ambas corregidas en los artefactos afectados.

---

## Adenda de la continuación (2026-07-20) — Rendimiento medido (PSI/Lighthouse)

**El sitio es rápido, y ahora está probado.** El informe PageSpeed que compartiste cierra la mayor laguna de medición de la auditoría original, que no pudo obtener LCP por limitaciones del entorno:

| | Móvil (4G lenta) | Escritorio |
|---|---|---|
| **Rendimiento** | **99** | **100** |
| Accesibilidad · Prácticas recomendadas · SEO | 100 · 100 · 100 | 100 · 100 · 100 |
| **Navegación agéntica** (categoría nueva de Lighthouse) | **3/3** | **3/3** |
| LCP · FCP · TBT · CLS · SI | 1,4 s · 0,9 s · 0 ms · 0,081 · 1,0 s | 0,4 s · 0,3 s · 0 ms · 0,005 · 0,3 s |

**Reconciliación de una cifra que parecía contradictoria:** la auditoría puntuó Rendimiento 67 y PSI da 99. No se contradicen — el 67 medía **controles de higiene** que siguen fallando (imágenes sobredimensionadas, assets sin versionar), mientras que el 99 mide la **experiencia entregada**, que es excelente. Ajusté el scorecard para que ambas lecturas convivan: Rendimiento 67 → **85**, y Core Web Vitals pasa de "sin puntuar" a **90**.

**Tres confirmaciones independientes de hallazgos que ya teníamos:** PSI señala 185 KiB de ahorro en imágenes (PERF-001), y marca como pendientes la CSP (SEC-002) y la política HSTS (SEC-001). Cuando una herramienta externa llega por su cuenta a las mismas conclusiones, sube la confianza del informe.

**Dos oportunidades nuevas:** solicitudes que bloquean el renderizado (450 ms) y minificación de CSS (3 KiB). Ninguna urgente con LCP en 1,4 s.

**Un dato que confirma el diagnóstico de visibilidad:** CrUX responde *"No hay datos"* — el sitio no alcanza el umbral de tráfico para tener datos de usuarios reales. Es la misma historia que cuentan DA 2 y la ausencia en consultas amplias, medida por una vía completamente distinta. También significa que **INP seguirá sin poder medirse** hasta que haya más tráfico.

**La comparativa móvil/escritorio aísla el problema de imágenes:** 185 KiB de ahorro en móvil frente a 42 KiB en escritorio (4,4×). Esa diferencia es exactamente la firma de la falta de `srcset` que ya señalaba PERF-001 — al móvil se le entregan imágenes dimensionadas para pantallas grandes.

**Y una corrección:** la auditoría registró CLS = 0 en ambos perfiles; PSI mide **0,081** en móvil y **0,005** en escritorio. Sigue en verde (<0,1), pero el 0 se midió sin throttling y era optimista, y revela que el desplazamiento es **específico de móvil** — conviene vigilarlo si se toca la portada.

---

## Adenda (2026-07-20) — Decisión sobre analítica

**No se usará Google Analytics.** Decisión definitiva del propietario, formalizada en **ADR 0019**.

El motivo no es ideológico sino de utilidad: con CrUX sin datos (tráfico bajo el umbral de Google), DA 2 y ausencia de la página 1 en las consultas amplias, GA4 produciría un puñado de sesiones al mes — ruido estadístico, no información. El cuello de botella medido no es qué hacen las visitas, sino que **no llegan**, y esa pregunta la responde **Search Console**: gratis, sin cookies y sin banner.

Se suma que el propósito del sitio no es comercial: la señal de participación real —que alguien acuda a la meditación del lunes— ya se observa directamente, y quien se acerca pasa por un canal humano donde puede preguntarse cómo llegó. A esta escala eso aporta más que la analítica agregada.

**Verificado:** producción no sirve **ninguna cookie propia**. Es una posición poco común que conviene preservar: elimina la necesidad de banner, reduce la superficie legal y encaja con el registro editorial de la comunidad.

**Efecto en la auditoría:** PRIV-001 baja de MEDIA a **BAJA** y se reenfoca a dos elementos concretos — los **10 embeds de vídeo** (8 YouTube + 2 Vimeo) que no usan la variante `nocookie` y pueden fijar cookies de terceros al reproducir, y la **política de privacidad**, que sigue siendo recomendable porque la Ley 1581/2012 cubre el tratamiento de datos personales en general, no solo cookies. TASK-0006 pasa de BLOQUEADA a lista: desaparece la decisión de consentimiento que la detenía.

**Si algún día hiciera falta medir comportamiento**, la vía será analítica sin cookies (Plausible, Fathom o equivalente), nunca volver a GA4.

---

## Adenda (2026-07-20) — Datos de Search Console y un matiz que lo reencuadra todo

Con los datos que compartiste, la auditoría pasa de estimar a **medir**. Y aparece un dato de contexto que cambia cómo hay que leer buena parte del análisis de visibilidad.

**El sitio actual se publicó el 2026-07-18. La auditoría se ejecutó el 07-19 — sobre un sitio de un día de vida.**

Los hallazgos técnicos no se ven afectados: el formulario roto, la ruta de los `.ics`, las imágenes, HSTS, CSP, accesibilidad y rendimiento son independientes de la antigüedad. Pero las conclusiones de posicionamiento sí necesitan matizarse:

| Lo que dije | Cómo debe leerse |
|---|---|
| «Ausente de página 1 en consultas amplias» | **Esperable a los dos días.** Sigue siendo la brecha a trabajar, pero no es un defecto |
| «Solo 4 de 13 URLs indexadas» | Progreso normal de rastreo |
| «#1 en budismo chan y tierra pura colombia» | **Más meritorio de lo que parecía**: logrado en dos días |
| «Dominio sin autoridad tras 7,5 años» | **Se mantiene** — es el hallazgo de fondo. El déficit viene de la etapa WordPress anterior, no del sitio nuevo |

**Lo que Search Console confirma:** 9 clics y 35 impresiones en total, y **una sola consulta genera impresiones: el nombre de la comunidad** (posición media 3,35; Colombia aporta 8 de los 9 clics). Es exactamente lo que había medido desde fuera, ahora con datos de Google.

**Un hallazgo nuevo:** Google todavía cuenta por separado `https://`, `https://www.` y `http://`. Las redirecciones son correctas, así que debería consolidarse solo — conviene revisarlo en 4-8 semanas.

**La consecuencia práctica más útil:** no saques conclusiones sobre si las acciones de autoridad y contenido funcionan hasta volver a mirar Search Console dentro de **4 a 8 semanas**. Medir hoy la eficacia de algo que aún no ha tenido tiempo de actuar solo produciría conclusiones falsas.

---

## Adenda (2026-07-21) — Tres correcciones que cambian el orden de prioridades

**1. Google Business Profile queda descartado, no pendiente.** La adenda del 2026-07-19 lo señalaba como «la acción individual de mayor palanca». Se retira: la comunidad **no tiene sede ni dirección física**, y las directrices de Google excluyen a las entidades exclusivamente en línea. Aunque una organización sin sede visible puede ocultar la dirección al público, Google exige una dirección real verificable en el back end, sin apartados postales ni oficinas virtuales.

Fue una deducción a partir del SERP —los packs locales dominaban todas las consultas amplias y locales— **sin verificar la elegibilidad**. Mismo patrón que el falso backlink de EcoEspiritualidad.

**Consecuencia estratégica:** las consultas locales dejan de contabilizarse como brecha. El esfuerzo se reorienta a autoridad y a la **práctica en línea**, donde la comunidad es el único caso virtual entre los seis dominios comparados.

**2. El directorio budismo.com se retira de las recomendaciones.** Su autoridad nunca se midió —no estaba en el baseline de competidores—, el sitio no muestra mantenimiento desde 2010, no tiene formulario de alta y su posición en los SERPs puede deberse en buena parte a la coincidencia exacta del nombre de dominio, que no implica autoridad transferible. Se recomendó aplicando un estándar de evidencia inferior al exigido al resto del análisis.

**3. TASK-0017 no estaba hecha.** El cierre en bloque del deploy v1.0.14 la marcó COMPLETED. Verificación del 2026-07-21: ninguno de sus cuatro criterios se cumple. Reabierta como READY — y es ahora **la acción de mayor recorrido disponible**, con el prerrequisito ya resuelto (puerta por WhatsApp, sin publicar el enlace de Zoom).

**Estado de la off-page:** peticiones a Buddhistdoor y EcoEspiritualidad enviadas; URL de la web puesta en Facebook; **pendiente en Instagram** — con la salvedad de que los enlaces de redes no transfieren autoridad y su valor es de tráfico y coherencia.
