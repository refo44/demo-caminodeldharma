# Camino del Dharma — Contexto del proyecto

Documento de contexto autocontenido, pensado para compartir con otra IA (ChatGPT u otra) que no tiene
acceso al repositorio. Resume el proyecto, su estado actual, sus decisiones arquitectónicas y lo que
está en curso, a fecha **2026-08-13**.

**Addenda 2026-08-29:** `/privacidad` está publicada (aviso provisional, ADR 0039). El aplazamiento de
ADR 0028 queda sustituido. Contact Form 7 sigue gated hasta actualizar el aviso y la revisión legal.
La versión del repositorio es **1.0.35**. Fase 3 (FSE) sigue sin iniciar.

---

## 1. Qué es el proyecto

**Camino del Dharma** es el sitio web de una comunidad buddhista en Colombia (Personería Jurídica
Especial – Ministerio del Interior). En palabras del propio plan del proyecto:

> "No se trata de una web informativa ni comercial, sino de un espacio de acogida que oriente, inspire
> confianza y facilite el primer contacto con la práctica buddhista."

No es una landing page, ni un sitio comercial, ni un CMS orientado a marketing. No tiene funnels, no
tiene presión de conversión, no tiene analítica de comportamiento. Es una plataforma comunitaria.

El sitio orienta hacia: la comunidad, el linaje (tradición Chan y Tierra Pura), la práctica (meditación
semanal, retiros, mantras), eventos, una galería, cómo contribuir (donaciones, nunca como transacción) y
cómo contactar (WhatsApp, correo, y en el futuro un formulario funcional).

---

## 2. Estado actual (2026-08-13)

| Aspecto | Estado |
|---|---|
| **Producción** | Sitio estático (HTML/CSS/JS, sin build step de servidor) en `https://caminodeldharma.org`, alojado en **Hostinger** (hosting compartido) |
| **Versión desplegada** | v1.0.23 |
| **Versión en el repositorio** | v1.0.28 (pendiente de despliegue) |
| **Fase activa** | Transición a **Fase 3 (WordPress)** — el repo aún no se ha reorganizado (`static/` todavía no existe; el HTML sigue en la raíz) |
| **Repo** | Monorepo único durante toda la transición (Git como fuente única de verdad) |
| **Despliegue** | Manual (ZIP → File Manager de Hostinger). CI/CD explícitamente pospuesto |
| **Corte a WordPress (reemplazo del sitio estático)** | **No antes del 10 de agosto de 2026** — decisión de la comunidad para no migrar justo antes del 7.º Encuentro Nacional Buddhista (7–9 agosto 2026, Puerto Colombia) |
| **Auditoría de producción (2026-07-19)** | Score 84/100; SEO técnico 100/100; 0 hallazgos críticos; formulario de contacto sin backend real es el hallazgo más relevante pendiente |

---

## 3. Principios de producto (qué es y qué NO es)

**El sitio SÍ permite:** actualizar la meditación semanal, publicar/ocultar eventos, mantener un
cronograma, incrustar videos (YouTube/Vimeo), conectar sanghas, compartir testimonios — todo sin tocar
código ni servidor, una vez WordPress esté implementado.

**El sitio NO tiene, por decisión explícita:**
- Buscador
- Área privada / registro de usuarios
- Sistema de cursos
- Pagos internos (cualquier inscripción con costo redirige a una plataforma externa)
- Analítica con cookies, de ningún tipo (ni siquiera "privacy-friendly" por defecto)
- PWA / manifest / Service Worker, en ninguna fase
- Un lightbox de galería propio (se usa el nativo de Gutenberg)

**Tono editorial:** sobrio, cálido, sin urgencia. CTAs como "Practica con nosotros", "Participar",
"Inscribirme", "Preinscribirme", "Ver evento" (Inicio, a esa ficha), "Ver evento →" (listado), "Donar" — nunca lenguaje de venta.

---

## 4. Arquitectura de decisiones (ADR) — resumen

El proyecto documenta cada decisión estructural como ADR (`docs/adr/`), 28 hasta la fecha. Resumen de
las más relevantes para cualquier trabajo futuro:

