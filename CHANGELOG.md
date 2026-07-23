# Changelog

Historial de versiones publicadas en producción. Hostinger no conserva un registro de despliegues cuando se sube un ZIP manualmente; este archivo es la referencia canónica de qué está en vivo.

La versión actual del repositorio está en [`VERSION`](VERSION).

Formato de paquete de despliegue: `camino-del-dharma-vX.Y.Z.zip`

**Antes de incrementar la versión:** actualizar `<lastmod>` en [`sitemap.xml`](sitemap.xml) para cada página HTML modificada (ver checklist en [`README.md`](README.md#despliegue-en-hostinger)).

## [1.0.21] - 2026-07-23

### Meditación semanal — copy y metadatos

- `practica/meditacion-semanal-en-linea/index.html`: descripción más concisa (retirado «guiada» redundante); estructura de la sesión aclarada en el cuerpo; JSON-LD alineado.
- `llms.txt`: descripción de la meditación semanal sincronizada.
- `sitemap.xml`: `<lastmod>` `2026-07-23` en `/practica/meditacion-semanal-en-linea`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.20] - 2026-07-21

### Galería — miniaturas en el grid (cierra PERF-001)

- `assets/js/gallery.js`: el grid deja de servir los originales y usa `assets/images/galeria/thumbs/` mediante `<picture>` con `srcset` 300w/600w. Antes cada página de 12 imágenes entregaba **~2 MB** para teselas de ~285 px; ahora **~216 KB** en móvil.
- `assets/images/galeria/thumbs/`: miniaturas para las 36 imágenes (600 px jpg + webp, y 300 px webp).
- Si una miniatura no fuese derivable del nombre, `gallery.js` cae al original: peor peso, pero nunca un hueco roto.
- `sitemap.xml`: **sin cambio de `<lastmod>`** — cambio de assets y JS, no de contenido indexable en `/galeria`.

**Corrección de un diagnóstico previo:** en v1.0.17 se justificó conservar los originales porque `/galeria` los necesitaría para un visor ampliado. **Era falso** — `gallery.js` no tiene visor ni manejador de clic sobre las imágenes; los únicos son de paginación. Los originales se servían como simples teselas.

### Auditoría e informes

- `.audit`: **tres tareas figuraban COMPLETED sin cumplir su Definition of Done** (mismo patrón ya detectado en TASK-0017). TASK-0007 reatribuida a v1.0.19; **TASK-0010 y TASK-0011 revertidas a NOT DONE**. PERF-001 pasa a RESOLVED; PERF-002 y AEO-001 siguen abiertos. Detalle y evidencia en `.audit/README.md` y `.audit/implementation/results/DEPLOY-v1.0.14.md`.
- `docs/informes-seo/`: nueva §16 en el informe técnico con el trabajo de rendimiento, y corrección de estados en §8 y §12. Las cifras de Lighthouse del 20 de julio **se conservan fechadas**, sin recalcular: la cuota de la API de PageSpeed estaba agotada.
- **No se modificó `.audit/raw/` ni `evidence-ledger.jsonl`**: son la evidencia congelada de la auditoría del 2026-07-19.

### Documentación de WordPress — decisión sobre el visor ampliado

- **ADR 0021 (nuevo):** el lightbox de la galería será el **nativo del bloque de Gutenberg** (WP 6.4+). No se implementa visor propio en la maqueta estática ni se instalará plugin. Fija además la arquitectura de imágenes: miniaturas para el grid, originales para el visor — por eso los 36 originales **no se borran**.
- `03-wordpress-content-model.md` §5.1 (nueva): cómo se modela la galería en WordPress y qué se migra.
- `12-theme-file-structure.md`: `gallery.js` **no viaja al tema**; la galería pasa a bloque de Gutenberg.
- `15-assets-strategy.md`, `17-orden-implementacion.md`, `19-accesibilidad-estandares.md`, `migracion-static-wordpress.md`: alineados con la decisión.
- Nota de orden registrada: si alguna vez se añadiera un visor propio a la maqueta, **primero** hay que cerrar TASK-0010 (AEO-001), o se agrava la dependencia de JavaScript que ese hallazgo señala.

### Informes SEO — política de privacidad

- Precisado en ambos informes por qué **la política de privacidad sigue siendo recomendable aunque el sitio no use cookies**: la Ley 1581/2012 cubre el tratamiento de datos personales en general, y tras retirarse el formulario sin backend (FUNC-001) el contacto por WhatsApp y correo sigue recogiendo nombre, teléfono y mensajes. La recogida no desapareció con las cookies — cambió de canal.
- Nuevo apartado en §9 del informe técnico; entrada en §5 del informe general (donde se pide lo que depende de la comunidad); hallazgo **PRIV-001b** separado del de los vídeos; y fila propia en el plan de acción de §12, que antes solo recogía la mitad de PRIV-001.
- En los tres sitios queda explícito que **la conclusión jurídica corresponde a asesoría legal, no a la auditoría técnica**.
- **RGPD añadido al análisis.** Search Console documenta visitantes desde España (1 clic / 2 impresiones de 9 / 35 totales), así que los informes plantean también esa norma. Se separan las dos vías del art. 3.2: *observar el comportamiento* **no aplica y es demostrable** (cero cookies, sin analítica, sin perfilado — ADR 0019 protege también en este frente); *ofrecer servicios* se deja planteada con sus elementos, señalando que la meditación por Zoom no tiene restricción geográfica. **No se emite conclusión jurídica**: se aportan los hechos verificables para que la valoración se haga sobre datos.

### Documentación — `/privacidad` prevista, y un faltante detectado

- **`/privacidad` dada de alta en la documentación** (aún sin publicar): `11-arbol-urls-final` (páginas fijas, árbol y URL→plantilla), `04-mapa-pantallas` (páginas fijas, conjunto total y plantillas), `05-arquitectura-informacion-navegacion` (enlace en el pie, nunca en el menú) y `03-wordpress-content-model` (la cubre `page.php`, sin plantilla propia).
- **Faltante detectado de paso:** `/practica/meditacion-semanal-en-linea` **no estaba documentada** en `11-arbol-urls-final` ni en `04-mapa-pantallas`, pese a estar publicada desde el 21/07 y presente en `sitemap.xml` y `llms.txt`. Añadida a ambos.
- Informe 00 §5: la sección de privacidad se reescribió para el público de liderazgo — empieza explicando **qué es** una política de privacidad y **cómo se vería en el sitio** (página nueva, enlace en el pie, sin banners). Informe 02 §9: nueva subsección **«Dónde y cómo se publica»** con los criterios de aceptación de PRIV-001.

### Informe 00 — precisiones de redacción

- **§5 separa el requisito de la decisión.** Decía «¿En qué ciudades hay práctica real?» como si el dato resolviera el asunto. No lo resuelve: el dato es el **paso 1** (requisito, solo obtenible dentro de la comunidad) y la decisión —**a qué ciudades se les creará página**— es el paso 2. **Que haya práctica real habilita la página, no obliga a crearla.** Se añadió el riesgo inverso, antes ausente: una página sobre una ciudad con poco que decir queda vacía aunque la actividad sea real. §1 alineado con este cambio.
- **Registro impersonal.** «Se necesita de la comunidad» → «Se necesita»; «Lo que necesito de ustedes» → «Lo que se necesita»; encabezado de §5 e índice ajustados en consecuencia. §5 ya usaba la forma impersonal en su otra subsección.
- **Se eliminaron** el párrafo «Lo que cuesta» de §1 y toda la subsección «Lo que ya se corrigió» de §2.
- **Enlace a la fuente oficial de la Ley 1581 de 2012**, en la cita principal de cada documento (informe 00 §5, informe 02 §9 y ADR 0019). La fuente principal es el **PDF de la Superintendencia de Industria y Comercio**, autoridad de protección de datos, servido por HTTPS. El informe técnico enlaza además la versión de la **Secretaría del Senado**, que aporta vigencia expresa y control de constitucionalidad, con la advertencia de que ese sitio **solo responde por HTTP**.
  - *Procedencia de la verificación:* el enlace del Senado se comprobó automáticamente (HTTP 200, documento correcto). El de la SIC **lo confirmó el propietario de forma manual**: ese host no responde desde el entorno de trabajo. Una ruta anterior que se había supuesto para la SIC resultó incorrecta y se descartó por no poder verificarse.
- **§6 explica por qué el contenido nuevo mueve la visibilidad.** El informe señalaba el volumen de contenido como una de las dos causas de la baja visibilidad (§1, §3) y listaba acciones editoriales en §6, pero **nunca explicaba el mecanismo**: por qué un artículo más cambia algo. La nueva subsección lo cubre sin salir del documento — cada artículo es una puerta de entrada nueva, y conecta con la brecha ya medida en §2 (el sitio gana «budismo chan colombia» y pierde «dónde practicar budismo chan en Colombia»).
  - Incluye la precisión sobre **frecuencia**: publicar seguido **no es un factor de posicionamiento**; lo que cuenta es el acumulado de páginas útiles, y publicar de forma irregular no penaliza. Se añade la única restricción real de calendario: para que la medición del 17/08–14/09 diga algo sobre contenido, lo publicado debe llevar semanas arriba.
  - La fila «Depende de: *Ritmo editorial*» remitía a un concepto no definido en ninguna parte; ahora dice «Decisión de publicar y con qué ritmo».
  - **Sin remisión al brief editorial:** es un entregable para otra audiencia, y el `README` de la carpeta exige que cada informe se entienda por sí solo.
- **Autoría en los tres entregables.** `informes-seo/00`, `informes-seo/02` y `24-brief-editorial-blog-y-visibilidad` acreditan ahora a **Rafael Figueredo Oropeza**, con LinkedIn y correo, en la cabecera y en la nota de cierre. Los datos se tomaron del pie del sitio; se usaron los profesionales (LinkedIn y `refo44@gmail.com`) y **no** el Instagram personal.
- **Recomendación de frecuencia de publicación, con evidencia: una pieza cada 3–4 semanas.** El desarrollo completo —cuatro hechos con fuente, razonamiento y orden de las primeras piezas— va en el **brief editorial §6.1**, que es el documento de quien decide qué y cuándo escribir. El **informe 00 §6 conserva solo lo indispensable**: la cifra, por qué no conviene ir más rápido y la advertencia de plazos. **Las fuentes viven únicamente en el brief**, que es donde se decide el ritmo; el informe ejecutivo no las carga y atribuye a Google en el propio texto.
  - Evidencia: el ritmo no es señal de posicionamiento (Google/Mueller); el presupuesto de rastreo solo aplica desde ~10.000 páginas (documentación de Google — el sitio tiene 14); una página tarda **2–6 meses** en asentarse (Ahrefs/Semrush); el volumen sin valor está tipificado como *scaled content abuse* (políticas de spam de Google).
  - **§5 muestra ahora cómo quedarían las direcciones** de las páginas por ciudad, que era la parte que faltaba para poder decidir. Dos opciones con ejemplo real: artículo en `caminodeldharma.org/blog/budismo-en-cali` (liviano y reversible) o sección propia en `caminodeldharma.org/sanghas/cali` (sección que hoy no existe, con compromiso de mantenimiento indefinido). **Se recomienda empezar por el blog:** una dirección permanente promete una presencia estable que conviene demostrar antes de comprometer. Coherente con el brief, que acota su alcance a `/blog/` y aclara que no pide crear `/sanghas/`.
  - **Aclarado el plazo de «2 a 6 meses», que se malinterpretaba** (y luego recortado por sobreexplicado). Podía leerse como que el artículo tarda meses en estar disponible, cuando en la práctica se publica y se comparte por WhatsApp el mismo día sin problema. Ahora ambos documentos separan los dos canales: **difundir es inmediato y no depende del SEO**; lo que tarda de 2 a 6 meses es que **Google muestre el artículo a quien busca el tema sin conocer a la comunidad**. Compartir alcanza a quien ya está cerca; el buscador trae desconocidos, y ese es el camino lento.
  - **La tabla de acciones de §6 ahora lleva el ritmo y las búsquedas.** Antes solo decía «Editorial · Depende de: decisión de publicar», sin cifra ni destino: quien escaneaba la tabla no veía ninguna recomendación. Ahora cada fila nombra **qué búsqueda ataca** y bajo la tabla va el ritmo sugerido.
  - **Faltaba la acción más importante.** La prosa llamaba a la pieza sobre la meditación en línea «la mayor brecha», pero **no era una fila de la tabla**: solo estaban «pregunta y respuesta» y «por ciudad». Añadida como primera fila.
  - **Cierra el «calendario fantasma».** `23-sistema-editorial` remitía cuatro veces a un calendario del brief §6 que no existía, incluido un ítem de checklist imposible de marcar. Ahora existe (§6.1) y las referencias del doc 23 se precisaron para apuntar ahí.
  - **Corrige una afirmación previa mía.** Había escrito que bastaba publicar «con varias semanas de anticipación» para que la medición de septiembre dijera algo del contenido. Con un horizonte de 2 a 6 meses, **eso es falso**. §8 se ajustó: el hito de ~15 de septiembre pasa a medir **solo enlaces y autoridad**, y se añade un hito de **~diciembre–enero** para el contenido. Medir contenido en septiembre habría producido la conclusión falsa de que no funcionó.
- **Corregida una confusión conceptual en §5.** El texto presentaba la política de privacidad como «una página más del sitio». **No lo es:** la política es un **compromiso escrito** sobre cómo se tratan los datos personales; publicarla como página es solo la forma de darla a conocer. La distinción tiene consecuencia práctica — el compromiso cubre también los datos que llegan por WhatsApp y correo y quedan en un teléfono o una bandeja de entrada, es decir **fuera del sitio web**. Por eso no es una tarea del equipo web: es una decisión de la comunidad, y el equipo web solo publica el texto.
- **Aclarada la frase que explica la causa (§1).** Decía *«la comunidad recibe menciones […] pero ninguna recomendación enlazada»*: obligaba a desandar la metáfora (enlace = recomendación) para entenderla. Ahora separa las dos cosas — la comunidad **sí** recibe menciones, pero la nombran **sin poner la dirección de la web**, y una mención sin enlace no cuenta para Google. Es la frase que sostiene el diagnóstico central del informe.
- **Corregida una imprecisión de posicionamiento.** El informe afirmaba ser primero «en chan y tierra pura» (§2) y tener posiciones estables en «budismo chan» y «budismo tierra pura» (§9). Las consultas realmente medidas llevan el país: **«budismo chan colombia»** y **«budismo tierra pura colombia»**. Sin ese calificador la afirmación se lee como liderazgo en el tema a nivel general, que no es lo medido. El resumen ejecutivo del audit (`.audit/executive-summary.md`) sí lo decía bien; el calificador se perdió al adaptarlo al entregable.

### Documentación — la ciudad es taxonomía, no URL (ADR 0022)

- **ADR 0022 (nuevo):** no se crearán URLs de filtro por ciudad para eventos (`/eventos/cali`, `/eventos/ciudad/cali`). Queda **derogada** la condición de revisión del 2026-07-20 que las preveía al superar ~5 eventos por ciudad. Motivos: (1) contradecía `03-wordpress-content-model` §3, que ya define el escalado del listado **por año** — el volumen crece por fecha, no por ciudad; (2) el umbral era de **cantidad** cuando la decisión es de **contenido**; (3) es navegación facetada, que Google recomienda no rastrear, y la canibalización con `/sanghas/{ciudad}` no mejora con el volumen.
- Se mantiene lo ya aprobado: **`event_city` como taxonomía sin archivo público** —mismo criterio que `event_type`— y los encuentros de cada ciudad **dentro de `/sanghas/{ciudad}`**.
- Propagado a `11-arbol-urls-final` §3, `03-wordpress-content-model` §4 (se añade `event_city` a la tabla de taxonomías), `.audit/decisions.md`, `.audit/state.md`, `.audit/working/url-hypotheses.md` (derogación anotada, análisis original conservado) y la nota de exclusión de **TASK-0021**.
- **Índice de ADR corregido:** el 0021 no se había añadido a la tabla numerada al crearlo. Ahora están el 0021 y el 0022.
- **Los informes SEO no se tocaron.**

### Pendiente

- Relanzar PageSpeed Insights tras desplegar y actualizar §6 del informe técnico.
- **Deriva repo↔producción:** `assets/images/logo.png` es 7.423 b en el repo (grises+alfa) y 10.079 b en producción (RGBA). Ambos 240×240 y válidos, pero no son el mismo archivo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.19] - 2026-07-21

