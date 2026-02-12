# Camino del Dharma — Mapa de pantallas

Lista de qué pantallas existen. No describe diseño; solo qué vistas hay que construir. Fuente única para «qué vistas construir»; el contenido de cada una está en 03; la estructura del theme en 12.

**Depende de / referencia:** `01-plataforma-comunidad-plan`, `03-wordpress-content-model`, `05-arquitectura-informacion-navegacion`. **Estructura de bloques por pantalla:** `06-wireframes`

---

## Páginas fijas

| Página | Función |
|--------|---------|
| Inicio | Título del sitio (site-title), hero, sección «Un poco de nuestra comunidad» (con sidebar CTA contribuir a la derecha en desktop), cómo practicamos, meditación semanal, caminos de participación, fila de imágenes de galería + enlace «Ver galería completa» |
| La comunidad | Quiénes somos, fundador, experiencia y propósito |
| El linaje | Tradición viva, Mahāyāna, Chan y Tierra Pura |
| Práctica y actividades | Meditación semanal, recitación práctica de la comida (texto + descarga PDF), talleres, retiros, videos, vida comunitaria |
| Eventos | Calendario estático (un mes, días con evento marcados), listado próximos / realizados con carteles e inscripción |
| Galería | Página dedicada con grid paginado de la galería comunitaria (imágenes `galeria-01.jpg` … `galeria-N.jpg`; ver 16). |
| Contribuir (donaciones) | Cómo contribuir, datos bancarios, texto sobre generosidad |
| Contacto | Formulario Nombre, Correo, Mensaje; bloque Redes sociales (Facebook, Instagram); enlaces WhatsApp y correo. |
| Blog | Listado de entradas; entradas individuales (opcional). |

---

## Página condicional

| Página | Condición |
|--------|------------|
| Eventos especiales | La ruta `/eventos/` **existe siempre**. Si no hay eventos vigentes → mensaje amable; si hay eventos → listado. El ítem en menú se muestra u oculta según haya evento vigente. |

---

## Vistas de contenido (single)

| Vista | Uso |
|-------|-----|
| Evento individual | Detalle de evento (si se implementa single-event) |

---

## Estados

| Estado | Descripción |
|--------|-------------|
| Sin eventos vigentes | En `/eventos/`: mensaje amable (la página existe; el contenido varía) |
| 404 | Página no existe |

---

## Elementos globales (componentes, no pantallas)

**Header:** logo + nombre del sitio + navegación principal (Inicio, Comunidad, Linaje, Práctica, Eventos) + subnav (Galería, Blog, Contribuir, Contacto). Presente en todas las páginas.

**Footer:** presente en todas las páginas. Contenido: Comunidad Buddhista Camino del Dharma, Personería Jurídica Especial – Ministerio del Interior de Colombia, Contacto | Redes sociales, Pausa Profunda (proyecto vinculado), información de donaciones, WhatsApp, correo.

---

## Conjunto total de pantallas a construir

**Páginas principales (estáticas):** 1. Inicio · 2. La comunidad · 3. El linaje · 4. Práctica y actividades · 5. Eventos · 6. Galería · 7. Contribuir (donaciones) · 8. Contacto · 9. Blog

**Página condicional:** 7. Eventos especiales — la ruta `/eventos/` existe siempre; contenido: listado si hay eventos vigentes, mensaje amable si no.

**Vistas de contenido:** 8. Evento individual — solo si se habilita vista detalle por evento.

**Estados:** Sin eventos vigentes (en `/eventos/`, mensaje amable) · 404

**Elementos globales (componentes):** Header (logo + navegación), Footer (identidad, contacto, redes, donaciones, WhatsApp, Pausa Profunda). No son pantallas; se repiten en todas las vistas.

---

## Traducción a WordPress (plantillas)

| Vista | Plantilla |
|-------|-----------|
| Inicio | `front-page.php` |
| La comunidad | `page-comunidad.php` |
| El linaje | `page-linaje.php` |
| Práctica y actividades | `page-practica.php` |
| Galería | `page-galeria.php` |
| Contribuir (donaciones) | `page-donaciones.php` |
| Contacto | `page-contacto.php` |
| Eventos especiales | `archive-event.php` o `page-eventos.php` (condicional) |
| Blog | `home.php` o `index.php` (según modelo) |
| Evento individual | `single-event.php` (si se activa) |
| 404 | `404.php` |
| Header global | `parts/header.php` (incluye navigation) |
| Footer global | `parts/footer.php` |

---

## Lo que el sitio NO tiene

- No buscador
- No área privada
- No registro de usuarios
- No sistema de cursos

Ayuda a acotar el alcance y evitar scope creep.

---

## Regla clave del sistema

La única lógica dinámica del sitio:

- **Ruta `/eventos/`:** existe siempre. Si hay al menos un evento vigente → listado (y ítem en menú). Si no hay → mensaje amable en la misma ruta (ítem en menú oculto o neutro).
- Todo lo demás es contenido estable. No hay jerarquías profundas ni flujos de marketing.

---

## Siguiente paso (orden sugerido)

1. Definir estructura del menú principal (`05-arquitectura-informacion-navegacion`).
2. Bajar a estructura real del theme (plantillas + naming en `12-theme-file-structure`).
3. Implementar lógica de visibilidad de «Eventos especiales» en WordPress (query por `event_status = vigente`).

---

**Versión:** 2.3