| ADR | Decisión | Por qué importa |
|---|---|---|
| 0001 | La maqueta estática es la base **definitiva**, no un prototipo desechable | WordPress no rediseña; solo cambia el motor |
| 0002 | WordPress = adaptación, no rediseño | Mismo CSS, mismos bloques, mismas URLs |
| 0003 | **Sin PWA nunca**, sin `manifest`, sin Service Worker | Sitio web tradicional, no app instalable |
| 0004 / 0005 | Git es la única fuente de verdad; **prohibida** la edición manual en producción | Todo cambio pasa por el repo |
| 0006 / 0007 | GitHub Actions + SSH/rsync es el mecanismo **objetivo** de CI/CD | Pero ver ADR 0016 — está pospuesto |
| 0008 | URLs de la maqueta son definitivas; enlaces internos **siempre absolutos de raíz**, nunca relativos | Ya rompió producción dos veces (incidentes reales documentados) |
| 0009 | CSS y tokens de diseño son invariantes durante la migración | Un solo `main.css`, sin frameworks, sin reescritura |
| 0010 / 0018 / 0020 | HSTS **desactivado**, aplazado hasta ≥30 días después del corte a WordPress | Tráfico casi nulo (~9 clics/28 días); no vale el riesgo de romper TLS a mitad de migración |
| 0012 | WordPress es el motor de contenido elegido (frente a otras opciones) | — |
| 0014 | Monorepo con `static/` + `wordpress/` al iniciar Fase 3 | — |
| 0015 / 0016 | Despliegue manual **temporal**; automatización **pospuesta**, no solo "no activada todavía" | No crear workflows de GitHub Actions aún |
| 0019 | **Sin analítica con cookies, nunca** — GA4 descartado definitivamente | El tráfico es tan bajo que la analítica sería ruido, no información; medición vía Search Console (gratis, sin cookies) |
| 0021 | Sin lightbox propio de galería — se usa el nativo de WordPress/Gutenberg | Menos JS propio que mantener |
| 0022 | La ciudad de un evento es **taxonomía, no URL** — sin `/eventos/cali` ni archivos por ciudad | Evita páginas doorway / navegación facetada |
| 0023 | Entorno de desarrollo local con **Docker**, replicando versiones reales de Hostinger | **PHP 8.3 y MariaDB 11.8 ya confirmados** esta semana. Tarea separada de implementar el theme (decisión expresa del propietario) |
| 0024 | Plugin propio `camino-del-dharma-core` desde el inicio; el theme `camino-del-dharma` **solo presenta** | El dominio (CPTs, taxonomías, roles) vive en el plugin, nunca en el theme |
| 0025 | Plugins de terceros solo con ADR propio; vetados por defecto: ACF, page builders, suites SEO todo-en-uno | — |
| 0026 | **Contact Form 7** para el formulario de contacto, buzón `caminodeldharma1@gmail.com` | Primer y único plugin de terceros aprobado |
| 0027 | Estándares de ingeniería para Fase 3: criterio senior + SOLID/KISS/YAGNI **cuando aplique** (no dogmático); WPCS y seguridad de WordPress **no negociables** | Guardrail explícito anti-sobre-ingeniería para un plugin de ~2 CPTs |
| 0028 | Política de privacidad **aplazada** hasta asesoría legal, pero es **gate obligatorio antes de publicar Contact Form 7** | No es un "algún día"; es una condición de release concreta |

---

## 5. Modelo de contenido y URLs

**Post types:** `page` (nativo, para las páginas institucionales) + CPT **`event`** (el único CPT en
alcance ahora mismo).

**CPT `sangha` está fuera de alcance** — aplazado por decisión expresa del propietario (2026-07-31) hasta
tener ciudades confirmadas y un wireframe, que todavía no existen. No implementarlo "ya que la
arquitectura del plugin lo soporta fácilmente".

**Taxonomías:** `event_type` (jerárquica: Curso, Taller, Retiro, Conferencia, Encuentro, Celebración) y
`event_city` (plana). **Ninguna tiene archivo público** — son solo etiquetas, nunca URLs, para evitar
páginas casi-duplicadas sin contenido real (doorway pages).