### Rendimiento — fuente subsetada, CSS minificado e imágenes responsive

- `assets/fonts/marlowe-escapade/marlowe-escapade-subset.woff2`: nuevo. MarloweEscapade subsetada a los 13 caracteres de "Camino del Dharma", los únicos que dibuja (`.site-name`, `.site-title`): **52,1 KB → 3,4 KB**.
- `assets/css/main.css`: `--font-heading` pasa de `"Fjalla One", "MarloweEscapade", serif` a `"Fjalla One", serif`. Con la fuente subsetada, dejarla de fallback mezclaría glifos sueltos con serif sobre texto arbitrario.
- `scripts/build-fonts.sh`: nuevo. Regenera el subset (requiere `pyftsubset`).
- **Nuevo paso de build:** `npm run build:css` (clean-css) genera `assets/css/main.min.css`, que es lo que enlazan las 15 páginas. Servido con Brotli: **9,0 KB → 5,8 KB (−36 %)**. `main.css` sigue siendo el único archivo que se edita; `main.min.css` queda en `.stylelintignore`.
- `index.html`: `srcset` + `sizes` en `inicio-encuentro-comunidad` (768w/1280w) e `inicio-kuan-yin` (768w/945w). En móvil se sirve el 768w; en retina de escritorio, el grande (antes había un solo tamaño para todo).

