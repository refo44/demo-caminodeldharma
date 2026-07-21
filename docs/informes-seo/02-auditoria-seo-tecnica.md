# Auditoría SEO Técnica

## Comunidad Buddhista Camino del Dharma — caminodeldharma.org

| | |
|---|---|
| **Cliente** | Comunidad Buddhista Camino del Dharma |
| **Sitio auditado** | https://caminodeldharma.org |
| **Versión en producción** | v1.0.14 |
| **Fecha del informe** | 20 de julio de 2026 |
| **Periodo de auditoría** | 19–20 de julio de 2026 |
| **Naturaleza** | Estado de salud técnica del sitio |
| **Destinatario** | Equipo de publicación web |

> **Alcance:** indexación y rastreo, higiene del índice, rendimiento, datos estructurados, seguridad y protocolo de medición. Cada hallazgo lleva estado, esfuerzo estimado y criterio de aceptación.

> **Naturaleza del sitio.** El estático actual es **temporal**: será sustituido por WordPress. Ninguna recomendación de este informe compromete al sitio a plazos largos —por ejemplo, cabeceras de seguridad con vigencia de un año—.

---

## Índice

1. [Veredicto](#1-veredicto)
2. [Alcance y metodología](#2-alcance-y-metodología)
3. [Cuadro de resultados](#3-cuadro-de-resultados)
4. [Indexabilidad y rastreo](#4-indexabilidad-y-rastreo)
5. [Higiene del índice](#5-higiene-del-índice)
6. [Rendimiento y Core Web Vitals](#6-rendimiento-y-core-web-vitals)
7. [Datos estructurados](#7-datos-estructurados)
8. [Hallazgos técnicos](#8-hallazgos-técnicos)
9. [Analítica, cookies y privacidad](#9-analítica-cookies-y-privacidad)
10. [Protocolo de medición](#10-protocolo-de-medición)
11. [Plan de acción técnico](#11-plan-de-acción-técnico)
12. [Limitaciones](#12-limitaciones)
13. [Glosario técnico](#13-glosario-técnico)

---

## 1. Contexto y veredicto

### El sitio

`caminodeldharma.org` es el sitio de la Comunidad Buddhista Camino del Dharma, una comunidad budista sin ánimo de lucro fundada en Cali en 2012, de tradición Chan y Tierra Pura. El sitio informa sobre la comunidad, su linaje y su práctica, publica eventos y un blog, y canaliza el contacto de quien quiere acercarse. **No hay transacciones ni área privada**: no se venden productos, no hay registro de usuarios ni autenticación.

Datos que condicionan varias decisiones de este informe:

| Hecho | Implicación |
|---|---|
| **Sitio estático**, sin base de datos ni backend | No hay superficie de ataque de servidor; el formulario no puede procesar envíos por sí solo |
| **Publicado el 18 de julio de 2026** (v1.0.0) | Al auditarse tenía **un día de vida**. Toda observación sobre indexación debe leerse como progreso de rastreo, no como fallo |
| **Es una etapa temporal**: será sustituido por WordPress | No procede ninguna configuración de compromiso largo (HSTS de un año, `preload`) |
| **Sin analítica y sin cookies propias** | Decisión formalizada de la comunidad. Verificado: producción no sirve ninguna cookie propia |
| **13 páginas publicadas** | Sitio pequeño: fue posible probarlas todas, sin muestreo |

### Veredicto

**La salud técnica del sitio es alta y está verificada por dos vías independientes.** La evaluación interna dio **8 de 8 controles superados**, y PageSpeed Insights (Lighthouse 13.4.0) puntúa **SEO 100 y Rendimiento 99 en móvil / 100 en escritorio**.

**No hay ningún hallazgo crítico ni ningún defecto técnico que esté frenando la indexación.**

Esto conviene decirlo con precisión, porque es el error de lectura más común en este proyecto: **la baja visibilidad orgánica del sitio no tiene causa técnica.** Lo técnico está resuelto.

La brecha está en dos cosas que no se corrigen desde el código:

- **Autoridad del dominio.** Prácticamente ningún sitio externo enlaza hacia él: en las escalas habituales del sector puntúa **2 sobre 100**, frente a una mediana de 17 entre comunidades comparables. Dos publicaciones especializadas describen a la comunidad en sus artículos, pero **ninguna incluye la dirección del sitio** en el texto — una de ellas enlaza a Facebook en su lugar.
- **Volumen de contenido.** Una sola entrada de blog en el momento de la auditoría.

**Ninguna tarea de este informe resolverá esas dos cosas.** Conviene tenerlo presente para no atribuir a la implementación técnica un resultado que no depende de ella — y también al revés: que la visibilidad tarde en llegar no indica que estas tareas se hayan hecho mal.

---

## 2. Alcance y metodología

Auditoría de **solo lectura** sobre el sitio en producción y su código fuente. No se modificó nada durante la evaluación; los cambios derivados se implementaron después, en una fase separada.

### Cobertura

- **13 direcciones** del mapa del sitio: **todas probadas**, sin muestreo.
- Página de error 404, puntos de entrada y archivos técnicos (`robots.txt`, `sitemap.xml`, `llms.txt`).
- Los **4 módulos de JavaScript**, revisados íntegramente.
- Validación local completa de los datos estructurados.
- Verificación de integridad de enlaces internos mediante script.
- **Comparación byte a byte de producción contra el código fuente: 14 de 14 páginas idénticas.**
- Perfiles de pantalla evaluados: **1440×900, 390×844 y 360×800**.

### Instrumentación

| Herramienta | Uso |
|---|---|
| PageSpeed Insights / Lighthouse 13.4.0 | Rendimiento y Core Web Vitals, móvil y escritorio |
| API de rendimiento del navegador | Mediciones base (3 ejecuciones por perfil) |
| `curl` y `openssl` | Inspección pasiva de cabeceras y TLS |
| Google Search Console | Datos de indexación de primera parte |
| Navegación real a Google (`hl=es`, `gl=co`) | Verificación de resultados sin personalización |

### Restricciones respetadas

No se enviaron formularios, no se crearon cuentas, no se resolvieron CAPTCHAs y no se realizaron pruebas de seguridad activas. Cuando la evidencia estaba condicionada por alguna de estas restricciones, se solicitó una ejecución manual al propietario y se registró su origen.

---

## 3. Cuadro de resultados

| Área | Puntuación | Cobertura | Confianza |
|---|---:|---:|---|
| SEO interno / on-page | **100** | 90 % | Alta |
| SEO técnico (estados, canónicas, mapa del sitio, redirecciones) | **100** | 90 % | Alta |
| Datos estructurados | **100** | 95 % | Alta |
| Arquitectura de contenido | **100** | 80 % | Media |
| Accesibilidad | **100** | 70 % | Media |
| Diseño adaptable | **100** | 80 % | Alta |
| Eficiencia de ejecución | **100** | 70 % | Media |
| Core Web Vitals | **90** | 70 % | Alta |
| Arquitectura y mantenibilidad | **90** | 60 % | Media |
| Rendimiento | **85** | 90 % | Alta |
| Buenas prácticas | **85** | 75 % | Media |
| Calidad de código | **78** | 70 % | Media |
| Preparación para búsqueda con IA | **70** | 90 % | Alta |
| Seguridad | **61** | 90 % | Alta |
| | | | |
| **Preparación para producción** | **80** | 85 % | Alta |

> **Sobre la puntuación de rendimiento.** Una medición preliminar dio 67 y PageSpeed da 99. No se contradicen: el 67 medía **controles de higiene** que siguen incumplidos (dimensionado de imágenes, versionado de archivos), mientras que el 99 mide la **experiencia entregada**, que es excelente. El cuadro reconcilia ambas lecturas en 85.

---

## 4. Indexabilidad y rastreo

**8 de 8 controles superados.**

| Control | Resultado |
|---|---|
| `robots.txt` abierto y con referencia al mapa del sitio | Correcto |
| `sitemap.xml` con 13 direcciones, coincidencia exacta con las páginas publicadas | Correcto |
| Etiquetas canónicas presentes y coherentes | Correcto |
| Política de URL sin barra final, impuesta por redirección permanente | Correcto |
| Títulos y descripciones únicos en todas las páginas | Correcto |
| Enlaces internos rotos | **Ninguno** |
| Rutas heredadas redirigidas correctamente | Correcto |
| `hreflang` | No aplica — sitio solo en español |

### Acceso de rastreadores de inteligencia artificial

Ocho rastreadores verificados **sin bloqueo ni contenido diferenciado**: GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot y Bingbot.

Coste de contexto por página: **400–1 600 tokens** (bajo). Cero patrones de inyección de instrucciones. Lighthouse otorga **3 de 3** en navegación agéntica, tanto en móvil como en escritorio.

**No hay ninguna acción pendiente en esta área.**

---

## 5. Higiene del índice

**Severidad: Media · Estado: corregido en fuente y publicado**

El dominio alojó una instalación de WordPress antes del sitio actual. El reemplazo se publicó sin un mapa de redirecciones para las direcciones antiguas, lo que dejó restos en los buscadores:

| Dirección residual | Estado detectado |
|---|---|
| `www.caminodeldharma.org/prueba/` | Redirección a 404 — página titulada «prueba», visible en resultados de marca |
| `caminodeldharma.org/category/noticias/` | Redirección a 404 |
| `caminodeldharma.org/category/sin-categoria/` | Redirección a 404 |
| `caminodeldharma.org/?page_id=10` | Copia duplicada de la portada (respuesta 200) |

### Corrección aplicada

Reglas añadidas al archivo de configuración del servidor:

- `410` (retirada definitiva) para `/prueba`
- `301 category/*` → `/blog`
- `301 ?page_id=10` → `/comunidad`
- `301 ?page_id=*` → `/`

### Criterios de aceptación

- `/prueba` debe responder **410**
- `/category/noticias` debe resolver a **200 en `/blog`**
- `?page_id=10` debe redirigir a `/comunidad`
- Tras el reindexado, la consulta `site:` no debe mostrar direcciones heredadas

**Matiz relevante:** verificado en Google Colombia, el listado de marca **ya estaba limpio** antes de la corrección; los restos aparecían en buscadores secundarios. La corrección sigue siendo pertinente, pero su urgencia era menor de lo que sugería la primera medición.

### Punto a vigilar: variantes de host y protocolo

Search Console contabiliza por separado tres formas de la portada:

| Dirección | Clics | Posición media |
|---|---:|---:|
| `https://caminodeldharma.org/` | 7 | 3,00 |
| `https://www.caminodeldharma.org/` | 1 | 8,33 |
| `http://caminodeldharma.org/` | 1 | 1,00 |

Las tres redirigen correctamente (301 verificado). Google aún no ha consolidado las señales en la dirección canónica, lo cual **es normal en un sitio de dos días**.

**Acción:** si a las 4–8 semanas siguen apareciendo separadas, investigar la consolidación.

---

## 6. Rendimiento y Core Web Vitals

**Fuente: PageSpeed Insights / Lighthouse 13.4.0, 20 de julio de 2026.**

| Métrica | Móvil (4G lenta) | Escritorio | Umbral |
|---|---:|---:|---|
| **Rendimiento** | **99** | **100** | — |
| Accesibilidad | 100 | 100 | — |
| Prácticas recomendadas | 100 | 100 | — |
| SEO | 100 | 100 | — |
| Navegación agéntica | 3/3 | 3/3 | — |
| **LCP** (mayor elemento visible) | **1,4 s** | 0,4 s | < 2,5 s ✅ |
| **FCP** (primer contenido visible) | 0,9 s | 0,3 s | < 1,8 s ✅ |
| **TBT** (bloqueo total) | 0 ms | 0 ms | < 200 ms ✅ |
| **CLS** (estabilidad visual) | **0,081** | 0,005 | < 0,1 ✅ |
| Índice de velocidad | 1,0 s | 0,3 s | — |
| **INP** (interacción) | **no disponible** | no disponible | requiere datos de campo |

**Todas las métricas medibles están en verde.**

### Dos observaciones

**1. El desplazamiento de diseño es exclusivo de móvil** (0,081 frente a 0,005: dieciséis veces mayor). Sigue dentro del umbral aceptable, pero cualquier cambio futuro en la portada podría empujarlo por encima de 0,1. **Vigilar tras cada cambio de maquetación en la home.**

Nota metodológica: una medición preliminar registró CLS 0 en ambos perfiles. Se midió sin limitación de red, que es precisamente la condición en la que un desplazamiento tardío no llega a registrarse. El valor de PageSpeed es el correcto.

**2. Google no dispone de datos de campo para este origen.** El informe indica «No hay datos»: el sitio no alcanza el umbral de tráfico del conjunto CrUX. Esto confirma el diagnóstico de visibilidad por una vía independiente, y significa que **INP no podrá medirse** hasta que haya más tráfico o se instrumente medición de usuarios reales.

### Oportunidades abiertas — ninguna urgente

| Oportunidad | Ahorro móvil | Ahorro escritorio | Relación |
|---|---|---|---|
| Entrega de imágenes adaptadas | **185 KB** | 42 KB | Falta `srcset` |
| Recursos que bloquean el renderizado | 450 ms | 200 ms | — |
| Minificar CSS | 3 KB | 3 KB | Menor |
| Versionado para caché eficiente | 1 KB | 1 KB | Ver §8 |

La proporción 185 KB en móvil frente a 42 KB en escritorio (4,4×) es la firma característica de la falta de `srcset`: al móvil se le entregan imágenes dimensionadas para pantallas grandes. El caso más claro es el **logotipo de 1000 px (46 KB servidos) renderizado en un hueco de 44 px**.

### Señales marcadas por Lighthouse pese al 100 en Prácticas

Política de seguridad de contenido efectiva frente a XSS, política HSTS sólida, aislamiento COOP y Trusted Types. Las dos primeras **corroboran de forma independiente** hallazgos ya registrados (§8). COOP y Trusted Types no se elevan a hallazgo: superficie mínima en un sitio estático sin autenticación ni datos de usuario.

---

## 7. Datos estructurados

**Puntuación: 100/100.** Validación local completa.

| Tipo | Ubicación | Estado |
|---|---|---|
| `Organization` | Portada | Correcto — incluye fundación 2012, origen Cali y áreas de conocimiento |
| `Event` | Páginas de eventos | Correcto |
| `BlogPosting` | Entradas del blog | Correcto |
| `llms.txt` | Raíz | Presente y servido |

### Dos decisiones deliberadas que conviene registrar

**Se eliminó el `alternateName` «budismo en Colombia».** No era un nombre real de la organización sino una palabra clave, y declarar palabras clave como nombres alternativos constituye marcado estructurado engañoso — con riesgo de acción manual sobre un dominio que ya parte de una autoridad mínima. Se sustituyó por señales verificables: `foundingDate` 2012, `foundingLocation` Cali y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena), reforzadas con mención textual visible en portada y `/comunidad`.

**Se descartó cubrir la grafía «buddhista» con texto invisible o `aria-label` cargado de palabras clave.** Es spam según las Search Essentials de Google, los atributos ARIA no son señal de posicionamiento —por lo que **no funcionaría**— y degradaría la experiencia de lectores de pantalla. La solución adoptada fue contenido visible: una nota editorial sobre el término en `/linaje`.

---

## 8. Hallazgos técnicos

| ID | Severidad | Hallazgo | Estado |
|---|---|---|---|
| FUNC-001 | **Alta** | Formulario de contacto con `action="#"` y sin backend: los mensajes se pierden en silencio | Mitigado con CTAs de WhatsApp y correo. Solución definitiva **en espera**: ver nota abajo |
| FUNC-002 | Media | Descarga `.ics` con ruta relativa que rompe bajo la política de URL sin barra final | **Corregido** |
| SEC-001 | Media | HSTS no activa | **Aplazado deliberadamente** hasta después de la migración a WordPress |
| SEC-002 | Media | Política de seguridad de contenido limitada a `upgrade-insecure-requests`: sin restricciones de script, frame ni objeto | Abierto |
| PERF-001 | Media | Imágenes sobredimensionadas, sin `srcset` | Abierto |
| PERF-002 | Baja | CSS y JS con caché de 7 días **sin versionado**: un despliegue puede servir archivos obsoletos hasta una semana | Abierto |
| A11Y | Baja | Galería renderizada solo por JavaScript, sin alternativa `noscript` | Abierto |
| SEC-003 | Baja | Ausencia de `/.well-known/security.txt` pese a mantenerse `SECURITY.md` | Abierto |
| PRIV-001 | Baja | Diez vídeos incrustados (8 YouTube + 2 Vimeo) sin variante `nocookie`; política de privacidad pendiente | Abierto |
| INFO-001 | Informativa | Cadena de redirección de dos saltos en la entrada `www` por HTTP; JavaScript servido como `application/x-javascript` | Abierto |

### Sobre el formulario de contacto (FUNC-001)

El formulario apuntaba a `action="#"` sobre un alojamiento estático sin ningún manejador de envío: **quien escribía creía haber contactado a la comunidad y el mensaje se perdía sin aviso**. Verificado por revisión de código y ausencia confirmada de manejador; no se comprobó mediante un envío real, por restricción de alcance.

**Mitigación ya desplegada:** el formulario se sustituyó por accesos directos de WhatsApp y correo, ambos operativos. Hoy nadie queda sin respuesta.

**Por qué no se implementó una solución definitiva:** un formulario funcional en un sitio estático exige un servicio externo de procesamiento, con su configuración, su mantenimiento y su tratamiento de datos personales. Es una decisión de la comunidad —no técnica— si ese canal adicional aporta algo sobre WhatsApp y correo. **No implementar nada es un desenlace válido de esa decisión.**

### Sobre la cabecera HSTS

Es **un criterio de seguridad de transporte entre muchos**, no el objetivo de la auditoría. La decisión vigente es un **despliegue escalonado**:

- **Fase 1 (ahora):** `max-age=604800` (7 días), reversible en días.
- **Fase 2 (post-migración):** `max-age=31536000` únicamente tras un corte a WordPress estable y verificado.

**No se recomienda el compromiso de un año, ni `preload`, ni `includeSubDomains` mientras el sitio actual sea temporal.** La evaluación de `includeSubDomains` está además bloqueada por la imposibilidad de enumerar subdominios (§12).

---

## 9. Analítica, cookies y privacidad

**No se utilizará Google Analytics.** Decisión formalizada de la comunidad.

El motivo es de utilidad, no ideológico: sin datos de campo en el conjunto de Google, con autoridad mínima y ausencia de las búsquedas amplias, una herramienta de analítica produciría un puñado de sesiones al mes — ruido estadístico, no información. El cuello de botella medido no es qué hacen las visitas, sino **que no llegan**, y esa pregunta la responde Search Console: gratis, sin cookies y sin banner de consentimiento.

**Verificado:** el sitio en producción **no sirve ninguna cookie propia**. Es una posición poco común que conviene preservar: elimina la necesidad de banner, reduce la superficie legal y encaja con el registro editorial de la comunidad.

**Queda pendiente** el cambio de los diez vídeos incrustados a variantes sin cookies, y la publicación de la política de privacidad —recomendable con independencia de las cookies, porque la normativa colombiana de protección de datos cubre el tratamiento de datos personales en general—.

**Si algún día hiciera falta medir comportamiento**, la vía será analítica sin cookies, nunca volver a una herramienta con seguimiento.

---

## 10. Protocolo de medición

Sin este protocolo, comparar la medición base con las siguientes no significa nada.

1. **Mismo método:** navegación real a `google.com/search` con `hl=es&gl=co`, **sin sesión iniciada** para evitar personalización. Archivar el texto de los resultados.
2. **Misma lista de palabras clave**, añadiendo nuevas al final sin alterar las existentes.
3. **Misma herramienta de autoridad** y **mismo día** para el dominio propio y las cinco comunidades comparables.
4. **Exportar los datos de Search Console** en cada medición: consultas, páginas, países, dispositivos y gráfico.
5. **Registrar la fecha exacta** y la versión del sitio en producción.

**La regla que hace comparables las mediciones:** misma herramienta, mismo método, mismo mercado, misma lista de palabras clave. Comparar métricas de proveedores distintos no aporta información.

**Cuándo:** entre el 17 de agosto y el 14 de septiembre de 2026. Antes no.

---

## 11. Plan de acción técnico

| Prioridad | Acción | Esfuerzo | Estado |
|---|---|---|---|
| 1 | Añadir `srcset` y redimensionar logotipo y miniaturas | ~2 h | Abierto |
| 2 | Versionar CSS y JS para permitir caché larga sin servir archivos obsoletos | ~1 h | Abierto |
| 3 | Ampliar la política de seguridad de contenido | ~2 h | Abierto |
| 4 | Cambiar los diez vídeos incrustados a variantes sin cookies | ~30 min | Abierto |
| 5 | Publicar `/.well-known/security.txt` | 15 min | Abierto |
| 6 | Alternativa `noscript` para la galería | ~1 h | Abierto |
| 7 | Reducir la cadena de redirección de la entrada `www` por HTTP | ~30 min | Abierto |
| 8 | Corregir el tipo MIME de JavaScript | 15 min | Abierto |
| 9 | Verificar consolidación de host y protocolo en Search Console | 15 min | **A las 4–8 semanas** |
| — | HSTS Fase 1 (`max-age=604800`) | 30 min | **Aplazado** — post-WordPress |
| — | Formulario con backend | — | **Bloqueado** — decisión de la comunidad |

---

## 12. Limitaciones

1. **INP no medible:** el conjunto de datos de campo de Google no tiene información para este origen. Solo se cerrará con más tráfico o instrumentando medición de usuarios reales.
2. **Los datos de PageSpeed son de laboratorio**, no de campo (móvil: dispositivo emulado con red 4G lenta). No sustituyen la experiencia real de los usuarios.
3. **Sin pase con tecnología de asistencia:** la accesibilidad se evaluó por semántica ARIA y revisión de código, no con un lector de pantalla real. La puntuación refleja controles estructurales.
4. **Muestreo de contraste:** calculado para selectores representativos en dos plantillas, no exhaustivamente para cada elemento y estado.
5. **Vista DNS parcial:** el resolutor local agotó el tiempo en registros AAAA, NS y MX. La enumeración de subdominios no fue posible, lo que bloquea por diseño la evaluación de `includeSubDomains`.
6. **Historial de certificados no disponible:** el servicio de registros de transparencia respondió con error en dos intentos. La fiabilidad de renovación se infiere del certificado vigente, gestionado por la plataforma.
7. **Una sola geografía y red:** comportamiento del CDN en otras ubicaciones no verificado.
8. **Formularios no enviados**, por restricción de alcance acordada. El hallazgo FUNC-001 se confirma por código fuente y ausencia verificada de manejador, no por un envío real.
9. **Alcance legal:** los hallazgos de privacidad describen comportamiento observado; las conclusiones de cumplimiento normativo requieren asesoría jurídica y no se afirman.

---

## 13. Glosario técnico

| Término | Significado |
|---|---|
| **Canónica** | Etiqueta que indica cuál es la dirección oficial de una página cuando existen varias formas de llegar a ella |
| **CLS** | Desplazamiento acumulado del diseño: mide si los elementos se mueven mientras la página carga |
| **Core Web Vitals** | Conjunto de métricas de Google sobre velocidad y estabilidad percibidas por el usuario |
| **CrUX** | Conjunto de datos de Google con mediciones de usuarios reales. Requiere un mínimo de tráfico |
| **CSP** | Política de seguridad de contenido: restringe qué recursos puede cargar y ejecutar una página |
| **FCP / LCP** | Momento en que aparece el primer contenido / el mayor elemento visible |
| **HSTS** | Cabecera que obliga al navegador a usar siempre conexión segura con el dominio |
| **INP** | Latencia de interacción: mide la respuesta a clics y pulsaciones. Solo medible con datos reales |
| **Redirección 301 / 410** | Reenvío permanente a una nueva dirección / declaración de retirada definitiva |
| **`srcset`** | Atributo que permite servir versiones de una imagen adaptadas al tamaño de pantalla |
| **TBT** | Tiempo total durante el cual la página no responde a interacciones mientras carga |

---

*Auditoría realizada sobre el sitio en producción y su código fuente, sin modificarlos. Toda afirmación procede de una medición registrada; las limitaciones se declaran en §12.*