**Regla clave de `/eventos/`:** siempre existe. Muestra dos bloques distintos — eventos **vigentes**
(agrupados por mes) y un **archivo de finalizados** (agrupados por año). Los finalizados **sí** se
muestran (decisión revertida el 2026-07-21): son la única señal geográfica honesta que tiene la
comunidad, ya que ninguna ciudad tiene sede fija ni es elegible para Google Business Profile.

El calendario de un mes en `/eventos/` marca los días de evento y, si un lunes no tiene otro evento,
la meditación semanal en línea (borde, no relleno; no es un ítem del listado).

**Inicio:** como máximo un evento **vigente** junto a «Un poco de nuestra comunidad» (rótulo
«Próximo evento», cartel WordPress `medium` (atajo de puntero a la ficha), «Ver evento» a esa ficha). Un destacado ya terminado
no aparece. Si no hay vigentes, el módulo se omite. No es un segundo listado.

**Árbol de URLs completo (todas indexables):**
```
/
/comunidad/
/linaje/
/practica/
/practica/videos/
/practica/meditacion-semanal-en-linea/
/eventos/
/eventos/{slug}/
/galeria/
/donaciones/
/contacto/
/blog/
/blog/{slug}/
/privacidad/          (publicada; aviso provisional — ADR 0039)
```

**Sin buscador, sin páginas de filtro, sin URLs creadas solo por SEO.**

---

## 6. Arquitectura técnica (theme + plugin WordPress)

- **Theme:** `camino-del-dharma` — WordPress clásico (PHP, no Full Site Editing), 13 plantillas
  aproximadamente. Solo presenta: templates, template parts, encolado de assets, render de metadatos
  (Open Graph, JSON-LD).
- **Plugin:** `camino-del-dharma-core` — dueño de todo el dominio: CPT `event`, taxonomías, roles
  editoriales, meta fields, cualquier comando WP-CLI propio. El theme nunca registra nada de esto.
- **CSS:** un solo archivo real, `assets/css/main.css` (heredado byte a byte de la maqueta estática
  donde sea posible). `style.css` del theme solo lleva metadata (obligatorio para WordPress, sin
  reglas). `theme.json` solo aporta tokens al editor de bloques. Sin frameworks, sin `!important`.
- **Galería:** bloque nativo de Gutenberg con lightbox nativo ("Ampliar al clic", WP 6.4+); `gallery.js`
  de la maqueta no se migra.
- **Estándares de código:** WordPress Coding Standards (PHPCS), prefijo único (`cdd_` o
  `camino_del_dharma_`) en toda función/hook/clase, escaping/sanitización/nonces siempre, cadenas
  preparadas para traducción aunque el sitio sea monolingüe español hoy.

---

## 7. Infraestructura

- **Hosting:** Hostinger, hosting compartido, dominio `caminodeldharma.org`.
- **PHP:** confirmado **8.3.30** (rango disponible en el plan: 8.2–8.5; recomendado por Hostinger: 8.3
  como más estable).
- **Base de datos:** confirmado **MariaDB 11.8.8** (obtenido vía `SELECT VERSION();` en phpMyAdmin,
  usando una base temporal creada solo para el chequeo).
- **Entorno local de desarrollo:** Docker Compose, 3 servicios (`db` con healthcheck, `wordpress`,
  `wpcli`), imágenes fijadas a `mariadb:11.8` + `wordpress:php8.3` para paridad real con Hostinger —
  no una versión "reciente genérica" por comodidad. Bind-mount limitado solo al theme y plugin propios;
  core de WordPress y BD viven en volúmenes Docker, nunca en Git.
- **Staging:** todavía no existe. El plan es crearlo en Hostinger como un sitio nuevo **sin dominio
  propio** (subdominio temporal tipo `algo.hostingersite.com`), pero **después** de tener una primera
  versión del theme funcionando en local — no como paso administrativo aislado al inicio.
- **SSH:** disponible en la cuenta de Hostinger pero inactivo; no se ha necesitado activar todavía.

---

## 8. Despliegue

- **Hoy:** completamente manual. Sitio estático: ZIP generado a mano (`static/` tras la reorganización)
  → subido por File Manager de Hostinger.