**Medido (Lighthouse móvil):** rendimiento 93 → 97, LCP 2,9 s → 2,4 s, FCP 2,0 s → 1,8 s, página 879 KB → 333 KB desde el inicio de la serie. `unminified-css` y el árbol de dependencias de red pasan.

**No se tocó:** redirecciones (la URL canónica ya resuelve con 0 saltos; solo quedan http→https y www→no-www, ambos correctos y necesarios) ni la caché (el ahorro que estima Lighthouse es de 1 KiB y subir el TTL exige cache-busting, que hoy no existe).

- `sitemap.xml`: **sin cambio de `<lastmod>`** — cambios de assets y estilos, no de contenido indexable.

### Dependencias y manifiesto

- `npm audit`: 0 vulnerabilidades (antes 1 alta). `fast-uri` 3.1.3 → 3.1.4, dependencia transitiva de stylelint; solo desarrollo, nunca llegó al sitio.
- `clean-css-cli` añadido como `devDependency` (build del CSS).
- `package.json` corregido: `license` decía `ISC` cuando [`LICENSE`](LICENSE) y el README dicen **MIT**; `version` estaba en 1.0.11 y ahora sigue a [`VERSION`](VERSION); `main` apuntaba a un `index.js` inexistente (eliminado); `author` vacío → Rafael Figueredo Oropeza; añadido `private: true` (repo de sitio, no se publica en npm).
- `package-lock.json` sincronizado; `npm ci` reproduce la instalación sin vulnerabilidades.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.18] - 2026-07-21

