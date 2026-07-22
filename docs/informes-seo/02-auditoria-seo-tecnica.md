# Auditoría SEO Técnica

## Comunidad Buddhista Camino del Dharma — caminodeldharma.org

| | |
|---|---|
| **Cliente** | Comunidad Buddhista Camino del Dharma |
| **Sitio auditado** | https://caminodeldharma.org |
| **Versión en producción** | v1.0.19 (`/galeria` pendiente de despliegue) |
| **Fecha del informe** | 20 de julio de 2026 · **actualizado el 21 de julio** |
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
10. [Migración a WordPress: verificación del corte](#10-migración-a-wordpress-verificación-del-corte)
11. [Protocolo de medición](#11-protocolo-de-medición)
12. [Plan de acción técnico](#12-plan-de-acción-técnico)
13. [Limitaciones](#13-limitaciones)
14. [Glosario técnico](#14-glosario-técnico)
15. [Cambios posteriores a la auditoría](#15-cambios-posteriores-a-la-auditoría-21-de-julio)
16. [Trabajo de rendimiento (v1.0.18–v1.0.19)](#16-trabajo-de-rendimiento-21-de-julio-v1018v1019)

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
| **14 páginas publicadas** | Sitio pequeño: fue posible probarlas todas, sin muestreo |

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

- **13 direcciones** del mapa del sitio en el momento de la auditoría: **todas probadas**, sin muestreo. *(El sitio tiene 14 desde el 21 de julio; ver §15.)*
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

> **Sobre la puntuación de rendimiento.** Una medición preliminar dio 67 y PageSpeed da 99. No se contradicen: el 67 medía **controles de higiene** incumplidos (dimensionado de imágenes, versionado de archivos), mientras que el 99 mide la **experiencia entregada**, que es excelente. El cuadro reconcilia ambas lecturas en 85.
>
> **Actualización 2026-07-21:** de esos dos controles de higiene, el **dimensionado de imágenes está resuelto** en la portada (y en `/galeria`, pendiente de desplegar); el **versionado de archivos sigue incumplido** — ver la corrección de §8. La puntuación de 85 del cuadro no se ha recalculado a la espera de una medición homogénea de PageSpeed.

---

## 4. Indexabilidad y rastreo

**8 de 8 controles superados.**

| Control | Resultado |
|---|---|
| `robots.txt` abierto y con referencia al mapa del sitio | Correcto |
| `sitemap.xml` con 14 direcciones, coincidencia exacta con las páginas publicadas | Correcto |
| Etiquetas canónicas presentes y coherentes | Correcto |
| Política de URL sin barra final, impuesta por redirección permanente | Correcto |
| Títulos y descripciones únicos en todas las páginas | Correcto |
| Enlaces internos rotos | **Ninguno** |
| Rutas heredadas redirigidas correctamente | Correcto |
| `hreflang` | No aplica — sitio solo en español |

### Acceso de rastreadores de inteligencia artificial

Ocho rastreadores verificados **sin bloqueo ni contenido diferenciado**: GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot, Googlebot y Bingbot.

Costo de contexto por página: **400–1 600 tokens** (bajo). Cero patrones de inyección de instrucciones. Lighthouse otorga **3 de 3** en navegación agéntica, tanto en móvil como en escritorio.

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

### Oportunidades abiertas — situación al 20 de julio

| Oportunidad | Ahorro móvil | Ahorro escritorio | Relación |
|---|---|---|---|
| Entrega de imágenes adaptadas | **185 KB** | 42 KB | Falta `srcset` |
| Recursos que bloquean el renderizado | 450 ms | 200 ms | — |
| Minificar CSS | 3 KB | 3 KB | Menor |
| Versionado para caché eficiente | 1 KB | 1 KB | Ver §8 |

La proporción 185 KB en móvil frente a 42 KB en escritorio (4,4×) era la firma característica de la falta de `srcset`: al móvil se le entregaban imágenes dimensionadas para pantallas grandes. El caso más claro era el **logotipo de 1000 px (46 KB servidos) renderizado en un hueco de 44 px**.

### Actualización 2026-07-21 — tres de las cuatro oportunidades, cerradas

Los cambios de las versiones v1.0.18 y v1.0.19 atacan directamente este cuadro.

| Oportunidad | Estado | Qué se hizo |
|---|---|---|
| Entrega de imágenes adaptadas | **Cerrada en la portada**; pendiente de desplegar en `/galeria` | `srcset`/`sizes` en las imágenes de la portada; logotipo a 240 px; miniaturas dedicadas para el grid de la galería |
| Recursos que bloquean el renderizado | **Cerrada** | `normalize.css` incorporado a `main.css`: una sola hoja bloqueante en vez de dos peticiones encadenadas |
| Minificar CSS | **Cerrada** | Nuevo paso `npm run build:css`; las páginas enlazan `main.min.css` |
| Versionado para caché eficiente | **Abierta** | Sin implementar. Ver la corrección de §8 |

**Medido en producción el 2026-07-21** (peticiones directas al servidor, no Lighthouse):

| Recurso | Antes | Ahora |
|---|---:|---:|
| Hojas de estilo bloqueantes | 2 peticiones | **1 petición** |
| CSS servido (Brotli) | 8.407 b + 2.280 b = 10.687 b | **~5.900 b** |
| `logo.png` | 1000 px, 46.025 b | **240 px, 10.079 b** |
| MarloweEscapade | 52,1 KB | **3,4 KB** (subset a los 13 caracteres de "Camino del Dharma") |

**Pendiente de desplegar:** el grid de `/galeria` todavía entrega los originales a tamaño completo — unos **2 MB por página de 12 imágenes** para teselas de ~285 px. La corrección ya está en el repositorio (miniaturas con `srcset` 300w/600w, ~216 KB por página en móvil), pero no en producción.

> **Nota sobre las cifras.** La tabla de Lighthouse de arriba sigue siendo la del **20 de julio** y no se ha recalculado: la cuota diaria de la API de PageSpeed estaba agotada al redactar esta actualización. Las cifras de esta sección son mediciones directas de recursos en producción, que es un dato distinto —y más estable— que una puntuación de laboratorio. **Conviene relanzar PageSpeed Insights** una vez desplegado lo de `/galeria` para obtener una lectura homogénea con la del 20 de julio.

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
| FUNC-003 | Media | Enlaces de navegación a `/practica` resuelven a la raíz — **misma causa que FUNC-002**, detectada de nuevo el 21/07 | **Corregido en fuente**, pendiente de despliegue |
| SEC-001 | Media | HSTS no activa | **Aplazado deliberadamente** hasta después de la migración a WordPress |
| SEC-002 | Media | Política de seguridad de contenido limitada a `upgrade-insecure-requests`: sin restricciones de script, frame ni objeto | Abierto |
| PERF-001 | Media | Imágenes sobredimensionadas, sin `srcset` | **Corregido** (v1.0.19, 21/07). Portada desplegada; miniaturas de `/galeria` pendientes de despliegue |
| PERF-002 | Baja | CSS y JS con caché de 7 días **sin versionado**: un despliegue puede servir archivos obsoletos hasta una semana | **Abierto.** Se dio por hecho el 20/07 y no lo estaba — ver nota abajo |
| A11Y | Baja | Galería renderizada solo por JavaScript, sin alternativa `noscript` | **Abierto.** Se dio por hecho el 20/07 y no lo estaba — ver nota abajo |
| SEC-003 | Baja | Ausencia de `/.well-known/security.txt` pese a mantenerse `SECURITY.md` | Abierto |
| PRIV-001 | Baja | Diez vídeos incrustados (8 YouTube + 2 Vimeo) sin variante `nocookie` | Abierto |
| PRIV-001b | Baja | **Política de privacidad pendiente.** Independiente de las cookies: la Ley 1581/2012 cubre el tratamiento de datos personales, y el contacto por WhatsApp y correo recoge datos. **Además hay visitantes desde España** (Search Console: 1 clic / 2 impresiones), lo que abre la pregunta del RGPD | Abierto — **requiere asesoría legal**, ver §9 |
| INFO-001 | Informativa | Cadena de redirección de dos saltos en la entrada `www` por HTTP; JavaScript servido como `application/x-javascript` | Abierto |

### Corrección de estados (21 de julio)

Dos hallazgos de esta tabla —**PERF-002** (versionado de CSS/JS) y **A11Y** (galería sin `noscript`)— constaban como resueltos tras el despliegue del 20 de julio. **No lo estaban.** La verificación del 21 de julio lo confirma con dos comprobaciones directas:

| Hallazgo | Comprobación | Resultado |
|---|---|---|
| PERF-002 | `?v=` en las referencias css/js de producción | **Ninguna.** El historial del repositorio no registra ningún cambio que lo introdujera |
| A11Y | `curl https://caminodeldharma.org/galeria \| grep -c '<img'` | **1** — el logotipo. El grid sigue construyéndose entero en el navegador |

**Causa:** el cierre del despliegue del 20 de julio se hizo por confirmación global, sin comprobar tarea por tarea. Afectó a cuatro tareas en total; el detalle está en `.audit/implementation/results/DEPLOY-v1.0.14.md`.

**Consecuencia práctica para este informe:** el plan de acción de §12 sigue teniendo abiertas las prioridades 2 y 6, que se creían cerradas. PERF-002 gana además relevancia: al pasar de `main.css` a `main.min.css` el cambio de nombre invalidó la caché una vez, pero **la próxima edición de ese mismo archivo volverá a chocar con los 7 días de caché**.

### Sobre las rutas relativas (FUNC-002 y FUNC-003)

**Es la única clase de fallo que ha reaparecido en este proyecto**, y conviene entender por qué para que no vuelva.

La política canónica del sitio es **sin barra final** (`/practica`, no `/practica/`). Un navegador situado en `/practica` interpreta el último segmento como archivo, no como carpeta, y resuelve cualquier ruta relativa **un nivel por encima** de lo esperado:

| Página actual | Enlace relativo | Resuelve a | Esperado |
|---|---|---|---|
| `/practica` | `meditacion-semanal-en-linea` | `/meditacion-semanal-en-linea` (404) | `/practica/meditacion-semanal-en-linea` |
| `/practica/videos` | `..` | `/` | `/practica` |

FUNC-002 fue la descarga `.ics`; FUNC-003 son los enlaces de navegación de `/practica/videos`, que llevaban así en producción desde su publicación.

**Regla, registrada en ADR 0008:** todos los enlaces internos usan **ruta absoluta de raíz** (`/practica/...`). Las relativas solo son admisibles para recursos que cuelgan de la raíz (`../../assets/...`), donde el acotado las hace equivalentes.

**En WordPress:** `home_url()` y `get_permalink()` siempre; nunca rutas relativas escritas a mano en plantillas. **La política sin barra final y las rutas relativas son incompatibles.**

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

**Queda pendiente** el cambio de los diez vídeos incrustados a variantes sin cookies, y la publicación de la política de privacidad.

### Política de privacidad — por qué sigue siendo recomendable sin cookies

Es el punto que más se malinterpreta al leer «el sitio no usa cookies», así que conviene dejarlo explícito.

Descartar la analítica resolvió el problema **de las cookies**: sin cookies propias no hace falta banner de consentimiento. Pero eso **no cubre el tratamiento de datos personales**, que es un asunto distinto y más amplio.

| | Cookies | Datos personales |
|---|---|---|
| Qué exige | Consentimiento previo para las no esenciales | Informar del tratamiento: qué se recoge, para qué, quién responde y cómo ejercer derechos |
| Estado en este sitio | **Resuelto** — cero cookies propias, verificado en producción | **Abierto** |

**La [Ley 1581 de 2012](https://www.sic.gov.co/recursos_user/documentos/normatividad/Leyes/2012/Ley_1581_2012.pdf) cubre el tratamiento de datos personales en general, no solo el que ocurre por cookies.** (PDF oficial de la **Superintendencia de Industria y Comercio**, autoridad de protección de datos. Versión con **vigencia expresa y control de constitucionalidad** en la [Secretaría del Senado](http://www.secretariasenado.gov.co/senado/basedoc/ley_1581_2012.html) — ese sitio solo responde por HTTP, no ofrece HTTPS.) Y el sitio **sí recoge datos personales**: tras retirarse el formulario sin backend (FUNC-001), la vía de contacto son **WhatsApp y correo**, que reciben nombre, teléfono y el contenido de cada mensaje.

Es decir: la recogida de datos no desapareció con las cookies — **cambió de canal**.

> **Alcance.** Este informe constata un hecho técnico —hay recogida de datos personales por canales de contacto— y señala que la normativa aplicable no depende de la presencia de cookies. **La conclusión jurídica corresponde a asesoría legal, no a esta decisión técnica.** No se ofrece aquí ni el texto de la política ni una valoración de cumplimiento.

Concuerda con lo ya registrado en **ADR 0019** («Costos aceptados») y en `.audit/decisions.md`: la decisión sobre analítica **no elimina** la política de privacidad.

### RGPD — hay visitantes desde España

**El dato, y su fuente.** Search Console registra **1 clic y 2 impresiones desde España** sobre un total de 9 clics y 35 impresiones (2026-07-20). Es un volumen mínimo, pero **es tráfico real y está documentado** — y conviene subrayar que el dato viene de Search Console, no de analítica: el sitio no tiene, y aun así la procedencia se conoce.

Esto abre una segunda pregunta, distinta de la de la Ley 1581/2012. Lo que sigue **no la responde**: ordena los hechos que necesitaría quien deba responderla.

**Cómo se estructura la pregunta.** El RGPD se aplica fuera de la UE por dos vías (art. 3.2), y conviene mirarlas por separado porque este sitio está en posiciones muy distintas frente a cada una:

| Vía del art. 3.2 | Situación de este sitio |
|---|---|
| **(b) Observar el comportamiento** de personas en la UE | **No se da, y es verificable.** Cero cookies propias, sin analítica, sin píxeles, sin perfilado. Nada rastrea a nadie |
| **(a) Ofrecer bienes o servicios** a personas en la UE | **Es la vía que hay que valorar.** No es automática: el considerando 23 dice que la mera accesibilidad del sitio no basta; hace falta una **intención aparente** de dirigirse a personas de la UE |

**Elementos que un análisis jurídico querría pesar**, tal como están hoy en el sitio:

- **En contra de esa intención:** `areaServed` declarado **Colombia** en los datos estructurados; teléfono +57; sin transacciones ni área privada; todo el contenido referido a Colombia; los encuentros presenciales son en Barranquilla, Bogotá, Cali y Medellín.
- **El idioma no es indicio aquí.** El sitio está en español porque es la lengua de Colombia, no como señal de dirigirse a España. Distinto sería ofrecer precios en euros o un teléfono español.
- **El elemento a valorar:** la **meditación semanal en línea por Zoom no tiene restricción geográfica**. Es una actividad recurrente y abierta a la que alguien desde España puede sumarse, y la vía de acceso es WhatsApp — es decir, con recogida de datos personales.

**Lo práctico, que sí es técnico.** Las dos normas piden en gran medida lo mismo: informar de qué datos se recogen, para qué, quién responde y cómo ejercer derechos. **Una política de privacidad bien redactada cubre la mayor parte de ambas.** El RGPD añade elementos propios —base jurídica, plazos de conservación, algunos derechos adicionales— que quien la redacte sabrá incorporar si concluye que aplica.

### Dónde y cómo se publica

Los criterios de aceptación ya están fijados en el hallazgo PRIV-001 de la auditoría. La implementación es pequeña y no toca ninguna decisión de diseño:

| Paso | Detalle |
|---|---|
| **Página nueva** | `/privacidad`, siguiendo la política canónica de URL sin barra final |
| **Enlace en el pie** | En la región de pie de **todas las páginas** (incluida la 404), junto a `.footer-copyright`. Es la convención que la gente espera y no interfiere con la lectura |
| **Mapa del sitio** | Añadir la URL a `sitemap.xml` con su `<lastmod>` |
| **Sin banner** | No lleva ventana emergente ni aviso de cookies: no hay cookies que consentir. Solo la ve quien la busca |

**Criterio de aceptación (PRIV-001):** *«Privacy policy published and linked in the footer»*.

**Nota para la migración:** al pasar a WordPress, la página se recrea como `page` y el enlace del pie vive en la plantilla de pie del tema. Conviene incluirla en el checklist del corte (§10) para que no se pierda al reconstruir el pie.

> **Alcance, otra vez.** Determinar si el RGPD resulta aplicable a esta comunidad **es una conclusión jurídica y corresponde a asesoría legal.** Este informe aporta los hechos verificables —volumen y procedencia del tráfico, ausencia total de rastreo, ámbito declarado del sitio, carácter global de la actividad en línea— para que esa valoración se haga sobre datos y no sobre suposiciones.

**Si algún día hiciera falta medir comportamiento**, la vía será analítica sin cookies, nunca volver a una herramienta con seguimiento.

---

## 10. Migración a WordPress: verificación del corte

El corte queda previsto **después del 10 de agosto de 2026**, tras el Encuentro Nacional, y se habrá probado antes en un servidor y una URL temporales.

**El ensayo en preproducción reduce mucho el riesgo, pero no cubre lo que solo existe en el dominio real.** Esta sección enumera exactamente eso.

### Lo que un entorno de pruebas NO valida

| Área | Por qué no se transfiere |
|---|---|
| **`robots.txt` y la opción «Disuadir a los motores de búsqueda»** | Los entornos de prueba se configuran para no indexarse. WordPress guarda esa opción en **Ajustes → Lectura**, y viaja con la base de datos si se migra. **Es el fallo más común y más silencioso de una migración** |
| **Canónicas** | En preproducción apuntan a la URL temporal. Si se migra la base de datos sin reemplazar el dominio, quedan canónicas apuntando al servidor de pruebas |
| **`.htaccess`** | WordPress reescribe su propio bloque `# BEGIN WordPress` al instalarse. El archivo de producción contiene reglas que **no existen en preproducción**: redirecciones heredadas, limpieza de URLs de la etapa WordPress anterior, `AddType text/calendar` y cabeceras de seguridad |
| **Certificado y redirecciones de entrada** | `http→https` y `www→sin www` son propias del dominio real |
| **Search Console** | La propiedad es del dominio real; la indexación no puede validarse desde otra URL |
| **Rendimiento** | Otro servidor, otros recursos. Las métricas de preproducción no predicen las de producción |

### Verificación obligatoria, 24–48 h tras el corte

1. **`robots.txt`** no contiene `Disallow: /`, y **Ajustes → Lectura** tiene desmarcada la opción de disuadir buscadores. **Comprobar esto primero.**
2. **Canónicas** apuntan a `caminodeldharma.org`, sin rastro de la URL de pruebas. Buscar la URL temporal en toda la base de datos y en el HTML servido.
3. **Sin `noindex`** en ninguna página del sitemap.
4. **Paridad de URLs:** todas las direcciones del `sitemap.xml` previo responden 200 y su canónica no cambió.
5. **Patrón de URL de los eventos:** WordPress debe generar `/eventos/{slug}/` — en plural, sin fecha intercalada ni `/evento/` en singular. **Comprobarlo explícitamente**, porque es donde un CMS impone su propio patrón por defecto: los eventos que hoy solo existen como tarjetas del listado nacerán en WordPress sin un «antes» contra el que compararse, y un patrón equivocado pasaría inadvertido.
6. **Enlaces internos absolutos:** ningún `href` interno resuelve a la raíz por usar ruta relativa. Es la única clase de fallo que ha reaparecido en el proyecto (FUNC-002 y FUNC-003) y WordPress la reintroduce con facilidad si las plantillas escriben rutas a mano. Recorrer los enlaces de las subpáginas y verificar destino real.
7. **`.htaccess`:** confirmar que sobrevivieron las redirecciones heredadas, la limpieza de URLs antiguas, `AddType text/calendar` y las cabeceras de seguridad.
8. **Sin cookies propias:** verificar que ni WordPress ni ningún plugin las introduce.
9. **Datos estructurados** íntegros tras el cambio de plantillas — incluidos los `Event` del archivo, con su `addressLocality`, y el `EventSeries` de la meditación semanal.
10. **`sitemap.xml` y `robots.txt`:** WordPress genera los suyos — no deben duplicar ni contradecir a los actuales.
11. **`llms.txt`** sigue servido, con la entrada de la meditación semanal.
12. **Descargas `.ics` y diálogo de calendario** operativos.
13. **Rendimiento** contra la fotografía previa: PHP y plugins cambian el perfil.

### Antes del corte, imprescindible

**Fotografía completa del estado actual**, 2–3 días antes: export de Search Console, PageSpeed móvil y escritorio, inventario de todas las URLs del sitemap con estado y canónica, posiciones de la batería de consultas, autoridad con la herramienta habitual, cabeceras completas de la portada y copia del `.htaccess` vigente.

**Sin un «antes» no hay forma de demostrar qué rompió la migración.**

**Criterio de vuelta atrás:** decidido de antemano, no durante. Copia del sitio estático lista para restaurar en minutos.

---

## 11. Protocolo de medición

Sin este protocolo, comparar la medición base con las siguientes no significa nada.

1. **Mismo método:** navegación real a `google.com/search` con `hl=es&gl=co`, **sin sesión iniciada** para evitar personalización. Archivar el texto de los resultados.
2. **Misma lista de palabras clave**, añadiendo nuevas al final sin alterar las existentes.
3. **Misma herramienta de autoridad** y **mismo día** para el dominio propio y las cinco comunidades comparables.
4. **Exportar los datos de Search Console** en cada medición: consultas, páginas, países, dispositivos y gráfico.
5. **Registrar la fecha exacta** y la versión del sitio en producción.

**La regla que hace comparables las mediciones:** misma herramienta, mismo método, mismo mercado, misma lista de palabras clave. Comparar métricas de proveedores distintos no aporta información.

**Cuándo:** entre el 17 de agosto y el 14 de septiembre de 2026. Antes no.

---

## 12. Plan de acción técnico

| Prioridad | Acción | Esfuerzo | Estado |
|---|---|---|---|
| 1 | Añadir `srcset` y redimensionar logotipo y miniaturas | ~2 h | **Hecho** (v1.0.19, 21/07). `/galeria` pendiente de despliegue |
| 2 | Versionar CSS y JS para permitir caché larga sin servir archivos obsoletos | ~1 h | **Abierto — ahora es la prioridad 1 real.** Se dio por hecho el 20/07 y no se había implementado |
| 3 | Ampliar la política de seguridad de contenido | ~2 h | Abierto |
| 4 | Cambiar los diez vídeos incrustados a variantes sin cookies | ~30 min | Abierto |
| 5 | Publicar `/.well-known/security.txt` | 15 min | Abierto |
| 6 | Alternativa `noscript` para la galería | ~1 h | **Abierto.** Se dio por hecho el 20/07 y no se había implementado |
| 7 | Reducir la cadena de redirección de la entrada `www` por HTTP | ~30 min | Abierto |
| 8 | Corregir el tipo MIME de JavaScript | 15 min | Abierto |
| 9 | Verificar consolidación de host y protocolo en Search Console | 15 min | **A las 4–8 semanas** |
| — | HSTS Fase 1 (`max-age=604800`) | 30 min | **Aplazado** — post-WordPress |
| — | **Publicar la política de privacidad** | — | **Requiere decisión y texto.** No es tarea técnica: la conclusión jurídica corresponde a asesoría legal, y ahora cubre dos normas (Ley 1581/2012 y, a valorar, RGPD por visitantes desde España). Recomendable aunque no haya cookies — ver §9 |
| — | Formulario con backend | — | **Bloqueado** — decisión de la comunidad |

---

## 13. Limitaciones

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

## 14. Glosario técnico

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

## 15. Cambios posteriores a la auditoría (21 de julio)

Trabajo desplegado después del informe original, en las versiones **v1.0.15** y **v1.0.16**. Se registra aquí para que el informe describa el sitio real.

### Meditación semanal en línea — página propia

`/practica/meditacion-semanal-en-linea`. Cierra ASO-002: la práctica continua de la comunidad ya no vive como párrafo dentro de otra página.

- `EventSeries` con `OnlineEventAttendanceMode` y `Schedule` (`P1W`, lunes, 19:30, `America/Bogota`)
- Entrada en `llms.txt`, en `sitemap.xml` y enlazada desde portada y `/practica`
- Vía de participación: **WhatsApp**. El enlace de la sala **no se publica** — decisión de la comunidad
- El bloque existente de `/practica` se conservó: informa (cuándo y cómo entro) mientras la página nueva explica (qué es y si es para mí)

**Sitemap: 13 → 14 direcciones.**

### Archivo de encuentros presenciales

Cinco encuentros ya celebrados, antes ausentes del sitio, incorporados al listado de eventos con datos estructurados completos:

| Fecha | Ciudad | `addressLocality` |
|---|---|---|
| 09/07/2026 | Barranquilla | Atlántico |
| 28/06/2026 | Bogotá | Bogotá D.C. |
| 23/05/2026 | Medellín | Antioquia |
| 22/05/2026 | Medellín | Antioquia |
| 09/05/2026 | Bogotá | Bogotá D.C. |

Todos con `Event`, `EventCompleted` y dirección completa. **Es la vía legítima de relevancia geográfica** para una comunidad sin sede física: ciudades declaradas sobre actividad real y verificable, no sobre páginas creadas para captar búsquedas.

### Mejoras del listado de eventos

- **Agrupación por año** con encabezados reales (`h3` para el año, `h4` para los títulos), de modo que un lector de pantalla pueda saltar entre años en lugar de recorrer todas las tarjetas
- **Carga diferida** en 8 de las 9 imágenes de evento: difiere ~1,8 MB. La primera queda en carga inmediata por ser candidata a LCP
- Todas declaran `width` y `height`, así que la carga diferida **no introduce desplazamiento de diseño**

> **Nota sobre las imágenes.** No se redimensionaron los carteles. Miden 1024–1122 px de ancho y el contenedor de lectura llega a ~520–580 px, que en pantallas de alta densidad equivalen a ~1 100 px: **no están sobredimensionados**. Reducirlos habría degradado la nitidez sin ganancia real. PERF-001 se refiere a un caso distinto —el logotipo de 1000 px en un hueco de 44 px—, que sigue abierto.

### FUNC-003

Detectado y corregido en esta tanda. Ver §8.

---

*Auditoría realizada sobre el sitio en producción y su código fuente, sin modificarlos. Toda afirmación procede de una medición registrada; las limitaciones se declaran en §13.*
---

## 16. Trabajo de rendimiento (21 de julio, v1.0.18–v1.0.19)

Cierra tres de las cuatro oportunidades del cuadro de §6. Se registra aquí con las mediciones que lo sostienen.

### Una sola hoja de estilos bloqueante

PageSpeed señalaba una cadena crítica `documento → normalize.css`: dos hojas bloqueantes en serie. `normalize.css` se incorporó al inicio de `main.css` y se retiró su enlace de las 15 páginas.

| | Peticiones | Servido (Brotli) |
|---|---:|---:|
| Antes | 2 | 8.407 b + 2.280 b = 10.687 b |
| Después | **1** | **~5.900 b** |

Comprimido dentro de `main.css`, el bloque de `normalize` cuesta ~500 b en vez de 2.280 b: Brotli aprovecha la redundancia entre ambos. La auditoría de dependencias de red pasa.

### CSS minificado — nuevo paso de build

Las páginas enlazan `main.min.css`, generado con `npm run build:css`. **Es el único paso de build del proyecto**; `main.css` sigue siendo el único archivo que se edita. Queda documentado en el checklist de despliegue: si se toca `main.css` y no se regenera, el paquete sale con CSS antiguo.

### Tipografía subsetada

`MarloweEscapade` pesaba **52,1 KB** y solo dibuja dos elementos —`.site-name` y `.site-title`—, que en las 15 páginas dicen lo mismo: "Camino del Dharma". Subsetada a esos 13 caracteres queda en **3,4 KB (−93 %)**.

Como contrapartida, dejó de poder usarse como respaldo de `--font-heading`: sobre texto arbitrario dibujaría solo algunos glifos y el resto caería a serif, mezclados. Si ese texto cambia, hay que regenerar el subset con `scripts/build-fonts.sh`.

### Imágenes adaptadas

- **Logotipo:** 1000 px / 46 KB → **240 px / 10 KB**.
- **Portada:** `srcset` con `sizes` reales. El primer intento usó `100vw`, que sobreestimaba el ancho —el real es 327 px, no 375— y hacía que el móvil se saltara la variante pequeña; corregido a `calc(100vw - 3rem)`.
- **Galería:** el grid entregaba los **originales completos**, ~2 MB por página de 12 imágenes, para teselas de ~285 px. Ahora sirve miniaturas dedicadas con `srcset` 300w/600w: **~216 KB por página en móvil**. **Pendiente de despliegue.**

> **Corrección de un diagnóstico previo.** En el análisis intermedio se afirmó que `/galeria` necesitaba los originales a tamaño completo porque los usaba un visor ampliado. Es falso: `gallery.js` no tiene visor ni ningún manejador de clic sobre las imágenes —los únicos son de paginación—. Los originales se servían como simples teselas, que es justo lo que PERF-001 describía.

### Lo que se revisó y se decidió no tocar

| Señal de PageSpeed | Decisión |
|---|---|
| «Evitar redirecciones» | **Nada que corregir.** La dirección canónica resuelve con **0 saltos**; solo quedan `http→https` y `www→sin www`, ambos necesarios y correctos |
| «Mejorar la caché» | **Aplazado.** El ahorro que estima la propia herramienta es de 1 KB, y alargar la caché exige antes el versionado de PERF-002 |
| «Latencia del documento inicial» | **Sin margen.** El desglose de LCP da TTFB de 0 ms |

### Estado de la medición

Las cifras de esta sección son **mediciones directas de recursos en producción**, no puntuaciones de laboratorio: la cuota diaria de la API de PageSpeed estaba agotada. La tabla de Lighthouse de §6 sigue siendo la del 20 de julio.

**Acción pendiente:** relanzar PageSpeed Insights una vez desplegado el cambio de `/galeria`, y actualizar §6 con una lectura homogénea.