- **CI/CD:** la dirección técnica ya está decidida (GitHub Actions + SSH/rsync, ADR 0006/0007), pero la
  **implementación queda pospuesta** (ADR 0016) — no se crean workflows todavía, ni siquiera con
  trigger manual (`workflow_dispatch`). Se retomará cuando: (1) la estructura `static/`+`wordpress/`
  esté estable, (2) WordPress esté validado en staging, (3) los alcances de sincronización estén bien
  documentados.
- **WordPress:** se despliega manualmente al staging (cuando exista) hasta el corte final.

---

## 9. Seguridad y privacidad

- **Sin cookies de analítica, nunca** (ADR 0019). Search Console es el único canal de medición.
- **HSTS desactivado**, aplazado hasta ≥30 días después de que WordPress esté estable en producción.
- **Política de privacidad (`/privacidad/`) publicada** (ADR 0039, aviso provisional hasta asesoría
  legal). **Gate de Contact Form 7:** actualizar este aviso (hoy dice que el formulario no envía) y
  la revisión legal **antes** de que CF7 entre en producción.
- Hoy el único canal de contacto es humano (WhatsApp/correo), sin recolección automatizada de datos.

---

## 10. Alcance de Fase 3 (WordPress) — dentro y fuera

**Dentro de alcance:**
- Reorganizar el repo (raíz → `static/` + `wordpress/`)
- Theme `camino-del-dharma` + plugin `camino-del-dharma-core`
- CPT `event` con sus taxonomías
- Migración de las páginas institucionales (extracción verbatim del HTML publicado; ADR 0040)
- Contact Form 7 (con el gate de privacidad del punto 9)
- QA de accesibilidad (WCAG 2.1/2.2 AA) y de SEO/datos estructurados

**Fuera de alcance (explícitamente, no por omisión):**
- CPT `sangha` (fase separada posterior)
- Buscador
- Cualquier analítica
- HSTS
- PWA
- URLs de filtro por ciudad o tipo de evento
- Sistema de fixtures/datos de demo (no hace falta a esta escala; los eventos reales ya existen)
- Automatización de despliegue (workflows de GitHub Actions)
- El corte de producción en sí (reemplazar el sitio estático) — eso es una fase posterior, no antes del
  10 de agosto de 2026

---

## 11. Pendientes y bloqueadores conocidos

- Confirmar si la política canónica de URLs es con o sin barra final — hay una tensión documentada entre
  ADR 0008 (dice "sin barra final") y el árbol de URLs oficial `11-arbol-urls-final.md` (todas las rutas
  llevan barra final). Se resuelve revisando el `.htaccess` real, no asumiendo.
- Redactar y publicar `/privacidad/` — **hecho** el 2026-08-29 (ADR 0039, provisional). Sigue la
  revisión legal y, antes de Contact Form 7, actualizar el aviso.
- Migrar los embeds de YouTube a `youtube-nocookie.com` (pendiente, independiente de la decisión de
  analítica).
- Descargas `.ics` de eventos devuelven 404 en el estático (hallazgo de auditoría, prioridad alta).
- Formulario de contacto del sitio estático no entrega (se resuelve en WordPress con Contact Form 7, no
  se toca el estático mientras tanto).

---

## 12. Documentos de referencia en el repositorio

- `docs/adr/README.md` — índice completo de las 28 decisiones arquitectónicas
- `docs/17-orden-implementacion.md` — orden oficial de fases, criterios de cierre, checklist
- `docs/03-wordpress-content-model.md` — modelo de contenido completo
- `docs/11-arbol-urls-final.md` — árbol de URLs oficial
- `docs/12-theme-file-structure.md` — estructura de archivos del theme
- `docs/14-css-architecture.md` — arquitectura CSS
- `docs/docker-wordpress-playbook.md` — entorno local Docker (con versiones ya confirmadas)
- `docs/migracion-static-wordpress.md` — registro vivo de diferencias estático/WordPress durante la
  transición
- `docs/FABLE5-Fase3-WordPress-Master-Prompt-v1.md` — prompt de ejecución completo para un agente que
  implemente Fase 3, con todo este contexto aplicado en detalle operativo

---

**Fin del documento de contexto.**