### CSS — una sola hoja bloqueante

- `assets/css/main.css`: `normalize.css` v8.0.1 incorporado literalmente al inicio (nueva sección 0.0), antes de fuentes y variables. Sin cambios en el orden de cascada.
- `assets/css/normalize.css`: eliminado (su contenido vive ahora en `main.css`; una sola fuente de verdad).
- 15 páginas HTML: retirado el `<link>` a `normalize.css`. En `index.html`, retirado además el `preload` redundante de `main.css` (el `<link rel="stylesheet">` iba inmediatamente después).
- `.stylelintrc.json`: eliminado el bloque `overrides` de `normalize.css` (el archivo ya no existe). Las excepciones se declaran ahora en línea y acotadas dentro de `main.css`; ver `docs/14-css-architecture.md` §8.

**Motivo:** PageSpeed señalaba una cadena crítica `documento → normalize.css`. Medido sobre Brotli en producción: antes 8.407 B + 2.280 B en 2 peticiones; ahora 8.915 B en 1 petición (~1,8 KB menos y un round trip menos). La auditoría de dependencias de red pasa.

- `sitemap.xml`: **sin cambio de `<lastmod>`** — la modificación es de infraestructura de estilos, no de contenido indexable. Ver nota abajo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.17] - 2026-07-21

