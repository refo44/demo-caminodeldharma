# Guía de ejecución — 6 insumos manuales pendientes

Instrucciones paso a paso. Orden recomendado: **1 → 2 → 3** (impacto SEO), **4** en paralelo con la
comunidad, **5 y 6** cuando toque. Al terminar cada uno, comparte lo indicado en *"Qué devolverme"*.

Datos del proyecto que necesitarás:

| Dato | Valor |
|---|---|
| Sitio | `https://caminodeldharma.org` |
| Correo | `caminodeldharma1@gmail.com` |
| Teléfono | +57 320 662 7608 |
| Fundación | Cali, 2012 |
| Fundador | Venerable Maestro Zheng Gong |
| Tradición | Budismo Chan y Tierra Pura (Mahāyāna) |
| Facebook | `facebook.com/caminodeldharmacolombia` |
| Instagram | `instagram.com/camino_del_dharma` |
| Meditación semanal | Lunes 7:30 p. m. (hora Colombia), virtual por Zoom |

---

## 1 · Google Search Console — ~20 min

**Por qué primero:** sustituye todas mis aproximaciones de posición por datos reales y acelera la
limpieza de las URLs residuales de WordPress.

### Paso 1 — comprobar si ya existe la propiedad

Entra en [search.google.com/search-console](https://search.google.com/search-console) con la cuenta
Google de la comunidad. El `CHANGELOG` menciona usar GSC, pero Google me mostró el anuncio *"¿Eres
dueño de caminodeldharma.org?"*, así que puede que nunca se completara. **Si ya aparece la propiedad,
salta al paso 3.**

### Paso 2 — verificar la propiedad

Elige **Dominio** (no "Prefijo de URL"): cubre `www`, sin `www`, http y https de una vez.

1. Escribe `caminodeldharma.org`.
2. Google te dará un registro **TXT**.
3. En hPanel de Hostinger → **Dominios → DNS / Nameservers** → añadir registro:
   - Tipo: `TXT` · Nombre: `@` · Valor: el que dio Google · TTL: por defecto
4. Guarda, espera unos minutos y pulsa **Verificar** en GSC.

### Paso 3 — enviar el sitemap

**Sitemaps** (menú izquierdo) → escribe `sitemap.xml` → **Enviar**.
Debe quedar en estado *Correcto* con **13 URLs detectadas**.

### Paso 4 — retirar las URLs residuales de WordPress

**Eliminaciones** → *Nueva solicitud* → **Eliminar temporalmente esta URL**, una por una:

```
https://caminodeldharma.org/prueba
https://www.caminodeldharma.org/prueba/
https://caminodeldharma.org/category/noticias/
https://caminodeldharma.org/category/sin-categoria/
https://caminodeldharma.org/?page_id=10
```

> Esto las oculta ~6 meses. La eliminación definitiva la producen los redirects 410/301 que ya están
> en el código fuente, **una vez desplegados** (insumo 6). Haz este paso *después* del despliegue si
> puedes; si no, hazlo ya y repite la inspección luego.

### Paso 5 — exportar datos

Espera **48–72 h** tras la verificación (GSC necesita acumular datos) y luego:

- **Rendimiento** → rango *Últimos 3 meses* → pestaña **Consultas** → botón **Exportar** → CSV
- **Páginas** → **Exportar** → CSV
- **Indexación → Páginas** → captura o exportación del resumen de cobertura

**Qué devolverme:** los CSV de Consultas y Páginas.
**Qué desbloquea:** las palabras clave reales por las que apareces, con impresiones y clics. Es la
única fuente que puede confirmar o corregir mi análisis de posicionamiento.

---

## 2 · Google Business Profile — decisión + ~30 min

**Por qué importa tanto:** en **todas** las búsquedas amplias y locales que probé
("budismo en colombia", "comunidad budista colombia", "budismo cali"), los tres primeros resultados
son un *pack local* de fichas de Google. Sin ficha no se puede competir ahí, por muy bueno que sea
el sitio. Tus competidores sí la tienen (Yamantaka, Soto Zen, Centro Budista Cali).

### Antes: la decisión de la comunidad

GBP exige **una dirección física** o, como mínimo, un **área de servicio**. Opciones:

| Opción | Qué implica |
|---|---|
| **Dirección pública** | Máxima visibilidad local. Requiere un lugar de práctica presencial que la comunidad acepte publicar |
| **Área de servicio sin dirección** | Se atiende una zona (p. ej. Cali) sin mostrar dirección. Menos potente, pero válido |
| **No crear ficha** | Se renuncia al pack local; hay que compensar con contenido y enlaces |

Google verifica por postal, teléfono o vídeo, así que la dirección debe ser real.

### Si la decisión es crear la ficha

1. [business.google.com](https://business.google.com) → **Añadir empresa**.
2. Nombre exacto: **Comunidad Buddhista Camino del Dharma** (idéntico al del sitio y el JSON-LD).
3. Categoría principal: **Templo budista** o **Centro de meditación**.
4. Completa: teléfono, sitio web `https://caminodeldharma.org`, horario (incl. la meditación de los
   lunes 7:30 p. m.), descripción, fotos reales.
5. Completa la verificación que Google proponga.

**Qué devolverme:** la decisión (y el motivo si es negativa) + la URL de la ficha si se crea.

---

## 3 · Tres peticiones de enlace — ~30 min

**El dato que lo justifica:** `budismocolombia.org` tiene 214 enlaces externos y tú 207 —
prácticamente los mismos— pero su DA es 8 y el tuyo 2. **No faltan enlaces, faltan enlaces buenos.**
Estas tres fuentes ya hablan de la comunidad; solo falta que enlacen.

Revisa los borradores y ajústalos a la voz de la comunidad antes de enviar.

### 3.1 · Buddhistdoor en Español

Su artículo *"El budismo en Colombia (II)"* describe a la comunidad como la referencia del budismo
Chan del país, pero solo enlaza a Facebook. Busca su contacto editorial en
[espanol.buddhistdoor.net](https://espanol.buddhistdoor.net).

> **Asunto:** Enlace al sitio web de la Comunidad Camino del Dharma
>
> Estimado equipo de Buddhistdoor en Español:
>
> Les escribo desde la Comunidad Buddhista Camino del Dharma, mencionada en su artículo *"El budismo
> en Colombia (II): las escuelas y organizaciones budistas"*. Agradecemos el rigor con que
> documentaron la presencia del budismo Chan en el país.
>
> Notamos que la referencia a nuestra comunidad enlaza a nuestra página de Facebook. Desde entonces
> publicamos nuestro sitio oficial, `https://caminodeldharma.org`, donde está la información
> institucional actualizada: el linaje Chan y Tierra Pura, las actividades y el calendario de
> práctica. ¿Sería posible añadir ese enlace junto a la mención?
>
> Quedamos a su disposición para cualquier dato adicional que necesiten.
>
> Con aprecio,
> [nombre] — Comunidad Buddhista Camino del Dharma
> caminodeldharma1@gmail.com

### 3.2 · EcoEspiritualidad

Tiene una **ficha completa** de la comunidad que es el **segundo resultado de Google** al buscar la
marca, y no contiene ningún enlace saliente. Busca su formulario o correo de contacto.

> **Asunto:** Actualización de la ficha de Camino del Dharma
>
> Hola:
>
> Escribo desde la Comunidad Buddhista Camino del Dharma. Gracias por incluirnos en su directorio.
>
> Vimos que nuestra ficha no incluye la dirección de nuestro sitio web. ¿Podrían añadir
> `https://caminodeldharma.org`? Así quienes lleguen desde su directorio podrán consultar el
> calendario de práctica y la información actualizada de la comunidad.
>
> Gracias por el trabajo de difusión que realizan.
>
> [nombre] — Comunidad Buddhista Camino del Dharma

### 3.3 · Directorio budismo.com

Lista 11 centros de Colombia y la comunidad no aparece. Ve a
[budismo.com/directorios/colombia.php](https://www.budismo.com/directorios/colombia.php), busca el
enlace de contacto o alta y envía:

```
Nombre:      Comunidad Buddhista Camino del Dharma
Tradición:   Budismo Chan y Tierra Pura (Mahāyāna)
Ciudad:      Cali, Colombia (fundada en 2012)
Sitio web:   https://caminodeldharma.org
Correo:      caminodeldharma1@gmail.com
Teléfono:    +57 320 662 7608
Actividades: Meditación semanal en línea (lunes 7:30 p. m., hora Colombia),
             talleres, retiros y encuentros nacionales.
```

### 3.4 · Perfiles sociales propios — 2 minutos

Añade `https://caminodeldharma.org` en:
- Facebook → *Editar página* → **Sitio web**
- Instagram → *Editar perfil* → **Sitio web**

No transfieren autoridad (son `nofollow`), pero dirigen tráfico real desde tus 6.800 seguidores.

**Qué devolverme:** a quién escribiste, cuándo y qué respondieron.
**Meta:** DA 8 (paridad con los competidores más cercanos) es alcanzable con esto.

---

## 4 · Cuatro decisiones de la comunidad

No requieren trabajo técnico: son conversaciones. Cada una desbloquea una tarea parada.

| # | Decisión | El intercambio real | Tarea |
|---|---|---|---|
| A | **¿Formulario de contacto con backend real, o solo WhatsApp y correo?** | Hoy el formulario acepta mensajes y los descarta **en silencio**: quien escribe cree haber contactado. Lo urgente es quitarlo; luego se decide si se reemplaza por uno funcional | TASK-0002 / TASK-0003 |
| B | **Enfoque de consentimiento y política de privacidad** | GA4 ya se retiró en la 1.0.12, lo que reduce el riesgo. Si algún día se reactiva analítica, hace falta política publicada y mecanismo de consentimiento | TASK-0006 |
| C | **¿Publicar el enlace de Zoom o mantener la puerta por WhatsApp?** | Publicarlo hace la meditación **citable por asistentes de IA** y elimina fricción. Mantener WhatsApp preserva el contacto humano y el control de quién entra. Ambas son legítimas | TASK-0017 |
| D | **Dirección editorial del contenido** | Publicar material educativo (¿qué es el budismo Chan?, la recitación de Amitabha) captaría búsquedas informacionales. Requiere decidir temas, quién escribe y con qué frecuencia | TASK-0016 |

**Qué devolverme:** la decisión y su razonamiento. Las registro y desbloqueo cada tarea.

> Sobre la **C**: es la más interesante de las cuatro. La meditación semanal online es tu mayor
> diferenciador frente a competidores presenciales, pero hoy ningún asistente de IA puede
> recomendarla porque no existe como entidad enlazable. Se puede tener casi todo: crear la página con
> horario y datos estructurados, y **mantener** el enlace de Zoom tras WhatsApp. La página sería
> citable aunque la puerta siga siendo humana.

---

## 5 · Inventario DNS — ~15 min

**Para qué:** decidir si HSTS debe incluir `includeSubDomains`. Esa directiva afectaría a **todos**
los subdominios; activarla sin saber cuáles existen puede dejar inaccesible alguno que no tenga
HTTPS. Por eso está bloqueada por diseño: nunca debe deducirse de que el dominio principal funcione.

1. hPanel de Hostinger → **Dominios** → `caminodeldharma.org` → **DNS / Nameservers**.
2. Copia la tabla completa de registros, o usa **Exportar zona** si está disponible.
3. Interesan sobre todo los registros **A**, **AAAA** y **CNAME** (cada uno puede ser un subdominio
   servido por web): correo, webmail, ftp, cpanel, staging, etc.

**Qué devolverme:** la lista de registros (captura o texto).

---

## 6 · Despliegue — TASK-0013 y TASK-0001

Sigue el proceso ya documentado en el `README.md` del proyecto (§ Despliegue en Hostinger,
despliegue manual por ADR 0015).

### Qué está esperando despliegue

Ya en código fuente, sin desplegar:
- `.htaccess`: 410 para `/prueba`, 301 de `category/*` → `/blog`, 301 de `?page_id=10` → `/comunidad`
- `index.html`: JSON-LD con `foundingDate` 2012, `foundingLocation` Cali y `knowsAbout`
- `index.html` y `comunidad/index.html`: mención "fundada en Cali en 2012"
- `eventos/index.html` y `blog/index.html`: títulos temáticos
- (1.0.12 incluye además la retirada de GA4, tampoco desplegada)

### Antes de generar el ZIP

1. `sitemap.xml` ya tiene `<lastmod>` 2026-07-19 en las páginas tocadas — **verifica** que coincide.
2. `VERSION` y `CHANGELOG.md` ya reflejan la 1.0.12 con la sección de SEO añadida.
3. `npm run lint:css` sin errores.
4. Genera el ZIP con el comando exacto del README (se ejecuta **en el Escritorio**, no dentro del repo).
5. Sube y extrae en `public_html` desde el File Manager de Hostinger.

### Verificación posterior (pégame la salida)

```bash
curl -s -o /dev/null -w "%{http_code}  /prueba (esperado 410)\n" https://caminodeldharma.org/prueba
curl -sSL -o /dev/null -w "%{http_code} %{url_effective}  (esperado /blog)\n" https://caminodeldharma.org/category/noticias
curl -sS -o /dev/null -w "%{http_code} %{redirect_url}  (esperado /comunidad)\n" "https://caminodeldharma.org/?page_id=10"
curl -s https://caminodeldharma.org/ | grep -c "foundingDate"
```

### TASK-0001 (rutas .ics) — aún NO implementada

Es un cambio de código, no un insumo manual: lo hace un agente implementador en sesión aparte
siguiendo `IMPLEMENTATION_AGENT_TEMPLATE.md`. Puede ir en el mismo despliegue si se implementa antes;
si no, en el siguiente.

**Qué devolverme:** confirmación del despliegue, versión desplegada y la salida de los `curl`.

---

## Resumen

| # | Insumo | Tiempo | Bloquea |
|---|---|---|---|
| 1 | Search Console | ~20 min + espera | Datos reales de posicionamiento |
| 2 | Google Business Profile | decisión + ~30 min | Visibilidad en búsquedas locales |
| 3 | Tres peticiones de enlace | ~30 min | Autoridad del dominio (meta DA 8) |
| 4 | Decisiones de la comunidad | conversación | TASK-0002/0003/0006/0016/0017 |
| 5 | Inventario DNS | ~15 min | Decisión `includeSubDomains` |
| 6 | Despliegue | ~20 min | Todo lo ya hecho en código fuente |

Si solo puedes hacer una cosa: la **1** (Search Console). Todo lo que he medido desde fuera son
aproximaciones; GSC las convierte en datos ciertos y puede corregir mis conclusiones.