### Inicio — galería y logo

- `index.html`: mini-galería con `<picture>` (WebP + JPEG), `loading="lazy"` y `decoding="async"`.
- Nuevas miniaturas en `assets/images/galeria/thumbs/` (jpg y webp).
- `assets/images/logo.png`: optimizado (menor peso).
- `sitemap.xml`: sin cambio — `/` ya tenía `<lastmod>` `2026-07-21` (única página HTML modificada).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.16] - 2026-07-21

### Enlaces internos (práctica)

- `practica/index.html`, `practica/meditacion-semanal-en-linea/index.html` y `practica/videos/index.html`: enlaces relativos sustituidos por rutas absolutas desde la raíz (`/…`) para evitar roturas con la política de URLs canónicas (ADR 0008; hallazgos FUNC-002/003).
- `sitemap.xml`: `<lastmod>` `2026-07-21` en `/practica/videos` (las demás URLs de práctica ya estaban en esa fecha).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.15] - 2026-07-21

### Meditación semanal en línea y eventos

- Nueva página `/practica/meditacion-semanal-en-linea` (horario, modalidad Zoom, enlace de participación).
- `index.html` y `practica/index.html`: enlace visible a la meditación semanal en línea.
- `eventos/index.html`: fichas de eventos pasados (Barranquilla, Calma en la Ciudad, Medellín, UniRemington, Vesak Bogotá) con imágenes nuevas.
- `eventos/encuentro-nacional-2026`: descripción del calendario `.ics` sin duplicar URL del cartel en el texto.
- `llms.txt`: entrada de la meditación semanal; `assets/css/main.css`: estilos de eventos pasados.
- `sitemap.xml`: `<lastmod>` `2026-07-21` en `/`, `/practica`, `/practica/meditacion-semanal-en-linea`, `/eventos` y `/eventos/encuentro-nacional-2026`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.14] - 2026-07-20

### Privacidad (embeds y almacenamiento local)

- `index.html`, `practica/index.html`, `practica/videos/index.html`: iframes de YouTube migrados a `youtube-nocookie.com`; Vimeo con `?dnt=1`; JSON-LD `embedUrl` alineado (hallazgo audit PRIV-001 / TASK-0006).
- `assets/js/main.js`: retirada la persistencia en `localStorage` del selector de idioma mientras el sitio es solo español (ADR 0019).
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` en `/`, `/practica` y `/practica/videos`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.13] - 2026-07-20

### Contenido editorial (grafía Buddhismo)

- `comunidad/index.html`: eliminada la sección «Cómo nos nombramos»; la explicación de grafía queda centralizada en Linaje. El copy usa «Buddhismo» y «buddhista» con naturalidad, sin justificación repetida.
- `linaje/index.html`: reescrita la nota «Sobre la palabra Buddhismo» — término sánscrito *buddha*, *Buddha*/*Buda* como título (no nombre propio), y reconocimiento de *budismo*/*budista* como formas extendidas en español.
- `sitemap.xml`: `<lastmod>` actualizado a `2026-07-20` solo en `/comunidad` y `/linaje` (únicas páginas modificadas; sin URLs nuevas ni retiradas).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.12] - 2026-07-19

### Privacidad

- **Google Analytics 4 desactivado** en las 14 páginas HTML: eliminado el bloque `gtag.js` (`G-B8FY69RGSS`). Motivo: cookies `_ga` sin consentimiento ni política de privacidad (hallazgo audit PRIV-001).
- **Reactivación:** solo tras política publicada y Consent Mode v2 (o alternativa acordada). Ver `.audit/implementation/tasks/TASK-0006.md` y `docs/17-orden-implementacion.md` §2.75 (PRIV-001). ID de propiedad conservado para uso futuro: `G-B8FY69RGSS`.
- Métricas de indexación: seguir usando **Google Search Console** (sin cookies en el sitio).

### SEO (auditoría externa — continuación 2026-07-19)

- `.htaccess`: limpieza del índice residual de la etapa WordPress — `410` para `/prueba`, `301` de `/category/*` → `/blog`, `301` de `/?page_id=10` → `/comunidad` y de otros `?page_id=` → portada (hallazgo SEO-EXT-002; estas URLs seguían indexadas y una página "prueba" aparecía en el SERP de marca).
- `index.html` (JSON-LD Organization): retirado `alternateName` "budismo en Colombia" (keyword, no nombre real); añadidos `foundingDate: 2012`, `foundingLocation: Cali` y `knowsAbout` (Chan, Tierra Pura, meditación, atención plena). Dirección postal no añadida: pendiente de confirmación de la comunidad.
- `index.html` y `comunidad/index.html`: mención textual de la fundación en Cali (2012) — señal local que el sitio no tenía.
- `eventos/index.html`: título "Eventos y Retiros Budistas en Colombia | Camino del Dharma" y descripción con intención temática (og/twitter sincronizados).
- `blog/index.html`: título "Blog de Budismo — Reflexiones y Enseñanzas | Camino del Dharma" (og/twitter sincronizados).
- Evidencia y análisis completo: `.audit/working/seo-external.md`; tareas derivadas TASK-0013–0016.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.11] - 2026-07-19

### Mejoras

- Google Analytics 4 (`G-B8FY69RGSS`): etiqueta `gtag.js` directa en las 14 páginas HTML (sin Google Tag Manager).
- `sitemap.xml`: `<lastmod>` en todas las URLs indexables (`2026-07-19`), al modificarse cada HTML del sitio.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.10] - 2026-07-19

### Mejoras

- Rendimiento (PageSpeed): preload del hero WebP, fuente Inter 400 y CSS principal en inicio.
- Inicio: imágenes con `<picture>` (WebP + JPEG), `fetchpriority="high"` en hero, lazy load bajo el pliegue.
- Imágenes optimizadas en `assets/images/inicio/` (JPEG recomprimidos + variantes `.webp`).
- CSS: `picture` en bloques hero y section-figure.
- `sitemap.xml`: `<lastmod>` actualizado en `/` (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.9] - 2026-07-19

### Mejoras

- Eventos: JSON-LD enriquecido en páginas de detalle (`@id`, organizer, performer, `validFrom`, dirección ampliada).
- Eventos: eliminado microdata (`itemscope`/`itemprop`) del listado y de las fichas; datos estructurados solo en JSON-LD de cada evento.
- `sitemap.xml`: `<lastmod>` actualizado en `/eventos`, `/eventos/encuentro-nacional-2026` y `/eventos/pausa-profunda-cali` (`2026-07-19`), tras los cambios JSON-LD en esas páginas.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.8] - 2026-07-19

### Mejoras

- `<meta name="theme-color">` en todas las páginas; favicon 48×48 añadido.
- SEO: títulos y descripciones refinados en inicio, comunidad, linaje y práctica.
- Inicio: tagline y enlace introductorio a la comunidad.
- Eliminado `site.webmanifest` y referencias PWA (decisión de no implementar app instalable).
- `sitemap.xml`: `<lastmod>` alineado en todas las URLs (`2026-07-19`).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.7] - 2026-07-18

### Mejoras

- `sitemap.xml`: fechas `<lastmod>` actualizadas en eventos y artículo del blog.
- Checklist de despliegue: paso obligatorio de revisar `sitemap.xml` antes de incrementar `VERSION`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.6] - 2026-07-18

### Mejoras

- Metadatos SEO y redes sociales refinados en todas las páginas (título, descripción, Open Graph, Twitter Cards).
- Favicons estandarizados (`assets/favicon/`) y `site.webmanifest`.
- Imagen por defecto para compartir (`assets/images/og-default.jpg`).
- `llms.txt`: ajustes menores de contenido.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.5] - 2026-07-18

### Mejoras

- `llms.txt`: guía curada del sitio para agentes de IA (convención llmstxt.org), con enlaces canónicos a las páginas principales.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.4] - 2026-07-18

### Mejoras

- Resolución de URLs canónicas en `calendar.js` y `share.js` para enlaces de eventos y compartir.
- Rutas absolutas corregidas en páginas de eventos, práctica y blog.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.3] - 2026-07-18

### Mejoras

- Imágenes actualizadas en galería, inicio, comunidad, linaje, eventos, práctica, blog, contacto, celebraciones y logo.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.2] - 2026-07-18

### Mejoras

- `sitemap.xsl`: texto introductorio más breve en la vista del mapa del sitio.

### Correcciones

- Eventos finalizados: eliminados CTAs obsoletos en HTML y estilos asociados en `main.css`.

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.1] - 2026-07-18

### Mejoras

- `sitemap.xsl`: vista legible del mapa del sitio al abrir `/sitemap.xml` en el navegador.
- `sitemap.xml`: referencia a la hoja de estilo XSL (sin impacto en buscadores).

### Estado

- Desarrollo: Finalizado
- Producción: Pendiente de despliegue

## [1.0.0] - 2026-07-18

### Publicación inicial

- Sitio web publicado en producción.
- SEO: meta description, canonical, Open Graph, Twitter Cards, `robots`.
- Metadatos documentales: `author`, `creator`, `publisher`, `developer`, `copyright`, `keywords`.
- `robots.txt` y `sitemap.xml` con URLs canónicas sin barra final.
- JSON-LD: Organization, WebSite, BreadcrumbList, Event, Article, VideoObject.
- Accesibilidad: HTML semántico, skip link, navegación por teclado, contraste.
- Diseño responsive; corrección del selector de idioma en móvil.
- `.htaccess`: HTTPS, URLs sin barra final, 404 personalizada, caché y cabeceras de seguridad.

### Estado

- Desarrollo: Finalizado
- Producción: Publicado

### Servidor

- Hostinger
- Dominio: <https://caminodeldharma.org>

### Observaciones

- Primera versión pública.
- Pendiente o en curso: verificación en Google Search Console y envío del sitemap.
